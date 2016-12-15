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
use Think\Controller;

class VirtualController extends Controller {
    
    private $virtual;
    private $fileserver;
    private $job;
    private $fileset;
    private $schedule;//备份策略
	
    function _initialize() {
        //parent::_initialize();
        $this->virtual = M('virtual');
        $this->fileserver = M('fileserver');
        $this->fileset = M('fileset');
        $this->job = M('job');
        $this->schedule = M('schedule');
    }
	
	/**
	 * 华三虚拟化配置
	 */
	public function h3c(){
        $data = $this->virtual->where("vid='h3c'")->select();
		if($data){
			foreach($data as $k=>$v){
				$keys = unserialize($v['values']);
				if(is_array($keys)){
					unset($keys['id']);
					$data[$k] = array_merge($data[$k],$keys);
				}
			}
		}
        $this->assign(array(
            'data'=>$data,
            'menuid'=>'vir_h3c',
        ));
        $this->display('h3c');
    }
	
	public function addH3c(){
        if(IS_POST){
            $post = I('post.');
			$data['id'] = $post['id']; 
			$data['vid'] = 'h3c';
			$data['values'] = serialize($post); 
            $data['count'] = array('exp','count+1');
			if($data['id']){
                $result = $this->virtual->where(array('id'=>I('post.id')))->save($data); 
            }else{
            	$find = $this->virtual->where(array('vid'=>'h3c','name'=>I('post.name')))->find();
				if($find){
					$this->error("操作失败，名称重复.");
				}
				$data['name'] = $post['name']; 
                $result = $this->virtual->add($data); 
            }
            if($result !== false){
                $this->success(L('success'),U('Virtual/h3c'));
            }else{
                $this->error(L('error'));
            }
        }
		
        $data = $this->virtual->where(array('vid'=>'h3c','id'=>I('get.id')))->find();
		if($data){
			$keys = unserialize($data['values']);
			if(is_array($keys)){
				unset($keys['id']);
				$data = array_merge($data,$keys);
			}
		}
		$this->assign(array(
            'menuid'=>'vir_h3c',
            'data'=>  $data
        ));
        $this->display();
	}
	
	/**
	 * 删除数据中心
	 */
	public function delH3c(){
		if(IS_POST){
            $id = I('post.id');
            //是否存在对应任务
            //$job = $this->job->where(array('virId'=>$id))->find();
            if($job){
                $this->error('删除失败，请先删除对应任务');
            }
            $result = $this->virtual->where(array('id'=>$id))->delete();
            if($result){
                $this->success(L('success'));
            }else{
                $this->error(L('error'));
            }
        }
	}
	
	/**
	 * 华三虚拟化配置
	 */
	public function vm(){
        $data = $this->virtual->where("vid='vm'")->select();
		if($data){
			foreach($data as $k=>$v){
				$keys = unserialize($v['values']);
				if(is_array($keys)){
					unset($keys['id']);
					$data[$k] = array_merge($data[$k],$keys);
				}
			}
		}
        $this->assign(array(
            'data'=>$data,
            'menuid'=>'vir_vm',
        ));
        $this->display('vm');
    }
	
	public function addVm(){
        if(IS_POST){
            $post = I('post.');
			$data['id'] = $post['id']; 
			$data['vid'] = 'vm';
			$data['values'] = serialize($post); 
            $data['count'] = array('exp','count+1');
			if($data['id']){
                $result = $this->virtual->where(array('id'=>I('post.id')))->save($data); 
            }else{
            	$find = $this->virtual->where(array('vid'=>'vm','name'=>I('post.name')))->find();
				if($find){
					$this->error("操作失败，名称重复.");
				}
				$data['name'] = $post['name']; 
                $result = $this->virtual->add($data); 
            }
            if($result !== false){
                $this->success(L('success'),U('Virtual/vm'));
            }else{
                $this->error(L('error'));
            }
        }
		
        $data = $this->virtual->where(array('vid'=>'vm','id'=>I('get.id')))->find();
		if($data){
			$keys = unserialize($data['values']);
			if(is_array($keys)){
				unset($keys['id']);
				$data = array_merge($data,$keys);
			}
		}
		$this->assign(array(
            'menuid'=>'vir_vm',
            'data'=>  $data
        ));
        $this->display();
	}
	
