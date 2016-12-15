DROP DATABASE IF EXISTS `webapp`;

CREATE DATABASE `webapp` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
use webapp;

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for t_apps
-- ----------------------------
DROP TABLE IF EXISTS `t_apps`;
CREATE TABLE `t_apps` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'appID',
  `pid` int(11) DEFAULT '0' COMMENT '针对APP有效 用于指定当前APP属于哪一个目录',
  `name` varchar(50) NOT NULL COMMENT 'APP名称',
  `content` varchar(255) NOT NULL COMMENT 'app url ',
  `desc` varchar(200) DEFAULT NULL COMMENT 'APP描述',
  `icon` varchar(100) NOT NULL COMMENT 'APP 图标',
  `group` smallint(6) NOT NULL COMMENT '分组ID',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'APP类型 1为URL模式  2 为JS模式 3为folder',
  `resize` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否允许调整窗口大小',
  `simple` tinyint(1) NOT NULL DEFAULT '0' COMMENT '窗口是否有边框',
  `width` smallint(5) NOT NULL DEFAULT '800' COMMENT '窗口宽度度',
  `height` smallint(5) NOT NULL DEFAULT '600' COMMENT '窗口高度',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态是否删除',
  `createtime` int(11) NOT NULL COMMENT '创建时间',
  `updatetime` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='应用表';

-- ----------------------------
-- Records of t_apps
-- ----------------------------


-- ----------------------------
-- Table structure for t_client
-- ----------------------------
DROP TABLE IF EXISTS `t_client`;
CREATE TABLE `t_client` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(10) DEFAULT '0' COMMENT '客户端分组ID',
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '客户端名称',
  `enabled` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'yes' COMMENT '是否激活',
  `protocol` enum('Native','NDMP') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '备份协议',
  `description` tinyblob COMMENT '描述说明',
  `class` enum('Windows','Linux','Unix') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '客户端类型',
  `authtype` enum('None','Clear','MD5') COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` tinyblob NOT NULL COMMENT 'IP地址',
  `fdPort` smallint(5) unsigned DEFAULT '9102',
  `catalogId` int(10) unsigned DEFAULT NULL,
  `username` tinyblob,
  `password` tinyblob NOT NULL COMMENT '通讯加密',
  `hardQuota` int(10) unsigned DEFAULT NULL,
  `softQuota` int(10) unsigned DEFAULT NULL,
  `softQuotaGracePeriod` int(10) unsigned DEFAULT NULL,
  `strictQuotas` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `quotaIncludeFailedJobs` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `fileRetention` tinyblob COMMENT '备份文件保留周期',
  `jobRetention` tinyblob COMMENT '备份任务保留周期',
  `autoPrune` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `maxConcurrentJobs` tinyint(3) unsigned DEFAULT '4',
  `maxBandwidthPerJob` tinyblob COMMENT '任务限速',
  `ndmpLoglevel` int(10) unsigned DEFAULT NULL,
  `ndmpBlocksize` int(10) unsigned DEFAULT NULL,
  `priority` tinyint(3) unsigned DEFAULT NULL COMMENT '优先级，越小优先级越高',
  `passive` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no' COMMENT 'NAT支持',
  `state` tinyint(1) DEFAULT '0' COMMENT '状态：1删除',
  `count` int(10) DEFAULT '0',
  `TLSEnable` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no' COMMENT 'tls开关',
  `TLSRequire` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'yes' COMMENT '只接受TLS',
  `TLSVerifyPeer` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'yes' COMMENT '证书有效性验证',
  `TLSCertificate` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '证书地址',
  `TLSKey` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '秘钥',
  `TLSCACertificateFile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'CA 证书文件',
  `algorithm` enum('aes128','aes256','camellia128','camellia256','blowfish') COLLATE utf8_unicode_ci DEFAULT 'aes128' COMMENT '加密算法',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_client
-- ----------------------------
INSERT INTO `t_client` VALUES ('1', '1', 'localclient', 'yes', 'Native', 0xE9BB98E8AEA4E5AEA2E688B7E7ABAF, 'Linux', null, 0x3139322E3136382E312E3338, '5102', null, null, '', '0', null, null, null, null, 0x3330, 0x3330, null, '4', 0x30, null, null, '5', 'no', '0', '10', 'no', 'yes', 'yes', '', '', '', 'camellia128');

