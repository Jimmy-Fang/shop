<?php

/*
 * Jrsn Backup Management System
 * @Description   用户Model层
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Model;
use Think\Model;

class UserModel extends Model {
    
    protected $_validate = array(
        array('username', 'require', '{%VERIFY_USERNAME_MUST}'),
        array('nickname', 'require', '{%VERIFY_NICKNAME_MUST}'),
        array('password', 'require', '{%VERIFY_PASSWORD_MUST}', 0, 'regex', 1),
        array('pwdconfirm', 'password', '{%VERIFY_REPASSWORD}', 0, 'confirm'),
        array('username', '', '{%VERIFY_USERNAME_YET}', 0, 'unique', 1),
        array('status', array(0, 1), '{%VERIFY_STATUS}', 2, 'in'),
    );
    
    protected $_auto = array(
        array('createtime', 'time', 1, 'function'),
        array('logintime', 'time', 3, 'function'),
        array('encrypt', 'genRandomString', 1, 'function', 6), //新增时自动生成验证码
    );
    
    /**
     * 获取用户信息
     * @param type $identifier 用户名或者用户ID
     * @param type $password
     */
    public function getUserInfo($identifier, $password = NULL){
        
        if(empty($identifier)){
            return false;
        }
        $where = array();
        if(is_int($identifier)){//判断是用户ID还是用户名
            $where['id'] = $identifier;
        }else{
            $where['username'] = $identifier;
        }
        $userInfo = $this->where($where)->find();
        if (empty($userInfo)) {
            return false;
        }
        //密码验证
        if (!empty($password) && $this->hashPassword($password, $userInfo['encrypt']) != $userInfo['password']) {
            return false;
        }
        return $userInfo;
    }
    
    /**
     * 加密文件，返回加密后的密文
     * @param type $password 明文密码
     * @param type $verify  认证码
     * @return type 密文密码
     */
    public function hashPassword($password, $verify = ""){
        return md5(md5($password) . md5($verify));
    }
    
    /**
     * 更新用户登陆状态信息
     * @param type $userId
     */
    public function updateLoginStatus($userId){
        $this->find($userId);
        $this->logintime = time();
        $this->loginip = get_client_ip();
        return $this->save();
    }
    
    /**
     * 修改用户密码
     * @param type $id
     * @param type $newPass
     * @param type $password
     */
    public function changePassword($id, $newPass, $password = NULL){
        //获取会员信息
        $userInfo = $this->getUserInfo((int) $id, $password);
        if (empty($userInfo)) {
            $this->error = '旧密码不正确或者该用户不存在！';
            return false;
        }
        $verify = genRandomString(6);
        $status = $this->where(array('id' => $userInfo['id']))->save(array('password' => $this->hashPassword($newPass, $verify), 'encrypt' => $verify));
        return $status !== false ? true : false;
    }
    
    /**
     * 修改管理员信息
     * @param type $data
     * @return boolean
     */
    public function updateManager($data) {
        if (empty($data) || !is_array($data) || !isset($data['username'])) {
            $this->error = '没有需要修改的数据！';
            return false;
        }
        $info = $this->where(array('username' => $data['username']))->find();
        if (empty($info)) {
            $this->error = '该管理员不存在！';
            return false;
        }
        //密码为空，表示不修改密码
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }
        if ($data['password']) {
            $verify = genRandomString(6);
            $this->verify = $verify;
            $this->password = $this->hashPassword($this->password, $verify);
        }
        $status = $this->where(array("username"=>$data['username']))->save($data);
        return $status !== false ? true : false;
    }
    
    /**
     * 创建新的管理员
     * @param type $data
     * @return boolean
     */
     public function createManager($data) {
        if (empty($data)) {
            $this->error = '没有数据！';
            return false;
        }
        if ($this->create($data)) {
            $id = $this->add();
            if ($id) {
                return $id;
            }
            $this->error = '入库失败！';
            return false;
        } else {
            return false;
        }
    }
    
    /**
     * 插入成功后调用方法
     * @param type $data
     * @param type $options
     */
    protected function _after_insert($data, $options) {
        //添加信息后，更新密码字段
        $this->where(array('id' => $data['id']))->save(array(
            'password' => $this->hashPassword($data['password'], $data['encrypt']),
        ));
    }
    
    /**
     * 删除管理员
     * @param type $username
     * @return boolean
     */
    public function deleteUser($username) {
        if (empty($username)) {
            $this->error = L('del_no_data');
            return false;
        }
        if ($username == 'admin') {
            $this->error = L('del_admin_no');
            return false;
        }
        if (false !== $this->where(array('username' => $username))->delete()) {
            return true;
        } else {
            $this->error = L('error');
            return false;
        }
    }
}
