/*
SQLyog Community v13.1.6 (64 bit)
MySQL - 5.7.30 : Database - 2023faka1
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `shua_account` */

DROP TABLE IF EXISTS `shua_account`;

CREATE TABLE `shua_account` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL,
  `permission` text,
  `addtime` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_account` */

/*Table structure for table `shua_apps` */

DROP TABLE IF EXISTS `shua_apps`;

CREATE TABLE `shua_apps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `taskid` int(11) unsigned NOT NULL DEFAULT '0',
  `domain` varchar(128) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `icon` varchar(256) DEFAULT NULL,
  `package` varchar(128) DEFAULT NULL,
  `android_url` varchar(256) DEFAULT NULL,
  `ios_url` varchar(256) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `shua_apps` */

/*Table structure for table `shua_article` */

DROP TABLE IF EXISTS `shua_article`;

CREATE TABLE `shua_article` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `addtime` datetime NOT NULL,
  `count` int(11) unsigned NOT NULL DEFAULT '0',
  `top` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_article` */

/*Table structure for table `shua_cache` */

DROP TABLE IF EXISTS `shua_cache`;

CREATE TABLE `shua_cache` (
  `k` varchar(32) NOT NULL,
  `v` longtext,
  `expire` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`k`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_cache` */

insert  into `shua_cache`(`k`,`v`,`expire`) values
('getcount','a:2:{s:4:\"time\";i:1685236943;s:4:\"data\";a:20:{s:4:\"code\";i:0;s:4:\"yxts\";d:1;s:6:\"count1\";s:1:\"0\";s:6:\"count2\";s:1:\"0\";s:6:\"count3\";s:1:\"0\";s:6:\"count4\";s:1:\"0\";s:6:\"count5\";d:0;s:6:\"count6\";s:1:\"0\";s:6:\"count7\";s:1:\"0\";s:6:\"count8\";d:0;s:6:\"count9\";d:0;s:7:\"count10\";d:0;s:7:\"count11\";d:0;s:7:\"count12\";d:0;s:7:\"count13\";d:0;s:7:\"count14\";d:0;s:7:\"count15\";d:0;s:7:\"count16\";d:0;s:7:\"count17\";s:1:\"0\";s:5:\"chart\";a:3:{s:4:\"date\";a:7:{i:0;a:2:{i:0;i:1;i:1;s:4:\"0521\";}i:1;a:2:{i:0;i:2;i:1;s:4:\"0522\";}i:2;a:2:{i:0;i:3;i:1;s:4:\"0523\";}i:3;a:2:{i:0;i:4;i:1;s:4:\"0524\";}i:4;a:2:{i:0;i:5;i:1;s:4:\"0525\";}i:5;a:2:{i:0;i:6;i:1;s:4:\"0526\";}i:6;a:2:{i:0;i:7;i:1;s:4:\"0527\";}}s:6:\"orders\";a:7:{i:0;a:2:{i:0;i:1;i:1;s:1:\"0\";}i:1;a:2:{i:0;i:2;i:1;s:1:\"0\";}i:2;a:2:{i:0;i:3;i:1;s:1:\"0\";}i:3;a:2:{i:0;i:4;i:1;s:1:\"0\";}i:4;a:2:{i:0;i:5;i:1;s:1:\"0\";}i:5;a:2:{i:0;i:6;i:1;s:1:\"0\";}i:6;a:2:{i:0;i:7;i:1;s:1:\"0\";}}s:5:\"money\";a:7:{i:0;a:2:{i:0;i:1;i:1;d:0;}i:1;a:2:{i:0;i:2;i:1;d:0;}i:2;a:2:{i:0;i:3;i:1;d:0;}i:3;a:2:{i:0;i:4;i:1;d:0;}i:4;a:2:{i:0;i:5;i:1;d:0;}i:5;a:2:{i:0;i:6;i:1;d:0;}i:6;a:2:{i:0;i:7;i:1;d:0;}}}}}',0),
('tongji','a:7:{s:6:\"orders\";s:1:\"0\";s:7:\"orders1\";s:1:\"0\";s:7:\"orders2\";s:1:\"0\";s:5:\"money\";d:0;s:6:\"money1\";d:0;s:4:\"site\";s:1:\"0\";s:4:\"gift\";N;}',0);

/*Table structure for table `shua_cart` */

DROP TABLE IF EXISTS `shua_cart`;

CREATE TABLE `shua_cart` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` varchar(32) NOT NULL,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `tid` int(11) NOT NULL,
  `input` text NOT NULL,
  `num` int(11) unsigned NOT NULL DEFAULT '1',
  `money` varchar(32) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  `blockdj` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_cart` */

