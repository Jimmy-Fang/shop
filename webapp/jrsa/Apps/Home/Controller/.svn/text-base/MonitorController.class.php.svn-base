<?php

/*
 * Jrsa Backup Management System
 * @Description    定制化功能 系统监控
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */
namespace Home\Controller;

class MonitorController extends CommonController{
    
    function _initialize() {
        parent::_initialize();
    }
    
    /**
     * 默认页面
     */
    public function index(){
        //获取服务器相关信息
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
            $dirInfo = "版本：{$tmp[1]}  最近启动时间：{$starttime}";
        }
        
        
    }
    
}
