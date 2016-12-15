<?php

/*
 * Jrsn Backup Management System
 * @Description   oracle数据库复制
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Controller;

class DbcopyController extends CommonController {
    
    private $databaseInfo; //数据库信息
    private $oracleuser; 
    private $dbmaster;
    private $dbslave;
    
    function _initialize() {
        parent::_initialize();
        $this->databaseInfo = M('oracleinfo');
        $this->oracleuser = M('oracleuser');
        if(ACTION_NAME !== 'info'){
            //检查主从数据库是否有记录
            $this->dbmaster = $this->databaseInfo->where('master = 1')->find();
            $this->dbslave = $this->databaseInfo->where('master = 0')->find();
            if(!$this->dbmaster || !$this->dbslave){
                $this->redirect('Dbcopy/info');
            }
        }
    }
    
    /**
     * 默认欢迎页面
     */
    public function index(){
        $dbmaster = $this->dbmaster;
        $dbslave = $this->dbslave;
        $status['mconn'] = oracleConnect($dbmaster);
        $status['sconn'] = oracleConnect($dbslave);
        if( $status['mconn'] == 1){
            //echo "oci_client_version():" . oci_client_version();
            $status['minfo'] = oracleSelect($dbmaster, 'select * from v$version');
            $status['mlog'] = oracleQuery($dbmaster, 'select LOG_MODE from v$database');
        }
        if( $status['sconn'] == 1){
            $status['sinfo'] = oracleSelect($dbslave, 'select * from v$version');
            $status['slog'] = oracleQuery($dbslave, 'select LOG_MODE from v$database');
        }
        $this->assign(array(
            'menuid'=>'index',
            'master'=>$dbmaster,
            'slave'=>$dbslave,
            'status'=>$status,
        ));
        $this->display();
    }
    
    /**
     * oracle 链接信息
     */
    public function info(){
        if(IS_POST){
            $data = I('post.');
            if($data['master']){
                $ndata = array();
                foreach ($data as $key=>$value){
                    if($key!='master'){
                        $ndata[ltrim($key,'m')] = $value;
                    }else{
                        $ndata[$key] = $value;
                    }
                }
                $ndata['count'] = array('exp','count+1');
                $tmp = $this->databaseInfo->where('master = 1')->find();
                if($tmp){
                    $result = $this->databaseInfo->where('master = 1')->save($ndata); 
                }  else {
                    $result = $this->databaseInfo->data($ndata)->add();
                }
            }else{
                $data['count'] = array('exp','count+1');
                $tmp = $this->databaseInfo->where('master = 0')->find();
                if($tmp){
                    $result = $this->databaseInfo->where('master = 0')->save($data); 
                }  else {
                    $result = $this->databaseInfo->data($data)->add();
                }
            }
            if($result){
                $this->success("操作成功");
            }else{
                $this->error("操作失败");
            }
        }
        $master = $this->databaseInfo->where('master = 1')->find();
        $slave = $this->databaseInfo->where('master = 0')->find();
        $this->assign(array(
            'menuid'=>'info',
            'master'=>$master,
            'slave'=>$slave,
        ));
        $this->display();
    }
    
    /**
     * 更改参数
     */
    public function parameter(){
        $dbmaster = $this->dbmaster;
        $dbslave = $this->dbslave;
        $status['mconn'] = oracleConnect($dbmaster);
        $status['sconn'] = oracleConnect($dbslave);
        $master['global_names'] = $this->_getOracleValue($dbmaster,'global_names');
        $master['open_links'] = $this->_getOracleValue($dbmaster,'open_links');
        $master['parallel_max_servers'] = $this->_getOracleValue($dbmaster,'parallel_max_servers');
        $master['processes'] = $this->_getOracleValue($dbmaster,'processes');
        $master['sessions'] = $this->_getOracleValue($dbmaster,'sessions');
        $master['timed_statistics'] = $this->_getOracleValue($dbmaster,'timed_statistics');
        //从数据库
        $slave['global_names'] = $this->_getOracleValue($dbslave,'global_names');
        $slave['open_links'] = $this->_getOracleValue($dbslave,'open_links');
        $slave['parallel_max_servers'] = $this->_getOracleValue($dbslave,'parallel_max_servers');
        $slave['processes'] = $this->_getOracleValue($dbslave,'processes');
        $slave['sessions'] = $this->_getOracleValue($dbslave,'sessions');
        $slave['timed_statistics'] = $this->_getOracleValue($dbslave,'timed_statistics');
        $this->assign(array(
            'menuid'=>'parameter',
            'status'=>$status,
            'master'=>$master,
            'slave'=>$slave,
        ));
        $this->display();
    }
    
    /**
     * 创建用户
     */
    public function user(){
        $dbmaster = $this->dbmaster;
        $dbslave = $this->dbslave;
        
        $master = $this->oracleuser->where('master = 1')->find();
        $slave = $this->oracleuser->where('master = 0')->find();
        if($master){
            $status['minfo'] = $master;
            $status['mtablespace'] = oracleQuery($dbmaster, 'select FILE_NAME from dba_data_files where TABLESPACE_NAME =  \''.$master['tablespace'].'\'');
        }
        if($slave){
            $status['sinfo'] = $slave;
            $status['stablespace'] = oracleQuery($dbslave, 'select FILE_NAME from dba_data_files where TABLESPACE_NAME =  \''.$slave['tablespace'].'\'');
        }
        //主数据库所有表空间
        $dbaDataFies['master'] = oracleSelect($dbmaster, 'select FILE_NAME,TABLESPACE_NAME from dba_data_files');
        //主数据库所有表空间
        $dbaDataFies['slave'] = oracleQuery($dbslave, 'select FILE_NAME,TABLESPACE_NAME from dba_data_files');
        if(IS_POST){
            $data = I('post.');
            if($data['master']){
                $ndata = array();
                foreach ($data as $key=>$value){
                    if($key!='master'){
                        $ndata[ltrim($key,'m')] = $value;
                    }else{
                        $ndata[$key] = $value;
                    }
                }
                $musername = oracleQuery($dbmaster, 'select username from dba_users where username = \''.$ndata['username'].'\'');
                if(!$musername){
                    $result = $this->oracleuser->data($ndata)->add();
                    oracleQuery($dbmaster, 'create user '.$ndata['username'].' identified by '.$ndata['userpwd'].' default tablespace '.$ndata['tablespace'].' quota unlimited on '.$ndata['tablespace'],0);
                    oracleQuery($dbmaster, 'grant connect,resource,dba,aq_administrator_role to '.$ndata['username'],0);
                    //创建用户缓存路径
                    $ndata['ip'] =  $dbmaster['dbhost'];
                    $ndata['dbname'] =  $dbmaster['dbname'];
                    $this->createDirectory($ndata,'create directory streams_dp_dir as \''.$ndata['cache'].'\'');
                    $this->success("操作成功");
                }else{
                    $this->error("用户已经存在");
                }
            }else{
                $susername = oracleQuery($dbslave, 'select username from dba_users where username = \''.$data['username'].'\'');
                if(!$susername){
                    $result = $this->oracleuser->data($data)->add();
                    oracleQuery($dbslave, 'create user '.$data['username'].' identified by '.$data['userpwd'].' default tablespace '.$data['tablespace'].' quota unlimited on '.$data['tablespace'],0);
                    oracleQuery($dbslave, 'grant connect,resource,dba,aq_administrator_role to '.$ndata['username'],0);
                    $data['ip'] =  $dbslave['dbhost'];
                    $data['dbname'] =  $dbslave['dbname'];
                    $this->createDirectory($data,'create directory streams_dp_dir as \''.$data['cache'].'\'');
                    $this->success("操作成功");
                }else{
                    $this->error("用户已经存在");
                }
            }
        }
        $this->assign(array(
            'menuid'=>'user',
            'status'=>$status,
            'master'=>$master,
            'dbaDataFies'=>$dbaDataFies,
        ));
        $this->display();
    }
    
    private function createDirectory($dbinfo,$sql){
        $conn = oci_connect($dbinfo['username'], $dbinfo['userpwd'], "{$dbinfo['ip']}/{$dbinfo['dbname']}");
        if (!$conn) { 
            $e = oci_error(); 
            print htmlentities($e['message']); 
            exit; 
        }
       $stid = oci_parse($conn, $sql); 
        if (!$stid) {
            $e = oci_error($conn); 
            if($output){
                print htmlentities($e['message']);
                exit; 
            }
        }
        $r = oci_execute($stid, OCI_DEFAULT); 
        if(!$r) {
            $e = oci_error($stid);
            if($output){
                print htmlentities($e['message']);
                exit; 
            }
        }
        $row = oci_fetch_all($stid, $results);
        $result = array();
        if ($row > 0) {
            $result = $results;
        }
        oci_free_statement($stid);
        oci_close($conn);
        return $result;
    }
    /**
     * 修改值
     */
    public function updValues(){
        if(IS_POST){
            $dbmaster = $this->dbmaster;
            $dbslave = $this->dbslave;
            $name = I('post.name');
            $value = I('post.value');
            if(strstr($name, "m-")){
                $name = str_replace("m-", "", $name);
                $scope = 'both';
                 if($name=='open_links' || $name=='processes'||$name=='sessions'){
                    $scope = 'spfile';
                }
                $result = $this->_updateOracleValue($dbslave,$name,$value,$scope);
                oracleQuery($dbslave, 'shutdown immediate',0);
                oracleQuery($dbslave, 'startup',0);
                $this->success(L('success'));
            }else{
                //主数据库
                $scope = 'both';
                 if($name=='open_links' || $name=='processes'||$name=='sessions'){
                    $scope = 'spfile';
                }
                $result = $this->_updateOracleValue($dbmaster,$name,$value,$scope);
                oracleQuery($dbmaster, 'shutdown immediate',0);
                oracleQuery($dbmaster, 'startup',0);
                $this->success(L('success'));
            }
        }
    }
    
    /**
     * 修改值
     * @param type $dbconn
     * @param type $name
     */
    private function _updateOracleValue($dbconn,$name,$value,$scope="both"){
        $conn = oracleQuery($dbconn, 'alter system set '.$name.'='.$value.' scope='.$scope);
        return $conn;
    }
    
    /**
     * 根据名称获取对应值
     * @param type $dbconn
     * @param type $name
     */
    private function _getOracleValue($dbconn,$name){
        $conn = oracleQuery($dbconn, 'select value from v$parameter where name=\''.$name.'\'');
        return $conn['VALUE'];
    }
    
    public function allParameter($id=0){
        $dbmaster = $this->dbmaster;
        $dbslave = $this->dbslave;
        if($id){
            //主数据
            $info = oracleSelect($dbmaster, 'select name,value from v$parameter order by name');
            $result = array();
            $i = 0;
            foreach ($info['NAME'] as $itme){
                $result[$i]['name'] = $itme;
                $i++;
            }
            $i = 0;
            foreach ($info['VALUE'] as $itme){
                $result[$i]['value'] = $itme;
                $i++;
            }
        }else{
             $info = oracleSelect($dbslave, 'select name,value from v$parameter order by name');
             $result = array();
            $i = 0;
            foreach ($info['NAME'] as $itme){
                $result[$i]['name'] = $itme;
                $i++;
            }
            $i = 0;
            foreach ($info['VALUE'] as $itme){
                $result[$i]['value'] = $itme;
                $i++;
            }
        }
        $this->assign(array(
            'info'=>$result,
        ));
        $this->display();
    }
    
    /**
     * 流复制 监控
     */
    public function status(){
        $dbmaster = $this->dbmaster;
        $dbslave = $this->dbslave;
        //第一步 查询捕获进程 根据 RULE_SET_NAME 获取对应的用户
        //第二步 根据dba_capture中的 QUEUE_NAME 获取对应的 dba_propagation 视图中的 PROPAGATION_NAME 状态
        //第三步 根据 dba_propagation视图 中的  DESTINATION_QUEUE_NAME 查找 dba_apply 视图 中的 QUEUE_NAME 的状态
        $dbinfo = array();
        $tmp = 0;
        $capture = oracleSelect($dbmaster, 'select RULE_SET_NAME from dba_capture');
        foreach ($capture['RULE_SET_NAME'] as $key=>$item){
            $rulename = oracleSelect($dbmaster, "select RULE_NAME from dba_rule_set_rules where RULE_SET_NAME= '".$item."'");
            //简单处理一下，规则名称默认为用户名+3位数字，默认去掉后面三位数字，若出现问题则查询dba_rules视图中的值
            //$dbinfo[$tmp]['username'] = substr_replace($rulename['RULE_NAME'][0],'',-3); 
            $dbinfo[$tmp]['username'] = preg_replace('/\d/','',$rulename['RULE_NAME'][0]);
            $capturename = oracleSelect($dbmaster, "select * from dba_capture where RULE_SET_NAME= '".$item."'");
            $dbinfo[$tmp]['capturename'] =  $capturename['CAPTURE_NAME'][0];
            $dbinfo[$tmp]['capturestatus'] =  $capturename['STATUS'][0];
            $propagationname = oracleSelect($dbmaster, "select * from dba_propagation where SOURCE_QUEUE_NAME= '".$capturename['QUEUE_NAME'][0]."'");//传输
            $dbinfo[$tmp]['propagationname'] =  $propagationname['PROPAGATION_NAME'][0];
            $dbinfo[$tmp]['propagationstatus'] =  $propagationname['STATUS'][0];
            $applynname = oracleSelect($dbslave, "select * from dba_apply where QUEUE_NAME= '".$propagationname['DESTINATION_QUEUE_NAME'][0]."'");//传输
            $dbinfo[$tmp]['applyname'] =  $applynname['APPLY_NAME'][0];
            $dbinfo[$tmp]['applystatus'] =  $applynname['STATUS'][0];
            $tmp++;
        }
        $this->assign(array(
            'menuid'=>'status',
            'dbinfo'=>$dbinfo,
        ));
        $this->display();
        
//        if(IS_POST){
//            $style = I('post.sty');
//            $capture  = oracleSelect($dbmaster, 'select capture_name from dba_capture');
//            if($capture){
//                $dbinfo['ip'] =  $dbmaster['dbhost'];
//                $dbinfo['username'] = 'repadmin';
//                $dbinfo['userpwd'] = 'repadmin';
//                $dbinfo['dbname'] = $dbmaster['dbname'];
//                if($style==='1'){
//                    //启动流复制
//                    $sql = "begin
//                                 dbms_capture_adm.start_capture(
//                                 capture_name => '".$capture['CAPTURE_NAME'][0]."'
//                                 );
//                                 end;
//                            ";
//                    $result = $this->createDirectory($dbinfo, $sql);
//                    $this->success(L('success'));
//                }else{
//                    $sql = "begin 
//                                 dbms_capture_adm.stop_capture(
//                                 capture_name => '".$capture['CAPTURE_NAME'][0]."'
//                                 );
//                                 end;
//                            ";
//                    $result = $this->createDirectory($dbinfo, $sql);
//                    $this->success(L('success'));
//                }
//            }
//        }
//        $capture  = oracleSelect($dbmaster, 'select CAPTURE_NAME,STATUS from dba_capture');
//        $propagation  = oracleSelect($dbmaster, 'select PROPAGATION_NAME,STATUS from dba_propagation');
//        $apply  = oracleSelect($dbslave, 'select APPLY_NAME,STATUS from dba_apply');
//        $this->assign(array(
//            'menuid'=>'status',
//            'capture'=>$capture,
//            'capturename'=>$capture_name['CAPTURE_NAME'],
//            'propagation'=>$propagation,
//            'apply'=>$apply,
//        ));
//        $this->display();
    }
    
     /**
     * 修改捕获进程
     */
    public function updStatus(){
        if(IS_POST){
            $dbmaster = $this->dbmaster;
            $dbslave = $this->dbslave;
            $style = I('post.sty');
            $name = I('post.name');
            $capture  = oracleSelect($dbmaster, 'select capture_name from dba_capture');
            if($capture){
                $dbinfo['ip'] =  $dbmaster['dbhost'];
                $dbinfo['username'] = 'repadmin';
                $dbinfo['userpwd'] = 'repadmin';
                $dbinfo['dbname'] = $dbmaster['dbname'];
                //全部操作
                if($name==='all'){
                    if($style==='1'){
                        foreach ($capture['CAPTURE_NAME'] as $item){
                            //启动流复制
                            $sql = "begin
                                         dbms_capture_adm.start_capture(
                                         capture_name => '".$item."'
                                         );
                                         end;
                                    ";
                            $result = $this->createDirectory($dbinfo, $sql);
                        }
                        $this->success(L('success'));
                    }else{
                        foreach ($capture['CAPTURE_NAME'] as $item){
                            $sql = "begin 
                                     dbms_capture_adm.stop_capture(
                                     capture_name => '".$item."'
                                     );
                                     end;
                                ";
                            $result = $this->createDirectory($dbinfo, $sql);
                        }
                        $this->success(L('success'));
                    }
                }else{
                    if($style==='1'){
                        //启动流复制
                        $sql = "begin
                                     dbms_capture_adm.start_capture(
                                     capture_name => '".$name."'
                                     );
                                     end;
                                ";
                        $result = $this->createDirectory($dbinfo, $sql);
                        $this->success(L('success'));
                    }else{
                        $sql = "begin 
                                     dbms_capture_adm.stop_capture(
                                     capture_name => '".$name."'
                                     );
                                     end;
                                ";
                        $result = $this->createDirectory($dbinfo, $sql);
                        $this->success(L('success'));
                    }
                }
            }
        }
    }
    
    /**
     * 预警联系人管理
     */
    public function contact(){
        $contact = M('contact');
        $appset = M('appsetting');
        $send['msg'] = $appset->where('appid = 2 and `key` = "sendmsg"')->find();
        $send['mail'] = $appset->where('appid = 2 and `key` = "sendmail"')->find();
        $send['crontab'] = $appset->where('appid = 2 and `key` = "crontab"')->find();
        $data['tel'] = $contact->where('appid = 2 and style = 0')->select();
        $data['mail'] = $contact->where('appid = 2 and style = 1')->select();
        if(IS_POST){
            $data = I('post.');
            if($data['style']){
                //邮件
                $count = $contact->where('appid = 2 and style = 1')->count();
                if($count>4){
                    $this->error("添加超过限制，如需修改，请删除后重新添加！");
                }
                $data['content'] = $data['m-content'];
                unset($data['m-content']);
                $resutl = $contact->data($data)->add();
            }else{
                //短信
                $count = $contact->where('appid = 2 and style = 0')->count();
                if($count>4){
                    $this->error("添加超过限制，如需修改，请删除后重新添加！");
                }
                $resutl = $contact->data($data)->add();
            }
            if($resutl){
                $this->success(L('success'));
            }else{
                $this->success(L('error'));
            }
        }
        $this->assign(array(
            "menuid"=>"contact",
            'data'=>$data,
            'send'=>$send,
        ));
        $this->display();
    }
    
    /**
     * 删除联系信息
     */
    public function delContact(){
        if(IS_POST){
            $data = I('post.');
            if($data['id']){
               M('contact')->where(array('id'=>$data['id']))->delete();
               $this->success(L('success'));
            }
        }
    }
    
    /**
     * 备份用户  修改版本1
     */
    public function users(){
        $dbmaster = $this->dbmaster;
        $user = oracleSelect($dbmaster, 'select username from dba_users');
        $userjson = array();
        $usertablejson = array();
        $i = 0;
        $c= count($user['USERNAME']);
        foreach ($user['USERNAME'] as $item){
            $userjson[$i]['id'] = $i + 1;
            $userjson[$i]['pId'] = 0;
            $userjson[$i]['name'] = $item;
            $userjson[$i]['open'] = false;
            $usertablejson[$i]['id'] = $i + 1;
            $usertablejson[$i]['pId'] = 0;
            $usertablejson[$i]['name'] = $item;
            $usertablejson[$i]['open'] = false;
            $usertable = oracleSelect($dbmaster, 'select object_name From dba_objects where owner=\''.$item.'\' and object_type=\'TABLE\'');
            foreach ($usertable['OBJECT_NAME'] as $value){
                $usertablejson[$c]['id'] = $c + 1;
                $usertablejson[$c]['pId'] = $i;
                $usertablejson[$c]['name'] = $value;
                $usertablejson[$c]['open'] = false;
                $c++;
            }
            $i++;
        }
        ksort($usertablejson);
        $this->assign(array(
            "menuid"=>"users",
            "userjson"=>  json_encode($userjson),
            "usertablejson"=>  json_encode($usertablejson),
        ));
        $this->display();
    }
    
    public function logs(){
        $dbslave = $this->dbslave;
        $log = oracleSelect($dbslave, 'select * from dba_apply_error');
        $logs = array();
        foreach ($log as $key=>$value){
            foreach ($value as $k=>$v){
                $logs[$k][$key] = $v;
            }
        }
        $this->assign(array(
            "menuid"=>"logs",
            "logs"=>$logs,
        ));
        $this->display();
    }
}
