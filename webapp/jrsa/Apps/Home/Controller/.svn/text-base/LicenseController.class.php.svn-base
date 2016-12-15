<?php

/*
 * Jrsn Backup Management System
 * @Description   授权管理
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Controller;
use Hlib\Util\Macinfo;

class LicenseController extends CommonController {
    
    private $license;
    function _initialize() {
        parent::_initialize();
        $this->license = parent::license();
    }
    
    /**
     * 默认页面
     */
    public function index(){
        //机器码
        $macinfo = new Macinfo();
        $macinfo->init(PHP_OS);
        $linfo = M('license')->find();
        $ldate = $linfo['installdate'] + (86400 * 30);
        $overdue['date'] = date('Y-m-d',$ldate);
        if($this->license['data']['nvalid']){
            $overdue['code'] = "2";
            $overdue['product'] = $this->license['data'];
        }else{
            if($this->license['status'] == 1 || $this->license['status'] == 0){
                $overdue['code'] = "0";
                $overdue['product'] = $this->license['data'];
            }elseif ($this->license['status'] == 2) {
                 $overdue['code'] = "-1";
            }else{//授权用户
                $overdue['date'] = $this->license['data']['valid'];
                $overdue['code'] = "1";
                $overdue['product'] = $this->license['data'];
            }
        }
        //是否到期
        if($this->license['status'] == 0 || $this->license['status'] == 3){
            $overdue['status'] = L('license_overdue');
        }else{
            $overdue['status'] = L('license_noverdue');
        }
        
        $this->assign(array(
            'menuid'=>'license',
            'macid'=> substr(strtoupper(md5(sha1($macinfo->mac_addr))),4,28),
            'overdue'=>$overdue,
        ));
        $this->display();
    }
    
    /**
     * 导入授权
     */
    public function reg(){
        if(IS_POST){
            //更新数据
            $license = M("License"); 
            $list = $license->where('id=1')->find();
            $data['license'] =  I('post.License');
            if($list['license'] && $list['license'] == $data['license']){
                $this->error(L('license_repeat'));
            }
            $result = $license->where('id=1')->save($data);
            if($result){
                $this->success(L('success'),U('License/index'));
            }else{
                $this->error(L('error'));
            }
        }
        $this->assign("menuid","license_reg");
        $this->display();
    }
    
}
