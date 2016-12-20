<?php

/* 
 * Jrsn Backup Management System
 * @Description   图表相关 
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Controller;

use Think\Model;

class ChartController extends CommonController {
    
    function _initialize() {
        parent::_initialize();
    }
	
	public function dash(){
//		$appset = M('appsetting');
//		$data = $appset->where(array('appid'=>'1','key'=>'dashdays'))->find();
//		if($data){
//			$days = I('get.days') ? I('get.days') : $data['value'];
//		}else{
//			$days = I('get.days') ? I('get.days') : 7;
//		}
//		$rongan = M('Job')->db(2,"mysql://rongan:rongandb@localhost:3306/rongan");
//		if($days != $data['value']){
//			if($data){
//				$appset->where(array('appid'=>'1','key'=>'dashdays'))->save(array('value'=>$days));
//			}else{
//				$appset->add(array('value'=>$days,'appid'=>'1','key'=>'dashdays'));
//			}
//		}
//
//		$okcount = $rongan->query("select count(*) as counts from Job where Job.Type = 'B' and Job.JobStatus = 'T' and TO_DAYS(NOW()) - TO_DAYS(EndTime) <= {$days}");
//		$errcount = $rongan->query("select count(*) as counts from Job where Job.Type = 'B' and (Job.JobStatus = 'B' or Job.JobStatus = 'E'  or Job.JobStatus = 'e' or Job.JobStatus = 'f') and TO_DAYS(NOW()) - TO_DAYS(EndTime) <= {$days}");
//		$wrrcount = $rongan->query("select count(*) as counts from Job where Job.Type = 'B' and Job.JobStatus = 'W' and TO_DAYS(NOW()) - TO_DAYS(EndTime) <= {$days}");
//
//		$jobinfo['okcount']  = $okcount[0]['counts'];
//		$jobinfo['errcount'] = $errcount[0]['counts'];
//		$jobinfo['wrrcount'] = $wrrcount[0]['counts'];
        //获取备份服务器状态
        @exec("sudo rongan status",$serverStatus); //服务器状态
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
        //获取介质服务器信息
        $dirstorage = M('Dirstorage');
        $dirstorage_rows = $dirstorage->select();
        //获取介质服务器状态
        foreach($dirstorage_rows as $key => $val){
           $ip = $val['address'];
           $port = $val['sdPort'];
            //获取状态
            @exec("sudo nmap -sT  -p $port -n $ip",$dir_status); //服务器状态
            //截取状态字符串
            $dir_status_str = implode($dir_status);
            if(strstr($dir_status_str,$port) && strstr($dir_status_str,'open')){
                $dirstorage_rows[$key]['status'] = 1;
            }else{
                $dirstorage_rows[$key]['status'] = 0;
            };

        }

        //查询存储池容量信息
        $pool = D('Pool');
        $capacity_info = $pool->getCapacityInfo();
        //客户端服务器信息
        $client_info =$pool->getClientInfo();
//        dump($client_info);
//        exit;
        $this->assign(array(
            'menuid'=>'chart_dash',
            //备份服务器信息
            'dirinfo'=>$dirInfo,
            'dir'=>$dir,
            //介质服务器信息
            'dirstorage_rows'=>$dirstorage_rows,
            'capacity_info'=>$capacity_info,
            'client_info'=>$client_info,
        ));
        
        $this->display();
    }

    public function showClientByStatus()
    {
        $pool = D('Pool');
        $client_info =$pool->getClientInfo();
        if($_GET['name'] == 'open'){
            $this->assign('client_rows',$client_info['client_open_rows']);
        }else{
            $this->assign('client_rows',$client_info['client_close_rows']);
        }
        $this->display();
    }
    public function client($type=0){
        $job = M('Job')->db(2,"mysql://rongan:rongandb@localhost:3306/rongan")->query("SELECT Sum(Job.JobBytes) AS JobBytes,Sum(Job.JobFiles) AS Jobfiles,Client.`Name` FROM Job ,Client WHERE Job.ClientId = Client.ClientId GROUP BY Client.ClientId ORDER BY Sum(Job.JobFiles) desc LIMIT 0, 10;");
        $jobbytes = M('Job')->db(2,"mysql://rongan:rongandb@localhost:3306/rongan")->query("SELECT Sum(Job.JobBytes) AS JobBytes,Sum(Job.JobFiles) AS Jobfiles,Client.`Name` FROM Job ,Client WHERE Job.ClientId = Client.ClientId GROUP BY Client.ClientId ORDER BY Sum(Job.JobBytes) desc LIMIT 0, 10;");
        if($type){
            $str = "客户端名称,备份文件数,备份文件大小\n";   
            $str = iconv('utf-8','gb2312',$str);
            foreach ($job as $item){
                $name = iconv('utf-8','gb2312',$item['Name']); //中文转码   
                $files = iconv('utf-8','gb2312',$item['Jobfiles']);   
                $str .= $name.",".$files.",".$item['JobBytes']."\n"; //用引文逗号分开   
            }
            $filename = date('Ymd').'.csv'; //设置文件名   
            export_csv($filename,$str); //导出
        }else{
            $jobinfo = null;
            $files = null;
            $bytes = null;
            foreach ($job as $item){
                $jobinfo['fcategories'] .="'{$item['Name']}',";
                $files .= $item['Jobfiles'] .",";
            }
            foreach ($jobbytes as $item){
                $jobinfo['bcategories'] .="'{$item['Name']}',";
                $bytes .= $item['JobBytes'] .",";
            }
            $jobinfo['fcategories'] = rtrim($jobinfo['fcategories'], ",");
            $jobinfo['bcategories'] = rtrim($jobinfo['bcategories'], ",");
            $jobinfo['bytesColumn'] ="{type:'column',name:'备份容量',data:[". rtrim($bytes, ",") . "]}";
            $jobinfo['filesColumn'] ="{type:'column',name:'备份文件数量',data:[". rtrim($files, ",") . "]}";
            $this->assign(array(
                'jobinfo'=>$jobinfo,
                'menuid'=>'chart_client',
            ));
            $this->display();
        }
    }
    public function job($type=0){
        $job = M('Job')->db(2,"mysql://rongan:rongandb@localhost:3306/rongan")->query("select Name,sum(JobBytes) as JobBytes ,sum(Jobfiles) as Jobfiles from Job where `Name` != 'Restore' group by Name ORDER BY Sum(JobFiles) desc LIMIT 0, 10;");
        $jobbytes = M('Job')->db(2,"mysql://rongan:rongandb@localhost:3306/rongan")->query("select Name,sum(JobBytes) as JobBytes ,sum(Jobfiles) as Jobfiles from Job where `Name` != 'Restore' group by Name ORDER BY Sum(JobBytes) desc LIMIT 0, 10;");
        if($type){
            $str = "任务名称,备份文件数,备份文件大小\n";   
            $str = iconv('utf-8','gb2312',$str);
            foreach ($job as $item){
                $name = iconv('utf-8','gb2312',$item['Name']); //中文转码   
                $files = iconv('utf-8','gb2312',$item['Jobfiles']);   
                $str .= $name.",".$files.",".$item['JobBytes']."\n"; //用引文逗号分开   
            }
            $filename = date('Ymd').'.csv'; //设置文件名   
            export_csv($filename,$str); //导出
        }else{
            $jobinfo = null;
            $files = null;
            $bytes = null;
             foreach ($job as $item){
                $jobinfo['fcategories'] .="'{$item['Name']}',";
                $files .= $item['Jobfiles'] .",";
            }
            foreach ($jobbytes as $item){
                $jobinfo['bcategories'] .="'{$item['Name']}',";
                $bytes .= $item['JobBytes'] .",";
            }
            $jobinfo['fcategories'] = rtrim($jobinfo['fcategories'], ",");
            $jobinfo['bcategories'] = rtrim($jobinfo['bcategories'], ",");
            $jobinfo['bytesColumn'] ="{type:'column',name:'备份容量',data:[". rtrim($bytes, ",") . "]}";
            $jobinfo['filesColumn'] ="{type:'column',name:'备份文件数量',data:[". rtrim($files, ",") . "]}";
            $this->assign(array(
                'jobinfo'=>$jobinfo,
                'menuid'=>'chart_job',
            ));
            $this->display();
        }
    }
    
    public function group($type=0){
        $job = $this->getClientInfoByName();
        if($type){
            $str = "客户端名称,备份文件数,备份文件大小\n";   
            $str = iconv('utf-8','gb2312',$str);
            foreach ($job as $item){
                $name = iconv('utf-8','gb2312',$item['Name']); //中文转码   
                $files = iconv('utf-8','gb2312',$item['Jobfiles']);   
                $str .= $name.",".$files.",".$item['JobBytes']."\n"; //用引文逗号分开   
            }
            $filename = date('Ymd').'.csv'; //设置文件名   
            export_csv($filename,$str); //导出
        }else{
            $jobinfo = null;
            $files = null;
            $bytes = null;
             foreach ($job as $item){
                $jobinfo['fcategories'] .="'{$item['Name']}',";
                $jobinfo['bcategories'] .="'{$item['Name']}',";
                $files .= $item['Jobfiles'] .",";
                $bytes .= $item['JobBytes'] .",";
            }
            $jobinfo['fcategories'] = rtrim($jobinfo['fcategories'], ",");
            $jobinfo['bcategories'] = rtrim($jobinfo['bcategories'], ",");
            $jobinfo['bytesColumn'] ="{type:'column',name:'备份容量',data:[". rtrim($bytes, ",") . "]}";
            $jobinfo['filesColumn'] ="{type:'column',name:'备份文件数量',data:[". rtrim($files, ",") . "]}";
            $this->assign(array(
                'jobinfo'=>$jobinfo,
                'menuid'=>'chart_group',
            ));
            $this->display();
        }
    }
    
    public function groupInfo(){
        $model = new \Think\Model();
        $sql = 'SELECT c.id AS id,g.`name` AS gname,c.gid,c.`name` FROM t_client AS c INNER JOIN t_clientgroup AS g ON c.gid = g.id ORDER BY g.id ASC';
        $result = $model->query($sql);
        $groupinfo = null;
        $tmp = null;
       foreach ($result as $item){
            if($tmp != $item['gname']){
                $tmp = $item['gname'];
                $names = $item['name'].',';
            }else{
                $names .=$item['name'].',';
            }
            $groupinfo[$tmp] = rtrim($names,',');
        }
        return $groupinfo;
    }
    
    public function getClintIdByName($name){
        $model = new \Think\Model();
        $names = explode(',', $name);
        foreach ($names as $key){
            $where .= " `Name` = '{$key}' or";
        }
        $where = substr($where, 0,  strlen($where) -3);
        $sql = "SELECT `Name`,ClientId FROM Client WHERE$where";
        $result = $model->db(2,"mysql://rongan:rongandb@localhost:3306/rongan")->query($sql);
        $ids = '';
        if($result){
            foreach ($result as $item){
                $ids .= $item['ClientId'] . ',';
            }
            $ids = substr($ids, 0,  strlen($ids) -1);
        }
        return $ids;
    }
    
    public function getClientInfoByName(){
        $group = $this->groupInfo();
        $model = new \Think\Model();
        $info = null;
        $count = 0;
        foreach ($group as $key=>$item){
            $clientid = $this->getClintIdByName($item);
            $ids = explode(',', $clientid);
            $where = '';
            foreach ($ids as $t){
                $where .= " ClientId = '{$t}' or";
            }
            $where = substr($where, 0,  strlen($where) -3);
            $sql = "select Name,sum(JobBytes) as JobBytes ,sum(Jobfiles) as Jobfiles from Job where$where and `Name` != 'Restore' ;";
            $result = $model->db(2,"mysql://rongan:rongandb@localhost:3306/rongan")->query($sql);
            $info[$count]['Name'] = $key;
            $info[$count]['JobBytes'] = $result[0]['JobBytes'] ? $result[0]['JobBytes'] : 0;
            $info[$count]['Jobfiles'] = $result[0]['Jobfiles'] ? $result[0]['Jobfiles'] : 0;
            $count++;
        }
        return $info;
    }
}

