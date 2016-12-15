<?php

/*
 * Jrsn Backup Management System
 * @Description   应用管理
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Controller;

class AppsController extends CommonController{
    
    private $apps;
    private $license;
    function _initialize() {
        parent::_initialize();
        $this->apps = M('apps');
        $this->license = parent::license();
    }
    
    /**
     * 获取应用列表 AJAX方式
     */
    public function getList(){
        
        $app = $this->license;
        $data['code'] = true;
        $filelist = $this->apps->where("type!=3")->select();
        if($this->license['status'] == 1){//试用未到期 全部APP可用
            $filelist = $this->addAppLicense($filelist,1);
        }elseif ($this->license['status'] == 4) {
            $filelist = $this->addAppLicense($filelist,$this->license['data']['apps'] );
        }else{
            $filelist = $this->addAppLicense($filelist,0);
        }
        $data['data']['filelist'] = $filelist ? $filelist : '[]';
        
        $this->ajaxReturn($data,'json');
    }
    
    /**
     * 授权APP
     * @param type $applist
     * @param type $sty
     * @return int
     */
    private function addAppLicense($applist,$sty){
        $result = array();
        if($applist){
            foreach ($applist as $key=>$item){
                $result[$key] = $item;
                if($sty == 1){//所有可用
                    $result[$key]['open'] = 1;
                }elseif($sty == 0){
                    $result[$key]['open'] = 0;
                }else{
                    if(in_array($item['id'],$sty)){
                         $result[$key]['open'] = 1;
                    }else{
                         $result[$key]['open'] = 0;
                    }
                }
            }
        }
        return $result;
    }
    
}
