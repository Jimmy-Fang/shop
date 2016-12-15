<?php

/*
 * Jrsn Backup Management System
 * @Description    
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Service;
use  \Hlib\Util\Encrypt;

class User {
    
    const userIdKey = "c_userid";
    const administraterRoleId = 1;  
    
    private static $userInfo = array();  
    
    /**
     * 初始化
     * @return [type] [description]
     */
    static public function getInit(){
        static $result = NULL;
        if(empty($result)){
            $result = new User();
        } 
        return $result;
    }
    
    /**
     * 魔术方法
     * @param type $name
     * @return type
     */
    public function __get($name) {
        if(isset(self::$userInfo[$name])){
            return self::$userInfo[$name];
        }else{
            $userInfo = $this->getInfo();
            if (!empty($userInfo)) {
                return $userInfo[$name];
            }
            return NULL;
        }
    }
    
    /**
     * 获取当前用户资料
     */
    public function getInfo(){
        if(empty(self::$userInfo)){
            self::$userInfo = $this->getUserInfo($this->isLogin());
        }
        return !empty(self::$userInfo) ? self::$userInfo : false;
    }
    
    /**
     * 获取用户信息
     * @param type $identifier 用户名或者用户ID
     */
    private function getUserInfo($identifier, $password = NULL){
        if(empty($identifier)){
            return false;
        }
        return D('Home/User')->getUserInfo($identifier, $password);
    }
    
    /**
     * 判断用户是否已经登录
     * @return type 成功返回当前登录用户ID
     */
    public function isLogin(){
        $userId = Encrypt::authcode(session(self::userIdKey),'DECODE');
        if(!empty($userId)){
            return (int)$userId;
        }
        return false;
    }
    
    /**
     * 后台登陆
     * @param type $identifier
     * @param type $password
     */
    public function login($identifier, $password){
        if (empty($identifier) || empty($password)) {
            return false;
        }
        $userInfo = $this->getUserInfo($identifier, $password);
        if (false == $userInfo) {
            //登陆失败 记录登录日志
            $this->loginLog($identifier, $password, 0);
            return false;
        }
          //登陆成功 记录登录日志
          $this->loginLog($identifier, $password,1);
          //注册登录状态
         $this->registerLogin($userInfo);
         return true;
    }
    
    /**
     * 用户登出
     * @return boolean
     */
    public function logout(){
        session('[destroy]');
        return true;
    }
    
    /**
     * 判断当前登陆用户是不是超级管理员
     * @return boolean
     */
    public function isAdministaator(){
        $userInfo = $this->getInfo();
        if(!empty($userInfo) && $userInfo["roleid"] == self::administraterRoleId){
            return true;
        }
        return false;
    }
    
    /**
     * 记录后台用户登陆日志
     * @param type $identifier
     * @param type $password
     * @param type $status
     */
    private function loginLog($identifier, $password,$status = 0){
        $data = array(
            "username" => $identifier,
            "status" => $status,
            "password" => $status ? '密码保密' : $password,
            "info" => is_int($identifier) ? '用户ID登录' : '用户名登录',
        );
        D('Home/Loginlog')->addLoginLog($data);
    }
    
    /**
     * 注册用户登陆状态
     * @param array $userInfo
     */
    private function registerLogin(array $userInfo){
        //写入session
        session(self::userIdKey, Encrypt::authcode((int) $userInfo['id'],''));
                
        D('Home/User')->updateLoginStatus((int) $userInfo['id']);//更新状态
    }
}