-- ----------------------------
-- Table structure for t_clientgroup
-- ----------------------------
DROP TABLE IF EXISTS `t_clientgroup`;
CREATE TABLE `t_clientgroup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `count` int(10) unsigned zerofill DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_clientgroup
-- ----------------------------
INSERT INTO `t_clientgroup` VALUES ('1', '默认分组', null);

-- ----------------------------
-- Table structure for t_device
-- ----------------------------
DROP TABLE IF EXISTS `t_device`;
CREATE TABLE `t_device` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '名称',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '描述',
  `archiveDevice` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '备份目录，由用户选择',
  `deviceType` enum('tape','tapes','file','fifo') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '类型，文件或磁带',
  `mediaType` enum('tape','tapes','file','fifo') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '类型，与备份介质保持一致',
  `autochanger` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no' COMMENT '是否是带库',
  `changerDevice` tinyblob COMMENT '带库设备名',
  `changerCommand` tinyblob,
  `alertCommand` tinyblob,
  `driveIndex` int(10) unsigned DEFAULT NULL,
  `autoselect` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `maxConcurrentJobs` tinyint(3) unsigned DEFAULT NULL,
  `maxChangerWait` int(10) unsigned DEFAULT NULL,
  `maxRewindWait` int(10) unsigned DEFAULT NULL,
  `maxOpenWait` int(10) unsigned DEFAULT NULL,
  `alwaysOpen` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `volumePollInterval` int(10) unsigned DEFAULT NULL,
  `closeonPoll` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `removeableMedia` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `randomAccess` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `requiresMount` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `mountPoint` tinyblob,
  `mountCommand` tinyblob,
  `unmountCommand` tinyblob,
  `labelMedia` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `automaticMount` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `blockChecksum` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `maxBlockSize` tinyblob,
  `minBlockSize` tinyblob,
  `labelBlockSize` tinyblob,
  `hardwareEndofMedium` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `firstForwardSpaceFile` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `useMTIOCGET` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `bsFatEOM` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `twoEOF` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `backwardSpaceRecord` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `backwardSpaceFile` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `forwardSpaceRecord` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `forwardSpaceFile` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `offlineOnUnmount` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `noRewindOnClose` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `driveCryptoEnabled` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `queryCryptoStatus` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `maxVolumeSize` bigint(20) unsigned DEFAULT NULL,
  `maxFileSize` bigint(20) unsigned DEFAULT NULL,
  `blockPositioning` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `maxNetworkBufferSize` int(10) unsigned DEFAULT NULL,
  `maxSpoolSize` int(10) unsigned DEFAULT NULL,
  `spoolDirectory` tinyblob,
  `autoDeflate` enum('in','out','both') COLLATE utf8_unicode_ci DEFAULT NULL,
  `autoDeflateAlgorithm` enum('GZIP','LZO','LZFAST','LZ4','LZ4HC') COLLATE utf8_unicode_ci DEFAULT NULL,
  `autoDeflateLevel` enum('0','1','2','3','4','5','6','7','8','9') COLLATE utf8_unicode_ci DEFAULT NULL,
  `autoInflate` enum('in','out','both') COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` tinyint(1) DEFAULT '0' COMMENT '状态：1为删除',
  `count` int(10) DEFAULT '0',
  `vtltype` tinyint(1) DEFAULT '1' COMMENT 'runstor vtl',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_device
-- ----------------------------
INSERT INTO `t_device` VALUES ('1', 'fileserver_device', '默认设备配置。', '/rongan/backup/', 'file', 'file', 'no', 0x30, '', null, '0', 'yes', '4', '0', '0', '0', 'yes', '0', 'yes', 'yes', 'yes', 'yes', null, null, null, 'yes', 'yes', 'yes', 0x3634353132, 0x3634353132, null, 'yes', 'yes', 'yes', 'yes', 'yes', 'yes', 'yes', 'yes', 'yes', 'yes', 'yes', 'yes', 'yes', '0', '20', 'yes', '32768', '20', null, 'in', 'GZIP', '1', 'in', '0', '15', '1');

