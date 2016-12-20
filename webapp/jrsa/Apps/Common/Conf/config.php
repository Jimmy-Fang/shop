<?php
return array(

    /* 全局过滤配置 */
    'DEFAULT_FILTER' => '', //全局过滤函数

    // 显示页面Trace信息
    'SHOW_PAGE_TRACE' =>false,
		
    /* 命名空间 */
    'AUTOLOAD_NAMESPACE' => array(
       'Hlib' =>C_PATH . '/Hlib',
    ),
    '_VERSION'=>'2.5',
    // 加载扩展配置文件
    'LOAD_EXT_CONFIG' => 'mail,sms',
    /* 数据库设置 */

        'DB_TYPE' => 'mysql', // 数据库类型
        'DB_HOST' => 'localhost', // 服务器地址
        'DB_NAME' => 'webapp', // 数据库名
        'DB_USER' => 'root', // 用户名
        'DB_PWD' => '', // 密码
        'DB_PORT' => '3306', // 端口
        'DB_PREFIX' => 't_', // 数据库表前缀
        'DB_CHARSET'=> 'utf8', // 字符集
        'DB_SQL_LOG'=>true,//SQL执行日志记录

   'TMPL_EXCEPTION_FILE'=>COMMON_PATH.'Extend/Logger/exception.php',
    
   /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__ . '/statics/',
        '__SH__' => __ROOT__ . '/sh/',
        '__ADDONS__' => __ROOT__ . '/statics/' . MODULE_NAME . '/Addons',
        '__IMG__' => __ROOT__ . '/statics/images',
        '__CSS__' => __ROOT__ . '/statics/css',
        '__JS__' => __ROOT__ . '/statics/js',
        '__ROOT__' => __ROOT__,
    ),
    
    'CONFIG_SAVE_PATH'=>'/var/www/html/webapp/conf/',//必须以斜杠 (/) 结尾
    'SH_SAVE_PATH'=>'/opt/rongan/etc/scripts/',//必须以斜杠 (/) 结尾
    'SH_PATH'=>'/var/www/html/webapp/sh/',//必须以斜杠 (/) 结尾
    //'CONFIG_SAVE_PATH'=>'d:/conf/',//必须以斜杠 (/) 结尾
);