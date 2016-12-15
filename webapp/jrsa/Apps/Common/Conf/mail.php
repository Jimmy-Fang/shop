<?php

/*
 * Jrsn Backup Management System
 * @Description   邮件配置
 * @version          v1.0
 * @author           Hink<Hink@live.cn>
 * @copyright      (c) 2013 - 9999, Hink, Inc.
 */

return array(
    //邮件配置
    'T_EMAIL' => array(
       'SMTP_HOST'   => 'smtp.qq.com', //SMTP服务器
       'SMTP_PORT'   => '465', //SMTP服务器端口
       'SMTP_USER'   => 'server@jingrongshuan.com', //SMTP服务器用户名
       'SMTP_PASS'   => 'jrsa2014', //SMTP服务器密码
       'FROM_EMAIL'  => 'server@jingrongshuan.com', //发件人EMAIL
       'FROM_NAME'   => '精容数安', //发件人名称
       'REPLY_EMAIL' => '', //回复EMAIL（留空则为发件人EMAIL）
       'REPLY_NAME'  => '', //回复名称（留空则为发件人名称）
    ),
);
