<?php

/*
 * Jrsn Backup Management System
 * @Description   公用函数库
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

/**
 * 获取当前地址路径
 * @param string $dir
 * @param type $list_file
 * @param type $check_children
 * @return type
 */
function path_list($dir, $list_file = true, $check_children = false) {
 $dir = rtrim($dir, '/') . '/';
 $dir=iconv ( "UTF-8", "GBK",$dir);
 if (!is_dir($dir) || !($dh = opendir($dir))) {
  return array('folderlist' => array(), 'filelist' => array());
 }
 $folderlist = array();
 $filelist = array(); //文件夹与文件
 while (($file = readdir($dh)) !== false) {
  if ($file != "." && $file != ".." && $file != ".svn") {
   $fullpath = $dir . $file;
   if (is_dir($fullpath)) {
    $info = folder_info($fullpath);
    if ($check_children) {
     $info['isParent'] = path_haschildren($fullpath, $list_file);
    }
    $folderlist[] = $info;
   } else if ($list_file) {//是否列出文件
    $info = file_info($fullpath);
    if ($check_children)
     $info['isParent'] = false;
    $filelist[] = $info;
   }
  }
 }
 closedir($dh);
 return array('folderlist' => $folderlist, 'filelist' => $filelist);
}

/**
 * 获取文件夹细信息
 */
function folder_info($path, $date_formate = false) {
 if (!$date_formate)
  $date_formate = $GLOBALS['L']['time_type'];
 $info = array(
     'name' => iconv_app(get_path_this($path)),
     'path' => iconv_app(get_path_father($path)),
     'type' => 'folder',
     'mode' => get_mode($path),
     'atime' => date($date_formate, fileatime($path)), //访问时间
     'ctime' => date($date_formate, filectime($path)), //创建时间
     'mtime' => date($date_formate, filemtime($path)), //最后修改时间
     'is_readable' => intval(is_readable($path)),
     'is_writeable' => intval(is_writeable($path))
 );
 return $info;
}

// 传入参数为程序编码时，有传出，则用程序编码，
// 传入参数没有和输出无关时，则传入时处理成系统编码。
function iconv_app($str) {
 return iconv("gb2312","UTF-8",$str);
}

/**
 * 获取一个路径(文件夹&文件) 当前文件[夹]名
 * test/11/ ==>11 test/1.c  ==>1.c
 */
function get_path_this($path) {
 $path = str_replace('\\', '/', rtrim(trim($path), '/'));
 return substr($path, strrpos($path, '/') + 1);
}

/**
 * 获取一个路径(文件夹&文件) 父目录
 * /test/11/==>/test/   /test/1.c ==>/www/test/
 */
function get_path_father($path) {
 $path = str_replace('\\', '/', rtrim(trim($path), '/'));
 return substr($path, 0, strrpos($path, '/') + 1);
}

/**
 * 获取文件(夹)权限 rwx_rwx_rwx
 */
function get_mode($file) {
 $Mode = fileperms($file);
 $Owner = array();
 $Group = array();
 $World = array();
 if ($Mode & 0x1000)
  $Type = 'p'; // FIFO pipe
 elseif ($Mode & 0x2000)
  $Type = 'c'; // Character special
 elseif ($Mode & 0x4000)
  $Type = 'd'; // Directory
 elseif ($Mode & 0x6000)
  $Type = 'b'; // Block special
 elseif ($Mode & 0x8000)
  $Type = '-'; // Regular
 elseif ($Mode & 0xA000)
  $Type = 'l'; // Symbolic Link
 elseif ($Mode & 0xC000)
  $Type = 's'; // Socket
 else
  $Type = 'u'; // UNKNOWN

// Determine les permissions par Groupe
 $Owner['r'] = ($Mode & 00400) ? 'r' : '-';
 $Owner['w'] = ($Mode & 00200) ? 'w' : '-';
 $Owner['x'] = ($Mode & 00100) ? 'x' : '-';
 $Group['r'] = ($Mode & 00040) ? 'r' : '-';
 $Group['w'] = ($Mode & 00020) ? 'w' : '-';
 $Group['e'] = ($Mode & 00010) ? 'x' : '-';
 $World['r'] = ($Mode & 00004) ? 'r' : '-';
 $World['w'] = ($Mode & 00002) ? 'w' : '-';
 $World['e'] = ($Mode & 00001) ? 'x' : '-';
 // Adjuste pour SUID, SGID et sticky bit
 if ($Mode & 0x800)
  $Owner['e'] = ($Owner['e'] == 'x') ? 's' : 'S';
 if ($Mode & 0x400)
  $Group['e'] = ($Group['e'] == 'x') ? 's' : 'S';
 if ($Mode & 0x200)
  $World['e'] = ($World['e'] == 'x') ? 't' : 'T';
 $Mode = $Type . $Owner['r'] . $Owner['w'] . $Owner['x'] . ' ' .
     $Group['r'] . $Group['w'] . $Group['e'] . ' ' .
     $World['r'] . $World['w'] . $World['e'];
 return $Mode;
}

