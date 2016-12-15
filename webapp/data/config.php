<?php

/*
 * Jrsn Backup Management System
 * @Description   数据库配置文件
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

return array(
    /* 数据库设置 */
    'DB_TYPE' => 'mysql', // 数据库类型
    'DB_HOST' => '127.0.0.1', // 服务器地址
    'DB_NAME' => 'webapp', // 数据库名
    'DB_USER' => '#DB_USER#', // 用户名
    'DB_PWD' => '#DB_PWD#', // 密码
    'DB_PORT' => '3306', // 端口
    'DB_PREFIX' => 't_', // 数据库表前缀
    'DB_CHARSET'=> 'utf8', // 字符集
    'DB_SQL_LOG'=>true,//SQL执行日志记录
);