/*Table structure for table `shua_class` */

DROP TABLE IF EXISTS `shua_class`;

CREATE TABLE `shua_class` (
  `cid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `sort` int(11) NOT NULL DEFAULT '10',
  `name` varchar(255) NOT NULL,
  `shopimg` text,
  `block` text,
  `blockpay` varchar(80) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_class` */

/*Table structure for table `shua_config` */

DROP TABLE IF EXISTS `shua_config`;

CREATE TABLE `shua_config` (
  `k` varchar(32) NOT NULL,
  `v` text,
  PRIMARY KEY (`k`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_config` */

insert  into `shua_config`(`k`,`v`) values
('adminlogin','2023-05-28 09:22:21'),
('admin_pwd','install_generated'),
('admin_user','admin'),
('alipay_api','2'),
('anounce','<p><li class="list-group-item"><span class="btn btn-danger btn-xs">1</span> 售后问题请联系在线客服，默认官方客服 TG @qqfaka。</li><li class="list-group-item"><span class="btn btn-success btn-xs">2</span> 下单前请仔细阅读商品说明、补充信息和售后规则。</li><li class="list-group-item"><span class="btn btn-info btn-xs">3</span> 订单状态可在查询页面自助查看，请保存好下单账号或订单号。</li><li class="list-group-item"><span class="btn btn-warning btn-xs">4</span> 请勿重复下单，等待前一笔任务完成后再提交新的订单。</li><div class="btn-group btn-group-justified"><a target="_blank" class="btn btn-info" href="https://t.me/qqfaka"><i class="fa fa-paper-plane"></i> 联系客服</a><a target="_blank" class="btn btn-warning" href="https://t.me/qqfaka"><i class="fa fa-users"></i> 官方群组</a><a target="_blank" class="btn btn-success" href="https://t.me/qqfaka"><i class="fa fa-home"></i> 官方入口</a></div></p>'),
('bottom',''),
('build','2023-05-28'),
('cache',''),
('captcha_open_free','1'),
('captcha_open_reg','1'),
('cdnpublic','0'),
('chatframe',''),
('cishu','3'),
('cronkey','0'),
('datepoint','a:7:{i:0;a:3:{s:4:\"date\";s:4:\"0527\";s:6:\"orders\";s:1:\"0\";s:5:\"money\";d:0;}i:1;a:3:{s:4:\"date\";s:4:\"0526\";s:6:\"orders\";s:1:\"0\";s:5:\"money\";d:0;}i:2;a:3:{s:4:\"date\";s:4:\"0525\";s:6:\"orders\";s:1:\"0\";s:5:\"money\";d:0;}i:3;a:3:{s:4:\"date\";s:4:\"0524\";s:6:\"orders\";s:1:\"0\";s:5:\"money\";d:0;}i:4;a:3:{s:4:\"date\";s:4:\"0523\";s:6:\"orders\";s:1:\"0\";s:5:\"money\";d:0;}i:5;a:3:{s:4:\"date\";s:4:\"0522\";s:6:\"orders\";s:1:\"0\";s:5:\"money\";d:0;}i:6;a:3:{s:4:\"date\";s:4:\"0521\";s:6:\"orders\";s:1:\"0\";s:5:\"money\";d:0;}}'),
('description','岁岁云商城，专注数字商品、自助下单、发卡交付与分站运营服务。'),
('faka_mail','<b>商品名称：</b> [name]<br/><b>购买时间：</b>[date]<br/><b>以下是你的卡密信息：</b><br/>[kmdata]<br/>----------<br/><b>使用说明：</b><br/>[alert]<br/>----------<br/>岁岁云商城自助下单平台<br/>[domain]'),
('favicon','/assets/img/favicon/favicon.ico'),
('fenzhan_buy','1'),
('fenzhan_edithtml','1'),
('fenzhan_expiry','12'),
('fenzhan_free','0'),
('fenzhan_kfqq','1'),
('fenzhan_price','10'),
('fenzhan_price2','20'),
('fenzhan_pricelimit','1'),
('fenzhan_rank','1'),
('fenzhan_tixian','0'),
('fenzhan_tixian_alipay','1'),
('fenzhan_tixian_qq','1'),
('fenzhan_tixian_wx','1'),
('gg_search','<span class=\"label label-primary\">待处理</span> 说明正在努力提交到服务器！<p></p><p></p><span class=\"label label-success\">已完成</span> 已经提交到接口正在处理！<p></p><p></p><span class=\"label label-warning\">处理中</span> 已经开始为您开单 请耐心等！<p></p><p></p><span class=\"label label-danger\">有异常</span> 下单信息有误 联系客服处理！'),
('gift_open','0'),
('invite_content','岁岁云商城自助下单平台，链接：[url] (请复制链接到浏览器打开)'),
('keywords','岁岁云商城,自助下单,发卡商城,数字商品'),
('kfqq','qqfaka'),
('lt_version','1012'),
('mail_port','465'),
('mail_smtp','smtp.qq.com'),
('modal',''),
('paymsg','<hr/>小提示：支付遇到问题请联系在线客服处理，请保留付款记录与订单信息。'),
('pricejk_time','600'),
('qiandao_day','15'),
('qiandao_mult','1.05'),
('qiandao_reward','0.02'),
('qqpay_api','2'),
('search_open','1'),
('shopdesc_editor','1'),
('shoppingcart','1'),
('sitename','岁岁云商城'),
('style','1'),
('sup_bond','0'),
('syskey',''),
('template','XHY-01'),
('tixian_limit','1'),
('tixian_min','10'),
('tixian_rate','98'),
('tongji_cachetime','1685236915'),
('tongji_time','300'),
('ui_background','3'),
('updatestatus','0'),
('updatestatus_interval','6'),
('user_open','1'),
('verify_open','1'),
('version','1010'),
('workorder_open','1'),
('workorder_type','业务补单|卡密错误|充值没到账|订单中途改了密码'),
('wxpay_api','2');

/*Table structure for table `shua_faka` */

DROP TABLE IF EXISTS `shua_faka`;

CREATE TABLE `shua_faka` (
  `kid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(11) unsigned NOT NULL,
  `km` varchar(255) DEFAULT NULL,
  `pw` varchar(255) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  `usetime` datetime DEFAULT NULL,
  `orderid` int(11) unsigned NOT NULL DEFAULT '0',
  `sid` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`kid`),
  KEY `orderid` (`orderid`),
  KEY `tid` (`tid`),
  KEY `getleft` (`tid`,`orderid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_faka` */

/*Table structure for table `shua_gift` */

DROP TABLE IF EXISTS `shua_gift`;

CREATE TABLE `shua_gift` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `tid` int(11) unsigned NOT NULL,
  `rate` int(3) NOT NULL,
  `ok` tinyint(1) NOT NULL DEFAULT '0',
  `not` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_gift` */

/*Table structure for table `shua_giftlog` */

DROP TABLE IF EXISTS `shua_giftlog`;

CREATE TABLE `shua_giftlog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '0',
  `tid` int(11) unsigned NOT NULL,
  `gid` int(11) unsigned NOT NULL,
  `userid` varchar(32) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `addtime` datetime DEFAULT NULL,
  `tradeno` varchar(32) DEFAULT NULL,
  `input` varchar(64) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_giftlog` */

/*Table structure for table `shua_invite` */

DROP TABLE IF EXISTS `shua_invite`;

CREATE TABLE `shua_invite` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nid` int(11) unsigned NOT NULL,
  `tid` int(11) unsigned NOT NULL,
  `qq` varchar(20) NOT NULL,
  `input` text NOT NULL,
  `key` varchar(30) NOT NULL,
  `ip` varchar(25) DEFAULT NULL,
  `plan` int(11) unsigned NOT NULL DEFAULT '0',
  `click` int(11) unsigned NOT NULL DEFAULT '0',
  `count` int(11) unsigned NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `nid` (`nid`),
  KEY `qq` (`qq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_invite` */

/*Table structure for table `shua_invitelog` */

DROP TABLE IF EXISTS `shua_invitelog`;

CREATE TABLE `shua_invitelog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `iid` int(11) unsigned NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  `ip` varchar(50) DEFAULT NULL,
  `orderid` int(11) unsigned DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `iid` (`iid`,`status`),
  KEY `iidip` (`iid`,`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_invitelog` */

/*Table structure for table `shua_inviteshop` */

DROP TABLE IF EXISTS `shua_inviteshop`;

CREATE TABLE `shua_inviteshop` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(11) unsigned NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `times` tinyint(1) NOT NULL DEFAULT '0',
  `value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `sort` int(11) NOT NULL DEFAULT '10',
  `addtime` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_inviteshop` */

/*Table structure for table `shua_kms` */

DROP TABLE IF EXISTS `shua_kms`;

CREATE TABLE `shua_kms` (
  `kid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `zid` int(11) unsigned NOT NULL DEFAULT '0',
  `tid` int(11) unsigned NOT NULL DEFAULT '0',
  `num` int(11) unsigned NOT NULL DEFAULT '1',
  `km` varchar(255) NOT NULL,
  `money` decimal(10,2) NOT NULL DEFAULT '0.00',
  `addtime` timestamp NULL DEFAULT NULL,
  `usetime` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `orderid` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`kid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_kms` */

/*Table structure for table `shua_logs` */

DROP TABLE IF EXISTS `shua_logs`;

CREATE TABLE `shua_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `action` varchar(32) NOT NULL,
  `param` varchar(255) NOT NULL,
  `result` text,
  `addtime` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_logs` */

insert  into `shua_logs`(`id`,`action`,`param`,`result`,`addtime`,`status`) values
(1,'后台登录','IP:127.0.0.1','','2023-05-28 09:22:21',1);

/*Table structure for table `shua_message` */

DROP TABLE IF EXISTS `shua_message`;

CREATE TABLE `shua_message` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `type` int(1) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `addtime` datetime NOT NULL,
  `count` int(11) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_message` */

/*Table structure for table `shua_orders` */

DROP TABLE IF EXISTS `shua_orders`;

CREATE TABLE `shua_orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(11) unsigned NOT NULL,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `input` varchar(256) NOT NULL,
  `input2` varchar(256) DEFAULT NULL,
  `input3` varchar(256) DEFAULT NULL,
  `input4` varchar(256) DEFAULT NULL,
  `input5` varchar(256) DEFAULT NULL,
  `value` int(11) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `djzt` tinyint(1) NOT NULL DEFAULT '0',
  `djorder` varchar(32) DEFAULT NULL,
  `url` varchar(32) DEFAULT NULL,
  `result` text,
  `userid` varchar(32) DEFAULT NULL,
  `tradeno` varchar(32) DEFAULT NULL,
  `money` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `addtime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  `uptime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `zid` (`zid`),
  KEY `input` (`input`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_orders` */

/*Table structure for table `shua_pay` */

DROP TABLE IF EXISTS `shua_pay`;

CREATE TABLE `shua_pay` (
  `trade_no` varchar(64) NOT NULL,
  `api_trade_no` varchar(64) DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `channel` varchar(10) DEFAULT NULL,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `tid` int(11) NOT NULL,
  `input` text NOT NULL,
  `num` int(11) unsigned NOT NULL DEFAULT '1',
  `addtime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `money` varchar(32) DEFAULT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `userid` varchar(32) DEFAULT NULL,
  `inviteid` int(11) unsigned DEFAULT NULL,
  `domain` varchar(64) DEFAULT NULL,
  `blockdj` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`trade_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_pay` */

/*Table structure for table `shua_points` */

DROP TABLE IF EXISTS `shua_points`;

CREATE TABLE `shua_points` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '0',
  `action` varchar(255) NOT NULL,
  `point` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bz` varchar(1024) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  `orderid` int(11) unsigned DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `zid` (`zid`),
  KEY `action` (`action`),
  KEY `orderid` (`orderid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_points` */

/*Table structure for table `shua_price` */

DROP TABLE IF EXISTS `shua_price`;

CREATE TABLE `shua_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '0',
  `kind` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 倍数 1 价格',
  `name` varchar(255) NOT NULL,
  `p_0` decimal(8,2) NOT NULL DEFAULT '0.00',
  `p_1` decimal(8,2) NOT NULL DEFAULT '0.00',
  `p_2` decimal(8,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_price` */

/*Table structure for table `shua_qiandao` */

DROP TABLE IF EXISTS `shua_qiandao`;

CREATE TABLE `shua_qiandao` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `qq` varchar(20) DEFAULT NULL,
  `reward` decimal(10,2) NOT NULL DEFAULT '0.00',
  `date` date NOT NULL,
  `time` datetime NOT NULL,
  `ip` varchar(50) DEFAULT NULL,
  `continue` int(11) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `zid` (`zid`),
  KEY `ip` (`ip`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_qiandao` */

/*Table structure for table `shua_sendcode` */

DROP TABLE IF EXISTS `shua_sendcode`;

CREATE TABLE `shua_sendcode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0邮箱 1手机',
  `mode` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0注册 1找回 2改绑',
  `code` varchar(32) NOT NULL,
  `to` varchar(32) DEFAULT NULL,
  `time` int(11) NOT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `shua_sendcode` */

/*Table structure for table `shua_shequ` */

DROP TABLE IF EXISTS `shua_shequ`;

CREATE TABLE `shua_shequ` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `paypwd` varchar(255) DEFAULT NULL,
  `paytype` tinyint(1) NOT NULL DEFAULT '0',
  `type` varchar(20) NOT NULL,
  `result` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `remark` varchar(255) DEFAULT NULL,
  `protocol` tinyint(1) NOT NULL DEFAULT '0',
  `monitor` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_shequ` */

/*Table structure for table `shua_site` */

DROP TABLE IF EXISTS `shua_site`;

CREATE TABLE `shua_site` (
  `zid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `upzid` int(11) unsigned NOT NULL DEFAULT '0',
  `utype` int(1) unsigned NOT NULL DEFAULT '0',
  `power` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `domain` varchar(50) DEFAULT NULL,
  `domain2` varchar(50) DEFAULT NULL,
  `user` varchar(20) NOT NULL,
  `pwd` varchar(32) NOT NULL,
  `email` varchar(64) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `qq_openid` varchar(64) DEFAULT NULL,
  `wx_openid` varchar(64) DEFAULT NULL,
  `nickname` varchar(64) DEFAULT NULL,
  `faceimg` varchar(150) DEFAULT NULL,
  `rmb` decimal(10,2) NOT NULL DEFAULT '0.00',
  `rmbtc` decimal(10,2) NOT NULL DEFAULT '0.00',
  `point` int(11) NOT NULL DEFAULT '0',
  `pay_type` int(1) NOT NULL DEFAULT '0',
  `pay_account` varchar(50) DEFAULT NULL,
  `pay_name` varchar(50) DEFAULT NULL,
  `qq` varchar(12) DEFAULT NULL,
  `sitename` varchar(80) DEFAULT NULL,
  `title` varchar(80) DEFAULT NULL,
  `keywords` text,
  `description` text,
  `kfqq` varchar(12) DEFAULT NULL,
  `kfwx` varchar(20) DEFAULT NULL,
  `anounce` text,
  `bottom` text,
  `modal` text,
  `alert` text,
  `price` text,
  `iprice` text,
  `appurl` varchar(150) DEFAULT NULL,
  `class` varchar(255) DEFAULT NULL,
  `ktfz_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ktfz_price2` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ktfz_domain` text,
  `addtime` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  `template` varchar(10) DEFAULT NULL,
  `msgread` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`zid`),
  UNIQUE KEY `user` (`user`),
  KEY `domain` (`domain`),
  KEY `domain2` (`domain2`),
  KEY `qq` (`qq`),
  KEY `qq_openid` (`qq_openid`),
  KEY `wx_openid` (`wx_openid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `shua_site` */

/*Table structure for table `shua_site_price` */

DROP TABLE IF EXISTS `shua_site_price`;

CREATE TABLE `shua_site_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL,
  `tid` int(11) unsigned NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cost2` decimal(10,2) NOT NULL DEFAULT '0.00',
  `del` tinyint(1) NOT NULL DEFAULT '0',
  `create_time` datetime NOT NULL,
  `update_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `zid_tid` (`zid`,`tid`),
  KEY `zid` (`zid`),
  KEY `tid` (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_site_price` */

/*Table structure for table `shua_supplier` */

DROP TABLE IF EXISTS `shua_supplier`;

CREATE TABLE `shua_supplier` (
  `sid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(20) NOT NULL,
  `pwd` varchar(32) NOT NULL,
  `email` varchar(64) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `qq_openid` varchar(64) DEFAULT NULL,
  `wx_openid` varchar(64) DEFAULT NULL,
  `nickname` varchar(64) DEFAULT NULL,
  `faceimg` varchar(150) DEFAULT NULL,
  `rmb` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bond` decimal(10,2) NOT NULL DEFAULT '0.00',
  `point` int(11) NOT NULL DEFAULT '0',
  `pay_type` int(1) NOT NULL DEFAULT '0',
  `pay_account` varchar(50) DEFAULT NULL,
  `pay_name` varchar(50) DEFAULT NULL,
  `qq` varchar(12) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  `msgread` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sid`),
  UNIQUE KEY `user` (`user`),
  KEY `qq` (`qq`),
  KEY `qq_openid` (`qq_openid`),
  KEY `wx_openid` (`wx_openid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `shua_supplier` */

/*Table structure for table `shua_suppoints` */

DROP TABLE IF EXISTS `shua_suppoints`;

CREATE TABLE `shua_suppoints` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sid` int(11) unsigned NOT NULL DEFAULT '0',
  `action` varchar(255) NOT NULL,
  `point` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bz` varchar(1024) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  `orderid` int(11) unsigned DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sid` (`sid`),
  KEY `action` (`action`),
  KEY `orderid` (`orderid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_suppoints` */

/*Table structure for table `shua_suptixian` */

DROP TABLE IF EXISTS `shua_suptixian`;

CREATE TABLE `shua_suptixian` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sid` int(11) unsigned NOT NULL,
  `money` decimal(10,2) NOT NULL DEFAULT '0.00',
  `realmoney` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pay_type` int(1) NOT NULL DEFAULT '0',
  `pay_account` varchar(50) NOT NULL,
  `pay_name` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `addtime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  `note` text,
  PRIMARY KEY (`id`),
  KEY `sid` (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_suptixian` */

/*Table structure for table `shua_tixian` */

DROP TABLE IF EXISTS `shua_tixian`;

CREATE TABLE `shua_tixian` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL,
  `money` decimal(10,2) NOT NULL DEFAULT '0.00',
  `realmoney` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pay_type` int(1) NOT NULL DEFAULT '0',
  `pay_account` varchar(50) NOT NULL,
  `pay_name` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `addtime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  `note` text,
  PRIMARY KEY (`id`),
  KEY `zid` (`zid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_tixian` */

/*Table structure for table `shua_toollogs` */

DROP TABLE IF EXISTS `shua_toollogs`;

CREATE TABLE `shua_toollogs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` longtext NOT NULL,
  `date` date DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_toollogs` */

insert  into `shua_toollogs`(`id`,`content`,`date`,`addtime`,`active`) values
(1,'1111','2023-05-26','2023-05-26 00:00:00',1);

/*Table structure for table `shua_tools` */

DROP TABLE IF EXISTS `shua_tools`;

CREATE TABLE `shua_tools` (
  `tid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `cid` int(11) unsigned NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '10',
  `name` varchar(255) NOT NULL,
  `value` int(11) unsigned NOT NULL DEFAULT '0',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `prid` int(11) NOT NULL DEFAULT '0',
  `cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cost2` decimal(10,2) NOT NULL DEFAULT '0.00',
  `prices` varchar(100) DEFAULT NULL,
  `input` varchar(250) NOT NULL,
  `inputs` varchar(255) DEFAULT NULL,
  `desc` text,
  `alert` text,
  `shopimg` text,
  `validate` tinyint(1) NOT NULL DEFAULT '0',
  `valiserv` varchar(15) DEFAULT NULL,
  `min` int(11) NOT NULL DEFAULT '0',
  `max` int(11) NOT NULL DEFAULT '0',
  `is_curl` tinyint(1) NOT NULL DEFAULT '0',
  `curl` varchar(255) DEFAULT NULL,
  `showcontent` text DEFAULT NULL COMMENT '购买后直接显示的内容',
  `repeat` tinyint(1) NOT NULL DEFAULT '0',
  `multi` tinyint(1) NOT NULL DEFAULT '0',
  `shequ` int(3) NOT NULL DEFAULT '0',
  `goods_id` int(11) NOT NULL DEFAULT '0',
  `goods_type` int(11) NOT NULL DEFAULT '0',
  `goods_param` text,
  `close` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `uptime` int(11) NOT NULL DEFAULT '0',
  `sales` int(11) NOT NULL DEFAULT '0',
  `stock` int(11) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  `goods_sid` tinyint(1) NOT NULL DEFAULT '0',
  `audit_status` tinyint(1) NOT NULL DEFAULT '0',
  `sup_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ts` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`tid`),
  KEY `cid` (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_tools` */

/*Table structure for table `shua_workorder` */

DROP TABLE IF EXISTS `shua_workorder`;

CREATE TABLE `shua_workorder` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `type` int(1) unsigned NOT NULL DEFAULT '0',
  `orderid` int(11) unsigned NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `picurl` varchar(150) DEFAULT NULL,
  `addtime` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `ts` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `zid` (`zid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `shua_workorder` */

-- 创建优惠券表
CREATE TABLE IF NOT EXISTS `shua_coupons` (
  `cid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `name` varchar(255) NOT NULL COMMENT '优惠券名称',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '优惠券类型：0满减券，1折扣券，2固定金额券',
  `value` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠券价值',
  `min_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '使用门槛',
  `max_discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最大优惠金额（仅折扣券）',
  `total` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '发放总量',
  `used` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '已使用数量',
  `start_time` datetime DEFAULT NULL COMMENT '开始时间',
  `end_time` datetime DEFAULT NULL COMMENT '结束时间',
  `valid_days` int(11) NOT NULL DEFAULT '0' COMMENT '有效期天数（0表示固定时间）',
  `description` text COMMENT '优惠券描述',
  `active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`cid`),
  KEY `zid` (`zid`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='优惠券表';

-- 创建用户优惠券关联表
CREATE TABLE IF NOT EXISTS `shua_user_coupons` (
  `ucid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `cid` int(11) unsigned NOT NULL COMMENT '优惠券ID',
  `userid` varchar(32) NOT NULL COMMENT '用户ID',
  `orderid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '使用订单ID',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0未使用，1已使用，2已过期',
  `get_time` datetime DEFAULT NULL COMMENT '获取时间',
  `use_time` datetime DEFAULT NULL COMMENT '使用时间',
  `expire_time` datetime DEFAULT NULL COMMENT '过期时间',
  PRIMARY KEY (`ucid`),
  KEY `zid` (`zid`),
  KEY `cid` (`cid`),
  KEY `userid` (`userid`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户优惠券关联表';

-- 创建优惠券发放记录表
CREATE TABLE IF NOT EXISTS `shua_coupon_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `cid` int(11) unsigned NOT NULL COMMENT '优惠券ID',
  `userid` varchar(32) NOT NULL COMMENT '用户ID',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型：0发放，1使用，2过期',
  `reason` varchar(255) DEFAULT NULL COMMENT '原因',
  `add_time` datetime DEFAULT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `zid` (`zid`),
  KEY `cid` (`cid`),
  KEY `userid` (`userid`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='优惠券发放记录表';

-- 创建优惠券发放规则表
CREATE TABLE IF NOT EXISTS `shua_coupon_rules` (
  `rid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `zid` int(11) unsigned NOT NULL DEFAULT '1',
  `scene` tinyint(1) NOT NULL DEFAULT '0' COMMENT '场景：0每日签到，1推广链接，2抽奖商品',
  `cid` int(11) unsigned NOT NULL COMMENT '优惠券ID',
  `params` text COMMENT '场景参数（JSON格式）',
  `active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`rid`),
  KEY `zid` (`zid`),
  KEY `scene` (`scene`),
  KEY `cid` (`cid`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='优惠券发放规则表';

-- 创建地区加价规则表 - 官网：t.me/qqfaka TG：@qqfaka
CREATE TABLE IF NOT EXISTS `pre_region_price_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `region_name` varchar(100) NOT NULL COMMENT '地区名称',
  `region_keywords` text NOT NULL COMMENT '地区关键词，逗号分隔',
  `add_price_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '加价类型：1固定金额，2百分比',
  `add_price_value` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '加价数值',
  `min_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最低价格限制',
  `max_price` decimal(10,2) NOT NULL DEFAULT '999999.99' COMMENT '最高价格限制',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1启用，0禁用',
  `sort_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='地区加价规则表';

-- 创建地区加价计算日志表
CREATE TABLE IF NOT EXISTS `pre_region_price_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `order_id` varchar(50) NOT NULL COMMENT '订单号',
  `tid` int(11) NOT NULL COMMENT '商品ID',
  `tool_name` varchar(255) NOT NULL COMMENT '商品名称',
  `original_price` decimal(10,2) NOT NULL COMMENT '原价',
  `final_price` decimal(10,2) NOT NULL COMMENT '最终价格',
  `add_price_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '加价金额',
  `region_id` int(11) DEFAULT NULL COMMENT '匹配的规则ID',
  `region_name` varchar(100) DEFAULT NULL COMMENT '地区名称',
  `add_price_type` tinyint(1) DEFAULT NULL COMMENT '加价类型',
  `add_price_value` decimal(10,2) DEFAULT NULL COMMENT '加价数值',
  `address` text COMMENT '收货地址',
  `user_ip` varchar(50) DEFAULT NULL COMMENT '用户IP',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_tid` (`tid`),
  KEY `idx_region_id` (`region_id`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='地区加价计算日志表';

-- 修改 shua_pay 表添加 address 字段
ALTER TABLE `shua_pay` ADD COLUMN `address` VARCHAR(500) DEFAULT '' COMMENT '收货地址' AFTER `input`;

-- 修改 shua_orders 表添加 address 字段
ALTER TABLE `shua_orders` ADD COLUMN `address` VARCHAR(500) DEFAULT '' COMMENT '收货地址' AFTER `input5`;

-- 修改 shua_cart 表添加 address 字段
ALTER TABLE `shua_cart` ADD COLUMN `address` VARCHAR(500) DEFAULT '' COMMENT '收货地址' AFTER `input`;

-- 创建秒杀商品表 (pre_前缀)
CREATE TABLE IF NOT EXISTS `pre_seckillshop` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(11) unsigned NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `value` int(11) unsigned NOT NULL DEFAULT 0,
  `num` int(11) unsigned NOT NULL DEFAULT 0,
  `count` int(11) unsigned NOT NULL DEFAULT 0,
  `sort` int(11) NOT NULL DEFAULT 10,
  `starttime` datetime NOT NULL,
  `endtime` datetime NOT NULL,
  `addtime` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='秒杀商品表';

-- 创建秒杀商品表 (shua_前缀)
CREATE TABLE IF NOT EXISTS `shua_seckillshop` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(11) unsigned NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `value` int(11) unsigned NOT NULL DEFAULT 0,
  `num` int(11) unsigned NOT NULL DEFAULT 0,
  `count` int(11) unsigned NOT NULL DEFAULT 0,
  `sort` int(11) NOT NULL DEFAULT 10,
  `starttime` datetime NOT NULL,
  `endtime` datetime NOT NULL,
  `addtime` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='秒杀商品表';

-- 创建站点任务表 (pre_前缀)
CREATE TABLE IF NOT EXISTS `pre_sitetask` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `task` tinyint(1) NOT NULL DEFAULT 0,
  `type` tinyint(1) NOT NULL DEFAULT 0,
  `tid` int(11) unsigned NOT NULL DEFAULT 0,
  `value` varchar(32) NOT NULL,
  `money` varchar(32) NOT NULL,
  `quantity` int(11) NOT NULL,
  `desc` text,
  `sort` int(11) NOT NULL DEFAULT 10,
  `addtime` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='站点任务表';

-- 创建站点任务表 (shua_前缀)
CREATE TABLE IF NOT EXISTS `shua_sitetask` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `task` tinyint(1) NOT NULL DEFAULT 0,
  `type` tinyint(1) NOT NULL DEFAULT 0,
  `tid` int(11) unsigned NOT NULL DEFAULT 0,
  `value` varchar(32) NOT NULL,
  `money` varchar(32) NOT NULL,
  `quantity` int(11) NOT NULL,
  `desc` text,
  `sort` int(11) NOT NULL DEFAULT 10,
  `addtime` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='站点任务表';

-- Q8 runtime tables
CREATE TABLE IF NOT EXISTS `shua_sitetask_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `taskid` int(11) NOT NULL DEFAULT 0,
  `taskname` varchar(255) NOT NULL DEFAULT '',
  `money` decimal(10,2) NOT NULL DEFAULT 0.00,
  `addtime` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `remark` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `taskid` (`taskid`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `shua_toollogs_offline` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` longtext NOT NULL,
  `date` date DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `shua_sync_category_map` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shequ_id` int(11) NOT NULL,
  `remote_cid` int(11) NOT NULL,
  `remote_pid` int(11) NOT NULL DEFAULT 0,
  `local_cid` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `level` tinyint(1) NOT NULL DEFAULT 1,
  `addtime` datetime DEFAULT NULL,
  `uptime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shequ_remote` (`shequ_id`,`remote_cid`),
  KEY `local_cid` (`local_cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `shua_sync_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shequ_id` int(11) NOT NULL,
  `sync_interval` int(11) NOT NULL DEFAULT '5',
  `sync_limit` int(11) NOT NULL DEFAULT '50',
  `auto_update` tinyint(1) NOT NULL DEFAULT '1',
  `delete_rule` tinyint(1) NOT NULL DEFAULT '0',
  `sync_class` tinyint(1) NOT NULL DEFAULT '0',
  `sync_sort` tinyint(1) NOT NULL DEFAULT '0',
  `sync_goods_sort` tinyint(1) NOT NULL DEFAULT '0',
  `sync_log` tinyint(1) NOT NULL DEFAULT '0',
  `sync_name` tinyint(1) NOT NULL DEFAULT '0',
  `sync_price` tinyint(1) NOT NULL DEFAULT '0',
  `sync_cost` tinyint(1) NOT NULL DEFAULT '0',
  `sync_desc` tinyint(1) NOT NULL DEFAULT '0',
  `sync_image` tinyint(1) NOT NULL DEFAULT '0',
  `sync_workorder` tinyint(1) NOT NULL DEFAULT '0',
  `add_class` tinyint(1) NOT NULL DEFAULT '0',
  `add_goods` tinyint(1) NOT NULL DEFAULT '0',
  `markup_template` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shequ_id` (`shequ_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