/**
 * 获取文件详细信息
 * 文件名从程序编码转换成系统编码,传入utf8，系统函数需要为gbk
 */
function file_info($path, $date_formate = false) {
 if (!$date_formate)
  $date_formate = $GLOBALS['L']['time_type'];
 $name = get_path_this($path);
 $size = abs(filesize($path));
 $info = array(
     'name' => iconv_app($name),
     'path' => iconv_app(get_path_father($path)),
     'ext' => get_path_ext($path),
     'type' => 'file',
     'mode' => get_mode($path),
     'atime' => date($date_formate, fileatime($path)), //访问时间
     'ctime' => date($date_formate, filectime($path)), //创建时间
     'mtime' => date($date_formate, filemtime($path)), //最后修改时间
     'is_readable' => intval(is_readable($path)),
     'is_writeable' => intval(is_writeable($path)),
     'size' => $size,
     'size_friendly' => size_format($size, 2)
 );
 return $info;
}

/**
 * 获取扩展名
 */
function get_path_ext($path) {
 $name = get_path_this($path);
 if (strstr($name, '.')) {
  $ext = substr($name, strrpos($name, '.') + 1);
  return strtolower($ext);
 } else {
  return '';
 }
}

/**
 * 文件大小格式化
 *
 * @param  $ :$bytes, int 文件大小
 * @param  $ :$precision int  保留小数点
 * @return :string
 */
function size_format($bytes, $precision = 2) {
 if ($bytes == 0)
  return "0 B";
 $unit = array('TB' => 1099511627776, // pow( 1024, 4)
     'GB' => 1073741824, // pow( 1024, 3)
     'MB' => 1048576, // pow( 1024, 2)
     'kB' => 1024, // pow( 1024, 1)
     'B ' => 1, // pow( 1024, 0)
 );
 foreach ($unit as $un => $mag) {
  if (doubleval($bytes) >= $mag)
   return round($bytes / $mag, $precision) . ' ' . $un;
 }
}

/**
 * 获取文件夹下所有文件
 * @param type $path
 * @return string
 */
function getFileNames($path=''){
 $arr = array();
 if ($handle = @opendir($path)) {
  while (false !== ($file = @readdir($handle))) {
   if($file=='.' || $file=='..'){
    continue;
   }
   $nextDir = $path."/".$file;
   if(is_dir($nextDir)){
    scanDir($nextDir);
    $arr[]['dir'] = $nextDir;
   }else{
    $arr[]['file'] = $nextDir;
   }
  }
  @closedir($handle);
 }
 return $arr;
}

/**
 * 获取指定长度的随机字符
 * @param type $len 长度
 * @return string
 */
function genRandomString($len = 6) {
 $chars = array(
     "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
     "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
     "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
     "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
     "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
     "3", "4", "5", "6", "7", "8", "9"
 );
 $charsLen = count($chars) - 1;
 // 将数组打乱
 shuffle($chars);
 $output = "";
 for ($i = 0; $i < $len; $i++) {
  $output .= $chars[mt_rand(0, $charsLen)];
 }
 return $output;
}

/**
 * 根据文件地址读取文件内容
 * @param type $filename
 */
