<?php

/*
 * Jrsn Backup Management System
 * @Description  企业级备份软件  
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

namespace Home\Controller;

use  \Hlib\Util\Encrypt;

class EnterpriseController extends CommonController
{

    private $dirDirector; //备份服务器Msc
    private $device;//备份设备管理
    private $dirStorage;//备份介质
    private $pool;
    private $client;
    private $fileset;
    private $schedule;//备份策略
    private $job;//备份任务
    private $license;//授权
    private $virtual;//数据中心
    private $fileserver;//文件服务器
    private $monitor = "/opt/rongan/monitor/clocking.sh";


    function _initialize()
    {
        parent::_initialize();
        $this->dirDirector = M('dirdirector');
        $this->device = M('device');
        $this->dirStorage = M('dirstorage');
        $this->pool = M('pool');
        $this->client = M('client');
        $this->fileset = M('fileset');
        $this->schedule = M('schedule');
        $this->job = M('job');
        $this->license = M('license');
        $this->virtual = M('virtual');
        $this->fileserver = M('fileserver');
    }

    /**
     * 首页
     */
    public function indexs()
    {
        $data['id'] = S('MachineID');
        if (!$data['id']) {
            //获取机器码
            @exec("sudo rongan start", $restarall);
            if ($restarall) {
                foreach ($restarall as $item) {
                    if (strstr($item, '[4;33m')) {
                        $tmp = explode('[4;33m', $item);
                        $id = str_replace('[0m', '', $tmp[1]);
                        S('MachineID', $id);
                    }
                }
            }
        }
        //首先判断是否有上传文件夹权限
        $statusFiles = file_mode_info("/opt/rongan/etc/license/");
        //判断文件是否存在
        $filesExists = is_file('/opt/rongan/etc/license/authorized_key');
        if ($filesExists) {
            $data['files'] = "授权文件已经存在，上传将覆盖！";
        }
        if ($statusFiles == 1) {
            $data['info'] = "/opt/rongan/etc/license/ 目录 无写入权限！";
        } else {
            if (IS_POST) {
                $upload = new \Think\Upload();
                $upload->maxSize = 64;// 设置附件上传大小
                $upload->minSize = 64;// 设置附件上传大小
                //$upload->exts      =     array('authorized_key');// 设置附件上传类型
                $upload->rootPath = '/opt/rongan/etc/license/'; // 设置附件上传根目录
                $upload->autoSub = false; // 不自动创建目录
                $upload->saveName = 'hink';
                $upload->saveExt = 'authorized_key';
                $upload->replace = true;
                // 上传文件 
                $info = $upload->upload();
                if (!$info) {// 上传错误提示错误信息
                    $this->error($upload->getError());
                } else {// 上传成功
                    $shell = "sudo mv -f /opt/rongan/etc/license/hink.authorized_key /opt/rongan/etc/license/authorized_key";
                    @exec($shell);
                    $this->success('上传成功！');
                }
            }
        }
        $this->assign(array(
            'menuid' => 'ep_index',
            'data' => $data,
        ));
        $this->display();
    }

    /**
     * 默认控制器  备份服务器配置
     */
    public function index()
    {
        $data = $this->dirDirector->find();
        if (IS_POST) {
            $data = I('post.');
            $data['count'] = array('exp', 'count+1');
            if ($data['optimizeForSpeed']) {
                $data['optimizeForSpeed'] = 'yes';
            } else {
                $data['optimizeForSpeed'] = 'no';
            }
            if ($data['TLSEnable']) {
                $data['TLSEnable'] = 'yes';
            } else {
                $data['TLSEnable'] = 'no';
            }
            if ($data['TLSRequire']) {
                $data['TLSRequire'] = 'yes';
            } else {
                $data['TLSRequire'] = 'no';
            }
            if ($data['TLSVerifyPeer']) {
                $data['TLSVerifyPeer'] = 'yes';
            } else {
                $data['TLSVerifyPeer'] = 'no';
            }
            if ($data['auditing']) {
                $data['auditing'] = 'yes';
            } else {
                $data['auditing'] = 'no';
            }
            if ($this->dirDirector->where('id=1')->save($data)) {
                $add = array("password" => '"dirpassword"', 'messages' => 'Daemon', 'queryFile' => '"/opt/rongan/etc/query.sql"', 'Plugin Directory' => '/opt/rongan/plugin');
                //$tls = $this->tlsConfTools($data);
                $ckey = array("maxConcurrentJobs" => "Maximum Concurrent Jobs", 'dirPort' => 'dir Port', 'fdConnTimeout' => 'FD Connect Timeout', 'sdConnTimeout' => 'SD Connect Timeout', 'optimizeForSpeed' => 'Optimize For Speed');
                //$ckey = array_merge($ckey,$tls['ckey']);
                $filter = 'id,password,messagesId,workingDir,queryFile,heartbeatInterval,pidDir,dirAddress,dirSourceAddress,statisticsRetention,verId,maxConsoleConn,optimizeForSize,EncryptionKey,NDMPSnooping,NDMPLoglevel,count';
                //$filter .= $tls['filter'];
                $this->_saveConf('dirDirector', $filter, $add, 'director', $ckey);
                //同时生成其他配置文件 fd_dir.conf、sd_dir.conf、fd_message.conf、sd_message.conf
                createConfig("fd_dir", array("Name" => I('post.name'), "Password" => "\"fdpassword\""), 'Director');
                createConfig("sd_dir", array("Name" => I('post.name'), "Password" => "\"sdpassword\""), 'Director');
                createConfig("fd_message", array("Name" => "Standard", "director" => I('post.name') . " = all, !skipped, !restored"), 'Messages');
                createConfig("sd_message", array("Name" => "Standard", "director" => I('post.name') . " = all"), 'Messages');
                $address = @exec("/sbin/ifconfig -a|grep inet|grep -v 127.0.0.1|grep -v inet6|awk '{print $2}'|tr -d 'addr:'", $return);//IP地址 192.168.1.1
                createConfig("con-dir", array("Name" => I('post.name'), "DIRport " => "9101", "address" => $address, "Password" => "\"sdpassword\""), 'Director');
                createConfig("console", array("Name " => I('post.name'), "DIRport " => "9101", "address " => $address, "Password  " => "\"dirpassword\""), 'Director');
                //_exec("sudo /opt/rongan/scripts/loadconfig");
                $this->success(L('success'));
            } else {
                $this->error(l('error'));
            }
        }
        $this->assign(array(
            'data' => $data,
            'menuid' => 'ep_server',
        ));
        $this->assign('menuid', 'ep_server');
        $this->display('dirdirector');
    }

    /**
     * 检查是否开启了TLS功能，并返回数组
     * @param type $data
     */
    public function tlsConfTools($data)
    {
        $tlsarr = array();
        //开启TLS功能
        if ($data['TLSEnable']) {
            if ($data['TLSEnable'] === 'yes') {
                $tlsarr['filter'] = '';
                $tlsarr['ckey'] = array("TLSEnable" => "TLS Enable", "TLSRequire" => "TLS Require", "TLSVerifyPeer" => "TLS Verify Peer", "TLSCertificate" => "TLS Certificate", "TLSKey" => "TLS Key", "TLSCACertificateFile" => "TLS CA Certificate File");
            } else {
                $tlsarr['ckey'] = '';
                $tlsarr['filter'] = ",TLSEnable,TLSRequire,TLSVerifyPeer,TLSCertificate,TLSKey,TLSCACertificateFile";
            }
        }
        return $tlsarr;
    }

    /**
     *
     * ############################
     * 已废弃
     * ############################
     * 设备管理 列表
     */
    public function equipment()
    {
        $data = $this->device->where('state=0')->select();
        $this->assign(array(
            'menuid' => 'ep_equipment',
            'data' => $data,
        ));
        $this->display();
    }

    /**
     *
     * ############################
     * 已废弃
     * ############################
     *
     * 添加设备
     */
    public function addEquipment()
    {

        //用于检查是磁带是否被使用
        $tapedata = $this->device->where(array('mediaType' => 'tape', 'state' => 0))->select();
        $tapesdata = $this->device->where(array('mediaType' => 'tapes', 'state' => 0))->select();
        if (IS_POST) {
            $data = I('post.');
            $data['mediaType'] = $data['deviceType'];
            $data['count'] = array('exp', 'count+1');
            if ($this->device->create($data)) {
                if ($data['id']) {
                    unset($data['deviceType']);
                    unset($data['mediaType']);
                    unset($data['archiveDevice']);
                    unset($data['archiveDevices']);
                    unset($data['archiveDevicef']);
                    $result = $this->device->where(array('id' => I('post.id')))->save($data);
                } else {
                    //判断磁带机或者磁带库是否被使用
                    if ($data['deviceType'] === 'tape') {
                        //磁带机
                        $tapeinfo = $tapedata;
                        $tmps = explode("@", $data['archiveDevice']);
                        $data['changerDevice'] = $tmps[1];
                        $data['archiveDevice'] = $tmps[0];
                    } elseif ($data['deviceType'] === 'tapes') {
                        $tapeinfo = $tapesdata;
                        $tmps = explode("@", $data['archiveDevices']);
                        $data['changerDevice'] = $tmps[1];
                        $data['archiveDevice'] = $tmps[0];
                    } else {
                        $data['changerDevice'] = 'file';
                        $data['archiveDevice'] = $data['archiveDevicef'];
                    }
                    foreach ($tapeinfo as $key => $value) {
                        if ($data['archiveDevice'] === $value['archiveDevice']) {
                            $this->error("该设备已被使用，如需修改请删除后重新添加");
                        }
                    }
                    unset($data['archiveDevices']);
                    unset($data['archiveDevicef']);
                    $result = $this->device->add($data);
                }
                if ($result) {
                    //修改成功 生成配置文件
                    $this->_factoryDevice();
                    $this->_factoryStorage();
                    $this->success(L('success'), U('Enterprise/equipment'));
                } else {
                    $this->error(L('error'));
                }
            } else {
                $error = $this->device->getError();
                $this->error($error ? $error : L('error'));
            }
        } else {
            $licenseData = $this->getLicenseData();
            $ddata = $this->device->where(array('id' => I('get.id'), 'state' => 0))->find();
            @exec("sudo lsscsi -g", $mediaType);
            $tapeList = array();
            $tapes = array();
            $tmpi = 0;
            $tmptape = 0;
            $tmpt = 1;
            $mediumxc = 0;
            foreach ($mediaType as $item) {
                if (preg_match("/^]\s*tape|mediumx|tape/", ltrim($item))) {
                    $item5 = null;
                    if (preg_match("/\/[a-z]+\/\w+/", ltrim($item), $output)) {
                        $item5 = $output;
                    }
                    $tmp = array_values(array_filter(explode(" ", ltrim($item)), '_filterEmpty'));
                    if (count($tmp) === 8) {
                        $tmp[3] = $tmp[3] . ' ' . $tmp[4];
                        $tmp[4] = $tmp[5];
                        $tmp[5] = $tmp[6];
                        $tmp[6] = $tmp[7];
                        unset($tmp[7]);
                    }
                    $tmp[5] = $item5[0];
                    //磁带
                    $tapes[$tmptape] = $tmp;
                    //磁带机 磁带 二者 取一
                    if ($tmp[1] === 'mediumx') {//磁带库
                        $mediumxc++;
                        $tapeList[$tmpi] = $tmp;
                        $tmpi++;
                        $tmpt = 1;
                    } else {
                        if ($tmpt) {
                            //$tapeList[$tmpi-1][5] = $tmp[5];
                            $tapeList[$tmpi - 1][5] = $item5[0];
                        }
                        $tmpt = 0;
                    }
                    $tmptape++;
                }
            }

            if ($mediumxc) {
                //存在磁带机
                foreach ($tapeList as $k => $item) {
                    if (count($item) < 5) {
                        unset($tapeList[$k]);
                    }
                }
            }
            $ttapes[0] = $tapes[0];
            //F('tapedata',NULL);
            F('tapesdata', $tapeList);
            F('tapedata', $ttapes);
            $this->assign(array(
                'menuid' => 'ep_equipment',
                'data' => $ddata,
                'tapeList' => $tapeList,
                'tapes' => $ttapes,
                'tapedata' => $tapedata,
                'tapesdata' => $tapesdata,
                'tape' => isset($licenseData['Tape']),
            ));
            $this->display();
        }
    }

    /**
     * 加载和卸载设备
     */
    public function mountdrive()
    {
        if (IS_POST) {
            $name = I('post.name');
            $type = I('post.type');
            //echo "sudo sh /opt/rongan/scripts/{$type} {$name}";
            @exec("sudo sh /opt/rongan/scripts/{$type} {$name}", $jobLog);
            $this->success(L('success'));
        }
    }

    /**
     *
     * ############################
     * 已废弃
     * ############################
     *
     * 删除设备 不是真正删除
     */
    public function delEquipment()
    {
        if (IS_POST) {
            $id = I('post.id');
            if ($id == 1) {
                $this->error(L('default_not_delete'));
            }
            $data['state'] = 1;
            $result = $this->device->where(array('id' => $id))->save($data);
            //$this->dirStorage->where(array('deviceId'=>$id))->save($data);//介质
            $this->dirStorage->where(array('deviceId' => $id))->delete();  //删除介质
            $tmp = $this->dirStorage->where(array('deviceId' => $id))->find();//介质数据
            if ($tmp) {
                $this->job->where(array('storageId' => $tmp['id']))->save($data);
                $this->_factoryStorage();
            }
            if ($result) {
//                 //修改成功 生成配置文件
//                $add = array("labelMedia"=>'yes','random Access'=>'yes','automaticMount'=>'yes','removableMedia'=>'no','alwaysOpen'=>'no');
//                $ckey = array("mediaType"=>"Media Type",'archiveDevice'=>'Archive Device');
//                $filter = 'count,id,changerCommand,alertCommand,driveIndex,autoselect,maxConcurrentJobs,maxChangerWait,maxRewindWait,maxOpenWait,alwaysOpen,volumePollInterval,closeonPoll,removeableMedia,randomAccess,requiresMount,mountPoint,mountCommand,unmountCommand,labelMedia,automaticMount,blockChecksum,minBlockSize,labelBlockSize,hardwareEndofMedium,firstForwardSpaceFile,useMTIOCGET,bsFatEOM,twoEOF,backwardSpaceRecord,backwardSpaceFile,forwardSpaceRecord,forwardSpaceFile,offlineOnUnmount,noRewindOnClose,driveCryptoEnabled,queryCryptoStatus,maxVolumeSize,maxFileSize,blockPositioning,maxNetworkBufferSize,maxSpoolSize,spoolDirectory,autoDeflate,autoDeflateAlgorithm,autoDeflateLevel,autoInflate,autochanger,changerDevice,deviceType';
//                 createDeviceConf('device',$this->_getConf('device',$filter,$add,'device',$ckey));
//                _exec("sudo /opt/rongan/scripts/loadconfig");
                //修改成功 生成配置文件
                $this->_factoryDevice();
                $this->success(L('success'));
            } else {
                $this->error(L('error'));
            }
        }
    }

    /**
     * 备份介质管理
     */
    public function medium()
    {
        $data = $this->dirStorage->where(array('state' => '0'))->select();
        $licenseData = $this->getLicenseData();
        $this->assign(array(
            'menuid' => 'ep_medium',
            'data' => $data,
            'tape' => $licenseData['Tape'],
        ));
        $this->display();
    }

    /**
     * 添加备份介质
     */
    public function addMedium()
    {
        if (IS_POST) {
            //处理客户端
            $data = I('post.');
            $cid = $data['cid'] ? $data['cid'] : 0;
            $deviceId = 0;
            if ($data['TLSEnable']) {
                $data['TLSEnable'] = 'yes';
            } else {
                $data['TLSEnable'] = 'no';
            }
            if ($data['TLSRequire']) {
                $data['TLSRequire'] = 'yes';
            } else {
                $data['TLSRequire'] = 'no';
            }
            if ($data['TLSVerifyPeer']) {
                $data['TLSVerifyPeer'] = 'yes';
            } else {
                $data['TLSVerifyPeer'] = 'no';
            }
            if ($data['tapetype'] == 'runstor') {//changerCommand 不需要参数
                $deviceArr['vtltype'] = '1';
            } else {
                $deviceArr['vtltype'] = '';
            }
            if ($data['deviceId'] || $cid) {//本机
                $deviceArr['id'] = ($data['deviceId'] > $cid) ? $data['deviceId'] : $cid;
                if ($data['deviceType']) {
                    $deviceArr['mediaType'] = $data['deviceType'];
                    $deviceArr['deviceType'] = $data['deviceType'];
                }
                $deviceArr['maxConcurrentJobs'] = $data['maxConcurrentJobs'];
                $deviceArr['count'] = array('exp', 'count+1');
                //自动生成name
                $deviceArr['name'] = $data['name'] . '_device';
                $deviceArr['minBlockSize'] = $data['minBlockSize'];
                $deviceArr['maxBlockSize'] = $data['minBlockSize'];
                $deviceArr['maxFileSize'] = $data['maxFileSize'];
                $deviceArr['maxSpoolSize'] = $data['maxSpoolSize'];
                $deviceArr['maxNetworkBufferSize'] = $data['maxNetworkBufferSize'];
                $deviceArr['changerCommand'] = $data['changerCommand'];
                if ($deviceArr['id'] > 0) {
                    $devices = $this->device->where(array('id' => $deviceArr['id']))->find();
                    if ($devices['deviceType'] == 'tapes') {
                        $deviceArr['deviceType'] == 'tape';
                        $deviceArr['changerDevice'] = $data['changerDevice'];
                        $deviceArr['archiveDevice'] = $data['archiveDevicetape'];
                        $deviceArr['changerCommand'] = $data['changerCommand'];
                    }
                    $result = $this->device->where(array('id' => $deviceArr['id']))->save($deviceArr);
                } else {
                    if ($data['deviceType'] == 'tapes') {
                        $deviceArr['deviceType'] == 'tape';
                        $deviceArr['changerDevice'] = $data['changerDevice'];
                        $deviceArr['archiveDevice'] = $data['archiveDevicetape'];
                        $deviceArr['changerCommand'] = $data['changerCommand'];
                    } else {
                        $deviceArr['changerDevice'] = 'file';
                        $deviceArr['archiveDevice'] = $data['archiveDevicef'];
                    }
                    $deviceId = $result = $this->device->add($deviceArr);
                }
                if (!$result) {
                    $this->error(L('error'));
                }
            } else {
                $storage = $this->dirStorage->where(array('id' => $data['id']))->find();
                if ($storage['deviceId']) {
                    $this->device->where(array('id' => $storage['deviceId']))->delete();
                    $storage = $this->dirStorage->where(array('id' => $data['id']))->save(array('deviceId' => '0'));
                }
            }
            //修改成功 生成配置文件
            $this->_factoryDevice();

            //介质
            if ($deviceId > $data['deviceId']) {
                $data['deviceId'] = $deviceId;
            }
            $cid = ($data['deviceId'] > $cid) ? $data['deviceId'] : $cid;
            if ($cid > 0) {
                $deviceInfo = $this->device->where(array('id' => $cid))->find();
                $storageArr['mediaType'] = $deviceInfo['deviceType'];
                $storageArr['deviceId'] = $cid;
            } else {
                $storageArr['deviceId'] = 0;
                if ($data['deviceType']) {
                    $storageArr['mediaType'] = $data['deviceType'];
                }
            }
            $storageArr['count'] = array('exp', 'count+1');
            $storageArr['allowCompression'] = $data['allowCompression'];
            $storageArr['maxConcurrentJobs'] = $data['maxConcurrentJobs'];
            if (!$data['allowCompression']) {
                $storageArr['allowCompression'] = 0;
            }
            $storageArr['name'] = $data['name'];
            $storageArr['sdPort'] = $data['sdPort'];
            $storageArr['address'] = $data['address'];
            $storageArr['description'] = $data['description'];

            $storageArr['TLSEnable'] = $data['TLSEnable'];
            $storageArr['TLSRequire'] = $data['TLSRequire'];
            $storageArr['TLSVerifyPeer'] = $data['TLSVerifyPeer'];
            $storageArr['TLSCertificate'] = $data['TLSCertificate'];
            $storageArr['TLSKey'] = $data['TLSKey'];
            $storageArr['TLSCACertificateFile'] = $data['TLSCACertificateFile'];
            if ($data['id']) {
                $result = $this->dirStorage->where(array('id' => $data['id']))->save($storageArr);
            } else {
                $result = $this->dirStorage->add($storageArr);
            }
            if ($result) {
                $this->_factoryStorage();
                $this->success(L('success'), U('Enterprise/medium'));
            } else {
                $this->error(L('error'));
            }
        }
        $data = $this->dirStorage->where(array('id' => I('get.id'), 'state' => 0))->find();
        $cdata = $this->device->where(array('id' => $data['deviceId']))->find();

        //设备部分
        $licenseData = $this->getLicenseData();
        $ddata = $this->device->where(array('id' => I('get.id'), 'state' => 0))->find();

        @exec("sudo lsscsi -g", $mediaType);
        $tapes = array();
        foreach ($mediaType as $item) {
            if (preg_match("/^]\s*tape|mediumx|tape/", ltrim($item))) {
                //$tmp = array_values(array_filter(explode(" ", ltrim($item)),'_filterEmpty'));
                $tapes[] = $item;
            }
        }
        $this->assign(array(
            'menuid' => 'ep_medium',
            'data' => $data,
            'cdata' => $cdata,
            'ddata' => $ddata,
            'tapelist' => $tapes,
            'tape' => isset($licenseData['Tape']),
        ));
        $this->display();
    }

    /**
     * 删除备份介质
     */
    public function delMedium()
    {
        if (IS_POST) {
            $id = I('post.id');
            if ($id == 1) {
                $this->error(L('default_not_delete'));
            }
            //是否存在对应任务
            $job = $this->pool->where(array('atorageId' => $id))->find();
            if ($job) {
                $this->error('删除失败，请先删除对应备份池。');
            }
            $storage = $this->dirStorage->where(array('id' => $id))->find();
            $result = $this->dirStorage->where(array('id' => $id))->delete();
            $this->job->where(array('storageId' => $id))->find();
            if ($storage['deviceId']) {
                $this->device->where(array('id' => $storage['deviceId']))->delete();
                $storage = $this->dirStorage->where(array('id' => $data['id']))->save(array('deviceId' => '0'));
            }
            //修改成功 生成配置文件
            $this->_factoryDevice();
            if ($result) {
                $this->_factoryStorage();
                $this->success(L('success'));
            } else {
                $this->error(L('error'));
            }
        }
    }

    /**
     * 备份池管理
     */
    public function pool()
    {
        $data = $this->pool->where('state=0')->select();
        foreach ($data as $k => $v) {
            $sdata = $this->dirStorage->where(array('id' => $v['atorageId']))->find();
            $data[$k]['atoragename'] = $sdata['name'];
        }
        $this->assign(array(
            'menuid' => 'ep_pool',
            'data' => $data,
        ));
        $this->display();
    }

    /**
     * 添加备份池
     */
    public function addPool()
    {
        if (IS_POST) {
            if ($this->pool->create()) {
                //授权判断
                $data = I('post.');
                $data['count'] = array('exp', 'count+1');
                $data['jobRetention'] = $data['fileRetention'];
                if ($data['id']) {
                    $result = $this->pool->where(array('id' => I('post.id')))->save($data);
                } else {
                    if ($data['types']) {//磁盘
                        $maxVolumes = $data['maxVolumes'];//卷数
                        $maxVolumeBytes = $data['maxVolumeBytes'];//卷容量
                        $volume = ($maxVolumes * $maxVolumeBytes) / 1024; //单位换算
                        if (!$this->checkLicense('disk', $volume)) {
                            $this->error(L('verify_license_than'));
                        }
                        $data['labelFormat'] = 'pool_' . $data['name'];
                    } else {
                        $data['types'] = 0;
                    }
                    $result = $this->pool->add($data);
                }
                if ($result) {
                    $this->_factoryPool();
                    $this->success(L('success'), U('Enterprise/pool'));
                } else {
                    $this->error(L('verify_name_one'));
                }
            } else {
                $error = $this->pool->getError();
                $this->error($error ? $error : L('error'));
            }
        }
        $this->assign(array(
            'menuid' => 'ep_pool',
            'storage' => $this->dirStorage->where(array('state' => '0'))->select(),
            'data' => $this->pool->where(array('id' => I('get.id'), 'state' => 0))->find(),
        ));
        $this->display();
    }

    /**
     * 判断是否存在相互复制
     */
    public function checCopyPool()
    {
        if (IS_POST) {
            $data = I('post.');
            $pres = $data['preid'];//源
            $posts = $data['postid'];//目标
            $pools = $this->pool->where(array('id' => $posts, 'nextPoolId' => $pres))->find();
            if ($pools) {//存在复制关系
                $this->error("不允许交叉复制！");
            }
            $jobs = $this->job->where(array('poolId' => $pres, 'nextPoolId' => $posts))->find();
            if ($jobs) {
                $this->error("已经存在任务，请到 任务 中直接修改！");
            }
            $this->success();
        }
    }

    /**
     * 复制pool中数据
     */
    public function copyPool()
    {
        if (IS_POST) {
            $data = I('post.');
            $pres = $data['preid'];//源
            $posts = $data['postid'];//目标
            $scheduleId = $data['sid'];//策略
            $this->pool->where(array('id' => $pres))->save(array('nextPoolId' => $posts));
            //新建备份任务
            $pre_data = $this->pool->where(array('id' => $pres))->find();
            $post_data = $this->pool->where(array('id' => $posts))->find();
            $jobInfo = array();
            $jobInfo['name'] = $pre_data['name'] . '_to_' . $post_data['name'];
            $jobs = $this->job->where(array('name' => $jobInfo['name']))->find();
            if (!$jobs) {
                $jobInfo['enabled'] = 'yes';
                $jobInfo['type'] = 'Copy';
                $jobInfo['poolId'] = $pres;
                $jobInfo['scheduleId'] = $scheduleId;
                $jobInfo['nextPoolId'] = $posts;
                $this->job->add($jobInfo);
            }
            $this->_factoryPool();
            $this->_factoryJobConf();
            $this->success(L('success'));
        }
    }

    /**
     * 动态获取Storage信息
     */
    public function getAjaxStorage()
    {
        if (IS_POST) {
            $type = I('post.type');
            if ($type == 'file') {
                $where = array('state' => '0', 'mediaType' => 'file');
            } else {
                $where = array('state' => '0', 'mediaType' => 'tapes');
            }
            $storage = $this->dirStorage->where($where)->select();
            $this->ajaxReturn($storage);
        }
    }

    /**
     * 删除备份池
     */
    public function delPool()
    {
        if (IS_POST) {
            $id = I('post.id');
            if ($id == 1) {
                $this->error(L('default_not_delete'));
            }
            //是否存在对应任务
            $job = $this->job->where(array('poolId' => $id))->find();
            if ($job) {
                $this->error('删除失败，请先删除对应任务');
            }
            $pool = $this->pool->where(array('id' => $id))->find();
            $result = $this->pool->where(array('id' => $id))->delete();
            $this->job->where(array('poolId' => $id))->delete();
            if ($result) {
                $this->_factoryPool();
                _exec("sudo /opt/rongan/scripts/deletepool {$pool['name']}");
                _exec("sudo /opt/rongan/scripts/loadconfig");
                $this->success(L('success'));
            } else {
                $this->error(L('error'));
            }
        }
    }

    /**
     * 添加客户端
     */
    public function addClient()
    {
        if (IS_POST) {
            if ($this->client->create()) {
                $data = I('post.');
                if ($data['passive']) {
                    $data['passive'] = 'yes';
                } else {
                    $data['passive'] = 'no';
                }
                if ($data['enabled']) {
                    $data['enabled'] = 'yes';
                } else {
                    $data['enabled'] = 'no';
                }
                if ($data['TLSEnable']) {
                    $data['TLSEnable'] = 'yes';
                } else {
                    $data['TLSEnable'] = 'no';
                }
                if ($data['TLSRequire']) {
                    $data['TLSRequire'] = 'yes';
                } else {
                    $data['TLSRequire'] = 'no';
                }
                if ($data['TLSVerifyPeer']) {
                    $data['TLSVerifyPeer'] = 'yes';
                } else {
                    $data['TLSVerifyPeer'] = 'no';
                }
                if (I('post.id')) {
                    //修改不做授权判断
                    if (!$data['maxBandwidthPerJob']) {
                        $data['maxBandwidthPerJob'] = 0;
                    }
                    $data['count'] = array('exp', 'count+1');
                    $result = $this->client->where(array('id' => I('post.id')))->save($data);
                } else {
                    $class = I('post.class');//X86/UNIX   Unixclient
                    if ($class == 'Windows' || $class == 'Linux') {
                        $map['class'] = array(array('eq', 'Windows'), array('eq', 'Linux'), 'or');
                        $map['state'] = array('eq', 0);
                        $class = 'X86';
                    } else {
                        $map['class'] = $class;
                        $map['state'] = array('eq', 0);
                    }
                    $value = $this->client->where($map)->count();
                    if ($class) {
                        if ($this->checkLicense(ucwords(strtolower($class)) . 'client', $value + 1)) {
                            if (!$data['maxBandwidthPerJob']) {
                                $data['maxBandwidthPerJob'] = 0;
                            }
                            $result = $this->client->add($data);
                        } else {
                            $this->error(L('verify_license_than'));
                        }
                    }
                }
                if ($result) {
                    $this->_factoryClient();
                    //更新任务
                    $this->_factoryJobConf();
                    $this->success(L('success'), U('Enterprise/client'));
                } else {
                    $this->error(L('verify_name_one'));
                }
            } else {
                $error = $this->client->getError();
                $this->error($error ? $error : L('error'));
            }
        }
        $licenseData = $this->getLicenseData();
        $this->assign(array(
            'menuid' => 'ep_client_list',
            'data' => $this->client->where(array('id' => I('get.id'), 'state' => 0))->find(),
            'ndmp' => $licenseData['ndmp'] ? 1 : 0,
            'group' => M('clientgroup')->select(),
        ));
        $this->display();
    }

    /**
     * 客户端管理
     */
    public function client()
    {
        $count = $this->client->where('state=0')->count();
        //分页
        $Page = new \Think\Page($count, 10);
        $Page->setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条记录&nbsp;</li>');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
        $show = $Page->show();
        $this->assign('page', $show);
        $data = $this->client->where('state=0')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign(array(
            'menuid' => 'ep_client_list',
            'data' => $data,
            'group' => M('clientgroup')->select(),
        ));
        $this->display();
    }

    /**
     * 删除客户端
     */
    public function delClient()
    {
        if (IS_POST) {
            $id = I('post.id');
            if ($id == 1) {
                $this->error(L('default_not_delete'));
            }
            $result = $this->client->where(array('id' => $id))->delete();
            $tmp = $this->job->where(array('clientId' => $id))->delete();
            if ($tmp) {
                $this->_factoryJobConf();
            }
            if ($result) {
                $this->_factoryClient();
                $this->success(L('success'));
            } else {
                $this->error(L('error'));
            }
        }
    }

    /**
     * 任务启动跟暂停
     */
    public function clientEnabled()
    {
        if (IS_POST) {
            $id = I('post.id');
            $data = $this->client->where('id=' . $id)->find();
            if ($data) {
                if ($data['enabled'] == 'yes') {
                    $upd = array('enabled' => 'no');
                } else {
                    $upd = array('enabled' => 'yes');
                }
                $this->client->where(array('id' => $id))->save($upd);
                $this->_factoryClient();
                $this->success("操作成功，页面刷新中...");
            } else {
                $this->error("操作失败，请重试");
            }
        }
    }

    /**
     * 添加任务
     */
    public function addJob()
    {
        if (IS_POST) {
            $data = I('post.');
            $data['count'] = array('exp', 'count+1');
            unset($data["deviceType"]);
            unset($data["fileSetName"]);
            unset($data["scheduleName"]);

            $data = $this->_jobDiy($data);
            if ($data['id']) {
                //修改不做授权判断
                $result = $this->job->where(array('id' => I('post.id')))->save($data);
                $data_info = $this->job->where(array('id' => I('post.id')))->find();
                //修改schedule和fileset name为当前任务xx_名称
                $this->fileset->where(array('id' => $data['fileSetId']))->save(array('name' => 'fileset_' . $data_info['name']));
                $this->schedule->where(array('id' => $data['scheduleId']))->save(array('name' => 'schedule_' . $data_info['name']));
                $this->_savePreSh($data['id'], $data['fileSetId']);
                $this->_factorySchedule();
                $this->_factoryFileset();
            } else {
                $result = $this->job->add($data); // 写入数据到数据库
                //第一次添加，修改schedule和fileset name为当前任务xx_名称
                $this->fileset->where(array('id' => $data['fileSetId']))->save(array('name' => 'fileset_' . $data['name']));
                $this->schedule->where(array('id' => $data['scheduleId']))->save(array('name' => 'schedule_' . $data['name']));
                $this->_savePreSh($result, $data['fileSetId']);
                $this->_factorySchedule();
                $this->_factoryFileset();
            }
            if ($result) {
                //在每次添加成功以后，查询fileset和schedule中是否存在垃圾数据
                $job_data = $this->job->field('fileSetId,scheduleId')->select();
                foreach ($job_data as $key => $v) {
                    $fileset_data[$key] = $v['fileSetId'];
                    $scheduleId_data[$key] = $v['scheduleId'];
                }
                $fileset_map['id'] = array('not in', implode(',', $fileset_data));
                $schedule_map['id'] = array('not in', implode(',', $scheduleId_data));
                $this->fileset->where($fileset_map)->delete();
                $this->schedule->where($schedule_map)->delete();
                $this->_factoryFileset();
                $this->_factorySchedule();
                $this->_factoryJobConf();
                $this->success(L('success'), U('Enterprise/job'));
            } else {
                $this->error(L('verify_name_one'));
            }
        }
        $data = $this->job->where(array('id' => I('get.id'), 'state' => 0))->find();
        $class = $this->client->where('id=' . $data['clientId'])->find();

        $data['scheduleName'] = "点击查看或修改";
        $data['fileSetName'] = "点击查看或修改";
        $licenseData = $this->getLicenseData();
        $this->assign(array(
            'menuid' => 'ep_job_list',
            'pool' => $this->pool->where('state=0')->select(),
            'client' => $this->client->where('state=0')->select(),
            'data' => $data,
            'ndmp' => $licenseData['ndmp'] ? 1 : 0,
            'bmr' => $licenseData['bmr'] ? 1 : 0,
            'class' => $class['class'],
        ));
        if (strtolower($data['type']) == 'copy') {
            $this->redirect('Enterprise/schedule', array('types' => 1, 'poolId' => $data['poolId'], 'id' => $data['scheduleId']));
        }
        $this->display();
    }


    public function _jobDiy($data)
    {
        //根据 fileSetId 判断备份类型 脚本等
        if ($data['fileSetId'] && $data['clientId']) {
            $job = $this->job->where(array('id' => $data['id']))->find();
            $fs = $this->fileset->where(array('id' => $data['fileSetId']))->find();
            $cs = $this->client->where(array('id' => $data['clientId']))->find();
            $jobname = $job['name'] ? $job['name'] : $data['name'];
            if ($fs['types'] == 'file') {//普通文件
                $script = explode('#-#', $fs['include']);
                $data['preScript'] = 0;
                $data['postScript'] = 0;
                $data['clientRunBeforeJob'] = '';
                $data['clientRunAfterJob'] = '';
                if ($cs['class'] == 'Windows') {
                    if ($script[1]) {
                        $data['preScript'] = 1;
                        $data['clientRunBeforeJob'] = '"\"C:/Program Files/RongAn Backup/scripts/pre_' . $jobname . '.bat\""';
                    }
                    if ($script[2]) {
                        $data['postScript'] = 1;
                        $data['clientRunAfterJob'] = '"\"C:/Program Files/RongAn Backup/scripts/post_' . $jobname . '.bat\""';
                    }
                } else {
                    if ($script[1]) {
                        $data['preScript'] = 1;
                        $data['clientRunBeforeJob'] = '"/opt/rongan/etc/scripts/pre_' . $jobname . '.sh"';
                    }
                    if ($script[2]) {
                        $data['postScript'] = 1;
                        $data['clientRunAfterJob'] = '"/opt/rongan/etc/scripts/post_' . $jobname . '.sh"';
                    }
                }
            } elseif (substr($fs['types'], 0, 2) == 'db') {//数据库
                $data['app'] = 1;
                $cname = substr($fs['types'], 2);
                $cff = $cffs = explode('@@', $fs['include']);
                if ($cname == 'ad') {//活动目录
                    if ($cff[0] == 1) {
                        $cff[0] = "0";
                    } else {
                        $cff[0] = "1";
                    }
                    $cff[1] = str_replace("\\", "\\\\\\", $cff[1]);
                    $data['clientRunBeforeJob'] = '"\"C:/Program Files/RongAn Backup/scripts/' . $cname . '_full.exe\" ' . $cff[1] . " " . $cff[0] . '"';
                    $data['clientRunAfterJob'] = "";
                } else {
                    array_shift($cffs);
                    $pars = implode(' ', $cffs);
                    if ($cs['class'] == 'Windows') {
                        $pars = str_replace("\\", "\\\\\\", $pars);
                        if ($cff[0] != 2) {
                            //全库
                            $data['clientRunBeforeJob'] = '"\"C:/Program Files/RongAn Backup/scripts/' . $cname . '_full.exe\" ' . $pars . '"';
                        } else {
                            $data['clientRunBeforeJob'] = '"\"C:/Program Files/RongAn Backup/scripts/' . $cname . '_diff.exe\" ' . $pars . '"';
                        }
                        $data['clientRunAfterJob'] = '"\"C:/Program Files/RongAn Backup/scripts/' . $cname . '_clean.exe\""';
                    } else {
                        if ($cff[0] != 2) {
                            //全库
                            $data['clientRunBeforeJob'] = '"/opt/rongan/etc/scripts/' . $cname . '_full.sh ' . $pars . '"';//2016517 自定义脚本参数
                        } else {
                            $data['clientRunBeforeJob'] = '"/opt/rongan/etc/scripts/' . $cname . '_diff.sh ' . $pars . '"';
                        }
                        $data['clientRunAfterJob'] = '"/opt/rongan/etc/scripts/' . $cname . '_clean.sh"';
                    }
                }
            } elseif ($fs['types'] == 'h3c') {//数据中心
                $data['preScript'] = 1;
                $data['clientRunBeforeJob'] = '"/opt/rongan/etc/scripts/pre_' . $jobname . '.sh"';
                $data['postScript'] = 1;
                $data['clientRunAfterJob'] = '"/opt/rongan/etc/scripts/post_' . $jobname . '.sh"';
            } elseif ($fs['types'] == 'vms') {
                $cff = explode('@@', $fs['include']);
                $vms = $this->virtual->where(array('id' => $cff[0]))->find();
                if ($vms) {
                    $vms = unserialize($vms['values']);
                    $data['clientRunBeforeJob'] = '"vmware_cbt_tool.py -s ' . $vms['ip'] . ' -u ' . $vms['user'] . ' -p ' . $vms['pwd'] . ' -d \'' . $vms['dc'] . '\' -f ' . $cff[1] . ' -v \'' . $cff[2] . '\' --enablecbt"';
                    $data['clientRunAfterJob'] = "";
                }
            }
        }
        return $data;
    }

    /**
     * 任务列表
     */
    public function job()
    {
        $count = $this->job->where('state=0')->count();
        //分页
        $Page = new \Think\Page($count, 10);
        $Page->setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条记录&nbsp;</li>');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
        $show = $Page->show();
        $this->assign('page', $show);
        $data = $this->job->where('state=0')->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign(array(
            'menuid' => 'ep_job_list',
            'data' => $data,
        ));
        $this->display();
    }

    /**
     * 任务启动跟暂停
     */
    public function enJob()
    {
        if (IS_POST) {
            $id = I('post.id');
            $data = $this->job->where('id=' . $id)->find();
            if ($data) {
                if ($data['enabled'] == 'yes') {
                    $upd = array('enabled' => 'no');
                } else {
                    $upd = array('enabled' => 'yes');
                }
                $this->job->where(array('id' => $id))->save($upd);
                $this->_factoryJobConf();
                $this->success("操作成功，页面刷新中...");
            } else {
                $this->error("操作失败，请重试");
            }
        }
    }

    /**
     * 删除任务
     */
    public function delJob()
    {

        if (IS_POST) {
            $id = I('post.id');
            $tmp = $this->job->where(array('id' => $id))->find();
            if ($tmp) {
                $this->fileset->where(array('id' => $tmp['fileSetId']))->delete();
                $this->schedule->where(array('id' => $tmp['scheduleId']))->delete();
                if (strtolower($tmp['type']) == 'copy') {//复制任务
                    $this->pool->where(array('id' => $tmp['poolId']))->save(array('nextPoolId' => ''));
                }
            } else {
                $this->error(L('error'));
            }
            $result = $this->job->where(array('id' => $id))->delete();
            if ($result) {
                $this->_factoryFileset();
                $this->_factorySchedule();
                $this->_factoryJobConf();
                $this->success(L('success'));
            } else {
                $this->error(L('error'));
            }
        }
    }

    /**
     * 添加备份策略
     */
    public function schedule($id = "", $types = 0, $pooId = 0)
    {
        if (IS_POST) {
            $tmp = I('post.');
            $types = $types ? $types : $tmp['types'];
            $pooId = $pooId ? $pooId : $tmp['pooId'];
            $data['name'] = $data['name'] = $this->_noDifName('schedule');
            $run = '';
            if ($tmp['year'] == 'years') {
                $run = "Run = {$tmp['styles']} {$tmp['mouth']} on {$tmp['date']} at {$tmp['time']}";
            } elseif ($tmp['year'] == 'mouths') {
                $run = "Run = {$tmp['styles']} on {$tmp['date']} at {$tmp['time']}";
            } elseif ($tmp['year'] == 'days') {
                $run = "Run = {$tmp['styles']} daily at {$tmp['time']}";
            } elseif ($tmp['year'] == 'weeks') {
                $run = "Run = {$tmp['styles']} {$tmp['week']} at {$tmp['time']}";
            } else {
                $run = "Run = {$tmp['styles']} hourly at {$tmp['time']}";
            }
            $data['run'] = $run;

            if ($this->schedule->create($data)) {
                if ($id) {
                    $ndata = array();
                    $olddata = $this->schedule->where("id=" . $id)->find();
                    if ($olddata['run']) {
                        $ndata['run'] = $olddata['run'] . '@@' . $data['run'];
                    } else {
                        $ndata['run'] = $data['run'];
                    }
                    //$ndata['run'] = $olddata['run'] . '@@' .  $data['run'];
                    $result = $this->schedule->where(array('id' => $id))->save($ndata); // 写入数据到数据库
                    if ($result) {
                        $this->_factorySchedule();
                        $url = U('Enterprise/schedule', array('id' => $id));
                        if ($types) {
                            $url = U('Enterprise/schedule', array('id' => $id, 'types' => $types, 'pooId' => $pooId));
                        }
                        $this->success($result, $url);
                    } else {
                        $this->error(L('error'));
                    }
                } else {
                    $result = $this->schedule->add(); // 写入数据到数据库 
                    if ($result) {
                        $this->_factorySchedule();
                        _exec("sudo /opt/rongan/scripts/loadconfig");
                        $url = U('Enterprise/schedule', array('id' => $result));
                        if ($types) {
                            $url = U('Enterprise/schedule', array('id' => $result, 'types' => $types, 'pooId' => $pooId));
                        }
                        $this->success($result, $url);
                    } else {
                        $this->error(L('error'));
                    }
                }
            } else {
                $error = $this->schedule->getError();
                $this->error($error ? $error : L('error'));
            }
        } else {
            if ($id) {
                $data = $this->schedule->where("id=" . $id)->find();
                $result = array();
                if ($data['run']) {
                    $data['list'] = explode("@@", $data['run']);
                    foreach (explode("@@", $data['run']) as $k => $v) {
                        $result[$k]['id'] = $data['id'];
                        $result[$k]['tid'] = $k + 1;
                        $tmp = explode("=", $v);
                        $r = explode(" ", ltrim($tmp[1]));
                        $result[$k]['type'] = $r[0];
                        $tmp1 = explode("=", $v);
                        if (count($r) == 4) {
                            $tmp1[1] = str_replace($r[0], '', $tmp1[1]);
                        }
                        $result[$k]['run'] = $tmp1[1];
                    }
                }
                $this->assign("data", $result);
            }
        }
        $this->assign(array(
            'month' => date("M"),
            'types' => $types,
            'pooId' => $pooId,
        ));
        $this->display();
    }

    /**
     * 删除单条备份策略中记录 真实删除
     */
    public function delSchedule()
    {
        if (IS_POST) {
            $id = I('post.id', 0);
            $tid = I('post.tid', 0);
            if ($id) {
                $tid = $tid - 1;
                $data = $this->schedule->where("id=" . $id)->find();
                $tmp = explode("@@", $data['run']);
                array_splice($tmp, $tid, 1);
                $data['run'] = implode("@@", $tmp);
                $result = $this->schedule->where(array('id' => $id))->save($data);
                if ($result) {
                    $this->_factorySchedule();
                    // _exec("sudo /opt/rongan/scripts/loadconfig");
                    $this->success(L('success'));
                } else {
                    $this->error(L('error'));
                }
            } else {
                $this->error(L('error'));
            }
        }
    }

    /**
     * 文件集管理
     */
    public function fileSet($id = "")
    {
        if (IS_POST) {
            $data = I('post.');
            $ndata = array();
            $include = I('post.fileSet');
            $ndata['exclude'] = '';
            if ($data['types'] == 'vms') {
                //$include = "{$data['dc']}@@{$data['ip']}@@{$data['user']}@@{$data['password']}@@{$data['vm']}@@{$data['file']}";
                $include = "{$data['vmvirtual']}@@{$data['file']}@@{$data['vm']}";
            } elseif ($data['types'] == 'dbsql') {//sqlserver
                if ($data['mssystem']) {
                    $include = "{$data['msinstance']}@@@@@@{$data['msdb']}";
                } else {
                    $include = "{$data['msinstance']}@@{$data['msusername']}@@{$data['mspassword']}@@{$data['msdb']}";
                }
            } elseif ($data['types'] == 'dboracle') {
                if (!$data['orinstance']) {
                    $data['orinstance'] = 0;
                }
                if (!$data['enableCom']) {
                    $data['enableCom'] = 0;
                }
                $include = "{$data['orctype']}@@{$data['ordir']}@@{$data['orinstance']}@@{$data['orusername']}@@{$data['ordays']}@@{$data['enableCom']}";
            } elseif ($data['types'] == 'dbsybase') {
                if (!$data['syuserpwd']) {
                    $data['syuserpwd'] = 0;
                }
                $include = "{$data['sytype']}@@{$data['sydir']}@@{$data['syinstance']}@@{$data['syusername']}@@{$data['syuserpwd']}@@{$data['sydbname']}";
            } elseif ($data['types'] == 'dbdb2') {
                $include = "{$data['dbtype']}@@{$data['dbdir']}@@{$data['dbinstance']}@@{$data['dbdbname']}";
            } elseif ($data['types'] == 'dbdm') {
                $include = "{$data['dmtype']}@@{$data['dmdir']}@@{$data['dminstance']}@@{$data['dmusername']}@@{$data['dmuserpwd']}";
            } elseif ($data['types'] == 'dbkingbase') {
                $include = "{$data['kbtype']}@@{$data['kbdir']}@@{$data['kbinstance']}@@{$data['kbusername']}@@{$data['kbuserpwd']}@@{$data['kbdbname']}";
            } elseif ($data['types'] == 'dbkdb') {
                if (!$data['kdbgd']) {
                    $data['kdbgd'] = 0;
                }
                $include = "{$data['kdbtype']}@@{$data['kdbdir']}@@{$data['kdbinstance']}@@{$data['kdbusername']}@@{$data['kdbuserpwd']}@@{$data['kdbgd']}";
            } elseif ($data['types'] == 'dbbmr') {
                if (!$data['bmrdisk']) {
                    $data['bmrdisk'] = 0;
                }
                $include = "{$data['bmrtype']}@@{$data['bmrdir']}@@{$data['bmrdisk']}";
            } elseif ($data['types'] == 'dbinf') {
                if (!$data['infinstance']) {
                    $data['infinstance'] = 0;
                }
                $include = "{$data['inftype']}@@{$data['infdir']}@@{$data['infinstance']}";
            } elseif ($data['types'] == 'dbmysql') {
                //$include = "{$data['myusername']}@@{$data['mypassword']}@@{$data['mydb']}";
                if (!$data['sqlusername']) {
                    $data['sqlusername'] = 0;
                }
                if (!$data['sqluserpwd']) {
                    $data['sqluserpwd'] = 0;
                }
                $include = "{$data['sqltype']}@@{$data['sqldir']}@@{$data['sqlusername']}@@{$data['sqluserpwd']}@@{$data['sqlport']}@@{$data['sqldbname']}";
            } elseif ($data['types'] == 'dbad') {
                $include = "{$data['adsys']}@@{$data['addir']}";
            } elseif ($data['types'] == 'dbpgsql') {
                $include = "";
            } elseif ($data['types'] == 'h3c') {
                $include = "{$data['h3cvirtual']}@@{$data['h3cfile']}@@{$data['vid']}@@{$data['h3csys']}@@{$data['h3ctmppath']}";
            } else {
                $include = implode("@@", array_filter(array_unique(explode("\n", str_replace("\\", "/", $include)))));  //去除空格和重复
                $exclude = implode("@@", array_filter(array_unique(explode("\n", str_replace("\\", "/", $data['exclude'])))));  //去除空格和重复
                $include .= "#-#{$data['preScript']}#-#{$data['postScript']}";
                $ndata['exclude'] = $data['exclude'];
            }
            $ndata['include'] = $include;
            if ($exclude) {
                $ndata['exclude'] = $exclude;
            }
            if (!$data['enableVSS']) {
                $ndata['enableVSS'] = 'no';
            } else {
                $ndata['enableVSS'] = 'yes';
            }
            $ndata['compression'] = $data['compression'];
            $ndata['description'] = $data['description'];
            $ndata['types'] = $data['types'];
            if (!I('post.id')) {
                $ndata['name'] = $this->_noDifName('fileset');
            }
            if ($this->fileset->create($ndata)) {
                if (I('post.id')) {
                    $result = $this->fileset->where(array('id' => I('post.id')))->save(); // 写入数据到数据库
                } else {
                    $result = $this->fileset->add(); // 写入数据到数据库 
                }
                if ($result !== false) {
                    $this->_factoryFileset();
                    $this->success($result);
                } else {
                    $this->error(L('error'));
                }
            } else {
                $error = $this->fileset->getError();
                $this->error($error ? $error : L('error'));
            }
        } else {
            $h3c = $this->virtual->where("vid='h3c'")->select();
            $vm = $this->virtual->where("vid='vm'")->select();
            if ($h3c) {
                $data['h3c'] = 1;
            }
            if ($vm) {
                $data['vms'] = 1;
            }
            $h3cfile = $this->fileserver->select();
            if ($id) {
                $data = $this->fileset->where("id=" . $id)->find();
                if ($data['types'] == 'vms') {
                    $info = explode("@@", $data['include']);
                    $data['vmvirtual'] = $info[0];
                    $data['file'] = $info[1];
                    $data['vm'] = $info[2];
                } elseif ($data['types'] == 'dbsql') {
                    $info = explode("@@", $data['include']);
                    $data['msinstance'] = $info[0];
                    $data['msusername'] = $info[1];
                    $data['mspassword'] = $info[2];
                    $data['msdb'] = $info[3];
                } elseif ($data['types'] == 'dboracle') {
                    $info = explode("@@", $data['include']);
                    $data['appType'] = $info[0];
                    $data['ordir'] = $info[1];
                    $data['orinstance'] = $info[2];
                    $data['orusername'] = $info[3];
                    $data['ordays'] = $info[4];
                    $data['enableCom'] = $info[5];
                } elseif ($data['types'] == 'dbsybase') {
                    $info = explode("@@", $data['include']);
                    $data['appType'] = $info[0];
                    $data['sydir'] = $info[1];
                    $data['syinstance'] = $info[2];
                    $data['syusername'] = $info[3];
                    $data['syuserpwd'] = $info[4];
                    $data['sydbname'] = $info[5];
                } elseif ($data['types'] == 'dbdb2') {
                    $info = explode("@@", $data['include']);
                    $data['appType'] = $info[0];
                    $data['dbdir'] = $info[1];
                    $data['dbinstance'] = $info[2];
                    $data['dbdbname'] = $info[3];
                } elseif ($data['types'] == 'dbdm') {
                    $info = explode("@@", $data['include']);
                    $data['appType'] = $info[0];
                    $data['dmdir'] = $info[1];
                    $data['dminstance'] = $info[2];
                    $data['dmusername'] = $info[3];
                    $data['dmuserpwd'] = $info[4];
                } elseif ($data['types'] == 'dbkingbase') {
                    $info = explode("@@", $data['include']);
                    $data['appType'] = $info[0];
                    $data['kbdir'] = $info[1];
                    $data['kbinstance'] = $info[2];
                    $data['kbusername'] = $info[3];
                    $data['kbuserpwd'] = $info[4];
                    $data['kbdbname'] = $info[5];
                } elseif ($data['types'] == 'dbsybase') {
                    $info = explode("@@", $data['include']);
                    $data['appType'] = $info[0];
                    $data['sydir'] = $info[1];
                    $data['syinstance'] = $info[2];
                    $data['syusername'] = $info[3];
                    $data['syuserpwd'] = $info[4];
                    $data['sydbname'] = $info[5];
                } elseif ($data['types'] == 'dbkdb') {
                    $info = explode("@@", $data['include']);
                    $data['appType'] = $info[0];
                    $data['kdbdir'] = $info[1];
                    $data['kdbinstance'] = $info[2];
                    $data['kdbusername'] = $info[3];
                    $data['kdbuserpwd'] = $info[4];
                    $data['kdbgd'] = $info[5];
                } elseif ($data['types'] == 'dbbmr') {
                    $info = explode("@@", $data['include']);
                    $data['appType'] = $info[0];
                    $data['bmrdir'] = $info[1];
                    $data['bmrdisk'] = $info[2];
                } elseif ($data['types'] == 'dbinf') {
                    $info = explode("@@", $data['include']);
                    $data['appType'] = $info[0];
                    $data['infdir'] = $info[1];
                    $data['infinstance'] = $info[2];
                } elseif ($data['types'] == 'dbad') {
                    $info = explode("@@", $data['include']);
                    $data['appType'] = $info[0];
                    $data['addir'] = $info[1];
                } elseif ($data['types'] == 'dbmysql') {
                    $info = explode("@@", $data['include']);
                    $data['appType'] = $info[0];
                    $data['sqldir'] = $info[1];
                    $data['sqlusername'] = $info[2];
                    $data['sqluserpwd'] = $info[3];
                    $data['sqlport'] = $info[4];
                    $data['sqldbname'] = $info[5];
                } elseif ($data['types'] == 'h3c') {
                    $info = explode("@@", $data['include']);
                    $data['h3cvirtual'] = $info[0];
                    $data['h3cfile'] = $info[1];
                    $data['vid'] = $info[2];
                    $data['h3csys'] = $info[3];
                    $data['h3ctmppath'] = $info[4];
                } else {
                    $include = explode('#-#', $data['include']);
                    $data['preScript'] = $include[1];
                    $data['postScript'] = $include[2];
                    $data['fileSet'] = str_replace("@@", "\n", $include[0]);
                    $data['exclude'] = str_replace("@@", "\n", $data['exclude']);
                }
                if ($h3c) {
                    $data['h3c'] = 1;
                }
                if ($vm) {
                    $data['vms'] = 1;
                }
            }
            $this->assign(array(
                "data" => $data,
                "h3c" => $h3c,
                "vm" => $vm,
                "h3cfile" => $h3cfile,
            ));
        }
        $this->display();
    }

    public function getVmName()
    {
        $id = I('post.id');
        if ($id) {
            $vm = $this->virtual->where("id=" . $id)->find();
            $info = unserialize($vm['values']);
            @exec("sudo /opt/rongan/bin/getallvms.py -s {$info['ip']} -u {$info['user']} -p {$info['pwd']}", $reslut);
            if ($reslut) {
                unset($reslut[0]);
                $this->success($reslut);
            }
        }
    }

    public function vmcbt()
    {
        $id = I('post.id');
        $vm = I('post.vm');
        $vmvirtual = I('post.vmvirtual');
        $files = I('post.files');
        if ($vm) {
            $info = array();
            $vms = $this->virtual->where("id=" . $vmvirtual)->find();
            $info = unserialize($vms['values']);
            @exec("sudo /opt/rongan/bin/vmware_cbt_tool.py -s {$info['ip']} -u {$info['user']} -p {$info['pwd']} -d {$info['dc']} -f {$files} -v '{$vm}' --{$id}", $reslut);
            $this->success($reslut);
        }
    }

    private function _noDifName($m)
    {
        $name = $m . '-' . date('M') . '-' . date('j') . rand(1, 1000);
        if (M($m)->where('name=' . $name)->find()) {
            $this->_noDifName($m);
        } else {
            return $name;
        }
    }

    /**
     * 任务单独保存conf
     * @param type $filter
     * @param type $associat
     * @param type $add
     */
    private function saveJobConf($filter, $associat, $add, $ckey = '', $unit = '')
    {
        $diysh = '';//2016517 自定义脚本参数
        $data = $this->job->where('state=0')->select();
        $ndata = array();
        $i = 0;
        foreach ($data as $k => $item) {
            if (is_array($ckey)) {
                //changge key 放在开始  避免过滤数据后数据丢失情况
                foreach ($ckey as $ck => $cv) {
                    $item[$cv] = $item[$ck];
                    unset($item[$ck]);
                }
            }
            //外表关联
            if ($associat) {
                foreach (explode("@@", $associat) as $tm) {
                    $ass = explode("|", $tm);
                    $assData = $this->$ass[0]->where('id=' . $item[$ass[1]])->find();
                    if ($assData) {
                        $item[$ass[2]] = $assData['name'];
                        //2016517 自定义脚本参数
                        if ($assData['type'] == 3) {//oracle xx.sh sid user dir days
                            $tmpsh = explode("@@", $assData['include']);
                            if (!$tmpsh[0]) {
                                $tmpsh[0] = " 0 ";
                            }
                            $diysh = "$tmpsh[0]$tmpsh[1] $tmpsh[2]$tmpsh[3]";
                        }

                    }
                }
            }
            //过滤
            if (is_array($filter)) {
                foreach ($filter as $f) {
                    unset($item[$f]);
                }
            } else {
                $filter = explode(',', $filter);
                foreach ($filter as $f) {
                    unset($item[$f]);
                }
            }
            //单位
            if (is_array($unit)) {
                foreach ($unit as $uk => $uv) {
                    $item[$uk] = $item[$uk] . $uv;
                }
            }
            //新加
            if (is_array($add)) {
                foreach ($add as $k1 => $v1) {
                    if (!$item[$k1]) {//原数据库中不存在记录新加才有效果
                        $item[$k1] = $v1;
                    }
                }
            }

            //2015-10-15 任务新增功能
            if (strcasecmp($item['rescheduleOnError'], 'yes')) {
                unset($item['rescheduleInterval']);
                unset($item['rescheduleTimes']);
            } else {
                $item['rescheduleInterval'] = $item['rescheduleInterval'] . ' minutes';
            }
            if (strcasecmp($item['spoolData'], 'yes')) {
                unset($item['spoolSize']);
            }
            //2015-10-27 任务复制功能
            if (strtolower($item['type']) == 'copy') {
                $item['Selection Type'] = 'PoolUncopiedJobs';
                unset($item['spoolAttributes']);
                unset($item['rescheduleOnError']);
                unset($item['priority']);
                unset($item['Maximum Bandwidth']);
                unset($item['client']);
                unset($item['fileset']);
                unset($item['write Bootstrap']);
                unset($item['enabled ']);
            }

            unset($item['preScript']);
            unset($item['postScript']);
            unset($item['clientId']);
            unset($item['appType']);
            unset($item['app']);
            unset($item['fileSetId']);
            $ndata[$i] = $item;
            $i++;
        }
        createConfig('job', $ndata);
    }

    /**
     * 根据M 获取数据 生成conf文件
     * @param type $m 数据库 M
     * @param type $filter 需要过滤数据 '1,2,3'
     * @param type $name 是否需要重新命名
     * @param type $ckey changge key name
     * @param type $associat 外表关联 k1|k2|k3  表名|字段|值 默认根据ID获取Name
     */
    private function _saveConf($m, $filter, $add, $name, $ckey, $associat = '', $unit = '')
    {
        $data = $this->$m->where('state = 0')->select();
        $ndata = array();
        if ($name) {
            $m = $name;
        }
        foreach ($data as $k => $item) {
            //处理TLS
            $tls = $this->tlsConfTools($item);
            if ($tls['ckey']) {
                $ckey = array_merge($ckey, $tls['ckey']);
            }
            if (!is_array($filter)) {
                $filter .= $tls['filter'];
            }
            if (is_array($ckey)) {
                //changge key 放在开始  避免过滤数据后数据丢失情况
                foreach ($ckey as $ck => $cv) {
                    $item[$cv] = $item[$ck];
                    unset($item[$ck]);
                }
            }
            //外表关联
            if ($associat) {
                foreach (explode("@@", $associat) as $tm) {
                    $ass = explode("|", $tm);
                    $assData = $this->$ass[0]->where('id=' . $item[$ass[1]])->find();
                    if ($assData) {
                        $item[$ass[2]] = $assData['name'];
                    }
                }
            }
            //过滤
            if (is_array($filter)) {
                foreach ($filter as $f) {
                    unset($item[$f]);
                }
            } else {
                $filter = explode(',', $filter);
                foreach ($filter as $f) {
                    unset($item[$f]);
                }
            }
            //新加
            if (is_array($add)) {
                foreach ($add as $k1 => $v1) {
                    $item[$k1] = $v1;
                }
            }
            //单位
            if (is_array($unit)) {
                foreach ($unit as $uk => $uv) {
                    $item[$uk] = $item[$uk] . $uv;
//                    foreach ($item as $ik=>$iv){
//                        if($ik == $uk){
//                            $item[$ik] = $iv . $uv;
//                        }
//                    }
                }
            }
            $ndata[$k] = $item;
            if ($m == 'pool') {
                if (!$item['types']) {//磁带
                    unset($ndata[$k]['Label Format']);
                    unset($ndata[$k]['Maximum Volume Bytes']);
                    unset($ndata[$k]['Maximum Volumes']);
                    $ndata[$k]['Cleaning Prefix'] = 'CLN';
                }
            }
        }
        createConfig($m, $ndata);
    }

    /**
     * 根据M 获取数据 生成conf文件
     * @param type $m 数据库 M
     * @param type $filter 需要过滤数据 '1,2,3'
     * @param type $name 是否需要重新命名
     * @param type $ckey changge key name
     * @param type $ckey 外表关联 k1|k2|k3  表名|字段|值 默认根据ID获取Name
     * @param type $tail 为字段赋值并添加尾巴 n1|k1|t1,n2|k2|t2, 字段名称|原字段名称|尾巴
     */
    private function _getConf($m, $filter, $add, $name, $ckey, $associat = '', $unit = '')
    {
        $data = $this->$m->where('state = 0')->select();
        $ndata = array();
        if ($name) {
            $m = $name;
        }
        $nameValue = '';
        foreach ($data as $k => $itme) {
            if (is_array($ckey)) {
                //changge key 放在开始  避免过滤数据后数据丢失情况
                foreach ($ckey as $ck => $cv) {
                    foreach ($itme as $ik => $iv) {
                        if ($ik == $ck) {
                            $tmp = $itme[$ik];
                            $itme[$cv] = $tmp;
                            unset($itme[$ik]);
                        }
                    }
                }
            }
            //外表关联
            if ($associat) {
                $ass = explode("|", $associat);
                $assData = $this->$ass[0]->where('id=' . $itme[$ass[1]])->find();
                if ($assData) {
                    $itme[$ass[2]] = $assData['name'];
                }
            }
            //过滤
            if (is_array($filter)) {
                foreach ($filter as $f) {
                    unset($itme[$f]);
                }
            } else {
                $filter = explode(',', $filter);
                foreach ($filter as $f) {
                    unset($itme[$f]);
                }
            }
            //新加
            if (is_array($add)) {
                foreach ($add as $k1 => $v1) {
                    $itme[$k1] = $v1;
                }
            }
            //单位
            if (is_array($unit)) {
                foreach ($unit as $uk => $uv) {
                    foreach ($itme as $ik => $iv) {
                        if ($ik == $uk) {
                            $itme[$ik] = $iv . $uv;
                        }
                    }
                }
            }
            $ndata[$k] = $itme;
            $nameValue = $itme['name'];
            if ($itme['deviceId']) {
                $ndata[$k]['device'] = $nameValue . '_device';
            }
            if (isset($itme['Media Type'])) {

            }
            if ($m == 'storage') {
                $ndata[$k]['device'] = $nameValue . '_device';
            }
        }
        return $ndata;
    }

    /**
     * 判断授权是否超出限制
     * @param type $itme
     * @param type $value
     */
    private function checkLicense($itme, $value)
    {
        if (LICENSE == -1 || LICENSE == 0 || LICENSE == 2 || LICENSE == 3) {
            return false;
        } elseif (LICENSE == 1) {
            return true;
        } else {
            $licenses = $this->license->find();
            $data = $this->getLicenseData();
            if ($data[$itme] < 0) {
                return true;
            }
            if (($data[$itme] + 1) >= $value) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 解析License
     * @param type $licenses
     * @return type
     */
    private function getLicenseData()
    {
        $licenses = $this->license->find();
        return json_decode(Encrypt::authcode($licenses['license'], 'DECODE', md5('jrsa')), true);
    }

    /**
     * 预警联系人管理
     */
    public function contact()
    {
        $contact = M('contact');
        $appset = M('appsetting');
        $send['msg'] = $appset->where('appid = 1 and `key` = "sendmsg"')->find();
        $send['mail'] = $appset->where('appid = 1 and `key` = "sendmail"')->find();
        $send['crontab'] = $appset->where('appid = 1 and `key` = "crontab"')->find();
        $data['tel'] = $contact->where('appid = 1 and style = 0')->select();
        $data['mail'] = $contact->where('appid = 1 and style = 1')->select();
        if (IS_POST) {
            $data = I('post.');
            if ($data['style']) {
                //邮件
                $count = $contact->where('appid = 1 and style = 1')->count();
                if ($count > 4) {
                    $this->error("添加超过限制，如需修改，请删除后重新添加！");
                }
                $data['content'] = $data['m-content'];
                unset($data['m-content']);
                $resutl = $contact->data($data)->add();
            } else {
                //短信
                $count = $contact->where('appid = 1 and style = 0')->count();
                if ($count > 4) {
                    $this->error("添加超过限制，如需修改，请删除后重新添加！");
                }
                $resutl = $contact->data($data)->add();
            }
            if ($resutl) {
                $this->success(L('success'));
            } else {
                $this->success(L('error'));
            }
        }

        //邮件解析
        $emailinfo = $this->_emailformat();
        $this->assign(array(
            "menuid" => "contact",
            'data' => $data,
            'send' => $send,
            'email' => $emailinfo,
        ));
        $this->display();
    }

    private function _emailformat()
    {
        //$arr = explode(PHP_EOL, read_file('D:/Works/rongan_monitor/monitor/clocking.sh'));
        $content = read_file($this->monitor);
        preg_match('/set from=(?<content>[\s\S]*?)"/', $content, $address);
        preg_match('/set smtp=(?<content>[\s\S]*?)"/', $content, $stmp);
        preg_match('/set smtp-auth-user=(?<content>[\s\S]*?)"/', $content, $user);
        preg_match('/set smtp-auth-password=(?<content>[\s\S]*?)"/', $content, $pwd);

        $data['address'] = '';
        $data['user'] = '';
        $data['stmp'] = '';
        $data['pwd'] = '';

        if ($address) {
            $data['address'] = $address[1];
        }
        if ($stmp) {
            $data['stmp'] = $stmp[1];
        }
        if ($user) {
            $data['user'] = $user[1];
        }
        if ($pwd) {
            $data['pwd'] = $pwd[1];
        }
        return $data;
    }

    /**
     * 发送短信、邮件状态
     */
    public function sendStatus()
    {
        if (IS_POST) {
            $appset = M('appsetting');
            $data = I('post.');
            $data['count'] = array('exp', 'count+1');
            if ($data['key'] != 'crontab') {
                $info = $appset->where(array("appid" => $data['appid'], "key" => $data['key']))->find();
                if ($info['value']) {
                    $data['value'] = 0;
                } else {
                    $data['value'] = 1;
                }
            }
            $result = $appset->where(array("appid" => $data['appid'], "key" => $data['key']))->save($data);
            if ($result) {
                $this->success(L('success'));
            } else {
                $this->error(L('error'));
            }
        }
    }

    /**
     * 删除联系信息
     */
    public function delContact()
    {
        if (IS_POST) {
            $data = I('post.');
            if ($data['id']) {
                M('contact')->where(array('id' => $data['id']))->delete();
                $this->success(L('success'));
            }
        }
    }

    /**
     * 客户端分组管理
     */
    public function group()
    {
        $group = M('clientgroup');
        if (IS_POST) {
            $data = I('post.');
            if ($data['action'] == 'add') {//添加
                $info = $group->where(array('name' => $data['name']))->find();
                if ($info) {
                    $this->error(L('error'));
                }
                $group->data(array('name' => $data['name']))->add();
            } elseif ($data['action'] == 'del') {//删除
                $info = $group->where(array('name' => $data['name']))->find();
                if ($info['id'] == 1) {
                    $this->error(L('default_not_delete'));
                }
                $this->client->where(array('gid' => $info['id']))->save(array("gid" => 1));
                $group->where(array('name' => $data['name']))->delete();
            }
            $this->success(L('success'));
        }
        $this->assign(array(
            'group' => $group->select(),
        ));
        $this->display();
    }

    /**
     * ajax 返回数据
     */
    public function ajaxGroup()
    {
        if (IS_POST) {
            $this->ajaxReturn(M('clientgroup')->select());
        }
    }

    /**
     * 生产任务Job数据
     */
    public function _factoryJobConf()
    {
        $ckey = array('maxBandwidth' => 'Maximum Bandwidth');
        $unit = array('Maximum Bandwidth' => ' Mb/s', 'spoolSize' => 'G');
        $associat = 'client|clientId|client@@fileset|fileSetId|fileset@@pool|poolId|pool@@schedule|scheduleId|schedule';
        $filter = 'msg,count,id,backupFormat,accurate,verifyJob,catalogId,jobDefId,bootstrap,writeBootstrap,base,messagesId,poolId,fullBackupPoolId,diffBackupPoolId,incrBackupPoolId,nextPoolId,maxStartDelay,maxRunTime,increMaxRunTime,diffMaxWaitTime,maxRunSchedTime,maxWaitTime,maxFullInterval,preferMountedVolumes,pruneJobs,pruneFiles,pruneVolumes,runBeforeJob,runAfterJob,runAfterFailedJob,returnFailedLevels,where,addPrefix,addSuffix,stripPrefix,regexWhere,replace,prefixLinks,maxConncurrentJobs,allowDuplicateJobs,cancelLowerLevelDup,cancleQueuedDup,cancleRunningDup,run,allowMixedPriority,protocol,level,scheduleId,storageId,PrefixLinks';
        $add = array('Messages' => 'Standard', 'type' => 'Backup', "write Bootstrap" => "\"/opt/rongan/bsr/%c_%v_%i.bsr\"");
        $this->saveJobConf($filter, $associat, $add, $ckey, $unit);
        _exec("sudo /opt/rongan/scripts/loadconfig");
    }

    /**
     * 生产设备Device数据
     */
    public function _factoryDevice()
    {
        $add = array("labelMedia" => 'yes', 'random Access' => 'yes', 'automaticMount' => 'yes', 'removableMedia' => 'no', 'alwaysOpen' => 'no');
        $ckey = array("mediaType" => "Media Type", 'archiveDevice' => 'Archive Device', 'maxBlockSize' => 'Maximum block size', 'minBlockSize' => 'Minimum block size', 'maxFileSize' => 'Maximum File Size', 'maxSpoolSize' => 'Maximum Spool Size', 'maxNetworkBufferSize' => 'Maximum Network Buffer Size', "maxConcurrentJobs" => "Maximum Concurrent Jobs");
        $filter = 'count,id,alertCommand,driveIndex,autoselect,maxChangerWait,maxRewindWait,maxOpenWait,alwaysOpen,volumePollInterval,closeonPoll,removeableMedia,randomAccess,requiresMount,mountPoint,mountCommand,unmountCommand,labelMedia,automaticMount,blockChecksum,minBlockSize,labelBlockSize,hardwareEndofMedium,firstForwardSpaceFile,useMTIOCGET,bsFatEOM,twoEOF,backwardSpaceRecord,backwardSpaceFile,forwardSpaceRecord,forwardSpaceFile,offlineOnUnmount,noRewindOnClose,driveCryptoEnabled,queryCryptoStatus,maxVolumeSize,maxFileSize,blockPositioning,maxSpoolSize,spoolDirectory,autoDeflate,autoDeflateAlgorithm,autoDeflateLevel,autoInflate,autochanger,deviceType';
        $unit = array('Maximum Spool Size' => ' G', 'Maximum File Size' => ' G');
        createDeviceConf('device', $this->_getConf('device', $filter, $add, 'device', $ckey, '', $unit));
        _exec("sudo /opt/rongan/scripts/loadconfig");
    }

    /**
     * 生产介质Storage数据
     */
    public function _factoryStorage()
    {
        $add = array('password' => '"sdpassword"');
        $ckey = array('mediaType' => 'Media Type', 'sdPort' => 'SD Port');
        $associat = 'device|deviceId|device';
        $filter = 'count,id,password,maxConcurrentReadJobs,heartbeatInterval,pairedStorage,autochanger,allowCompression';
        createStorageConf('storage', $this->_getConf('dirStorage', $filter, $add, 'storage', $ckey, $associat));
        // _exec("sudo /opt/rongan/scripts/loadconfig");
    }

    /**
     * 生产备份池Pool数据
     */
    public function _factoryPool()
    {
        $add = array('Pool Type' => 'Backup', 'Recycle' => 'yes', 'AutoPrune' => 'yes');
        $ckey = array('labelFormat' => 'Label Format', 'maxVolumeBytes' => 'Maximum Volume Bytes', 'maxVolumes' => 'Maximum Volumes', 'volumeRetention' => 'Volume Retention', 'fileRetention' => 'File Retention', 'jobRetention' => 'Job Retention', 'recycleOldestVolume' => 'Recycle Oldest Volume');
        $associat = 'dirStorage|atorageId|storage@@pool|nextPoolId|next pool';
        $unit = array('Volume Retention' => ' days', 'File Retention' => ' days', 'Job Retention' => ' days', 'Maximum Volume Bytes' => 'G');
        $filter = 'count,id,useVolumeOnce,maxVolumeJobs,maxVolumeFiles,volumeUseDuration,catalogFiles,catalogId,autoPrune,actionOnPurge,nextPoolId,scratchPoolId,recyclePoolId,recycle,recycleCurrentVolume,purgeOldestVolume,cleaningPrefix,poolType,atorageId';
        $this->_saveConf('pool', $filter, $add, 'pool', $ckey, $associat, $unit);
        _exec("sudo /opt/rongan/scripts/loadconfig");
    }

    /**
     * 生产客户端Client数据
     */
    public function _factoryClient()
    {
        $add = array('AutoPrune' => 'yes', 'Password' => '"fdpassword"');
        $ckey = array('maxBandwidthPerJob' => 'Maximum Bandwidth Per Job', "maxConcurrentJobs" => "Maximum Concurrent Jobs", "hardQuota" => "Hard Quota", "fdPort" => "FD Port");
        $associat = '';
        $unit = array('Maximum Bandwidth Per Job' => ' Mb/s', 'Hard Quota' => 'G');
        $filter = 'algorithm,fileRetention,jobRetention,gid,count,id,authtype,password,ndmpLoglevel,ndmpBlocksize,catalogId,autoPrune,username,softQuota,softQuotaGracePeriod,strictQuotas,quotaIncludeFailedJobs,ndmploglevel,ndmpblocksize,class,priority,protocol';
        $this->_saveConf('client', $filter, $add, 'client', $ckey, $associat, $unit);
    }

    /**
     * 生产备份文件集Fileset数据
     */
    public function _factoryFileset()
    {
        $ckey = array('enableVSS' => 'enable VSS');
        $this->_saveConf('fileset', 'id,ignoreFileSetChanges', '', 'fileset', $ckey);
    }

    /**
     * 生产备份文件集Schedule数据
     */
    public function _factorySchedule()
    {
        $this->_saveConf('schedule', 'id', '', 'schedule');
    }

    public function stmp($testtype = 0)
    {
        $data = I('post.');
        if ($testtype == 1) {
            //测试发送邮件
            $info = $this->_sendEmails($data);
            $this->success($info);
        } else {
            //文件生成
            $content = read_file($this->monitor);

            preg_match('/set from=(?<content>[\s\S]*?)"/', $content, $address);
            preg_match('/set smtp=(?<content>[\s\S]*?)"/', $content, $stmp);
            preg_match('/set smtp-auth-user=(?<content>[\s\S]*?)"/', $content, $user);
            preg_match('/set smtp-auth-password=(?<content>[\s\S]*?)"/', $content, $pwd);
            $content = str_replace('from=' . $address[1] . '"', 'from=' . $data['address'] . '"', $content);
            $content = str_replace('smtp=' . $stmp[1] . '"', 'smtp=' . $data['stmp'] . '"', $content);
            $content = str_replace('smtp-auth-user=' . $user[1] . '"', 'smtp-auth-user=' . $data['user'] . '"', $content);
            $content = str_replace('smtp-auth-password=' . $pwd[1] . '"', 'smtp-auth-password=' . $data['pwd'] . '"', $content);
            _exec("sudo chmod 777 $this->monitor");
            saveFile($this->monitor, $content);
            _exec('sudo sh /opt/rongan/monitor/clocking.sh');
            $this->success("修改成功");
        }
    }

    public function _sendEmails($config, $body = "这是一封测试邮件")
    {
        $name = "jrsa";
        import('PHPMailer');
        require C_PATH . '/Hlib/Util/class.phpmailer.php';
        $mail = new \PHPMailer(); //PHPMailer对象
        $mail->CharSet = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->IsSMTP();  // 设定使用SMTP服务
        $mail->SMTPDebug = 0;                     // 关闭SMTP调试功能

        $mail->SMTPAuth = true;                  // 启用 SMTP 验证功能
        $mail->SMTPSecure = 'ssl';                 // 使用安全协议
        $mail->Host = $config['address'];  // SMTP 服务器
        $mail->Username = $config['user'];  // SMTP服务器用户名
        $mail->Password = $config['pwd'];  // SMTP服务器密码
        $mail->SetFrom($config['user'], 'jrsa');
        $replyEmail = $config['user'];
        $replyName = 'jrsa';
        $mail->AddReplyTo($replyEmail, $replyName);
        $mail->Subject = $subject;
        $mail->MsgHTML($body);
        $mail->AddAddress($config['test'], $name);
//        if(is_array($attachment)){ // 添加附件
//            foreach ($attachment as $file){
//                is_file($file) && $mail->AddAttachment($file);
//            }
//        }
        return $mail->Send() ? true : $mail->ErrorInfo;
    }

    /**
     * 磁带打标签
     */
    public function media($tape = "", $name = "")
    {
        if (IS_POST) {
            $data = I('post.');
            $type = $data['type'];
            $storagename = str_replace('_type', '', $data['storage']);
            $id = str_replace(array('*', '%', '@'), '', rtrim($data['id'], ','));
            if (trim($type) == "add") {
                $poolname = $data['pool'];
                @exec("sudo sh /opt/rongan/scripts/labeltape {$storagename} {$poolname} {$id}", $tapeinfo);
                //echo "sudo sh /opt/rongan/scripts/labeltape {$storagename} {$poolname} {$id}";
            } elseif (trim($type) == "exp") {//导出磁带
                @exec("sudo sh /opt/rongan/scripts/exptape {$storagename} {$id}", $tapeinfo);
            } else {
                @exec("sudo sh /opt/rongan/scripts/imptape {$storagename}");
            }
            //echo "sudo sh /opt/rongan/scripts/labeltape {$storagename} {$poolname} {$id}";
            $this->success(L('success'));
        } else {
            if ($tape) {
                ;
                @exec("sudo sh /opt/rongan/scripts/listslots {$tape}", $tapeinfo);
                $start = 0;
                $end = 100;
                $taps = array();
                foreach ($tapeinfo as $k => $v) {
                    if (preg_match("/^-----/", ltrim($v))) {
                        $start = $k;
                    }
                    if (preg_match("/^You\shave\smessages|exit/", ltrim($v))) {
                        $end = $k;
                    }
                    if ($start) {
                        if (($k > $start) && ($end > $k)) {
                            $vs = str_replace(array(" |", "|", '?'), array("|", "", '-'), $v);
                            $tmp = array_values(array_filter(explode(" ", ltrim($vs)), "hink_not_filter0"));
                            $taps[] = $tmp;
                        }
                    }
                }
                $this->assign(array(
                    'data' => $taps,
                    'sname' => $tape,
                    'poolname' => $name,
                ));
                $this->display();
            } else {
                echo "获取数据出错";
            }
        }
    }

    /**
     * 根据Job ID 生成 前置脚本
     */
    private function _savePreSh($jobid, $filesetid)
    {
        if (!$jobid) {
            return 0;
        }
        $fileset = $this->fileset->where(array('id' => $filesetid))->find();
        if ($fileset['types'] == 'h3c') {
            $job = $this->job->where(array('id' => $jobid))->find();
            $include = explode('@@', $fileset['include']);
            $vids = explode(',', $include[2]);
            $fileservers = $this->fileserver->where(array('id' => $include[1]))->find();
            $file = C('SH_PATH') . 'pre_h3c.sh';
            $files = read_file($file);
            $urls = "";
            $ip = gethostbyname($_SERVER["SERVER_NAME"]);
            foreach ($vids as $k => $v) {
                $urls .= "http://" . $ip . "/webapp/index.php/Home/Virtual/backup/id/$jobid/vid/$v.html http://" . $ip . "/webapp/index.php/Home/Virtual/getMsg/id/$jobid.html ";
            }
            $urls = rtrim($urls);
            $files = str_replace('##HINKDIY##', $urls, $files);
            $path = rtrim($fileservers['path'], '/');
            $files = str_replace('##HINKPATH##', "rm -rf {$path}/*.tar", $files);
            //$files .= PHP_EOL . "exit 0";
            saveFile(C('SH_SAVE_PATH') . "pre_" . $job['name'] . ".sh", $files);
            $pools = $this->pool->where(array('id' => $job['poolId']))->find();
            $days = '30';
            if ($pools) {
                $days = $pools['volumeRetention'];
            }
            $post = "#!/bin/bash
			find {$path}  -mtime +{$days} -name \"*.tar\" -exec rm -rf {} \\;";
            saveFile(C('SH_SAVE_PATH') . "post_" . $job['name'] . ".sh", $post);
            _exec("sudo chmod 777 " . C('SH_SAVE_PATH') . "/*");
            return 1;
        }
        return 0;
    }

}
