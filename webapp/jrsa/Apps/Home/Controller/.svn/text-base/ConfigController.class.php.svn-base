<?php

/*
 * Jrsn Backup Management System
 * @Description   配置管理
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Controller;

class ConfigController extends CommonController {
    
    function _initialize() {
        parent::_initialize();
    }
    
    /**
     * 配置管理
     */
    public function index(){
        if(strtolower(PHP_OS) == 'linux' || strtolower(PHP_OS) == 'unix'){
            //提升权限
            _chmod('/etc/sysconfig/network-scripts/ifcfg-*', '776', false);
            $eth_array = array();
            $data = path_list('/etc/sysconfig/network-scripts');
            //获取网卡列表
            foreach ($data['filelist'] as $key=>$value){
                if(strstr($value['name'], 'ifcfg-')){
                    $sname = str_replace('ifcfg-', '', $value['name']);
                    if($sname == 'lo'){
                        continue;
                    }
                    $data = explode(PHP_EOL, read_file($value['path'].$value['name']));
                    $tmp = $this->_network_tools($data);
                    $tmp['name'] = $value['name'];
                    $tmp['path'] = $value['path'];
                    $tmp['sname'] = $sname;
                    $eth_array[] = $tmp;
                }
            }
            if(IS_POST){
                $data = I('post.');
                print_r($data);
                foreach ($data as $k=>$v){
                    //echo strpos($v, 'onboot');
                    if(!filter_var($v,FILTER_VALIDATE_IP) && !empty($v) && strstr($v, 'onboot')){
                        $this->error(L('data').L('illegal'));
                    }elseif(!empty($v)){
                        $name = explode('-', $k);
//                        $this->_update_network_conf('/etc/sysconfig/network-scripts/ifcfg-'.$name[1], $name[0], $v);
                        foreach ($eth_array as $key=>$value){
                            if($value['sname'] == $name[1]){
                                if($data[$k] != $value[$name[0]] ){
                                    $this->_update_network_conf('/etc/sysconfig/network-scripts/ifcfg-'.$name[1], $name[0], $v);
                                }
                                continue;
                            }
                        }
                    }
                }
                _exec("sudo service network restart");
                $this->success("success");
            }
            $this->assign(array(
                'eth_array'=>$this->_array_sort($eth_array, 'sname'),
            ));
        }
        $this->assign("menuid","ip");
        $this->display();
    }
    
    /**
     * 服务器关机重启
     */
    public function system(){
        if(IS_POST){
            $sys = I('post.sty');
            if($sys == 2){
                _exec("sudo sh /var/www/html/webapp/sh/poweroff.sh");
                $this->success(L('success'));
            }elseif ($sys == 1) {
                _exec("sudo sh /var/www/html/webapp/sh/reboot.sh");
                $this->success(L('success'));
            }
        }
        $this->assign("menuid","system");
        $this->display();
    }
    
    private function _network_tools($arr){
        $network['ipaddr'] = '';
        $network['netmask'] = '';
        $network['gateway'] = '';
        $network['dns1'] = '';
        $network['onboot'] = 'no';
        $network['bootproto'] = 'static';
        if(is_array($arr)){
            foreach ($arr as $k=>$v){
                $v = str_replace('"', '', $v);
                if(strstr($v, 'IPADDR=')){
                    $network['ipaddr'] = str_replace('IPADDR=', '', $v);
                }elseif(strstr($v, 'NETMASK=')){
                    $network['netmask'] = str_replace('NETMASK=', '', $v);
                }elseif(strstr($v, 'GATEWAY=')){
                    $network['gateway'] = str_replace('GATEWAY=', '', $v);
                }elseif(strstr($v, 'DNS1=')){
                    $network['dns1'] = str_replace('DNS1=', '', $v);
                }elseif(strstr($v, 'ONBOOT=')){
                    $network['onboot'] = str_replace('ONBOOT=', '', $v);
                }elseif(strstr($v, 'BOOTPROTO=')){
                    $network['bootproto'] = str_replace('BOOTPROTO=', '', $v);
                }
            }
        }
        return $network;
    }
    
    /**
     * 排序
     * @param type $array
     * @param type $key
     * @param type $order
     * @return type
     */
    private function _array_sort($array,$key,$order="asc"){
        $arr_nums = array();
        foreach ($array as $k=>$v){
            $arr_nums[$k] = $v[$key];
        }
        if($order == 'asc'){
            asort($arr_nums);
        }else{
            arsort($arr_nums);
        }
        foreach($arr_nums as $k=>$v){
            $arr[$k]=$array[$k];
        }
        return $arr;
    }
    /**
     * 修改配置文件
     * @param type $path 文件路径
     * @param type $item 修改条目
     * @param type $values 新的值
     */
    private function _update_network_conf($path,$item,$values){
        $data = explode(PHP_EOL, read_file($path));
        $flag = false;
        foreach ($data as $k=>$v){
            if(strstr($v, strtoupper($item).'=')){
                $flag = true;
                $data[$k] = strtoupper($item).'="'.$values.'"';
            }
        }
        if(!$flag){
            $data[count($data)+1] = strtoupper($item).'="'.$values.'"';
        }
        $content = implode(PHP_EOL,$data);
        saveFile($path, $content);
    }
    
    /**
     * 提权
     */
    private function _chmod_files(){
        //if(!S('_chmod_ifcfg')){
            _exec("sudo chmod 777 /etc/sysconfig/network-scripts/ifcfg-*");
        //}
        //S('_chmod_ifcfg',"1");
    }
}
