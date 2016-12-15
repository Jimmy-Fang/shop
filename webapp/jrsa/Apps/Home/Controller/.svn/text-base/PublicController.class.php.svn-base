<?php

/*
 * Jrsn Backup Management System
 * @Description   公用模块
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Controller;
use Think\Controller;
use Hlib\Util\Checkcode;
use Home\Service\User;

class PublicController extends Controller {
    
     /**
     * 用户登陆
     */
    public function login(){
        if(IS_POST){
            $ip = get_client_ip();
            $username = I("post.name", "", "trim");
            $password = I("post.password", "", "trim");
            $code = I("post.check_code", "", "trim");
            if (empty($username) || empty($password)) {
                $this->error(L('VERIFY_NAME_OR_PWD_MUST'), U("Public/login"));
            }
            if (empty($code)) {
                $this->error(L('VERIFY_CODE_MUST'), U("Public/login"));
            }
            //验证码开始验证
            if (!$this->verify($code)) {
                $this->error(L('VERIFY_CODE_ERROE'), U("Public/login"));
            }
            if (User::getInit()->login($username, $password)) {
                $forward = cookie("forward");
                if (!$forward) {
                    $forward = U("Index/index");
                } else {
                    cookie("forward", NULL);
                }
                $this->success(L('LOGIN_OK'),U('Index/index'));
            } else {
                $this->error(L('LOGIN_ERROR'), U("Public/login"));
            }
        }else{
            $this->display();
        }
    } 
    
    /**
     * 用户登出
     */
    public function loginout(){
        $user = User::getInit();
        $user->logout();
        $this->redirect('Public/login');
    }
    
    /**
     * 验证码处理
     */
    public function checkCode(){
        $checkcode = new Checkcode();
        //验证码类型
        $checkcode->type = I('get.type', 'verify', 'strtolower');
        //设置长度
        $codelen = I('get.code_len', 0, 'intval');
        if ($codelen) {
            if ($codelen > 8 || $codelen < 2) {
                $codelen = 4;
            }
            $checkcode->codelen = $codelen;
        }
        //设置验证码字体大小
        $fontsize = I('get.font_size', 0, 'intval');
        if ($fontsize) {
            $checkcode->fontsize = $fontsize;
        }
        //设置验证码图片宽度
        $width = I('get.width', 0, 'intval');
        if ($width) {
            $checkcode->width = $width;
        }
        //设置验证码图片高度
        $height = I('get.height', 0, 'intval');
        if ($height) {
            $checkcode->height = $height;
        }
        //设置背景颜色
        $background = I('get.background', '', '');
        if ($background) {
            $checkcode->background = $background;
        }
        //设置字体颜色
        $fontcolor = I('get.font_color', '', '');
        if ($fontcolor) {
            $checkcode->fontcolor = $fontcolor;
        }

        //显示验证码
        $checkcode->output(I('refresh', false, ''));
        return true;
    }
    
    /**
     * 验证验证码
     */
    protected function verify($input,$type='verify'){
         $checkcode = new Checkcode();
        $checkcode->type = $type;
        return $checkcode->validate($input, false);
    }
}
