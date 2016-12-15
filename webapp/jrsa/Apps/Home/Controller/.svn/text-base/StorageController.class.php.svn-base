<?php

/* 
 * Jrsn Backup Management System
 * @Description   存储功能 
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Controller;

class StorageController extends CommonController {
    
    private $_nfs;
    
    function _initialize() {
        parent::_initialize();
        $this->_nfs = M('nfs');
    }
    
    public function index(){
        if(!IS_WIN){
            
        }
        $this->assign(array(
            'menuid'=>'stor_index',
        ));
        $this->display();
    }
    
    /**
     * 配置NFS选项
     */
    public function nfs(){
        if(!IS_WIN){
            $data = $this->_nfs->select();
            $nfs = array();
            foreach ($data as $k=>$v){
                $nfs[$k] = $this->_nfsdata($v);
                $nfs[$k]['maps'] = $nfs[$k]['squash'];
                $nfs[$k]['others'] = $nfs[$k]['sync'] ;
            }
            $this->assign(array(
                'data'=>$nfs,
                'menuid'=>'stor_nfs',
            ));
            $this->display();
        }else{
            $this->error('不支持windows系统');
        }
    }
    
    /**
     * 添加规则
     */
    public function addnfs(){
        if(IS_POST){
            $data = I('post.');
            if($data['mapsid']){
                $data['maps']  = $data['maps'] . '@' . $data['mapsid'] ;
            }
            $_data['output'] = $data['output'];
            $_data['count'] = array('exp','count+1');
            $_data['content'] = $data['ip'] . '(' . $data['access'] .'|' . $data['squash']  .'|' . $data['sync'] ;
            if($data['id']){
                $result = $this->_nfs->where(array('id'=>$data['id']))->save($_data); 
            }else{
                $result = $this->_nfs->add($_data);
            }
            if($result){
                //修改同时修改其他的数据的output值
//                $output['output'] = $data['output'];
//                $this->_nfs->where(array('count'=>'1'))->save($output);
                
                $this->_nfsConf();
                $this->success(L('success'),U('Storage/nfs'));
            }else{
                $this->error(L('error'));
            }
        }
        $nfs = $this->_nfs->where(array('id'=>I('get.id')))->find();
        $nfs = $this->_nfsdata($nfs);
        $this->assign(array(
            'menuid'=>'stor_nfs',
            'data'=>$nfs,
        ));
        $this->display();
    }
    
    /**
     * 删除NFS 客户端记录
     */
    public function delNfs(){
        if(IS_POST){
            $data = I('post.');
            $result = $this->_nfs->where(array('id'=>$data['id']))->delete();
            if($result){
                $this->_nfsConf();
                $this->success(L('success'));
            }else{
                $this->error(L('error'));
            }
        }
    }
    
    /**
     * 生成nfs解析数据
     * @param type $data
     */
    private function _nfsdata($nfs){
        $data = array();
        if($nfs){
            $data['id'] = $nfs['id'];
            $data['output'] = $nfs['output'];
            $iptmp = explode('(', $nfs['content']);
            $info = explode('|', $iptmp[1]);
            $data['ip'] = $iptmp[0];
            $data['access'] = $info[0];
            
            $_data['content'] = $data['ip'] . '(' . $data['access'] .'|' . $data['squash']   .'|' . $data['sync'] ;
            $data['squash'] = $info[1];
            $data['sync'] = $info[2];
        }
        return $data;
    }
    
    /**
     * 生成Conf文件
     */
    private function _nfsConf(){
        //<输出目录> [客户端1 选项（访问权限,用户映射,其他）] [客户端2 选项（访问权限,用户映射,其他）]
        $data = $this->_nfs->select();
        $str = '';
        $client = '';
        $count = count($data) - 1;
        foreach ($data as $k=>$v){
//            if(strstr($v['content'], '*')){
//                $str = $v['output'] .' ' . str_replace(array('|','@'), array(' ','='), $v['content']) . ')';
//                break;
//            }
//            $client .= ' ' . str_replace(array('|','@'), array(' ','='), $v['content']) . ')';
//            if($count === $k){//最后一次循环
//                $str = $v['output'] . $client;
//            }
           $str .= $v['output'] .' ' . str_replace(array('|','@'), array(',','='), $v['content']) . ") \r\n";
        }
        //_exec("sudo echo $str > /etc/exports");
        //saveFile("/var/www/html/webapp/exports", $str);
        saveFile("/etc/exports.d/nfs.exports", $str);
        _exec("sudo exportfs -r");
    }
}

