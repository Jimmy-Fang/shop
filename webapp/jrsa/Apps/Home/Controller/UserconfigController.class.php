<?php

/*
 * Jrsn Backup Management System
 * @Description   用户配置
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Controller;

class UserconfigController extends CommonController {
    
    private $userconfig;
    function _initialize() {
        parent::_initialize();
        $this->userconfig = M('userconfig');
    }
    
    /**
     * 修改用户配置
     * 支持多个参数和多个值  例如：k=list1,list2$val=v1,v2
     */
    public function set(){
        $key = I('k');
        $value = I('v');
        if ($key !='' && $value != '') {
            $arr_k = explode(',', $key);
            $arr_v = explode(',',$value);
            $num = count($arr_k);

            for ($i=0; $i < $num; $i++) { 
                $data[$arr_k[$i]] = $arr_v[$i];
            }
            $where['uid'] = UID;
            $this->userconfig->where($where)->save($data);
            $this->success(L('save_success'));
        }else{
            $this->error(L('error'));
        }
    }
    
}
