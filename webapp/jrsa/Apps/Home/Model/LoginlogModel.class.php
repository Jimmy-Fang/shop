<?php

/*
 * Jrsn Backup Management System
 * @Description    后台登陆日志
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Model;
use Think\Model;

class LoginlogModel extends Model {
    
     //array(填充字段,填充内容,[填充条件,附加规则])
    protected $_auto = array(
        array('logintime', 'time', 1, 'function'),
        array('loginip', 'get_client_ip', 3, 'function'),
    );
    
     /**
     * 删除一个月前的日志
     * @return boolean
     */
    public function deleteAMonthago() {
        $status = $this->where(array("logintime" => array("lt", time() - (86400 * 30))))->delete();
        return $status !== false ? true : false;
    }
    
    /**
     * 添加登陆日志
     * @param type $data
     * @return type
     */
    public function addLoginLog($data) {
        $this->create($data);
        return $this->add() !== false ? true : false;
    }
}