	/**
	 * 删除数据中心
	 */
	public function delVm(){
		if(IS_POST){
            $id = I('post.id');
            //是否存在对应任务
            //$job = $this->job->where(array('virId'=>$id))->find();
            if($job){
                $this->error('删除失败，请先删除对应任务');
            }
            $result = $this->virtual->where(array('id'=>$id))->delete();
            if($result){
                $this->success(L('success'));
            }else{
                $this->error(L('error'));
            }
        }
	}
	
	/**
	 * ajax 方式获取虚拟机列表
	 */
	public function ajaxVirtualList(){
		$id  = I('post.id');
		$vid = I('post.vid');
		$vid = explode(',', $vid);
		if($id){
			$data = $this->virtual->where(array('vid'=>'h3c','id'=>$id))->find();
			if($data){
				$url = "/cas/casrs/host/";
				$result = $this->_initUrl($url, $data);
				$xml = simplexml_load_string($result);
				if($xml){
					$json = "[";
					foreach ($xml as $key => $value) {
						$hostId = $value->id;
						$hname = $value->name;
						$hip = $value->ip;
						$json .="{ id:$hostId, pId:0, name:'$hname - $hip',nocheck:true, open:true},";
						$xurl = "/cas/casrs/vm/vmList?hostId={$hostId}";
						$xresult = $this->_initUrl($xurl, $data);
						$xxml = simplexml_load_string($xresult);
						foreach ($xxml as $xkey => $xvalue) {
							$xname = $xvalue->name;
							$xdesc = $xvalue->osDesc;
							$xid   = $xvalue->id;
							$xip   = $xvalue->ipv4Attribute->ipv4->ipAddress;
							$check = '';
							if(is_array($vid)){
								if(in_array($xid, $vid)){
									$check = ',checked:true';
								}
							}
							$json .="{ id:1$xid, pId:$hostId, name:'$xname - $xip - $xdesc' $check},";
						}
					}
					$json = rtrim($json,",");
					$json .="]";
					exit($json);
				}else{
					exit("Connection error.");
				}
			}
		}
	}
	
	/**
	 * 备份操作，第三方调用
	 */
	public function backup(){
		$id = I('get.id');
		$vid = I('get.vid');
		if(!$id && !$vid){
			return "";
		}
		$job = $this->job->where("id=$id")->find();
		if($job){
			$data = $this->fileset->where("id={$job['fileSetId']}")->find();
			if($data){
				$tmp = explode('@@', $data['include']);
				$dvirtual = $this->virtual->where("id={$tmp[0]}")->find();
				$dfileserver = $this->fileserver->where("id={$tmp[1]}")->find();
				if($dvirtual && $dfileserver){
					$vids = $tmp[2];
					$arr_vid = explode(',', $vids);
					if(is_array($arr_vid)){
						if(in_array($vid, $arr_vid)){
							if(strstr($tmp[3],'Full')){//全备
								$backupType = 0;
							}elseif(strstr($tmp[3],'Differential')){//差异
								$backupType = 2;
							}else{
								$backupType = 1;
							}
							if(!$tmp[4]){
								$tmppath = '/vms/vmbackuptmp';
							}else{
								$tmppath = $tmp[4];
							}
							$msg = '';
							$now = time();
							$url = "/cas/casrs/vm/backUp";
							$files = "<vmBackUpParameter>"
								  ."<id>{$vid}</id>"
								  ."<storeMode>1</storeMode>"
								  ."<directory>{$dfileserver['path']}</directory>"
								  ."<targetAddr>{$dfileserver['ip']}</targetAddr>"
								  ."<userName>{$dfileserver['user']}</userName>"
								  ."<password>{$dfileserver['pwd']}</password>"
								  ."<type>{$dfileserver['type']}</type>"
								  ."<backupType>{$backupType}</backupType>"
								  ."<backupName>jrsa-{$vid}{$now}</backupName>"
								  ."<tmpDir>{$tmppath}</tmpDir>"
								  ."<keepTimes>10</keepTimes>"
								  ."<isMd5Check>0</isMd5Check>"
								  ."<isCompression>0</isCompression>"
								  ."</vmBackUpParameter>";
							$result = $this->_initPut($url, $dvirtual,$files);
							$xml = simplexml_load_string($result);
							$msgId = $xml->msgId; //1463966794454
							$msg .= $msgId;
							$this->job->save(array('msg'=>$msg,'id'=>$id));
							echo "vmID:$vid \nCreate backup task successfully.";
						}
					}
				}else{
					echo "err：数据中心或文件服务器不存在.";
				}
			}
		}
	}
	