function read_file($filename){
 if(is_file($filename)){
  $handle = fopen($filename, 'r');
  $content = '';
  while(!feof($handle)){
   $content .= fread($handle, 1024);
  }
  fclose($handle);
  return $content;
 }
 return '';
}


/**
 * 数据签名认证
 * @param $data
 * @return string
 */
function data_auth_sign($data){
 if(!is_array($data)){
  $data = (array)$data;
 }
 ksort($data);//排序
 $code = http_build_query($data);//生成 url-encoded 之后的请求字符串描述
 $sign = sha1($code); //生成签名
 return $sign;
}

/**
 * 生成配置文件
 * @param type $name
 * @param type $data
 */
function createConfig($name,$data,$kname=''){
 $content = '';
 foreach ($data as $item){
  if(is_array($item)){
   $content .= getConfDate($name,$item,$kname) . "\n";
  }else{
   $content = getConfDate($name,$data,$kname);
  }
 }
 saveFile(C('CONFIG_SAVE_PATH') . $name . ".conf", $content);
 saveFile(C('CONFIG_SAVE_PATH') . 'bak/' . $name . date('YmdHis', time()) . ".conf", $content);
}

function createDeviceConf($name,$data){
 $content = '';
 $changerDevice = '';
 $archiveDevice = '';
 foreach ($data as $item) {
  if ($item['Media Type'] === 'tapes') {
   //磁带库
   $autochanger = array(
       'Name' =>$item['name'],
       'Changer Command'=>'"/opt/rongan/etc/rongan/mtx-changer %c %o %S %a %d"',
       'Changer Device'=>$item['changerDevice'],
   );

   $devices = '';
   $autho_device = '';
   $arr_device = array();
   $archive = explode("\n",$item['Archive Device']);
   foreach ($archive as $key=>$value){
    $autho_device .= "\n\tDevice = Drive-" . $value;
    //$arr_device['Device'] = $value;
    $arr_device['Name'] = 'Drive-' . $value;
    $arr_device['Drive Index'] = $key;
    $arr_device['Media Type'] = str_replace('_device', '_type', $item['name']);;
    $arr_device['Archive Device'] = $value;
    $arr_device['AutomaticMount'] = 'yes';
    $arr_device['RemovableMedia'] = 'yes';
    $arr_device['AlwaysOpen'] = 'yes';
    $arr_device['RandomAccess'] = 'no';
    $arr_device['AutoChanger'] = 'yes';
    $arr_device['Maximum Concurrent Jobs'] = '1';
    $arr_device['Alert Command'] = '"sh -c \'tapeinfo -f %c |grep TapeAlert|cat\'"';
    if($item['vtltype']){
     $arr_device['Hardware End of Medium'] = 'no';
     $arr_device['Fast Forward Space File'] = 'no';
    }else{
     $arr_device[$item['changerCommand']] = '';
    }
    $devices .= getConfDate('Device ',$arr_device,'',1) . "\n";
   }
   //$content .= getConfDate('Autochanger ',$autochanger) . "\n";
   $content .= "Autochanger{ \n\tName = ".$item['name']."\n\tChanger Command = \"/opt/rongan/etc/rongan/mtx-changer %c %o %S %a %d\"\n\tChanger Device = ".$item['changerDevice'].$autho_device."\n}\n";
   $content .= $devices;
  }elseif ($item['Media Type'] === 'tape') {//磁带机
//            foreach (F('tapedata') as $value){
//                if($item['Archive Device'] ==$value[5] ){
//                    $changerDevice = $value[6];
//                    $archiveDevice = $value[5];
//                }
//            }
//            $archiveDevices = explode('/', $archiveDevice);
//            $archcount = count($archiveDevices);
//            $archiveDevice = str_replace($archiveDevices[$archcount-1], 'n'.$archiveDevices[$archcount-1],$archiveDevice);
//            $drvice = array(
//                'name' => $item['changerDevice'],
//                'Media Type' => str_replace('_device', '_type', $item['name']),
//                'Archive Device' =>$archiveDevice,
//                 'AutomaticMount' => 'yes',
//                 'AlwaysOpen' => 'yes',
//                 'RemovableMedia ' => 'yes',
//                 'RandomAccess' => 'no',
//            );
//            $content .= getConfDate('device',$drvice) . "\n";
  }else{
   unset($item['changerCommand']);
   unset($item['changerDevice']);
   unset($item['alwaysOpen']);
   unset($item['vtltype']);
   if($item['Media Type'] === 'file'){
    $item['Media Type'] = str_replace('_device', '_type', $item['name']);
   }
   $item['AlwaysOpen'] = 'yes';
   $content .= getConfDate($name,$item) . "\n";
  }
 }
 saveFile(C('CONFIG_SAVE_PATH') . $name . ".conf", $content);
 saveFile(C('CONFIG_SAVE_PATH') . 'bak/' . $name . date('YmdHis', time()) . ".conf", $content);
}

