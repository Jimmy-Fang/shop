<?php
/**
 * 
 * Jrsn Backup Management System
 * 
 * @Description    程序 入口文件
 * @version        v1.0
 * @author         Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

header('Content-Type: text/html; charset=utf-8');

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
/**
 * 系统调试设置 上线后设置为false
 */
define('APP_DEBUG',true);
define('APP_VERSION','20160601');
define('RIGHTS_URL','www.jingrongshuan.com');
define('RIGHTS_NAME','四川精容数安科技有限公司');
define('RIGHTS_TEL','4000-732-832');
define('VTL_VERSION','Runstor VTL 5.0 Build 20160820');

define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME)); // 该文件的名称
define('SITE_PATH', str_replace( '\\' , '/' ,str_replace(SELF, '', __FILE__))); // 网站根目录
define('HOST',          'http://'.$_SERVER['HTTP_HOST'].'/');
define("WEB_ROOT", str_replace( '\\' , '/' , $_SERVER['DOCUMENT_ROOT'].'/'));
define('APPHOST',       HOST.str_replace(WEB_ROOT,'',SITE_PATH));//程序根目录

define('C_PATH',SITE_PATH . 'jrsa');
// 定义应用目录
define('APP_PATH',C_PATH . '/Apps/');

//定义运行时目录
define('RUNTIME_PATH',SITE_PATH . '.Runtime/');

// 引入ThinkPHP入口文件
require C_PATH . '/Core/ThinkPHP/ThinkPHP.php';