	/**
	 * 查看任务状态
	 */
	public function getMsg(){
		$id = I('get.id');
		if(!$id){
			return "";
		}
		$job = $this->job->where("id=$id")->find();
		if($job){
			$data = $this->fileset->where("id={$job['fileSetId']}")->find();
			if($data){
				$tmp = explode('@@', $data['include']);
				$dvirtual = $this->virtual->where("id={$tmp[0]}")->find();
				$msgList = explode("#", $job['msg']);
				foreach($msgList as $k=>$v){
					$url = "/cas/casrs/message/$v";
					$results = $this->_initUrl($url, $dvirtual);
					$xml = simplexml_load_string($results);
					$msgId = $xml->msgId;
					$name = iconv('GBK','UTF-8',$xml->name);
					$targetId = $xml->targetId;
					$targetName = $xml->targetName;
					$detail = $xml->detail;
					$completed = $xml->completed;
					$result = $xml->result;
					$progress = $xml->progress;
					$failMsg = $xml->failMsg;
					$start = $xml->start;
					$complete = $xml->complete;
					$detail = $xml->detail;
					$smsgId = F('msgId');
					if($result==1){
						$result = "备份失败.";
					}elseif($result==2){
						$result = "部分成功.";
					}else{
						$result = "备份成功.";
					}
					if($smsgId){
						if($completed == 'true'){
							echo "complete:{$complete} ";
							echo "result:{$result} ";
							echo "Message:{$detail}";
						}else{
							echo "status:{$progress}%";
						}
					}else{
						echo "{$name} start:{$start}";
						F('msgId',1);
					}
				}
			}
		}
	}
	
	/**
	 * get 方式 访问接口 返回XML
	 */
	private function _initUrl($url,$data){
		$keys = unserialize($data['values']);
		if(is_array($keys)){
			$url = "http://" . $keys['ip'] . ":" .$keys['port'] . $url;
			$auth = $keys['user'] . ":" . $keys['pwd'];
			$result = $this->access($url,'',$auth,'GET');
			if($result){
				return $result;
			}else{
				return '';
			}
		}
		return '';
	}
	
	/**
	 * put 方式 访问接口 返回XML
	 */
	private function _initPut($url,$data,$files){
		$keys = unserialize($data['values']);
		if(is_array($keys)){
			$url = "http://" . $keys['ip'] . ":" .$keys['port'] . $url;
			$auth = $keys['user'] . ":" . $keys['pwd'];
			$result = $this->access($url,$files,$auth,'PUT');
			if($result){
				return $result;
			}else{
				return '';
			}
		}
		return '';
	}
	
	/**
	 * 访问接口
	 */
	public function access($url,$data,$auth='',$method='post'){
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		if($method != 'get'){
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/xml"));		
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}else{
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/xml'));
		}
		
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_USERPWD, $auth);
		
		$document = curl_exec($ch);
		
		if(!curl_errno($ch)){
			$info = curl_getinfo($ch);
		}else{
			exit("Connection error.");
			return 0;
		}
		curl_close($ch);
		return $document;
	}
}