function createStorageConf($name,$data){
 $content = '';
 foreach ($data as $key=>$item) {
  //处理TLS
  if($item['TLSEnable']){
   if($item['TLSEnable'] === 'yes'){
    $tlsarr = array("TLSEnable"=>"TLS Enable","TLSRequire"=>"TLS Require","TLSVerifyPeer"=>"TLS Verify Peer","TLSCertificate"=>"TLS Certificate","TLSKey"=>"TLS Key","TLSCACertificateFile"=>"TLS CA Certificate File");
    foreach($tlsarr as $ck=>$cv){
     $item[$cv] = $item[$ck];
     unset($item[$ck]);
    }
   }else{
    $tlsarr = "TLSEnable,TLSRequire,TLSVerifyPeer,TLSCertificate,TLSKey,TLSCACertificateFile";
    $filter = explode(',', $tlsarr);
    foreach ($filter as $f){
     unset($item[$f]);
    }
   }
  }
  if (strtolower($item['Media Type']) == 'tape') {//磁带机
   $deviceInfo = M('device')->where(array('id'=>$item['deviceId']))->find();
   $drvice = array(
       'name' => $item['name'],
       'SD Port' => $item['SD Port'],
       'Address '=>$item['address'],
       'Password '=>$item['password'],
       'Device' => $deviceInfo['changerDevice'],
       'Media Type' => $deviceInfo['changerDevice'],
       'Maximum Concurrent Jobs' => $item['maxConcurrentJobs'],
    //'Autochanger' => 'yes',
   );
   $content .= getConfDate('storage',$drvice) . "\n";
  }elseif(strtolower($item['Media Type']) == 'tapes'){
   $deviceInfo = M('device')->where(array('id'=>$item['deviceId']))->find();
   $drvice = array(
       'name' => $item['name'],
       'SD Port' => $item['SD Port'],
       'Address '=>$item['address'],
       'Password '=>$item['password'],
    //'Device' => $deviceInfo['name'],
       'Device' => $item['name'] . '_device',
    //'Device' => $deviceInfo['changerDevice'],
       'Media Type' => $item['name'] . '_type',
       'Autochanger' => 'yes',
       'Maximum Concurrent Jobs' => $item['maxConcurrentJobs'],
   );
   $content .= getConfDate('storage',$drvice) . "\n";

  }else{
   unset($item['deviceId']);
   $item['Media Type'] = $item['name'] . '_type';
   $item['Maximum Concurrent Jobs'] = $item['maxConcurrentJobs'];
   unset($item['maxConcurrentJobs']);
   $content .= getConfDate($name,$item) . "\n";
  }
 }
 saveFile(C('CONFIG_SAVE_PATH') . $name . ".conf", $content);
 saveFile(C('CONFIG_SAVE_PATH') . 'bak/' . $name . date('YmdHis', time()) . ".conf", $content);
}

/**
 * 根据data生成数据
 * @param type $data
 */
