ALTER TABLE `shua_tools`
ADD COLUMN `goods_sid` tinyint(1) NOT NULL DEFAULT '0',
ADD COLUMN `audit_status` tinyint(1) NOT NULL DEFAULT '0',
ADD COLUMN `sup_price` decimal(10,2) NOT NULL DEFAULT '0.00';

ALTER TABLE `shua_faka`
ADD COLUMN `sid` int(11) unsigned DEFAULT 0;

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
