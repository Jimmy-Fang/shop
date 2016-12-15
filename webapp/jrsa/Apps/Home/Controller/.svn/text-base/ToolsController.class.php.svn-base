<?php

/*
 * Jrsn Backup Management System
 * @Description  工具类
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Controller;

class ToolsController extends CommonController {
    
    function _initialize() {
        parent::_initialize();
    }
    
    public function index(){
        
        $this->display();
    }
    
    public function folder($path="/",$isfile = "", $fileId = "filepath",$dir=""){
        $path = rtrim(str_replace("@@", "/", $path),'/');
        if($isfile){
            $isfile = true;
        }else{
            $isfile = false;
        }
        $data = path_list($path,$isfile);
        $path = explode("/", $path);
        $paths = array();
        foreach ($path as $k=>$v){
            $paths[$k]["name"] = $v;
            if($k==0){
                $paths[$k]["url"] = $v;
            }else{
                $paths[$k]["url"] = $paths[$k-1]['url'] . '/' . $paths[$k]['name'];
            }
        }
        $this->assign(array(
            'data'=>$data,
            'path'=>$paths,
            'fileId'=>$fileId,
            'dir'=>$dir ? '\n' : '',
            'isfile'=>$isfile ? '1' : '',
        ));
        $this->assign("data",$data);
        $this->display();
    }
    
}
