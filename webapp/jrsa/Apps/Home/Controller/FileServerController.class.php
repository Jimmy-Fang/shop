<?php

/**
 * 
 * Jrsa Backup Management System
 * 
 * @Description    虚拟化配置
 * @version        v1.0
 * @author         Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Controller;

class FileServerController extends CommonController {
    
    private $fileserver;
    private $job;
	
    function _initialize() {
        parent::_initialize();
        $this->fileserver = M('fileserver');
        $this->job = M('job');
    }
	
	/**
	 * 华三虚拟化配置
	 */
	public function server(){
        $data = $this->fileserver->select();
        $this->assign(array(
            'data'=>$data,
            'menuid'=>'file_server',
        ));
        $this->display();
    }
	
	public function addServer(){
        if(IS_POST){
            $data = I('post.');
            $data['count'] = array('exp','count+1');
			if($data['id']){
				unset($data['name']);
                $result = $this->fileserver->where(array('id'=>I('post.id')))->save($data); 
            }else{
            	$find = $this->fileserver->where(array('name'=>$data['name']))->find();
				if($find){
					$this->error("操作失败，名称重复.");
				}
                $result = $this->fileserver->add($data); 
            }
            if($result !== false){
                $this->success(L('success'),U('FileServer/server'));
            }else{
                $this->error(L('error'));
            }
				
        }
		
        $data = $this->fileserver->where(array('id'=>I('get.id')))->find();
		if($data){
			$keys = unserialize($data['values']);
			if(is_array($keys)){
				$data = array_merge($data,$keys);
			}
		}
		$this->assign(array(
            'menuid'=>'file_server',
            'data'=>  $data
        ));
        $this->display();
	}
	
	/**
	 * 删除数据中心
	 */
	public function delServer(){
		if(IS_POST){
            $id = I('post.id');
            //是否存在对应任务
            $job = $this->job->where(array('virId'=>$id))->find();
            if($job){
                $this->error('删除失败，请先删除对应任务');
            }
            $result = $this->fileserver->where(array('id'=>$id))->delete();
            if($result){
                $this->success(L('success'));
            }else{
                $this->error(L('error'));
            }
        }
	}
}