<?php

/* 
 * Jrsn Backup Management System
 * @Description   服务器相关 
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Controller;

class ServerController extends CommonController {
    
    function _initialize() {
        parent::_initialize();
    }
    
    /**
     * 默认
     */
    public function index(){
        
    }
    
    /**
     * 服务器状态
     */
    public function status(){
        if(IS_POST){
            $sty = I('post.sty');
            if($sty == 1){
                //启动所有
                $result = $this->checkStatus();
                @exec("sudo rongan start",$restarall);
                $this->success($result);
            }elseif($sty == 2){
                //重启所有
                $result = $this->checkStatus();
                @exec("sudo rongan restart",$restarall);
                $this->success($result);
            }else{
                //停止所有
                @exec("sudo rongan stop",$starall);
                $this->success(L('success'));
            }
        }
        @exec("sudo rongan status",$serverStatus); //服务器状态
        $sd = strstr($serverStatus[0], 'running') ? 1 : 0;//介质服务器
        $fd = strstr($serverStatus[1], 'running') ? 1 : 0;//本地服务器
        $dir = strstr($serverStatus[2], 'running') ? 1 : 0;//备份服务器
        if($dir){
            @exec("sudo sh /opt/rongan/scripts/showdir",$dirStatus); //服务器状态
            $tmp = explode(':', $dirStatus[4]);
            $tmp = explode(' ',$tmp[1]);
            $tmp1 = explode('started', $dirStatus[5]);
            $tmp1 = explode('.',$tmp1[1]);
            $starttime = ltrim($tmp1[0]);
            if(strstr($starttime,'月')){
                $time = explode(' ',$starttime);
                $year = explode('月',$time[0]);
                $mouth = explode('-',$year[0]);
                $data = '2'.$year[1].'-'.$mouth[1] . '-' . $mouth[0] .' ' . $time[1] .':00';
                $starttime = $data;
            }else{
                $starttime =  date("Y-m-d H:i",  strtotime($starttime));
            }	
            //$dirInfo = "版本：{$tmp[1]}  操作体统：{$tmp[7]} {$tmp[8]} {$tmp[9]} {$tmp[10]} 启动时间：{$starttime}";
            $dirInfo = "版本：{$tmp[1]} build ".APP_VERSION."  &nbsp;&nbsp;最近启动时间：{$starttime}";
        }
        //有备份任务在运行的话，不能获取到返回值
//        if($sd){
//            @exec("sudo sh /opt/rongan/scripts/showsd",$sdStatus); //服务器状态
//            $tmp = explode(':', $sdStatus[6]);
//            $tmp = explode(' ',$tmp[1]);
//            $tmp1 = explode('started', $sdStatus[7]);
//            $tmp1 = explode('.',$tmp1[1]);
//            $starttime = $tmp1[0];
//            $starttime =  date("Y-m-d H:i",  strtotime($starttime));	
//            $sdInfo = "版本：{$tmp[1]}  最近启动时间：{$starttime}";
//        }
        $this->assign(array(
            'menuid'=>'sys_server_status',
            'sd'=>$sd,
            'fd'=>$fd,
            'dir'=>$dir,
            'dirInfo'=>$dirInfo,
        ));
        $this->display();
    }
    
    /**
     * 启动重启时候检查状态
     */
    private function checkStatus(){
        $result = "";
            @exec("sudo sh /opt/rongan/scripts/test_dirconfig",$starall2);//备份服务器
            if($starall2){
                foreach ($starall2 as $item){
                    if(!strstr($item,"[36m")){
                        $result .=str_replace("===", "=", $item) .'##';
                    }
                }
            }
            $result .="@@@";
            @exec("sudo sh /opt/rongan/scripts/test_sdconfig",$starall1);//介质服务器
            if($starall1){
                $i = 0;
                foreach ($starall1 as $item){
                    if($i!=0){
                        $result .=$item .'##';
                    }
                    $i++;
                }
            }
            //[4;33m   [31m
        $result = str_replace("[31m","",str_replace('[4;33m','',str_replace("[0m", "", $result)));
        return $result;
    }
    /**
     * 客户端状态
     */
    public function client(){
        $data = M('client')->where('state=0')->select();
        $job = M('job');
        $ndata = array();
        foreach ($data as $key=>$item){
            $ndata[$key] = $item;
            $ndata[$key]['jobcount'] = $job->where('state=0 and clientId = '.$item['id'])->count();
        }
        $this->assign(array(
            'menuid'=>'sys_client_status',
            'data'=>$ndata,
        ));
        $this->display();
    }
    
    /**
     * 服务器的客户端状态信息
     */
    public function clientInfo($name=""){
        if($name){
            @exec("sudo sh  /opt/rongan/scripts/showclient {$name}",$clientStatus); //客户端状态
            $serverInfo = getServerInfo($clientStatus);
             //已经完成的任务
            $count = 0;
            $terminatedJob = array();
            foreach ($clientStatus as $key=>$item){
                if($key>3){
                    if(preg_match("/^\d+/",  ltrim($item))){
                        //$tmp = array_values(array_filter(explode(" ", ltrim($item)),'_filterEmpty'));
                        $tmp = array_values(array_filter(explode(" ", ltrim($item)),"hink_not_filter0"));
                        if($tmp[1] != 'Full' && $tmp[1] != 'Diff' && $tmp[1] != 'Incr'){
                            array_splice($tmp, 1,0,'-');
                        }
                        if(count($tmp)==8){
                            array_splice($tmp,2,0,0); 
                        }
                        if(count($tmp)==7){
                            array_splice($tmp,1,0,0); 
                            
                        }
//                        if(!$tmp[3]){
//                            array_splice($tmp,4,0,array(0=>'0')); 
//                        }
                        //$ddate = mb_convert_encoding($tmp[6], "GBK", "UTF-8");
                        $ddate = iconv("GBK", "UTF-8",$tmp[6]);
                        if(preg_match("/^\d{2}-\d{6}/",  $ddate)){
                            $ddate = dataFormat($ddate);
                        }
                        $dtmp = $tmp[4] ? $tmp[4] : ' ';
                        $terminatedJob[$count]['id'] = $tmp[0];
                        $terminatedJob[$count]['level'] = $tmp[1];
                        $terminatedJob[$count]['files'] = $tmp[2];
                        $terminatedJob[$count]['bytes'] = $tmp[3] . $dtmp;
                        $terminatedJob[$count]['finished'] = $ddate . ' ' .$tmp[7];
                        $terminatedJob[$count]['name'] = $tmp[8];
                        $terminatedJob[$count]['status'] = $tmp[5];
                        $count ++;
                    }
                }
            }
            
            //进行中的任务
            $runingJob = $this->runningJob($clientStatus);
            $this->assign(array(
                'serverInfo'=>$serverInfo,
                'terminatedJob'=>  array_reverse($terminatedJob),
                'runingJob'=>$runingJob,
            ));
        }
        $this->display();
    }
    
    /**
     * 任务状态
     */
    public function job(){
        @exec("sudo sh /opt/rongan/scripts/showdir",$jobStatus); //任务状态
        $jobStatus = array_values(array_filter($jobStatus));
        //计划任务 根据Full Differential Incremental
        $scheduledJob = array();
        $runningJob = array();
        $count = 0;
        $rcount = 0;
        foreach ($jobStatus as $item){
            if(preg_match("/^Full|Differential|Incremental|Copy/",  ltrim($item))){
                $tmp = array_values(array_filter(explode(" ", ltrim($item))));
                if($tmp[0] == 'Copy'){
                    array_splice($tmp, 0,0,'-');
                    array_splice($tmp, 6,0,'-');
                }
                $scheduledJob[$count]['level'] = $tmp[0];
                $scheduledJob[$count]['type'] = $tmp[1];
                $scheduledJob[$count]['pri'] = $tmp[2];
                $scheduledJob[$count]['scheduled'] = $tmp[3] . ' ' .$tmp[4];
                if(strstr($scheduledJob[$count]['scheduled'],'月')){
					$time = explode(' ',$scheduledJob[$count]['scheduled']);
					$year = explode('月',$time[0]);
					$mouth = explode('-',$year[0]);
					$data = '2'.$year[1].'-'.$mouth[1] . '-' . $mouth[0] .' ' . $time[1] .':00';
					$scheduledJob[$count]['scheduled'] = $data;
                }else{
                    $scheduledJob[$count]['scheduled'] = date('Y-m-d H:i:s',strtotime($scheduledJob[$count]['scheduled']));
                }
                $scheduledJob[$count]['name'] = $tmp[5];
                $scheduledJob[$count]['volume'] = $tmp[6];
                $count++;
            }else if(preg_match("/^\d+/",  ltrim($item))){//进行中的任务
                if(strstr($item, "is running") || strstr($item, "is waiting")){
                    $tmp = array_values(array_filter(explode(" ", ltrim($item))));
                    if($tmp[1] != 'Full' && $tmp[1] != 'Differe' && $tmp[1] != 'Increme'){
                        array_splice($tmp, 1,0,'-');
                    }
                    $runingJob[$rcount]['id'] = $tmp[0];
                    $tname = explode(".", $tmp[2]);
                    $runingJob[$rcount]['name'] = $tname[0];
                    $runingJob[$rcount]['level'] = $tmp[1];
                    $tname = explode($tname[0].'.', $item);
                    $runingJob[$rcount]['status'] = $tname[1];
                    $rcount++;
                }
            }
        }
        $post = I('get.');
        $where = '';
        if($post['name']){
            //$sql .="Name like '%{$post['name']}%' and ";
            $where['Name'] = $post['name'];
        }
        if($post['client']){
            $where['ClientId'] = $post['client'];
        }
        if($post['pool']){
            $where['PoolId'] = $post['pool'];
        }
        if($post['starttime']){
            $where['StartTime'] = array('EGT',$post['starttime']);
        }
        if($post['endtime']){
             //$ends = $post['endtime'] + 1;
            $ends = $post['endtime'];
            $ends = date('Y-m-d',strtotime("$ends +1 day")); 
            $where['EndTime'] = array('ELT',$ends);
        }
        if($post['status']){
            if($post['status'] == 'T'){
                $where['JobStatus'] = $post['status'];
            }else{
                $sql .="JobStatus != 'T' and ";
                $where['JobStatus'] = array('NEQ','T');
            }
        }
        $model = new \Think\Model();
        $count = $model->db(2,"mysql://rongan:rongandb@localhost:3306/rongan")->table("Job")->where($where)->count();
        
        //分页
        $Page       = new \Think\Page($count,10);
        $Page->setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条记录&nbsp;</li>');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
        $show = $Page->show();
        $this->assign('page',$show);
        
        //$terminatedJob = $model->db(2)->table("Job")->where($where)->order('JobId desc')->page($_GET['p'].',5')->select(); 
        $terminatedJob = $model->db(2)->table("Job")->where($where)->order('JobId desc')->limit($Page->firstRow.','.$Page->listRows)->select(); 
        $pool = $model->db(2,"mysql://rongan:rongandb@localhost:3306/rongan")->query("select * from Pool");
        //$client = M('client')->where('state=0')->select();
        $client = $model->db(2)->query("select * from Client");
        $tername = $model->db(2)->table("Job")->field('Name')->group('Name')->select();
        $this->assign(array(
            'menuid'=>'sys_job_status',
            'scheduledJob'=>$scheduledJob,
            'runingJob'=>$runingJob,
            'terminatedJob'=>$terminatedJob,
            'tername'=>$tername,
            'client'=>$client,
            'pool'=>$pool,
        ));
        $this->display();
    }
    
	/**
     * 任务状态
     */
    public function diyJob(){
		$days = I('get.days');
		$type = I('get.type');
        $where = "Job.Type = 'B' and ";
        if($type){
        	//任务状态 success
            if($type == 'success'){
				$title  = "{$days} 天内成功的任务";
            	$where .= "Job.JobStatus = 'T'";
            }elseif($type == 'error'){
				$title  = "{$days} 天内失败的任务";
            	$where .= "(Job.JobStatus = 'B' or Job.JobStatus = 'E'  or Job.JobStatus = 'e' or Job.JobStatus = 'f')";
            }else{
				$title  = "{$days} 天内警告的任务";
            	$where .= "Job.JobStatus = 'W'";
            }
        }
        if($days){
        	$where .=" and TO_DAYS(NOW()) - TO_DAYS(EndTime) <= {$days}";
        }
		
        $model = new \Think\Model();
        $count = $model->db(2,"mysql://rongan:rongandb@localhost:3306/rongan")->table("Job")->where($where)->count();
        
        //分页
        $Page       = new \Think\Page($count,10);
        $Page->setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条记录&nbsp;</li>');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
        $show = $Page->show();
        $this->assign('page',$show);
         
        $terminatedJob = $model->db(2)->table("Job")->where($where)->order('JobId desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $client = $model->db(2)->query("select * from Client");
        $this->assign(array(
            'terminatedJob'=>$terminatedJob,
            'client'=>$client,
            'title'=>$title,
        ));
        $this->display();
    }

    /**
     * 获取动态刷新Job列表
     */
    public function getAjaxJob(){
        if(IS_POST){
//            $post = I('post.');
//            $sql = "select * from Job where ";
//            if($post['name']){
//                //$sql .="Name like '%{$post['name']}%' and ";
//                $sql .="Name = '{$post['name']}' and ";
//            }
//            if($post['client']){
//                $sql .="ClientId = '{$post['client']}' and ";
//            }
//            if($post['pool']){
//                $sql .="PoolId = '{$post['pool']}' and ";
//            }
//            if($post['starttime']){
//                $sql .="StartTime >= '{$post['starttime']}' and ";
//            }
//            if($post['endtime']){
//                 //$ends = $post['endtime'] + 1;
//                $ends = $post['endtime'];
//                $ends = date('Y-m-d',strtotime("$ends +1 day")); 
//                $sql .="EndTime <= '{$ends}' and ";
//            }
//            if($post['status']){
//                if($post['status'] == 'T'){
//                    $sql .="JobStatus = '{$post['status']}' and ";
//                }else{
//                    $sql .="JobStatus != 'T' and ";
//                }
//            }
//            $sql = substr_replace($sql,'',-4); 
//            $sql .= ' order by JobId desc';
//            $terminatedJob = M('Job')->db(2,"mysql://rongan:rongandb@localhost:3306/rongan")->query($sql);
            $post = I('post.');
            $where = '';
            if($post['name']){
                //$sql .="Name like '%{$post['name']}%' and ";
                $where['Name'] = $post['name'];
            }
            if($post['client']){
                $where['ClientId'] = $post['client'];
            }
            if($post['pool']){
                $where['PoolId'] = $post['pool'];
            }
            if($post['starttime']){
                $where['StartTime'] = array('EGT',$post['starttime']);
            }
            if($post['endtime']){
                 //$ends = $post['endtime'] + 1;
                $ends = $post['endtime'];
                $ends = date('Y-m-d',strtotime("$ends +1 day")); 
                $where['EndTime'] = array('ELT',$ends);
            }
            if($post['status']){
                if($post['status'] == 'T'){
                    $where['JobStatus'] = $post['status'];
                }else{
                    $sql .="JobStatus != 'T' and ";
                    $where['EndTime'] = array('NEQ','T');
                }
            }
            $model = new \Think\Model();
            $count = $model->db(2,"mysql://rongan:rongandb@localhost:3306/rongan")->table("Job")->where($where)->count();
            $terminatedJob = $model->db(2)->table("Job")->where($where)->order('JobId desc')->page($_GET['p'].',5')->select();            
            
            $nterminatedJob = array();
            $i = 0;
            foreach ($terminatedJob as $ter){
                $nterminatedJob[$i] = $ter;
                $ttmp = M('Client')->db(2)->query("select * from Client where ClientId=" . $ter['ClientId']);
                $nterminatedJob[$i]['client'] = $ttmp[0]['Name'];
                $i++;
            }
            $this->success($nterminatedJob);
        }
    }
    
    /**
     * 删除任务
     */
    public function delJob(){
        if(IS_POST){
            $id = I('post.id');
            if($id){
                @exec("sudo sh /opt/rongan/scripts/deletejob {$id}",$jobLog); 
                $this->success(L('success'));
            }
        }
    }
    
    /**
     * 取消执行任务
     */
    public function cancelJob(){
        if(IS_POST){
            $id = I('post.id');
            @exec("sudo sh /opt/rongan/scripts/canceljob {$id}",$jobLog); 
            $this->success(L('success'));
        }
    }
    
    /**
     * 获取已经完成任务详细信息
     * @param type $id
     */
    public function terminatedJob($id=""){
        if($id){
            $data = M('Job')->db(2,"mysql://rongan:rongandb@localhost:3306/rongan")->query("select JobId,Name,Type,Level,ClientId,JobStatus,StartTime,EndTime,JobFiles,JobBytes, PoolId from Job where JobId = " . $id);
            if($data){
                $client = M('Client')->db(2)->query("select * from Client where ClientId=" . $data[0]['ClientId']);
                $pool = M('Pool')->db(2)->query("select * from Pool where PoolId=" . $data[0]['PoolId']);
                @exec("sudo sh /opt/rongan/scripts/showlog {$id}",$jobLog); //任务状态
                //$jobLog = $jobLog;
            }
            unset($jobLog[count($jobLog)-1]);
            for($i=0;$i<5;$i++){
                unset($jobLog[$i]);
            }
            $sjobLog = array_filter($jobLog);
            $sjobLog = implode('@@',$sjobLog);
            $infos = array();
             if (preg_match("/Rate:\s+([^<]+?)@@/i", $sjobLog, $temp_array)) {
                $infos['rate'] =  $temp_array[1];
            }
            if (preg_match("/Elapsed\stime:\s+([^<]+?)@@/i", $sjobLog, $temp_array)) {
                $infos['elapsed'] = str_replace("secs", "秒", str_replace("mins", "分", $temp_array[1])); 
            }
            $this->assign(array(
                'data'=>$data[0],
                'client'=>$client[0],
                'pool'=>$pool[0],
                'jobLog'=>$jobLog,
                'infos'=>$infos,
            ));
        }
        $this->display('jobInfo');
    }
    
    /**
     * 进行中的任务返回对应数字
     */
    private function _regexNum($str,$name=""){
        if (preg_match("/{$name}([^<]+?)\s/i", $str, $temp_array)) {
                return $temp_array[1];
            }
    }
    
    /**
     * 进行中的任务返回对应数字
     */
    private function _regexString($str,$name=""){
        if (preg_match("/{$name}([^<]+?)@/i", $str, $temp_array)) {
                return $temp_array[1];
            }
    }
    /**
     * 动态刷新进行中任务状态
     */
    public function getRunningJob($name=""){
        @exec("sudo sh  /opt/rongan/scripts/showclient {$name}",$clientStatus); //客户端状态
        //进行中的任务
        $clientStatus = array_filter($clientStatus);
        $clientStatus = implode('@',$clientStatus);
        //正则表达式：JobId([^<]+?)====
        $runingJob = array();
        if (preg_match("/@JobId([^<]+?)@=/i", $clientStatus, $temp_array)) {
            $result = "文件数：<font color='red'>{$this->_regexNum($temp_array[1], "Files=")}</font>个  容量：<font color='red'>{$this->_regexNum($temp_array[1], "Bytes=")}</font>字节  速度：<font color='red'>{$this->_regexNum($temp_array[1], "Bytes\/sec=")} </font>字节/秒 带宽限制： <font color='red'>";
            if(str_replace("@", "", $this->_regexNum($temp_array[1], "Bwlimit="))==0){
                $result .= "不限制";
            }else{
                $result .= str_replace("@", "", $this->_regexNum($temp_array[1], "Bwlimit="));
            }
            $result .= "</font><br/>当前备份任务：<font color='red' style='word-break:break-all'>";
            $result .= $this->_regexString($temp_array[1], 'Processing file:');
            $result .="</font>";
            $this->success($result);
        }else{
            $this->error(L('error'));
        }
    }
    
    /**
     * 获取正在进行中的任务
     * @param type $status
     * @return type
     */
    public function runningJob($status){
        $status = array_filter($status);
        $status = implode('@',$status);
        //正则表达式：JobId([^<]+?)====
        $runingJob = array();
        if (preg_match("/@JobId([^<]+?)@=/i", $status, $temp_array)) {
            $runingJob['id'] = $this->_regexNum($temp_array[1], "\s");
            $runingJob['files'] = $this->_regexNum($temp_array[1], "Files=");
            $runingJob['bytes'] = $this->_regexNum($temp_array[1], "Bytes=");
            $runingJob['bytessec'] = $this->_regexNum($temp_array[1], "Bytes\/sec=");
            $runingJob['bwlimit'] = str_replace("@", "", $this->_regexNum($temp_array[1], "Bwlimit="));
            $runingJob['pfile'] = $this->_regexString($temp_array[1], "Processing file:");
        }
        return $runingJob;
    }
    
    /**
     * 运行任务
     */
    public function runJob(){
        if(IS_POST){
            $name = I('post.name');
            $runlevel = I('post.runlevel');
            @exec("sudo sh /opt/rongan/scripts/runjob {$name} {$runlevel}",$jobLog); 
            $this->success(L('success'));
        }
    }
    
    /**
     * 模拟任务执行，返回文件数量 和 大小
     */
    public function estimateJob(){
        if(IS_POST){
            $name = I('post.name');
            $job = M('job')->where(array('name'=>$name))->find();
            $fileset = M('fileset')->where('id='.$job['fileSetId'])->find();
            if($job['type']){
                $result = "<font color='red'>任务模拟不支持复制任务！</font>";
                $this->success($result);
            }
            if($fileset['types'] != 'file'){
                $result = "<font color='red'>任务模拟只支持文件数据！</font>";
                $this->success($result);
            }
            @exec("sudo sh /opt/rongan/scripts/estimatejob {$name}",$jobLog); 
            foreach ($jobLog as $item){
                if(strstr($item,"bytes=")){
                    $bytes = explode("bytes=", $item);
                    if(count($bytes) >1){
                        $result = "文件数：<font color='red'>{$this->_regexNum($item, "files=")}</font> 个 文件大小：<font color='red'>{$bytes[1]}</font> bytes";
                    }else{
                        $result = "文件数：<font color='red'> 0 </font> 个 文件大小：<font color='red'> 0 </font> bytes";
                    }
                }
            }
            $this->success($result);
        }
    }
    
    /**
     * 恢复任务
     */
    public function restoreJob(){
        if(IS_POST){
            $id = I('post.id');
            $client = I('post.client');
            $reclient = I('post.reclient');
            $where = I('post.rewhere');
            $where = str_replace('\\','/',$where);
            @exec("sudo sh /opt/rongan/scripts/restorejobid {$id} {$reclient} {$client} {$where}",$jobLog); 
            $this->success(L('success'));
        }
    }
    
    /**
     * 介质状态
     */
    public function storage(){
        $sql = "select PoolId,Name,NumVols,MaxVols,VolRetention,MaxVolBytes from Pool order by PoolId desc";
        $data = M('Pool')->db(2,"mysql://rongan:rongandb@localhost:3306/rongan")->query($sql);
        $this->assign(array(
            'menuid'=>'sys_storage_status',
            'data'=>$data,
        ));
        $this->display();
    }
    
    /**
     * 卷信息
     */
    public function media($pooid=""){
        if($pooid){
            $sql = "select MediaId,VolumeName,FirstWritten,LastWritten,VolBytes,VolStatus from Media where PoolId = '{$pooid}' order by MediaId desc";
            $data = M('Media')->db(2,"mysql://rongan:rongandb@localhost:3306/rongan")->query($sql);
            $this->assign(array(
                'data'=>$data,
            ));
            $this->display();
        }else{
            echo "获取数据出错";
        }
    }
    
    /**
     * 删除卷
     */
    public function delMedia(){
        if(IS_POST){
            $id = I('post.id');
            $type = I('post.type');
            if($type == 'del'){
                @exec("sudo sh /opt/rongan/scripts/deletemedia {$id}",$jobLog); 
            }else{
                @exec("sudo sh /opt/rongan/scripts/purgevol {$id}",$jobLog); 
            }
            $this->success(L('success'));
        }
    }
    
    /**
     * 导出日志
     */
    public function exportLog(){
        @exec("sudo ronganlog"); 
        @exec("sudo tar -zcPf /var/www/html/webapp/log.tar /opt/rongan/log/ /var/www/html/webapp/conf/",$info); 
        @exec("sudo rm -rf /opt/rongan/log/*"); 
        $file = "/var/www/html/webapp/log.tar";
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
}