function getConfDate($name,$data,$kname='',$removeeq = ''){
 if($kname){
  $content = $kname .' {';
 }else{
  $content = $name .' {';
 }
 $type = $data['types'];
 $include = $data['include'];
 $compression = $data['compression'];
 unset($data['description']);
 unset($data['state']);
 unset($data['types']);
 unset($data['compression']);
 foreach ($data as $k=>$v){
  if($v){
   if(strtolower($name) == "schedule"){
    if($k =="run"){
     foreach (explode("@@", $v) as $key){
      $content  .= "\n\t$key";
     }
    }else{
     $content  .= "\n\t$k = $v";
    }
   }elseif (strtolower($name) == "fileset") {
    if($k =="include"){
     $content  .= "\n\t$k{\n\t\tOptions{\n\t\t\tsignature = MD5\n\t";
     if($compression){
      $content .="\t\tcompression = $compression\n\t";
     }
     $content .="\t}";
     $tmp = explode("@@", $v);
     if($type =='file'){
      $tmp = explode('#-#', $v);
      foreach (explode("@@", $tmp[0]) as $key){
       $key = str_replace("\\", "/", $key);
       $content  .= "\n\t\tFile = \"$key\"";
      }
     }elseif($type=='h3c'){
      //$fileserver = M('fileserver')->where(array('id'=>$tmp[1]))->find();
      //if($fileserver){
      //	$fileserver['path'] = str_replace("\\", "/", $fileserver['path']);
      //	$content  .= "\n\t\tFile = \"{$fileserver['path']}/\"";
      //}else{
      $content  .= "\n\t\tFile = \"\"";
      //}
     }elseif(substr($type,0,2) == 'db' && $type!='dbsql'){
      $include = explode("@@", $include);
      $path = rtrim($include[1],'/');
      $path = rtrim($include[1],'\\');
      $path = str_replace("\\", "/", $path);
      $directory = "/";
//						if(substr($path, 0,1) != "/"){
//							$directory = '\\';
//						}
      if($type == 'dbad'){//活动目录
       $content  .= "\n\t\tFile = \"{$path}\"";
      }else{
       if($include[0] == 1){
        $content  .= "\n\t\tFile = \"{$path}{$directory}full\"";
       }else{
        $content  .= "\n\t\tFile = \"{$path}{$directory}log\"";
       }
      }
     }else{
      $content  .= "\n\t\tPlugin = \"";
      if($type=='vms'){
       //$content  .= "python:module_path=/opt/rongan/plugin:module_name=rongan-fd-vmware:dc=$tmp[0]:folder=$tmp[5]:vmname=$tmp[4]:vcserver=$tmp[1]:vcuser=$tmp[2]:vcpass=$tmp[3]\"";
       $virtual = M('virtual')->where(array('vid'=>'vm','id'=>$tmp[0]))->find();
       if($virtual){
        $keys = unserialize($virtual['values']);
        $content .= "python:module_path=/opt/rongan/plugin:module_name=rongan-fd-vmware:dc={$keys['dc']}:folder=$tmp[1]:vmname=$tmp[2]:vcserver={$keys['ip']}:vcuser={$keys['user']}:vcpass={$keys['pwd']}\"";
       }else{
        $content  .= "";
       }
      }elseif($type=='dbsql'){
       if($tmp[1]){
        $content  .= "mssqlvdi:instance=$tmp[0]:database=$tmp[3]:username=$tmp[1]:password=$tmp[2]\"";
       }  else {
        $content  .= "mssqlvdi:instance=$tmp[0]:database=$tmp[3]\"";
       }
      }
//	                    elseif($type=='dbmysql'){
//	                        $content  .= "python:module_path=/opt/rongan/plugin:module_name=rongan-fd-mysql:mysqluser=$tmp[0]:mysqlpassword=$tmp[1]:db=$tmp[2]\"";
//	                        //$db = str_replace(',', ' ', $tmp[2]);
//	                        //$content  .= "bpipe:/mysql/db.sql:mysqldump  --user=$tmp[0] --password=$tmp[1]  --databases $db:mysql --user=$tmp[0] --password=$tmp[1]\"";
//	                    }
      elseif($type=='dbpgsql'){//postgres sql
       $content  .= "bpipe:/pgsql/dump.sql:pg_dumpall -U postgres\"";
      }
     }
     $content  .= "\n\t}";
    }elseif($k =="exclude"){
     if($v){
      $content  .= "\n\t$k{";
      foreach (explode("@@", $v) as $key){
       $content  .= "\n\t\tFile = \"$key\"";
      }
      $content  .= "\n\t}";
     }
    }else{
     $content  .= "\n\t$k = $v";
    }
   }else{
    if($removeeq){
     if($v===""){
      $content  .= "\n\t$k";
     }else{
      $content  .= "\n\t$k = $v";
     }
    }else{
     $content  .= "\n\t$k = $v";
    }
   }
  }
 }
 $content .= "\n}";
 return $content;
}

