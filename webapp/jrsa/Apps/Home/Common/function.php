<?php

use  \Hlib\Util\Encrypt;
/*
 * Jrsn Backup Management System
 * @Description   Home 公用函数库
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

/**
 * 获取服务器相关信息  版本 操作系统，最后启动时间等
 */
function getServerInfo($status){
    $status = array_filter($status);
    $str = implode('@',$status);
    $serverInfo = array();
//    if (preg_match("/Version:\s\S*/i", $str, $temp_array)) {
//        $serverInfo['version'] = $temp_array[0];
//    }
    $tmp = explode("Version:", $str);
    $tmp = explode(' ',$tmp[1]);
    $serverInfo['version'] = $tmp[1];
    $tmp = explode('started', $str);
    $tmp = explode('.',$tmp[1]);
    $tmp = $tmp[0];
    //$tmp = mb_convert_encoding($tmp, "GBK", "UTF-8");
    $tmp = iconv("GBK", "UTF-8",$tmp);
    if(preg_match("/^\d{2}-\d{6}\s\d{2}:\d{2}/", ltrim($tmp))){
         $tmp = explode(" ", ltrim($tmp));
         $tmp = dataFormat($tmp[0]) . ' ' .$tmp[1];
    }
    $tmp =  date("Y-m-d H:i",  strtotime($tmp));
    $serverInfo['date'] = $tmp;
    //$tmp = explode(' ', ltrim($status[7]));
    //$serverInfo['os'] = "{$tmp[7]} {$tmp[8]} {$tmp[9]} {$tmp[10]} {$tmp[11]} {$tmp[12]}";
    $os = null;
    foreach ($status as $key=>$item){
        if(strstr($item,"Client Version")){
            $tmp = explode(' ', ltrim($item));
            $serverInfo['os'] = "{$tmp[7]} {$tmp[8]} {$tmp[9]} {$tmp[10]} {$tmp[11]} {$tmp[12]}";
        }
        if(strstr($item,"virtual3-fd Version")){
            $tmp = explode(' ', ltrim($item));
            $serverInfo['os'] = "{$tmp[7]} {$tmp[8]} {$tmp[9]} {$tmp[10]} {$tmp[11]} {$tmp[12]}";
        }
        if(strstr($item,"x86_64")){
            $os = $item;
        }
    }
        
    if(!$serverInfo['os']){
        $tmp = explode(' ', ltrim($os));
        $serverInfo['os'] = "{$tmp[7]} {$tmp[8]} {$tmp[9]} {$tmp[10]} {$tmp[11]} {$tmp[12]}";
    }
    
    return $serverInfo;
}


/**
 * 不过滤为0的函数
 * @param type $v
 * @return boolean
 */
function _filterEmpty($v){
    if($v){
        return true;
    }else{
        if($v == '0'){
            return true;
        }
        return FALSE;
    }
}

/**
 * //28-102014 日期格式化
 * @param type $ddate
 * @return string
 */
function dataFormat($ddate){
    $dyear =substr($ddate, -4);//年 
    $ddate = substr_replace($ddate,'',-4); 
    $ddate = explode("-", $ddate);
    $ddate = $dyear . '-' .$ddate[1] . '-' .$ddate[0];
    return $ddate;
}

/******************** ==数据库复制公用函数== ********************/

function oracleConnect($dbinfo){
    $conn = oci_connect($dbinfo['dbuser'], $dbinfo['dbpwd'], "{$dbinfo['dbhost']}:{$dbinfo['dbport']}/{$dbinfo['dbname']}", null, OCI_SYSDBA);
    if (!$conn) {
        $e = oci_error();
        $result = htmlentities($e['message']);
    }else{
        $result = 1;
        oci_close($conn);
    }
    return $result;
}

function oracleConnVersion($dbinfo){
    $conn = oci_connect($dbinfo['dbuser'], $dbinfo['dbpwd'], "{$dbinfo['dbhost']}:{$dbinfo['dbport']}/{$dbinfo['dbname']}", null, OCI_SYSDBA);
    $result = oci_server_version($conn);
    oci_close($conn);
    return $result;
}

/**
 * 
 * @param type $dbinfo
 * @param type $sql
 * @return type
 */
function oracleQuery($dbinfo,$sql,$output=1){
    $conn = oci_connect($dbinfo['dbuser'], $dbinfo['dbpwd'], "{$dbinfo['dbhost']}:{$dbinfo['dbport']}/{$dbinfo['dbname']}", null, OCI_SYSDBA);
    if (!$conn) {
        $e = oci_error();
        if($output){
            print htmlentities($e['message']);
            exit; 
        }
    }
    $stid = oci_parse($conn, $sql); 
    if (!$stid) {
        $e = oci_error($conn); 
        if($output){
            print htmlentities($e['message']);
            exit; 
        }
    }
    $r = oci_execute($stid, OCI_DEFAULT); 
    if(!$r) {
        $e = oci_error($stid);
        if($output){
            print htmlentities($e['message']);
            exit; 
        }
    }
    $row = oci_fetch_array($stid, OCI_RETURN_NULLS);
    oci_free_statement($stid);
    oci_close($conn);
    return $row;
}

/**
 * 数据查询 返回数组
 * @param type $dbinfo
 * @param type $sql
 * @param type $output
 * @return type
 */
function oracleSelect($dbinfo,$sql,$output=1){
    $conn = oci_connect($dbinfo['dbuser'], $dbinfo['dbpwd'], "{$dbinfo['dbhost']}:{$dbinfo['dbport']}/{$dbinfo['dbname']}", null, OCI_SYSDBA);
    if (!$conn) {
        $e = oci_error();
        if($output){
            print htmlentities($e['message']);
            exit; 
        }
    }
    $stid = oci_parse($conn, $sql); 
    if (!$stid) {
        $e = oci_error($conn); 
        if($output){
            print htmlentities($e['message']);
            exit; 
        }
    }
    $r = oci_execute($stid, OCI_DEFAULT); 
    if(!$r) {
        $e = oci_error($stid);
        if($output){
            print htmlentities($e['message']);
            exit; 
        }
    }
    //$row = oci_fetch_array($stid, OCI_RETURN_NULLS);
    $row = oci_fetch_all($stid, $results);
    $result = array();
    if ($row > 0) {
        $result = $results;
    }
    oci_free_statement($stid);
    oci_close($conn);
    return $result;
}

/**
 * array_filter 过滤空元素 不包括 0
 * @param type $var
 * @return type
 */
function hink_not_filter0($var){
   return($var<>'');
}

/**
 * 解析License
 */
function _getLicenseData(){
    $licenses = M('license')->find();
    return json_decode(Encrypt::authcode($licenses['license'],'DECODE',md5('jrsa')),true);
}

/**
 * 判断授权是否超出限制
 * @param type $itme
 * @param type $value
 * -1:无任何授权
 * 0:试用期到期
 * 1:试用未到期
 * 2:授权信息有误
 * 3: 存在授权信息 到期
 * 4: 存在授权信息 未到期
  * @return boolean
  */
function _checkLicense($itme,$value){
    if(LICENSE == -1 || LICENSE == 0 || LICENSE == 2 || LICENSE == 3){
        return false;
    }elseif(LICENSE == 1){
        return true;
    }else{
        $data = _getLicenseData();
        if(!$data[$itme]){
             return false;
        }
        if($value =='trueorfalse'){
            if($data[$itme]){
                return true;
           }
        }
        if($data[$itme]<0){
            return true;
        }
        if(($data[$itme] + 1)>=$value){
            return true;
        }else{
            return false;
        }
    }
}
