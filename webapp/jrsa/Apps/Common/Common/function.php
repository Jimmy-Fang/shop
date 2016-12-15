<?php

/*
 * Jrsn Backup Management System
 * @Description   ���ú�����
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

/**
 * ��ȡ��ǰ��ַ·��
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
 $filelist = array(); //�ļ������ļ�
 while (($file = readdir($dh)) !== false) {
  if ($file != "." && $file != ".." && $file != ".svn") {
   $fullpath = $dir . $file;
   if (is_dir($fullpath)) {
    $info = folder_info($fullpath);
    if ($check_children) {
     $info['isParent'] = path_haschildren($fullpath, $list_file);
    }
    $folderlist[] = $info;
   } else if ($list_file) {//�Ƿ��г��ļ�
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
 * ��ȡ�ļ���ϸ��Ϣ
 */
function folder_info($path, $date_formate = false) {
 if (!$date_formate)
  $date_formate = $GLOBALS['L']['time_type'];
 $info = array(
     'name' => iconv_app(get_path_this($path)),
     'path' => iconv_app(get_path_father($path)),
     'type' => 'folder',
     'mode' => get_mode($path),
     'atime' => date($date_formate, fileatime($path)), //����ʱ��
     'ctime' => date($date_formate, filectime($path)), //����ʱ��
     'mtime' => date($date_formate, filemtime($path)), //����޸�ʱ��
     'is_readable' => intval(is_readable($path)),
     'is_writeable' => intval(is_writeable($path))
 );
 return $info;
}

// �������Ϊ�������ʱ���д��������ó�����룬
// �������û�к�����޹�ʱ������ʱ�����ϵͳ���롣
function iconv_app($str) {
 return iconv("gb2312","UTF-8",$str);
}

/**
 * ��ȡһ��·��(�ļ���&�ļ�) ��ǰ�ļ�[��]��
 * test/11/ ==>11 test/1.c  ==>1.c
 */
function get_path_this($path) {
 $path = str_replace('\\', '/', rtrim(trim($path), '/'));
 return substr($path, strrpos($path, '/') + 1);
}

/**
 * ��ȡһ��·��(�ļ���&�ļ�) ��Ŀ¼
 * /test/11/==>/test/   /test/1.c ==>/www/test/
 */
function get_path_father($path) {
 $path = str_replace('\\', '/', rtrim(trim($path), '/'));
 return substr($path, 0, strrpos($path, '/') + 1);
}

/**
 * ��ȡ�ļ�(��)Ȩ�� rwx_rwx_rwx
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
 * ��ȡ�ļ���ϸ��Ϣ
 * �ļ����ӳ������ת����ϵͳ����,����utf8��ϵͳ������ҪΪgbk
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
     'atime' => date($date_formate, fileatime($path)), //����ʱ��
     'ctime' => date($date_formate, filectime($path)), //����ʱ��
     'mtime' => date($date_formate, filemtime($path)), //����޸�ʱ��
     'is_readable' => intval(is_readable($path)),
     'is_writeable' => intval(is_writeable($path)),
     'size' => $size,
     'size_friendly' => size_format($size, 2)
 );
 return $info;
}

/**
 * ��ȡ��չ��
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
 * �ļ���С��ʽ��
 *
 * @param  $ :$bytes, int �ļ���С
 * @param  $ :$precision int  ����С����
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
 * ��ȡ�ļ����������ļ�
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
 * ��ȡָ�����ȵ�����ַ�
 * @param type $len ����
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
 // ���������
 shuffle($chars);
 $output = "";
 for ($i = 0; $i < $len; $i++) {
  $output .= $chars[mt_rand(0, $charsLen)];
 }
 return $output;
}

/**
 * �����ļ���ַ��ȡ�ļ�����
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
 * ����ǩ����֤
 * @param $data
 * @return string
 */
function data_auth_sign($data){
 if(!is_array($data)){
  $data = (array)$data;
 }
 ksort($data);//����
 $code = http_build_query($data);//���� url-encoded ֮��������ַ�������
 $sign = sha1($code); //����ǩ��
 return $sign;
}

/**
 * ���������ļ�
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
   //�Ŵ���
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
  }elseif ($item['Media Type'] === 'tape') {//�Ŵ���
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
  //����TLS
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
  if (strtolower($item['Media Type']) == 'tape') {//�Ŵ���
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
 * ����data��������
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
      if($type == 'dbad'){//�Ŀ¼
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
 * �ݹ鴴��Ŀ¼
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
 * �����ļ�
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
 * ִ������
 * @param type $name
 */
function _exec($name){
 @exec($name);
}

/**
 * ϵͳ�ʼ����ͺ���
 * @param string $to    �����ʼ�������
 * @param string $name  �����ʼ�������
 * @param string $subject �ʼ�����
 * @param string $body    �ʼ�����
 * @param string $attachment �����б�
 * @return boolean
 */