/**
 * 递归创建目录
 * @param $dir
 * @return bool
 */
function mkdirs($dir) {
 if (!$dir)
  return FALSE;
 if (!is_dir($dir)) {
  mkdirs(dirname($dir));
  mkdir($dir, 0777);
 }
 return true;
}

/**
 * 保存文件
 * @param $fileName
 * @param $text
 * @return bool
 */
function saveFile($fileName, $text,$style='w') {
//    if (!$fileName || !$text)
//        return false;

 if (mkdirs(dirname($fileName))) {
  if ($fp = fopen($fileName, $style)) {
   if (@fwrite($fp, $text)) {
    fclose($fp);
    return true;
   } else {
    fclose($fp);
    return false;
   }
  }
 }
 return false;
}

/**
 * 执行命令
 * @param type $name
 */
function _exec($name){
 @exec($name);
}

/**
 * 系统邮件发送函数
 * @param string $to    接收邮件者邮箱
 * @param string $name  接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body    邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 */
function _sendMail($to, $name, $subject = '', $body = '', $attachment = null){
 $config = C('T_EMAIL');
 import('PHPMailer');
 require C_PATH . '/Hlib/Util/class.phpmailer.php';
 //vendor('PHPMailer.class#phpmailer'); //从PHPMailer目录导class.phpmailer.php类文件
 $mail             = new PHPMailer(); //PHPMailer对象
 $mail->CharSet    = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
 $mail->IsSMTP();  // 设定使用SMTP服务
 $mail->SMTPDebug  = 0;                     // 关闭SMTP调试功能
 // 1 = errors and messages
 // 2 = messages only
 $mail->SMTPAuth   = true;                  // 启用 SMTP 验证功能
 $mail->SMTPSecure = 'ssl';                 // 使用安全协议
 $mail->Host       = $config['SMTP_HOST'];  // SMTP 服务器
 $mail->Port       = $config['SMTP_PORT'];  // SMTP服务器的端口号
 $mail->Username   = $config['SMTP_USER'];  // SMTP服务器用户名
 $mail->Password   = $config['SMTP_PASS'];  // SMTP服务器密码
 $mail->SetFrom($config['FROM_EMAIL'], $config['FROM_NAME']);
 $replyEmail       = $config['REPLY_EMAIL']?$config['REPLY_EMAIL']:$config['FROM_EMAIL'];
 $replyName        = $config['REPLY_NAME']?$config['REPLY_NAME']:$config['FROM_NAME'];
 $mail->AddReplyTo($replyEmail, $replyName);
 $mail->Subject    = $subject;
 $mail->MsgHTML($body);
 $mail->AddAddress($to, $name);
 if(is_array($attachment)){ // 添加附件
  foreach ($attachment as $file){
   is_file($file) && $mail->AddAttachment($file);
  }
 }
 return $mail->Send() ? true : $mail->ErrorInfo;
}

/**
 * 短信发送
 * @param type $tel
 * @param type $message
 * @return int 0 短信发送成功,等待审核 1 短信发送成功 else 短信发送失败
 */
function _sendMessage($tel,$message){
 $config = C('T_SMS');
 $client = new SoapClient('http://mb345.com:999/ws/LinkWS.asmx?wsdl',array('encoding'=>'UTF-8'));
 $sendParam = array(
     'CorpID'=>$config['SMS_UID'],
     'Pwd'=>$config['SMS_UPWD'],
     'Mobile'=>$tel,
     'Content'=>$message,
     'Cell'=>'',
     'SendTime'=>''
 );
 $result = $client->BatchSend($sendParam);
 $result = $result->BatchSendResult;
 $client = null;
 return $result;
}

/**
 * 导出为CSV文件
 * @param type $filename
 * @param type $data
 */
