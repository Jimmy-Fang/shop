<?php

/*
 * Jrsn Backup Management System
 * @Description   用户相关
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Controller;
use Hlib\Util\Checkcode;
use Home\Service\User;

class UserController extends CommonController {
    
    private $user;
    private $userinfo;
    private $duser;
    function _initialize() {
        parent::_initialize();
        $this->user = User::getInit();
        $this->userinfo = $this->user->getInfo();
        $this->duser = D("user");
    }
    
    public function info(){
        if(IS_POST){
            if(I('password_now') === I('password1')){
                $this->error(L('updata_password_yet'));
            }
            $duser = D("user");
             if($this->duser->changePassword(UID, I("password1"), I("password_now"))){
                 $this->success(L('success'),U('public/loginout'));
             }else{
                $this->error(L('update_user_info_error'));
            }
        }
        $this->assign("menuid","user_info");
        $this->display();
    }
    
    public function member(){
        $this->assign("menuid","members");
        $this->display();
    }
    
    /**
     * 修改用户密码
     */
    public function updinfo(){
        if(IS_AJAX){
            if(I('pnew') === I('pold')){
                $this->error(L('updata_password_yet'));
            }
            $duser = D("user");
             if($this->duser->changePassword(UID, I("pnew"), I("pold"))){
                 $this->success(L('success'));
             }else{
                $this->error("旧密码不正确或者该用户不存在！");
            }
        }else{
            $this->error("error!!!");
        }
    }
    
    /**
     * 用户列表
     */
    public function lists(){
        $data['data'] = M('user')->where("status = 1")->select();
        if($data['data']){
            $data['code'] = 1;
        }else{
            $data = L('NO_USER_DATA');
        }
        $this->ajaxReturn($data);
    }
    
    /**
     * 添加管理员
     */
    public function add(){
        if(IS_POST){
            if($this->duser->createManager(I('post.'))){
                $this->success(L('success'));
            }else{
                $error = $this->duser->getError();
                $this->error($error ? $error :L('error'));
            }
        }else{
            $this->error("error!!!");
        }
    }
    
        /**
     * 修改管理员
     */
    public function upd(){
        if(IS_POST){
            if($this->duser->updateManager(I('post.'))){
                $this->success(L('success'));
            }else{
                $error = $this->duser->getError();
                $this->error($error ? $error : L('error'));
            }
        }else{
            $this->error("error!!!");
        }
    }
    
    /**
     * 删除用户
     */
    public function del(){
        if(IS_POST){
            $username = I('post.username');
                if (empty($username)) {
                    $this->error( L('del_no_data'));
                }
                $userinfo = User::getInfo();
                if ($username == $userinfo['username']) {
                    $this->error(L('del_myself'));
                }
                //执行删除
                if ($this->duser->deleteUser($username)) {
                    $this->success(L("success"));
                } else {
                    $this->duser->getError()? : L('error');
                }
        }
    }
}