-- ----------------------------
-- Table structure for t_dirdirector
-- ----------------------------
DROP TABLE IF EXISTS `t_dirdirector`;
CREATE TABLE `t_dirdirector` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '名称，缺省为servervault',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '描述说明',
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `messagesId` int(11) NOT NULL,
  `workingDir` tinyblob,
  `pidDir` tinyblob,
  `queryFile` tinyblob NOT NULL,
  `heartbeatInterval` int(10) unsigned DEFAULT '0',
  `maxConcurrentJobs` int(10) unsigned DEFAULT '4' COMMENT '最大并发任务数',
  `fdConnTimeout` int(10) unsigned DEFAULT NULL,
  `sdConnTimeout` int(10) unsigned DEFAULT NULL,
  `dirPort` smallint(5) unsigned DEFAULT '9101',
  `dirAddress` tinyblob,
  `dirSourceAddress` tinyblob,
  `statisticsRetention` bigint(20) unsigned DEFAULT NULL,
  `verId` tinyblob,
  `maxConsoleConn` tinyint(3) unsigned DEFAULT '20',
  `optimizeForSize` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `optimizeForSpeed` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `EncryptionKey` tinyblob,
  `NDMPSnooping` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `NDMPLoglevel` int(11) DEFAULT NULL,
  `state` tinyint(1) DEFAULT '0' COMMENT '状态 1 删除',
  `count` int(10) DEFAULT '0',
  `TLSEnable` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no' COMMENT 'tls开关',
  `TLSRequire` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'yes' COMMENT '只接受TLS',
  `TLSVerifyPeer` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'yes' COMMENT '证书有效性验证',
  `TLSCertificate` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '证书地址',
  `TLSKey` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '秘钥',
  `TLSCACertificateFile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'CA 证书文件',
  `auditing` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no' COMMENT '审计功能',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_dirdirector
-- ----------------------------
INSERT INTO `t_dirdirector` VALUES ('1', 'servervault', '默认服务器，请不要随意修改服务器名称，以免导致系统异常', '', '0', null, 0x35313031, '', '0', '20', '20', '20', '5101', null, null, null, null, '20', 'no', 'yes', null, 'no', null, '0', '36', 'no', 'no', 'no', '/opt/rongan/cert/director.crt', '/opt/rongan/cert/director.key', '/opt/rongan/cert/cacert.pem', 'yes');
-- ----------------------------
-- Table structure for t_dirstorage
-- ----------------------------
DROP TABLE IF EXISTS `t_dirstorage`;
CREATE TABLE `t_dirstorage` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '名称',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '描述信息',
  `address` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'IP地址',
  `sdPort` smallint(5) unsigned DEFAULT '9103',
  `password` tinyblob NOT NULL,
  `deviceId` int(10) unsigned NOT NULL COMMENT '设备ID',
  `mediaType` tinyblob NOT NULL COMMENT '介质类型',
  `autochanger` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no' COMMENT '是否为带库',
  `maxConcurrentJobs` tinyint(3) unsigned DEFAULT '4',
  `maxConcurrentReadJobs` tinyint(3) unsigned DEFAULT '1',
  `allowCompression` tinyint(1) DEFAULT '0' COMMENT '启动压缩 0 不压缩 1压缩',
  `heartbeatInterval` int(10) unsigned DEFAULT '0',
  `pairedStorage` tinyblob,
  `state` tinyint(1) DEFAULT '0' COMMENT '状态：1为删除',
  `count` int(10) DEFAULT '0',
  `TLSEnable` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no' COMMENT 'tls开关',
  `TLSRequire` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'yes' COMMENT '只接受TLS',
  `TLSVerifyPeer` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'yes' COMMENT '证书有效性验证',
  `TLSCertificate` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '证书地址',
  `TLSKey` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '秘钥',
  `TLSCACertificateFile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'CA 证书文件',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_dirstorage
