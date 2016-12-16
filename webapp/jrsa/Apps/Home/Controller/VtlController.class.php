<?php

/* 
 * Jrsn Backup Management System
 * @Description   VTL功能 
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Controller;

class VtlController extends CommonController {
//    protected $lun_arr;
    function _initialize() {
        parent::_initialize();
    }

    public function index(){
        if(IS_POST){
            $sty = I('post.sty');
            if($sty == 1){
                //启动映射系统
                @exec("sudo /opt/vtl/bin/maprestart.sh",$result);
                $this->success(L('success'));
            }elseif($sty == 2){
                //重启VTL
                @exec("sudo /opt/vtl/bin/vtlrestart.sh",$result);
                $this->success(L('success'));
            }else{
                //停止
                @exec("sudo /opt/vtl/bin/vtlstop.sh",$result);
                $this->success(L('success'));
            }
        }
        @exec("sudo /opt/vtl/bin/vtlstatus.sh",$status);
        @exec("sudo /opt/vtl/scripts/scst_status",$scst);
        $vtl  = strstr($status[0], 'running') ? 1 : 0;
        $scst = strstr($scst[0], 'running') ? 1 : 0;
        $license = _getLicenseData();
        $data['vtlDedupe'] = $license['vtlDedupe'];
        $data['vtlReplicate'] = $license['vtlReplicate'];
        $data['vtlDisk'] = $license['vtlDisk'];
        $this->assign(array(
            'menuid'=>'vtl_index',
            'vtl'=>$vtl,
            'scst'=>$scst,
            'data'=>$data,
            'dirInfo'=>VTL_VERSION,
        ));
        $this->display();
    }

    /**
     * 存储池列表
     */
    public function pool(){
        @exec("sudo /opt/vtl/bin/spconfig -l",$result);
        $type = array('Name','Disks','WORM','Dedupe','Meta','Verify','Export','Replicate','Threshold');

        $this->assign(array(
            'menuid'=>'vtl_pool',
            'pool'=>  $this->_getInfo($result,$type),
        ));
        $this->display();
    }

    /*
     * 添加编辑存储池
     */
    public function addPool(){
        $license['vtlDedupe'] = _checkLicense('vtlDedupe', 'trueorfalse');
        $license['vtlReplicate'] = _checkLicense('vtlReplicate', 'trueorfalse');
        if(IS_POST){
            $post = I('post.');
            $type = array('Name','Disks','WORM','Dedupe','Meta','Verify','Export','Replicate','Threshold','oldname');
            foreach ($type as $key){
                if($post[$key]){
                    $data[$key] = $post[$key];
                }else{
                    $data[$key] = 0;
                }
            }
            if($license['vtlDedupe'] && $data['Dedupe']){
                $data['Dedupe'] = '-d ' . $data['Dedupe'];
            }else{
                $data['Dedupe'] = '';
            }
            if($license['vtlReplicate']){
                $data['Replicate'] = '-r ' . $data['Replicate'];
            }else{
                $data['Replicate'] = '';
            }
            if($data['oldname']){//修改
                @exec("sudo /opt/vtl/bin/spconfig -m -g {$data['oldname']} -n {$data['Name']} -v {$data['Verify']} -e {$data['Export']} -r {$data['Replicate']} -t {$data['Threshold']}",$result);
                $this->success(L('success'),U('Vtl/pool'));
            }else{//添加
                @exec("sudo /opt/vtl/bin/spconfig -a -g {$data['Name']} {$data['Dedupe']} -u {$data['Meta']} -v {$data['Verify']} -e {$data['Export']} {$data['Replicate']} -w {$data['WORM']} 2>&1",$result);
                $this->success($this->_floatOutput($result),U('Vtl/pool'));
            }
        }
        $name = I('get.name');
        if($name){
            $info = $this->_getPoolInfo($name);
            $this->assign("data",$info);
        }
        $this->assign(array(
            'menuid'=>'vtl_pool',
            'license'=>$license,
        ));
        $this->display();
    }

    public function delPool(){
        if(IS_POST){
            $name = I('post.name');
            if($name){
                @exec("sudo /opt/vtl/bin/spconfig -x -g {$name} 2>&1",$result);
                $this->success($this->_floatOutput($result));
            }
        }
    }

    public function bd(){
        @exec("sudo /opt/vtl/bin/bdconfig  -l -c",$result);
        @exec("sudo /opt/vtl/bin/bdconfig  -l -e",$bdconfig);
        $type = array('ID','Vendor','Model','SerialNumber','Name','Pool','Size','Used','Status');
        //pool
        @exec("sudo /opt/vtl/bin/spconfig -l",$pool);
        $typepool = array('Name','Disks','WORM','Dedupe','Meta','Verify','Export','Replicate','Threshold');
        $pool = $this->_getInfo($pool,$typepool);

        $data = $this->_getInfo($result,$type);
        $edata = $this->_getInfo($bdconfig,$type);
        $this->assign(array(
            'menuid'=>'vtl_bd',
            'data'=>$data,
            'pool'=>$pool,
            'bdata'=>$edata,
        ));
        $this->display();
    }

    /**
     * VTL
     * 加入池和移除池操作
     */
    public function bdAction(){
        if(IS_POST){
            $name = I('post.name');
            $path = I('post.path');
            if($name){//加入池操作
                //授权判断
                @exec("sudo /opt/vtl/bin/bdconfig  -l -c",$result);
                $type = array('ID','Vendor','Model','SerialNumber','Name','Pool','Size','Used','Status');
                $data = $this->_getInfo($result,$type);
                $disk = 0;
                foreach ($data as $item){
                    $disk = $disk + $item['Size'];
                }
                //无授权的时候只对容量做限制
                $licenseInfo = _getLicenseData();
                if(!$licenseInfo){
                    if($disk>400){
                        $this->error("未授权用户，最多只能创建 <font color='red'>400GB</font> 容量");
                    }
                }
                if(!_checkLicense('vtlDisk', $disk)){
                    $this->error(L('verify_license_than'));
                }
                $license['vtlDisk'] = _checkLicense('vtlDisk', 1);

                if($name=='Default'){
                    @exec("sudo /opt/vtl/bin/bdconfig -a -d {$path} 2>&1",$result);
                    $this->success($this->_floatOutput($result));
                }
                else{
                    @exec("sudo /opt/vtl/bin/bdconfig -a -d {$path} -g {$name} 2>&1",$result);
                    $this->success($this->_floatOutput($result));
                }
            }else{//从池中移除
                @exec("sudo /opt/vtl/bin/bdconfig -x -d {$path} 2>&1",$result);
                $this->success($this->_floatOutput($result));
            }
        }
    }
    /**
     * VTL
     */
    public function vt(){
        @exec("sudo /opt/vtl/bin/vtconfig -l",$result);
        $type = array('Name','DevType','Type');
        $data = $this->_getInfo($result,$type);
        $this->assign(array(
            'menuid'=>'vtl_vt',
            'data'=>$data,
        ));
        $this->display();
    }

    /*
     * 添加编辑存储池
     */
    public function addVtl(){
        if(IS_POST){
            $data = I('post.');
            @exec("sudo /opt/vtl/bin/vtconfig -a -v {$data['Name']} -t {$data['librarytype']} -s {$data['slots']} -i {$data['ieports']} -d {$data['drivetype0']} -c {$data['ndrives']} 2>&1",$result);
            $this->success($this->_floatOutput($result),U('Vtl/vt'));
        }
        $this->assign(array(
            'menuid'=>'vtl_vt',
        ));
        $this->display();
    }

    /**
     * VTL 信息
     * @param type $name
     */
    public function vtlInfo($name=""){
        if($name){
            @exec("sudo /opt/vtl/bin/vtconfig -l -v $name",$result);
            $data = $this->_getVtlInfo($result);
            $data['name'] = $name;
            $this->assign(array(
                'data'=>$data,
            ));
            $this->display();
        }
    }

    /**
     * 删除VTL
     */
    public function delVtl(){
        if(IS_POST){
            $name = I('post.name');
            if($name){
                @exec("sudo /opt/vtl/bin/vtconfig -x -v {$name} 2>&1",$result);
                $this->success($this->_floatOutput($result));
            }
        }
    }

    /**
     * 删除Vc
     */
    public function delVc(){
        if(IS_POST){
            $name = I('post.name');
            $label = I('post.label');
            if($name){
                @exec("sudo /opt/vtl/bin/vcconfig -x -v {$name} -p {$label} 2>&1",$result);
                $this->success($this->_floatOutput($result));
            }
        }
    }

    /**
     * 添加Vc
     */
    public function addVc(){
        if(IS_POST){
            $post = I('post.');
            @exec("sudo /opt/vtl/bin/vcconfig -a -v {$post['name']} -g {$post['pool']} -p {$post['label']} -t {$post['type']} -c {$post['number']} 2>&1",$result);
            $this->success($this->_floatOutput($result),U('Vtl/vtlInfo',array('name'=>$post['name'])));
        }
        $data['name'] = I('get.name');
        $data['vtype'] = urldecode(I('get.Vtype'));
        //@exec("sudo /opt/vtl/bin/bdconfig  -l -c",$result);
        //$type = array('ID','Vendor','Model','SerialNumber','Name','Pool','Size','Used','Status');
        @exec("sudo /opt/vtl/bin/spconfig -l",$result);
        $type = array('Name','Disks','WORM','Dedupe','Meta','Verify','Export','Replicate','Threshold');
        @exec("sudo /opt/vtl/bin/vcconfig  -h",$retype);
        $ttype = array('Type','Description');
        $pool = $this->_getInfo($result,$type);
        $type = $this->_getInfo($retype,$ttype);
        $this->assign(array(
            'menuid'=>'vtl_vt',
            'pool'=>$pool,
            'data'=>$data,
            'type'=>$type,
        ));
        $this->display();
    }

    public function vc(){
        $this->assign(array(
            'menuid'=>'vtl_vc',
        ));
        $this->display();
    }

    /**
     * 更新VC中WORM值
     */
    public function updWorm(){
        if(IS_POST){
            $data = I('post.');
            @exec("sudo /opt/vtl/bin/vcconfig -m -v {$data['name']} -p {$data['label']} -w {$data['worm']} 2>&1",$result);
            $this->success($this->_floatOutput($result));
        }
    }

    /**
     * 映射
     */
    public function fc(){
        @exec("sudo /opt/vtl/bin/fcconfig -l",$result);
        $type = array('WWPN','Rule','VTL');
        $data = $this->_getInfo($result,$type);
        $this->assign(array(
            'menuid'=>'vtl_fc',
            'data'=>$data,
        ));
        $this->display();
    }

    /**
     * 添加Fc
     */
    public function addFc(){
        if(IS_POST){
            $post = I('post.');
            if($post['wwpn'] == 1){
                $post['wwpn'] = '';
            }elseif($post['wwpn'] == 2){
                $post['wwpn'] = '-w' . $post['wwpn2'];
            }else{
                $post['wwpn'] = '-w' . $post['wwpn4'];
            }
            @exec("sudo /opt/vtl/bin/fcconfig -a -v {$post['vtl']} -r {$post['rule']} {$post['wwpn']} 2>&1",$result);
            $this->success($this->_floatOutput($result),U('Vtl/fc'));
        }
        @exec("sudo /opt/vtl/bin/vtconfig -l",$VtlResult);
        $VtlType = array('Name','DevType','Type');
        $vtl = $this->_getInfo($VtlResult,$VtlType);
        $this->assign(array(
            'menuid'=>'vtl_fc',
            'vtl'=>$vtl,
        ));
        $this->display();
    }

    /**
     * 删除Fc
     */
    public function delFc(){
        if(IS_POST){
            $vtl = I('post.vtl');
            $rule = I('post.rule');
            $wwpn = I('post.wwpn');
            if($wwpn == 'All'){
                $wwpn = '';
            }  else {
                $wwpn = '-w'.  I('post.wwpn');
            }
            @exec("sudo /opt/vtl/bin/fcconfig -x -v {$vtl} -r {$rule} {$wwpn} 2>&1",$result);
            $this->success($this->_floatOutput($result));
        }
    }

    /**
     * 获取所有的Pool信息
     */
    private function _getInfo($input,$source){
        $data= null;
        array_splice($input, 0,1);
        foreach ($input as $key=>$item){
            $tmp = array_values(array_filter(explode(" ", ltrim($item)),"hink_not_filter0"));
            $count = 0;
            $tc = count($tmp);
            $ts = count($source);
            foreach ($source as $k){
                if($ts == $count + 1){
                    for($i = $count;$i<$tc;$i++){
                        $data[$key][$k] .=$tmp[$i] . " ";
                    }
                }else{
                    $data[$key][$k] = $tmp[$count];
                    $count++;
                }
            }
        }
        return $data;
    }

    private function _getPoolInfo($name){
        @exec("sudo /opt/vtl/bin/spconfig -l",$result);
        $type = array('Name','Disks','WORM','Dedupe','Meta','Verify','Export','Replicate','Threshold');
        $pool = $this->_getInfo($result,$type);
        foreach ($pool as $key=>$value){
            if($value['Name'] == $name){
                return $pool[$key];
            }
        }
    }

    /**
     * 获取VTL 信息
     */
    private function _getVtlInfo($data){
        $result = "";
        $Drive ="";
        $VCartridge = "";
        $data = array_values(array_filter($data,"hink_not_filter0"));
        $vtl = array("Type","Name","Number","Slots","Ports");
        $result['vtl']['Type'] = $tmp[1];
        $vcount =0;
        $dcount = 0;
        $pcount = 0;
        foreach ($data as $k=>$v){
            if(strstr($v, 'VCartridge ')){
                $vcount++;
            }
            if($k<5){
                $pos = stripos($v, ':');
                $result['vtl'][$vtl[$k]] = ltrim(substr($v, $pos+1));
            }elseif($vcount<1){
                if($dcount>0){
                    $Drive[$dcount-1] = $v;
                }
                $dcount++;
            }else{
                if($pcount>0){
                    $VCartridge[$pcount-1] = $v;
                }
                $pcount++;
            }
        }
        /**
         * $VCartridge 解析
         */
        $diy= array('Vtype'=>'4,5,6');
        $VCartridgeItem = array('Pool','Label','Element','Address','Vtype','WORM','Size','Used','Status');
        $VCartridge = $this->_getInfoDiy($VCartridge,$VCartridgeItem,$diy);
        $diy= array('DType'=>'1,2,3');
        $DriveItem = array('Name','DType','Number','VCartridge');
        $Drive = $this->_getInfoDiy($Drive,$DriveItem,$diy);
        $result['VCartridge'] = $VCartridge;
        $result['Drive'] = $Drive;
        return $result;
    }

    /**
     * 获取所有的Pool信息
     */
    private function _getInfoDiy($input,$source,$diy){
        $data= null;
        array_splice($input, 0,1);
        foreach ($input as $key=>$item){
            $tmp = array_values(array_filter(explode(" ", ltrim($item)),"hink_not_filter0"));
            $count = 0;
            $tc = count($tmp);
            $ts = count($source);
            foreach ($source as $k){
                if($diy){
                    foreach ($diy as $k1=>$v1){
                        if($k==$k1){
                            $expo = explode(',',$v1);
                            $ntmp = "";
                            foreach ($expo as $v2){
                                $ntmp .= $tmp[$v2] . " ";
                                $count = $v2;
                            }
                            $data[$key][$k] = $ntmp;
                            $count++;
                        }else{
                            $data[$key][$k] = $tmp[$count];
                            $count++;
                        }
                    }
                }else{
                    $data[$key][$k] = $tmp[$count];
                    $count++;
                }
            }
        }
        return $data;
    }

    private function _floatOutput($str){
        $result = '';
        if($str){
            foreach ($str as $key){
                $result .= $key.'</br>';
            }
        }
        return $result;
    }

    /**
     * license 相关
     */

    /**
     * 判断授权是否超出限制
     * @param type $itme
     * @param type $value
     */
    private function checkLicense($itme,$value){
        if(LICENSE == -1 || LICENSE == 0 || LICENSE == 2 || LICENSE == 3){
            return false;
        }elseif(LICENSE == 1){
            return true;
        }else{
            $licenses = $this->license->find();
            $data = $this->getLicenseData();
            if($data[$itme]<0){
                return true;
            }
            if(($data[$itme] + 1)>=$value){
                return true;
            }else{
                return false;
            }
        }
    }

    /**
     * 解析License
     * @param type $licenses
     * @return type
     */
    private function getLicenseData(){
        $licenses = $this->license->find();
        return json_decode(Encrypt::authcode($licenses['license'],'DECODE',md5('jrsa')),true);
    }

    /**
     * 端口管理
     * @return [type] [description]
     */
    public function target(){
        $target_model = M('target');
        $data_list    = $target_model->select();
        //还要一个组装一个状态
        //状态
        foreach($data_list as $key=>$val){
            $wwpn=$val['wwpn'];
            exec("/opt/vtl/scripts/target_status $wwpn",$status);
            $data_list[$key]['status']=$status[0];
        }
        $this->assign(array(
            'menuid'=>'vtl_target',
            'data'=>  $data_list
        ));
        $this->display();
    }

    /**
     * initiator管理
     * @return [type] [description]
     */
    public function initiator(){
        $initiator_model = M('initiator');
        $data_list    = $initiator_model->select();
        $this->assign(array(
            'menuid'=>'vtl_initiator',
            'data'=>  $data_list
        ));
        $this->display();
    }

    public function delinitiator(){
        $id = I('post.id');
        $initiator_model = M('initiator');
        $initiator_model->where('id='.$id)->delete();
        $this->success(L('success'),U('Vtl/initiator'));
    }

    public function addinitiator(){
        $initiator_model = M('initiator');
        if(IS_POST){
            $data = I('post.');
            unset($data['values_data']);
            unset($data['valuess']);
            unset($data['hink_tools']);
            if($data['id']){
                $result = $initiator_model->save($data);
            }else{
                $initiator_data = $initiator_model->where(array('values'=>$data['values']))->find();
                if($initiator_data){
                    $this->error("该initiator已被添加");
                }
                $result = $initiator_model->add($data);
            }
            $this->success(L('success'),U('Vtl/initiator'));
            if($result){
                $this->success(L('success'),U('Vtl/initiator'));
            }else{
                $this->error(L('error'),U('Vtl/initiator'));
            }
        }else{
            $id = I('get.id');
            $data    = $initiator_model->where('id='.$id)->find();
            @exec("sudo /opt/vtl/scripts/get_initiator_wwpn",$result);
            $values_data = '';
            foreach ($result as $key => $value) {
                $values_data .= "{k:'{$value}',v:'{$value}'}|";
            }
            $values_data = rtrim($values_data,'|');
            $this->assign(array(
                'values_data'=>$values_data,
                'menuid'=>'vtl_initiator',
                'data'=>  $data
            ));
            $this->display();
        }
    }

    /**
     * 端口管理 启用禁用
     * @return [type] [description]
     */
    public function updtarget(){
        if(IS_POST){
            $data = I('post.');
            $target_model = M('target');
            $_data = $target_model->where(array('id'=>$data['id']))->find();
            if($_data){
                if($data['type'] == 'Name'){//更新name
                    $_datas['Name'] = $data['name'];
                    $result = $target_model->where(array('id'=>$data['id']))->save($_datas);
                }elseif($data['type'] == 'refresh'){//刷新
                    @exec("sudo /opt/vtl/scripts/target_manager {$_data['wwpn']} 3",$result);
                    $result = "1";
                }elseif($data['type'] == 'infos'){//刷新
                    @exec("sudo /opt/vtl/scripts/target_manager {$_data['wwpn']} 4",$result);

                    $result = implode('<br/>',$result);

                    $this->success($result);
                }else{
                    if($_data['flag'] == 1){
                        $_datas['flag'] = 2;
                    }else{
                        $_datas['flag'] = 1;
                    }
                    $result = $target_model->where(array('id'=>$data['id']))->save($_datas);
                    @exec("sudo /opt/vtl/scripts/target_manager {$_data['wwpn']} {$_datas['flag']}",$results);
                }
                if($result){
                    $this->success('操作成功.');
                }else{
                    $this->error('操作失败.');
                }
            }else{
                $this->error('操作失败.');
            }
        }
    }

    /**
     * 主机组管理
     * @return [type] [description]
     */
    public function hostgroup(){
        $hostgroup_model = M('hostgroup');
        $data_list    = $hostgroup_model->select();
        $this->assign(array(
            'menuid'=>'vtl_hostgroup',
            'initiator'=>$initiator,
            'data'=>  $data_list
        ));
        $this->display();
    }

    public function delhostgroup(){
        $id = I('post.id');
        $name = I('post.name');
        if($id){
            $hostgroup_model = M('hostgroup');
            $hostgroup_model->where(array('id'=>$id))->delete();
            @exec("sudo /opt/vtl/scripts/del_hostgroup {$name}",$vtl);
            $this->success(L('success'),U('Vtl/hostgroup'));
        }else{
            $this->error("操作失败.");
        }
    }

    /**
     * 添加映射记录
     * 修改映射记录
     */
    public function addHostGroup(){
        $target_model    = M('target');
        $group           = M('vtlgroup');
        $initiator_model = M('initiator');
        $hostgroup_model = M('hostgroup');
        //提交数据
        if(IS_POST){
            $data = I('post.');
//            echo '<pre/>';
            //处理VTL,把lun中id作为键值,name作为值拼成数组
            $vtl=$data['lun'];
            foreach($vtl as $val){
                $arr=explode(':', $val);
                $vtl_lun[$arr[0]]=$arr[1];
                $vtl_id[]=$arr[0];
            }
            //处理拼接好的ID数组,按第一位顺序分成二维数组组
            foreach($vtl_id as $val){
                $num1=substr($val,0,1);
                $vtl_id_num[$num1][]=$val;

            }
            //判断该数数组键值下有木有以键值为第一位,1为第二位,两位数,就是选了机械臂的ID
            //如果有则可以操作,如果没有就删除这些驱动的ID不保存
            foreach($vtl_id_num as $key=>$val){
                    //需要的机械臂ID
                $jiexiebi_id=$key.'1';
                //如果没有就把这些ID从
                if (!in_array($jiexiebi_id,$val)) {
                        foreach($val as $v){
                            unset($vtl_lun[$v]);
                        }
                }
            }
            //拼接数据库需要的 lun
            $lun='';
            foreach($vtl_lun as $val){
                $lun.=$val.',';
            }
            //去掉最后一个字符串
            $data['lun']=substr($lun, 0, -1);
//            dump($data);
            //组装initiator
            $initiator='';
            foreach($data['initiator'] as $val){
                $initiator.=$val.',';
            }
            //去掉最后一个字符串
            $data['initiator']=substr($initiator, 0, -1);
//            dump($data);
            unset($data['initiator_data']);
            unset($data['initiators']);
//            dump($data);
//            exit;
            //判断有没有id,有就是修改没有就是添加
            if($data['id']){
                $find = $hostgroup_model->where(array('id'=>$data['id']))->find();
                if($find['initiator'] == $data['initiator'] && $find['target'] == $data['target']){
                    $result = $hostgroup_model->where(array('id'=>$data['id']))->save($data);

                }else{
                    $_tmp = explode(',', $data['initiator']);
                    foreach ($_tmp as $k => $v) {
                        $find = $hostgroup_model->where(array('id'=>array('neq',$data['id']),'target'=>$data['target'],'initiator'=>array('like',"%{$v}%")))->find();
                        if($find){
                            $this->error("操作失败，该端口已存在相同initiator.");
                        }
                    }

                    $result = $hostgroup_model->where(array('id'=>$data['id']))->save($data);
                }
                //@exec("sudo /opt/vtl/scripts/add_hostgroup {$name}",$vtl);
            }else{
                //添加
                $find = $hostgroup_model->where(array('name'=>$data['name']))->find();
                if($find){
                    $this->error("操作失败，名称重复.");
                }
                $find = $hostgroup_model->where(array('target'=>$data['target'],'initiator'=>array('like',"%{$data['initiator']}%")))->find();
                if($find){
                    $this->error("操作失败，该端口已存在相同initiator.");
                }
                $result = $hostgroup_model->add($data);
                //@exec("sudo /opt/vtl/scripts/add_hostgroup {$name}",$vtl);
            }

            if($result !== false){
                $this->success(L('success'),U('Vtl/hostgroup'));
            }else{
                $this->error(L('error'));
            }
        }else{
            $target_list     = $target_model->select();
            $initiator_list  = $initiator_model->select();
            $data = $hostgroup_model->where(array('id'=>I('get.id')))->find();
            //给个标志看是否是修改
            if($data != false){
                $row=1;
            }
            //$data中的lun处理成数组
            $data['lun']=explode(',',$data['lun']);
            //$data中额initiator处理成数组
            $data['initiator']=json_encode(explode(',',$data['initiator']));
            $this->assign(array(
                'menuid'=>'vtl_hostgroup',
                'target_list'=>$target_list,
                'initiator_list'=>$initiator_list,
                'data'=>$data,
                'row'=>$row,
                'lun'=>json_encode($data['lun']),
                //ztree数据
                'lunjson'=>$this->ajaxLunList(),

            ));
//            dump($data);
//            exit;
            $this->display();
        }
    }

    /**
     * Vtl 模块
     * @return [type] [description]
     */
    public function group(){
        $types = I('get.types');
        $group = M('vtlgroup');
        if(IS_POST){
            $data = I('post.');
            if($data['action'] == 'add'){//添加
                $info = $group->where(array('name'=>$data['name'],'types'=>$types))->find();
                if($info){
                    $this->error(L('error'));
                }
                $group->data(array('name'=>$data['name'],'types'=>$types))->add();
            }elseif($data['action'] == 'del'){//删除
                $group->where(array('name'=>$data['name'],'types'=>$types))->delete();
            }
            $this->success(L('success'));
        }
        $grop = $group->where(array('types'=>$types))->select();
        $this->assign(array(
            'group'=>$grop,
            'types'=>$types,
        ));
        $this->display();
    }

    /**
     * ajax grop
     * @return [type] [description]
     */
    public function ajaxLunList(){

        $lid = I('post.lun');
        $lid = explode(',', $lid);
        $grouplun = array();
        @exec("sudo /opt/vtl/scripts/get_vtl",$vtl);
        if($vtl){
//            echo '<pre/>';
//            var_dump($vtl);
//            echo '<hr/>';
            $json = "[";
            $i = 1;
            foreach ($vtl as $k => $v) {
                $changer = $tape = array();
                @exec("sudo /opt/vtl/scripts/get_vtl_changer_id ". $v,$changer);
                @exec("sudo /opt/vtl/scripts/get_vtl_tape_id ". $v,$tape);
//                var_dump($changer);
//                echo '<hr/>';
//                var_dump($tape);
//                echo '<hr/>';
//                exit;
                $json .="{ id:111{$k}, pId:0, name:'$v',nocheck:true},";
//                $json .="{ id:1{$k}00, pId:111{$k}, name:'机械臂',nocheck:true, open:true},";
//                $json .="{ id:2{$k}00, pId:111{$k}, name:'驱动器(请先选择机械臂)',nocheck:true, open:true},";

                $j = 0;
                foreach ($changer as $_k => $_v) {
                    $j++;
                    $check = '';
                    $_tmp = explode(" ",$_v);
                    if(in_array($_tmp[1], $lid)){
                        $check = ',checked:true';
                    }
                    $json .="{ id:$i$j, pId:111{$k},open:true, name:'{$_tmp[1]}' $check ,},";
                }

                $l=$j;
                foreach ($tape as $_k => $_v) {
                    $l++;

                    $check = '';
                    $_tmp = explode(" ",$_v);
                    if(in_array($_tmp[1], $lid)){
                        $check = ',checked:true';
                    }
                    $json .="{ id:$i$l, pId:$i$j, name:'{$_tmp[1]}' $check},";
                    $this->lun_arr[$j]=$i.$l;
                }
                $i++;
            }
            $json = rtrim($json,",");
            $json .="]";

//            echo $json;
            return $json;
        }else{
            return 'error';
        }
    }

    public function exportLog(){
        @exec("sudo /opt/vtl/scripts/vtl_log");
        @exec("sudo tar -zcPf /var/www/html/webapp/vtllog.tar /opt/vtl/log/",$info);
        @exec("sudo rm -rf /opt/vtl/log/*");
        $file = "/var/www/html/webapp/vtllog.tar";
        if(is_file($file)){
            header("Content-Type:application/force-download");
            header("Content-Disposition:attachment;filename=" . basename($file));
            header("Accept-Length:" . filesize($file));
            readfile($file);
            exit;
        }else{
            echo "获取日志失败";
        }
    }

    public function PoolInfo()
    {
        $pool_name = $_GET['name'];
       
//        dump($arr);
//        dump($ret);
//        exit;
        $fp = fopen("/opt/vtl/scripts/pool_info/$pool_name", "r");

//        $fp = file_get_contents("/opt/vtl/scripts/pool_info");
//        $data = trim(fgets($fp, 255));
        $pool_info=array();
        while(!feof($fp))
//
        {
//
            $data=fgets($fp,200);
            $data =  explode(',', $data);
            $data[0] = str_replace('\'',"",$data[0]);
            $data[1] = str_replace('\'',"",$data[1]);
            $pool_info[$data[0]]= $data[1];
        }
        fclose($fp);
        array_pop($pool_info);
//        dump($pool_info);
        $this->assign('pool_info',$pool_info);
        $this->assign('pool_name',$pool_name);
        $this->display();
    }

    public function test()
    {
        $val='Default';
        system("/usr/bin/python /opt/vtl/scripts/get_pool_detail $val",$result);
        dump($result);

    }
}

