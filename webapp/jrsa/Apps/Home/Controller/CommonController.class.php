<?php

/*
 * Jrsn Backup Management System
 * @Description    公用Controller
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Controller;
use Think\Controller;
use Home\Service\User;
use  \Hlib\Util\Encrypt;
use Hlib\Util\Macinfo;

class CommonController extends Controller {
    
    private $userconfig;
    private $license;
    function _initialize() {
        
        //检查是否安装
        if ($this->richterInstall() == false) {
            echo "软件还未安装，请先安装!!";
            //redirect(APPHOST . 'install.php');
            return false;
        }
        $this->userconfig = M('userconfig');
        $this->license = M('license');
        $user = User::getInit();
        define('UID',$user->isLogin());
        $linfo = $this->license();
        define("LICENSE", $linfo['status']);
        $userinfo = $user->getInfo();
        
        if(IS_POST){
            if($userinfo['roleid']==2){
                if(CONTROLLER_NAME.ACTION_NAME != 'Userinfo'){
                    $this->error(L('verify_permissions_not'));
                }
            }
        }
        
        $this->assign("userinfo",$userinfo);
        $this->assign("userconfig",  $this->userconfig->where("uid = " . UID)->find());
        if(!UID){//判断用户是否已经登录
            $this->redirect('Public/login');
        }
        //设置用户语言
        $userinfo = M('userconfig')->where("uid = " . UID)->find();
        if(cookie('think_language')){
            cookie('think_language',null);
        }
        cookie('think_language',$userinfo['language'],3600);
    }
    
    /**
     * 授权管理
     * -1:无任何授权
     * 0:试用期到期
     * 1:试用未到期
     * 2:授权信息有误
     * 3: 存在授权信息 到期
     * 4: 存在授权信息 未到期
     */
    protected function license(){
        $nvalid = 0;
        $result = array();
        $licenses = $this->license->find();
        //添加安装时间
        if(!$licenses['installdate']){
            $licenses['installdate'] = time();
            $licenses['license'] = $this->addTestLicense();
             $this->license->save($licenses); 
        }
        if($licenses){
            if($licenses['license']){
                 $data = json_decode(Encrypt::authcode($licenses['license'],'DECODE',md5('jrsa')),true);
                 if($data){
                     //机器码
                    $macinfo = new Macinfo();
                    $macinfo->init(PHP_OS);
                     if($data['mid'] != substr(strtoupper(md5(sha1($macinfo->mac_addr))),4,28)){
                         $result['status'] = 2;
                         return $result;
                     }
                     if($data['nvalid']){
                         $nvalid = 1;
                     }
                     if($nvalid){
                         $result['status'] = 4;
                     }else{
                        //不是永久授权
                        $valid = $data['valid'];
                        if($this->licenseStatic(strtotime($valid))){
                            $result['status'] = 4;
                        }else{
                            $result['status'] = 3;
                        }
                     }
                     $result['data'] = $data;
                 }else{
                     //授权信息有误
                     $result['status'] = 2;
                 }
            }else{
                if($this->licenseStatic($licenses['installdate'],30)){
                    $result['status'] = 1;
                }else{
                    $result['status'] = 0;
                }
                        
            }
        }else{
            //$result['status'] = -1;
            $result['status'] = 1;
        }
        return $result;
    }
    
    /**
     * License 是否过期
     * @param type $ldate
     * @param type $rdate
     */
    protected function licenseStatic($ldate,$day = 0){
        $now=strtotime (date("y-m-d")); //当前时间
        $date = strtotime(date('y-m-d',$ldate));
        $date = $date + (86400 * $day);
        if($now > $date){
            return 0;//过期
        }else{
            return 1;//未过期
        }
    }
    /**
     * Ajax方式返回数据到客户端
     * @param type $data 要返回的数据
     * @param type $type AJAX返回数据格式
     */
    protected function ajaxReturn($data, $type = ''){
        if (isset($data['url'])) {
            $data['referer'] = $data['url'];
            unset($data['url']);
        }
        //提示类型，success fail
        $data['state'] = $data['status'] ? "success" : "fail";
        if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
                exit($handler.'('.json_encode($data).');');
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            default     :
                // 用于扩展其他返回格式数据
                Hook::listen('ajax_return',$data);
        }
    }

    /**
     * 消息提示
     * @param type $message
     * @param type $jumpUrl
     * @param type $ajax
     */
    public function success($message='',$jumpUrl='',$ajax=false) {
        $text = "应用：" . GROUP_NAME . ",模块：" . MODULE_NAME . ",方法：" . ACTION_NAME . "<br>提示语：" . $message;
        $this->addLogs($text);
        parent::success($message, $jumpUrl, $ajax);
    }

    /**
     * 模板显示
     * @param type $templateFile 指定要调用的模板文件
     * @param type $charset 输出编码
     * @param type $contentType 输出类型
     * @param string $content 输出内容
     * 此方法作用在于实现后台模板直接存放在各自项目目录下。例如Admin项目的后台模板，直接存放在Admin/Tpl/目录下
     */
    public function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
        parent::display($templateFile, $charset, $contentType, $content);
    }

    //空操作
    public function _empty() {
        $this->error('该页面不存在！');
    }

    /**
     * 写入操作日志
     * @param String $info 操作说明
     * @param type $status 状态,1为写入，2为更新，3为删除
     * @param type $data 数据
     * @param type $options 条件
     */
    final public function addLogs($info, $status = 1, $data = array(), $options = array()) {
        if (!UID) {
            return false;
        }
        $data = serialize($data);
        $options = serialize($options);
        $get = $_SERVER['HTTP_REFERER'];
        $post = "";
        M("Operationlog")->add(array(
            "uid" => UID,
            "time" => date("Y-m-d H:i:s"),
            "ip" => get_client_ip(),
            "status" => $status,
            "info" => $info,
            "data" => $data,
            "options" => $options,
            "get" => $get,
            "post" => $post
        ));
    }
    
    /**
     * 是否安装检测
     */
    private function richterInstall() {
        $dbHost = C('DB_HOST');
        if (empty($dbHost) && !defined('INSTALL')) {
            return false;
        }
        return true;
    }
    
    /**
     * 添加测试授权
     */
    private function addTestLicense(){
        //机器码
        $macinfo = new Macinfo();
        $macinfo->init(PHP_OS);
        $data['valid'] = date('Y-m-d',time()+(30*24*60*60));
        $data['Unixclient'] = -1;
        $data['X86client'] = -1;
        $data['Unixapp'] = -1;
        $data['X86app'] = -1;
        $data['disk'] = -1;
        $data['lanfree'] = 1;
        $data['bmr'] = 1;
        $data['ndmp'] = 1;
        $data['Tape'] = 1;
        $data['replication'] = 1;
        $data['vtlDedupe'] = 1;
        $data['vtlReplicate'] = 1;
        $data['vtlDisk'] = -1;
        $data['License'] = "";
        $data['apps'] = '1,2,3,4';
        $data['mid'] = substr(strtoupper(md5(sha1($macinfo->mac_addr))),4,28);
        return Encrypt::authcode(json_encode($data),'',md5('jrsa'));
    }
}