-- ----------------------------
INSERT INTO `t_dirstorage` VALUES ('1', 'fileserver', '默认介质配置', '192.168.1.38', '5103', '', '1', 0x66696C65, 'no', '4', '1', '1', '0', null, '0', '12', 'no', 'yes', 'yes', '', '', '');

-- ----------------------------
-- Table structure for t_fileset
-- ----------------------------
DROP TABLE IF EXISTS `t_fileset`;
CREATE TABLE `t_fileset` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '名称',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '描述',
  `enableVSS` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no' COMMENT '启动VSS，只针对WINDOWS有效',
  `ignoreFileSetChanges` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `include` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '所需备份的文件',
  `exclude` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '所需排除的文件',
  `compression` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '压缩方式',
  `state` tinyint(1) DEFAULT '0' COMMENT '状态 1删除',
  `types` varchar(20) COLLATE utf8_unicode_ci DEFAULT '0' COMMENT '备份文件类型 0：普通文件 1：虚拟机文件',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_fileset
-- ----------------------------

-- ----------------------------
-- Table structure for t_job
-- ----------------------------
DROP TABLE IF EXISTS `t_job`;
CREATE TABLE `t_job` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '任务名称',
  `enabled` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'yes' COMMENT '是否激活',
  `type` enum('Backup','Restore','Verify','Admin','Copy') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '类型',
  `protocol` enum('Native','NDMP') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '协议',
  `backupFormat` enum('Dump','Tar','SMTape') COLLATE utf8_unicode_ci DEFAULT NULL,
  `level` enum('Full','Incremental','Differential','InitCatalog','Catalog','VolumeToCatalog','DiskToCatalog') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '备份模式',
  `accurate` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `verifyJob` tinyblob,
  `catalogId` int(10) unsigned DEFAULT NULL,
  `jobDefId` int(10) unsigned DEFAULT NULL,
  `bootstrap` tinyblob,
  `writeBootstrap` tinyblob,
  `clientId` int(10) unsigned DEFAULT NULL COMMENT '对应的客户端',
  `fileSetId` int(10) unsigned DEFAULT NULL COMMENT '对应的文件集',
  `app` enum('Oracle','SQL Server','Sybase','DB2','Exchange','Informix','Mysql','Active Directory','PostgreSQL','SharePoint','BMR') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '对应备份的应用',
  `preScript` tinyint(1) DEFAULT '0',
  `postScript` tinyint(1) DEFAULT '0',
  `base` blob,
  `messagesId` int(10) unsigned DEFAULT NULL,
  `poolId` int(10) unsigned DEFAULT NULL,
  `fullBackupPoolId` int(10) unsigned DEFAULT NULL,
  `diffBackupPoolId` int(10) unsigned DEFAULT NULL,
  `incrBackupPoolId` int(10) unsigned DEFAULT NULL,
  `nextPoolId` int(10) unsigned DEFAULT NULL,
  `scheduleId` int(10) unsigned DEFAULT NULL COMMENT '对应的计划',
  `storageId` int(10) unsigned DEFAULT NULL COMMENT '写入的介质',
  `maxStartDelay` int(10) unsigned DEFAULT NULL,
  `maxRunTime` int(10) unsigned DEFAULT NULL,
  `increMaxRunTime` int(10) unsigned DEFAULT NULL,
  `diffMaxWaitTime` int(10) unsigned DEFAULT NULL,
  `maxRunSchedTime` int(10) unsigned DEFAULT NULL,
  `maxWaitTime` int(10) unsigned DEFAULT NULL,
  `maxBandwidth` int(10) unsigned DEFAULT NULL COMMENT '任务限速',
  `maxFullInterval` int(10) unsigned DEFAULT '0',
  `preferMountedVolumes` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `pruneJobs` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `pruneFiles` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `pruneVolumes` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `runBeforeJob` tinyblob,
  `runAfterJob` tinyblob,
  `runAfterFailedJob` tinyblob,
  `clientRunBeforeJob` tinyblob,
  `clientRunAfterJob` tinyblob,
  `returnFailedLevels` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `spoolData` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `spoolAttributes` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `spoolSize` int(10) unsigned DEFAULT NULL,
  `where` tinyblob,
  `addPrefix` tinyblob,
  `addSuffix` tinyblob,
  `stripPrefix` tinyblob,
  `regexWhere` tinyblob,
  `replace` enum('always','ifnewer','ifolder','never') COLLATE utf8_unicode_ci DEFAULT NULL,
  `PrefixLinks` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `maxConncurrentJobs` int(10) unsigned DEFAULT '1',
  `rescheduleOnError` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `rescheduleInterval` tinyblob,
  `rescheduleTimes` int(10) unsigned DEFAULT '0',
  `allowDuplicateJobs` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `cancelLowerLevelDup` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `cancleQueuedDup` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `cancleRunningDup` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `run` tinyblob,
  `priority` tinyint(3) unsigned DEFAULT '10',
  `allowMixedPriority` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `state` tinyint(1) DEFAULT '0' COMMENT '状态 1删除',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '描述',
  `appType` tinyint(1) DEFAULT '1' COMMENT '1全库  2 日志',
  `count` int(10) DEFAULT '0',
  `msg` varchar(3000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '描述',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_job
-- ----------------------------

-- ----------------------------
-- Table structure for t_license
-- ----------------------------
DROP TABLE IF EXISTS `t_license`;
CREATE TABLE `t_license` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `license` text,
  `installdate` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_license
-- ----------------------------
INSERT INTO `t_license` VALUES ('1', '', '0');

-- ----------------------------
-- Table structure for t_loginlog
-- ----------------------------
DROP TABLE IF EXISTS `t_loginlog`;
CREATE TABLE `t_loginlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `username` char(30) NOT NULL COMMENT '登录帐号',
  `logintime` int(10) NOT NULL COMMENT '登录时间戳',
  `loginip` char(20) NOT NULL COMMENT '登录IP',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态,1为登录成功，0为登录失败',
  `password` varchar(30) NOT NULL DEFAULT '' COMMENT '尝试错误密码',
  `info` varchar(255) NOT NULL COMMENT '其他说明',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='后台登陆日志表';

-- ----------------------------
-- Records of t_loginlog
-- ----------------------------

-- ----------------------------
-- Table structure for t_operationlog
-- ----------------------------
DROP TABLE IF EXISTS `t_operationlog`;
CREATE TABLE `t_operationlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `uid` int(11) NOT NULL COMMENT '操作用户ID',
  `time` datetime NOT NULL COMMENT '操作时间',
  `ip` varchar(20) NOT NULL DEFAULT '' COMMENT 'IP',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态,1为写入，2为更新，3为删除',
  `info` text COMMENT '其他说明',
  `data` text COMMENT '数据',
  `options` varchar(255) DEFAULT NULL COMMENT '条件',
  `get` varchar(255) DEFAULT NULL COMMENT 'get数据',
  `post` text COMMENT 'post数据',
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `username` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='后台操作日志表';

-- ----------------------------
-- Records of t_operationlog
-- ----------------------------

-- ----------------------------
-- Table structure for t_pool
-- ----------------------------
DROP TABLE IF EXISTS `t_pool`;
CREATE TABLE `t_pool` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '名称',
  `types` tinyint(3) DEFAULT '1' COMMENT '标识：磁盘磁带',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '描述信息',
  `maxVolumes` smallint(5) unsigned DEFAULT NULL COMMENT '最大卷数',
  `poolType` enum('Archive','Cloned','Migration','Copy','Save') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '池类型',
  `atorageId` int(10) unsigned NOT NULL COMMENT '介质ID',
  `useVolumeOnce` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `maxVolumeJobs` tinyint(3) unsigned DEFAULT '0',
  `maxVolumeFiles` int(10) unsigned DEFAULT '0',
  `maxVolumeBytes` bigint(20) unsigned DEFAULT '0' COMMENT '每个卷最大容量，与卷数之积不能超过授权',
  `volumeUseDuration` bigint(20) unsigned DEFAULT '0',
  `catalogFiles` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `catalogId` int(10) unsigned DEFAULT NULL,
  `autoPrune` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `volumeRetention` tinyblob,
  `actionOnPurge` enum('Truncate') COLLATE utf8_unicode_ci DEFAULT NULL,
  `nextPoolId` int(10) unsigned DEFAULT NULL,
  `scratchPoolId` int(10) unsigned DEFAULT NULL,
  `recyclePoolId` int(10) unsigned DEFAULT NULL,
  `recycle` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `recycleOldestVolume` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `recycleCurrentVolume` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `purgeOldestVolume` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT NULL,
  `fileRetention` tinyblob,
  `jobRetention` tinyblob,
  `cleaningPrefix` tinyblob,
  `labelFormat` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '卷标签定义',
  `state` tinyint(1) DEFAULT '0' COMMENT '状态：1删除',
  `count` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_pool
-- ----------------------------
INSERT INTO `t_pool` VALUES ('1', 'default', '1', '默认备份池配置', '10', 'Cloned', '1', 'no', '0', '0', '100', '0', null, null, null, 0x3335, null, null, null, null, null, 'no', null, null, 0x3330, 0x3330, null, 'pool_default', '0', '1');

-- ----------------------------
-- Table structure for t_schedule
-- ----------------------------
DROP TABLE IF EXISTS `t_schedule`;
CREATE TABLE `t_schedule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '名称',
  `run` varchar(1000) COLLATE utf8_unicode_ci NOT NULL COMMENT '定义值',
  `state` tinyint(1) DEFAULT '0' COMMENT '状态 1删除',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of t_schedule
-- ----------------------------

-- ----------------------------
-- Table structure for t_user
-- ----------------------------
DROP TABLE IF EXISTS `t_user`;
CREATE TABLE `t_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` char(16) NOT NULL,
  `password` char(32) NOT NULL COMMENT '密码',
  `encrypt` varchar(6) NOT NULL COMMENT '密钥',
  `nickname` varchar(20) NOT NULL DEFAULT '' COMMENT '姓名',
  `logincount` int(10) unsigned DEFAULT '0' COMMENT '登录次数',
  `loginip` varchar(40) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `logintime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `createip` varchar(40) NOT NULL DEFAULT '0' COMMENT '创建用户IP',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户创建时间',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '用户状态',
  `remark` varchar(255) NOT NULL COMMENT '备注',
  `roleid` tinyint(1) NOT NULL DEFAULT '1' COMMENT '系统只需要两个管理角色 1为 管理员  2为审计员',
  PRIMARY KEY (`id`),
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='管理员表';

-- ----------------------------
-- Records of t_user
-- ----------------------------
INSERT INTO `t_user` VALUES ('1', 'admin', '72bf1984382e5c20d90dfe549f4baa32', 'S2xNxZ', '管理员', '0', '0.0.0.0', '1411609861', '0', '0', '1', '', '1');

-- ----------------------------
-- Table structure for t_userconfig
-- ----------------------------
DROP TABLE IF EXISTS `t_userconfig`;
CREATE TABLE `t_userconfig` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `wall` varchar(50) NOT NULL COMMENT '壁纸',
  `language` varchar(10) NOT NULL COMMENT '用户语言',
  `order` varchar(50) NOT NULL COMMENT '排序字段，升序OR降序',
  `theme` varchar(50) NOT NULL COMMENT '主题样式',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_userconfig
-- ----------------------------
INSERT INTO `t_userconfig` VALUES ('1', '1', 'images/wall_page/1.jpg', 'zh-cn', '0,0', 'metro');

-- ----------------------------
-- oracle about
-- ----------------------------

DROP TABLE IF EXISTS `t_contact`;
CREATE TABLE `t_contact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `style` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 手机号 1 邮件',
  `content` varchar(50) DEFAULT NULL COMMENT '邮件地址或手机号码',
  `appid` int(10) DEFAULT NULL COMMENT '应用Id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `t_notify`;
CREATE TABLE `t_notify` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `content` text COMMENT '消息内容',
  `tel` varchar(255) DEFAULT NULL COMMENT '手机号码 多个用 , 分割',
  `email` varchar(500) DEFAULT NULL COMMENT '邮箱号码 多个用 , 分割',
  `createtime` int(11) DEFAULT NULL COMMENT '通知创建时间',
  `updatetime` int(11) DEFAULT NULL COMMENT '通知最后一次更改时间',
  `state` tinyint(1) DEFAULT '0' COMMENT '通知状态：0 等待发送 1 成功发送 -1 发送失败不再次发送 ',
  `errorcount` smallint(5) DEFAULT '0' COMMENT '发送失败次数',
  `count` int(10) DEFAULT '0' COMMENT '更新标识',
  `appid` int(10) DEFAULT '1' COMMENT 'APPID',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for t_oracleinfo
-- ----------------------------
DROP TABLE IF EXISTS `t_oracleinfo`;
CREATE TABLE `t_oracleinfo` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `master` tinyint(1) DEFAULT '1' COMMENT '主从，默认主 1',
  `dbhost` tinyblob COMMENT 'ip地址',
  `dbname` varchar(20) DEFAULT NULL COMMENT '数据库实例',
  `dbuser` varchar(20) DEFAULT NULL COMMENT '数据库用户',
  `dbpwd` varchar(20) DEFAULT NULL COMMENT '数据库密码',
  `dbport` varchar(10) DEFAULT NULL COMMENT '数据库端口号',
  `count` int(10) DEFAULT '0' COMMENT '更新标识',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for t_oracleuser
-- ----------------------------
DROP TABLE IF EXISTS `t_oracleuser`;
CREATE TABLE `t_oracleuser` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `userpwd` varchar(50) DEFAULT NULL COMMENT '用户密码',
  `tablespace` varchar(50) DEFAULT NULL COMMENT '表空间名称',
  `master` tinyint(1) DEFAULT '1' COMMENT '主从，默认主 1',
  `cache` varchar(200) DEFAULT NULL COMMENT '缓存路径',
  `count` int(11) DEFAULT NULL COMMENT '更新标识',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_appsetting
-- ----------------------------
DROP TABLE IF EXISTS `t_appsetting`;
CREATE TABLE `t_appsetting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `appid` int(11) NOT NULL COMMENT 'APPid',
  `key` varchar(50) NOT NULL COMMENT '配置名称',
  `value` varchar(50) NOT NULL COMMENT '配置值',
  `count` int(11) DEFAULT NULL COMMENT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_appsetting
-- ----------------------------
INSERT INTO `t_appsetting` VALUES ('1', '1', 'sendmsg', '1', '3');
INSERT INTO `t_appsetting` VALUES ('2', '1', 'sendmail', '0', '2');
INSERT INTO `t_appsetting` VALUES ('3', '1', 'crontab', '5', '2');
INSERT INTO `t_appsetting` VALUES ('4', '2', 'sendmsg', '1', '0');
INSERT INTO `t_appsetting` VALUES ('5', '2', 'sendmail', '0', '0');
INSERT INTO `t_appsetting` VALUES ('6', '2', 'crontab', '1', '0');

-- ----------------------------
-- Table structure for t_fileserver
-- ----------------------------
DROP TABLE IF EXISTS `t_fileserver`;
CREATE TABLE `t_fileserver` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '名称',
  `ip` varchar(50) NOT NULL COMMENT '虚拟化厂商ID',
  `user` varchar(50) NOT NULL COMMENT '配置值',
  `pwd` varchar(50) NOT NULL COMMENT '配置值',
  `type` int(1) NOT NULL DEFAULT '0' COMMENT '0:ftp方式 1 scp方式',
  `path` varchar(1000) NOT NULL COMMENT '配置值',
  `tmppath` varchar(1000) NOT NULL COMMENT '临时目录',
  `count` int(11) DEFAULT NULL COMMENT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_virtual
-- ----------------------------
DROP TABLE IF EXISTS `t_virtual`;
CREATE TABLE `t_virtual` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '名称',
  `vid` varchar(50) NOT NULL COMMENT '虚拟化厂商ID',
  `values` varchar(1000) NOT NULL COMMENT '配置值',
  `count` int(11) DEFAULT NULL COMMENT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

grant all privileges  on *.* to rongan@'127.0.0.1' identified by "rongandb";
grant all privileges  on *.* to rongan@'localhost' identified by "rongandb";
