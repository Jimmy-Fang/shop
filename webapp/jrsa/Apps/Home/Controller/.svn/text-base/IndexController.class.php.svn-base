<?php

/*
 * Jrsn Backup Management System
 * @Description   程序 首页
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Controller ;

class IndexController extends CommonController {
    
    function _initialize() {
        parent::_initialize();
    }
    
    public function index(){
        $this->assign('langs',  $this->getLanguage());
        $this->display();
    }
    
    /**
     * 获取多语言
     * @return type
     */
    protected function getLanguage(){
        $langnames = getFileNames(MODULE_PATH . 'Lang');
        $langs = array();
        $i = 0;
        foreach ($langnames as $lang){
            if($lang['file']){
                $lang = basename($lang['file'],'.php');
                $langs[$i]['enname'] = $lang;
                $langs[$i]['name'] = L($lang);
                $i++;
            }
        }
        return $langs;
    }
    
}