function _sendMail($to, $name, $subject = '', $body = '', $attachment = null){
 $config = C('T_EMAIL');
 import('PHPMailer');
 require C_PATH . '/Hlib/Util/class.phpmailer.php';
 //vendor('PHPMailer.class#phpmailer'); //��PHPMailerĿ¼��class.phpmailer.php���ļ�
 $mail             = new PHPMailer(); //PHPMailer����
 $mail->CharSet    = 'UTF-8'; //�趨�ʼ����룬Ĭ��ISO-8859-1����������Ĵ���������ã���������
 $mail->IsSMTP();  // �趨ʹ��SMTP����
 $mail->SMTPDebug  = 0;                     // �ر�SMTP���Թ���
 // 1 = errors and messages
 // 2 = messages only
 $mail->SMTPAuth   = true;                  // ���� SMTP ��֤����
 $mail->SMTPSecure = 'ssl';                 // ʹ�ð�ȫЭ��
 $mail->Host       = $config['SMTP_HOST'];  // SMTP ������
 $mail->Port       = $config['SMTP_PORT'];  // SMTP�������Ķ˿ں�
 $mail->Username   = $config['SMTP_USER'];  // SMTP�������û���
 $mail->Password   = $config['SMTP_PASS'];  // SMTP����������
 $mail->SetFrom($config['FROM_EMAIL'], $config['FROM_NAME']);
 $replyEmail       = $config['REPLY_EMAIL']?$config['REPLY_EMAIL']:$config['FROM_EMAIL'];
 $replyName        = $config['REPLY_NAME']?$config['REPLY_NAME']:$config['FROM_NAME'];
 $mail->AddReplyTo($replyEmail, $replyName);
 $mail->Subject    = $subject;
 $mail->MsgHTML($body);
 $mail->AddAddress($to, $name);
 if(is_array($attachment)){ // ��Ӹ���
  foreach ($attachment as $file){
   is_file($file) && $mail->AddAttachment($file);
  }
 }
 return $mail->Send() ? true : $mail->ErrorInfo;
}

/**
 * ���ŷ���
 * @param type $tel
 * @param type $message
 * @return int 0 ���ŷ��ͳɹ�,�ȴ���� 1 ���ŷ��ͳɹ� else ���ŷ���ʧ��
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
 * ����ΪCSV�ļ�
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
 * �ļ���Ŀ¼Ȩ�޼�麯��
 *
 * @access          public
 * @param           string  $file_path   �ļ�·��
 * @param           bool    $rename_prv  �Ƿ��ڼ���޸�Ȩ��ʱ���ִ��rename()������Ȩ��
 *
 * @return          int     ����ֵ��ȡֵ��ΧΪ{0 <= x <= 15}��ÿ��ֵ��ʾ�ĺ��������λ������������Ƴ���
 *                          ����ֵ�ڶ����Ƽ������У���λ�ɸߵ��ͷֱ����
 *                          ��ִ��rename()����Ȩ�ޡ��ɶ��ļ�׷������Ȩ�ޡ���д���ļ�Ȩ�ޡ��ɶ�ȡ�ļ�Ȩ�ޡ�
 */
function file_mode_info($file_path)
{
 /* ��������ڣ��򲻿ɶ�������д�����ɸ� */
 if (!file_exists($file_path))
 {
  return false;
 }
 $mark = 0;
 if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
 {
  /* �����ļ� */
  $test_file = $file_path . '/cf_test.txt';
  /* �����Ŀ¼ */
  if (is_dir($file_path))
  {
   /* ���Ŀ¼�Ƿ�ɶ� */
   $dir = @opendir($file_path);
   if ($dir === false)
   {
    return $mark; //���Ŀ¼��ʧ�ܣ�ֱ�ӷ���Ŀ¼�����޸ġ�����д�����ɶ�
   }
   if (@readdir($dir) !== false)
   {
    $mark ^= 1; //Ŀ¼�ɶ� 001��Ŀ¼���ɶ� 000
   }
   @closedir($dir);
   /* ���Ŀ¼�Ƿ��д */
   $fp = @fopen($test_file, 'wb');
   if ($fp === false)
   {
    return $mark; //���Ŀ¼�е��ļ�����ʧ�ܣ����ز���д��
   }
   if (@fwrite($fp, 'directory access testing.') !== false)
   {
    $mark ^= 2; //Ŀ¼��д�ɶ�011��Ŀ¼��д���ɶ� 010
   }
   @fclose($fp);
   @unlink($test_file);
   /* ���Ŀ¼�Ƿ���޸� */
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
   /* ���Ŀ¼���Ƿ���ִ��rename()������Ȩ�� */
   if (@rename($test_file, $test_file) !== false)
   {
    $mark ^= 8;
   }
   @unlink($test_file);
  }
  /* ������ļ� */
  elseif (is_file($file_path))
  {
   /* �Զ���ʽ�� */
   $fp = @fopen($file_path, 'rb');
   if ($fp)
   {
    $mark ^= 1; //�ɶ� 001
   }
   @fclose($fp);
   /* �����޸��ļ� */
   $fp = @fopen($file_path, 'ab+');
   if ($fp && @fwrite($fp, '') !== false)
   {
    $mark ^= 6; //���޸Ŀ�д�ɶ� 111�������޸Ŀ�д�ɶ�011...
   }
   @fclose($fp);
   /* ���Ŀ¼���Ƿ���ִ��rename()������Ȩ�� */
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
 * Ϊ�ļ�����Ŀ¼����Ȩ��
 * @param type $file �ļ�����Ŀ¼ ֧�� /xxx-*��ģʽ
 * @param type $rwx ��д�޸ĵ�Ȩ�� 777
 * @param type $cacle �Ƿ������棬���������� ���ֻ����Ƿ���ڣ����ظ�ִ��
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
 * ��ȡ�ַ���  ��������+...
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