function export_csv($filename,$data){
 header("Content-type:text/csv");
 header("Content-Disposition:attachment;filename=".$filename);
 header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
 header('Expires:0');
 header('Pragma:public');
 echo $data;
}

/**
 * 文件或目录权限检查函数
 *
 * @access          public
 * @param           string  $file_path   文件路径
 * @param           bool    $rename_prv  是否在检查修改权限时检查执行rename()函数的权限
 *
 * @return          int     返回值的取值范围为{0 <= x <= 15}，每个值表示的含义可由四位二进制数组合推出。
 *                          返回值在二进制计数法中，四位由高到低分别代表
 *                          可执行rename()函数权限、可对文件追加内容权限、可写入文件权限、可读取文件权限。
 */
function file_mode_info($file_path)
{
 /* 如果不存在，则不可读、不可写、不可改 */
 if (!file_exists($file_path))
 {
  return false;
 }
 $mark = 0;
 if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
 {
  /* 测试文件 */
  $test_file = $file_path . '/cf_test.txt';
  /* 如果是目录 */
  if (is_dir($file_path))
  {
   /* 检查目录是否可读 */
   $dir = @opendir($file_path);
   if ($dir === false)
   {
    return $mark; //如果目录打开失败，直接返回目录不可修改、不可写、不可读
   }
   if (@readdir($dir) !== false)
   {
    $mark ^= 1; //目录可读 001，目录不可读 000
   }
   @closedir($dir);
   /* 检查目录是否可写 */
   $fp = @fopen($test_file, 'wb');
   if ($fp === false)
   {
    return $mark; //如果目录中的文件创建失败，返回不可写。
   }
   if (@fwrite($fp, 'directory access testing.') !== false)
   {
    $mark ^= 2; //目录可写可读011，目录可写不可读 010
   }
   @fclose($fp);
   @unlink($test_file);
   /* 检查目录是否可修改 */
   $fp = @fopen($test_file, 'ab+');
   if ($fp === false)
   {
    return $mark;
   }
   if (@fwrite($fp, "modify test.\r\n") !== false)
   {
    $mark ^= 4;
   }
   @fclose($fp);
   /* 检查目录下是否有执行rename()函数的权限 */
   if (@rename($test_file, $test_file) !== false)
   {
    $mark ^= 8;
   }
   @unlink($test_file);
  }
  /* 如果是文件 */
  elseif (is_file($file_path))
  {
   /* 以读方式打开 */
   $fp = @fopen($file_path, 'rb');
   if ($fp)
   {
    $mark ^= 1; //可读 001
   }
   @fclose($fp);
   /* 试着修改文件 */
   $fp = @fopen($file_path, 'ab+');
   if ($fp && @fwrite($fp, '') !== false)
   {
    $mark ^= 6; //可修改可写可读 111，不可修改可写可读011...
   }
   @fclose($fp);
   /* 检查目录下是否有执行rename()函数的权限 */
   if (@rename($test_file, $test_file) !== false)
   {
    $mark ^= 8;
   }
  }
 }
 else
 {
  if (@is_readable($file_path))
  {
   $mark ^= 1;
  }
  if (@is_writable($file_path))
  {
   $mark ^= 14;
  }
 }
 return $mark;
}

/**
 * 为文件或者目录提升权限
 * @param type $file 文件或者目录 支持 /xxx-*的模式
 * @param type $rwx 读写修改的权限 777
 * @param type $cacle 是否开启缓存，若开启缓存 则坚持缓存是否存在，不重复执行
 */
function _chmod($file,$rwx,$cacle=false){
 if($cacle){
  if(!S($file)){
   _exec("sudo chmod $rwx $file");
   S($file,1);
  }
 }else{
  _exec("sudo chmod $rwx $file");
 }
}
/**
 * 截取字符串  超出长度+...
 * @param  [type] $text   [description]
 * @param  [type] $length [description]
 * @return [type]         [description]
 */
function subtext($text, $length)
{
 if(mb_strlen($text, 'utf8') > $length)
  return mb_substr($text, 0, $length, 'utf8').'...';
 return $text;
}