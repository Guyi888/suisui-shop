<?php

if (!function_exists('q8_toollog_parse_line')) {
	function q8_toollog_parse_line($line)
	{
		$line = trim((string)$line);
		if ($line === '') return null;
		$tid = 0;
		if (preg_match('/\\[(?:tid|ID)[:=](\\d+)\\]/i', $line, $match)) {
			$tid = intval($match[1]);
			$line = trim(str_replace($match[0], '', $line));
		}
		$line = preg_replace('/^(?:商品上架|商品下架|上架|下架)[:：\\s]*/u', '', $line);
		$name = trim($line);
		if ($name === '') return null;
		return array('name' => $name, 'tid' => $tid);
	}
}

if (!function_exists('q8_toollog_parse_content')) {
	function q8_toollog_parse_content($content)
	{
		$entries = array();
		foreach (preg_split('/\\r\\n|\\r|\\n/', (string)$content) as $line) {
			$entry = q8_toollog_parse_line($line);
			if ($entry) q8_toollog_append_unique_entry($entries, $entry['name'], $entry['tid']);
		}
		return $entries;
	}
}

if (!function_exists('q8_toollog_build_line')) {
	function q8_toollog_build_line($prefix, $name, $tid = 0)
	{
		$line = trim((string)$prefix) . trim((string)$name);
		$tid = intval($tid);
		if ($tid > 0) $line .= ' [tid:' . $tid . ']';
		return $line;
	}
}

if (!function_exists('q8_toollog_build_content')) {
	function q8_toollog_build_content($prefix, $entries)
	{
		$lines = array();
		foreach ((array)$entries as $entry) {
			if (is_array($entry)) {
				$name = isset($entry['name']) ? trim((string)$entry['name']) : '';
				$tid = isset($entry['tid']) ? intval($entry['tid']) : 0;
			} else {
				$name = trim((string)$entry);
				$tid = 0;
			}
			if ($name !== '') $lines[] = q8_toollog_build_line($prefix, $name, $tid);
		}
		return implode("\n", $lines);
	}
}

if (!function_exists('q8_toollog_ensure_tables')) {
	function q8_toollog_ensure_tables()
	{
		global $DB;
		if (!$DB) return;
		$DB->exec("CREATE TABLE IF NOT EXISTS `pre_toollogs_offline` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`content` text NOT NULL,
			`date` date DEFAULT NULL,
			`addtime` datetime DEFAULT NULL,
			`active` tinyint(1) NOT NULL DEFAULT 1,
			PRIMARY KEY (`id`),
			KEY `date` (`date`),
			KEY `active` (`active`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	}
}

if (!function_exists('q8_toollog_append_unique_entry')) {
	function q8_toollog_append_unique_entry(&$entries, $name, $tid = 0)
	{
		$name = trim((string)$name);
		$tid = intval($tid);
		if ($name === '') return false;
		foreach ($entries as $key => $entry) {
			$entryName = isset($entry['name']) ? trim((string)$entry['name']) : '';
			$entryTid = isset($entry['tid']) ? intval($entry['tid']) : 0;
			if (($tid > 0 && $entryTid === $tid) || ($tid <= 0 && $entryTid <= 0 && $entryName === $name)) {
				if ($entryName === '' && $name !== '') $entries[$key]['name'] = $name;
				if ($entryTid <= 0 && $tid > 0) $entries[$key]['tid'] = $tid;
				return false;
			}
		}
		$entries[] = array('name' => $name, 'tid' => $tid);
		return true;
	}
}

if (!function_exists('q8_toollog_merge_rows')) {
	function q8_toollog_merge_rows($type = 'online', $date = null)
	{
		global $DB;
		if (!$DB) return;
		q8_toollog_ensure_tables();
		$type = $type === 'offline' ? 'offline' : 'online';
		$table = $type === 'offline' ? 'pre_toollogs_offline' : 'pre_toollogs';
		$prefix = $type === 'offline' ? '商品下架：' : '商品上架：';
		$dateSql = $date ? " AND date=:date" : '';
		$params = $date ? array(':date' => $date) : array();
		$rows = $DB->getAll("SELECT * FROM {$table} WHERE active=1{$dateSql} ORDER BY date DESC,id DESC", $params);
		$bucket = array();
		foreach ((array)$rows as $row) {
			$rowDate = isset($row['date']) ? (string)$row['date'] : '';
			if (!isset($bucket[$rowDate])) $bucket[$rowDate] = array('ids' => array(), 'entries' => array());
			$bucket[$rowDate]['ids'][] = intval($row['id']);
			foreach (q8_toollog_parse_content($row['content']) as $entry) {
				q8_toollog_append_unique_entry($bucket[$rowDate]['entries'], $entry['name'], $entry['tid']);
			}
		}
		foreach ($bucket as $rowDate => $item) {
			if (count($item['ids']) < 2) continue;
			$keepId = array_shift($item['ids']);
			$content = q8_toollog_build_content($prefix, $item['entries']);
			$DB->exec("UPDATE {$table} SET content=:content WHERE id=:id", array(':content' => $content, ':id' => $keepId));
			foreach ($item['ids'] as $deleteId) {
				$DB->exec("DELETE FROM {$table} WHERE id=:id LIMIT 1", array(':id' => $deleteId));
			}
		}
		q8_toollog_clear_cache();
	}
}

if (!function_exists('q8_toollog_append_group')) {
	function q8_toollog_append_group($type, $name, $tid = 0, $date = null)
	{
		global $DB;
		if (!$DB) return false;
		q8_toollog_ensure_tables();
		$type = $type === 'offline' ? 'offline' : 'online';
		$table = $type === 'offline' ? 'pre_toollogs_offline' : 'pre_toollogs';
		$prefix = $type === 'offline' ? '商品下架：' : '商品上架：';
		$date = $date ?: date('Y-m-d');
		$row = $DB->getRow("SELECT * FROM {$table} WHERE date=:date AND active=1 ORDER BY id DESC LIMIT 1", array(':date' => $date));
		if ($row) {
			$entries = q8_toollog_parse_content($row['content']);
			if (q8_toollog_append_unique_entry($entries, $name, $tid)) {
				$DB->exec("UPDATE {$table} SET content=:content,addtime=NOW() WHERE id=:id", array(':content' => q8_toollog_build_content($prefix, $entries), ':id' => $row['id']));
				q8_toollog_clear_cache();
			}
			return true;
		}
		$content = q8_toollog_build_content($prefix, array(array('name' => $name, 'tid' => $tid)));
		$DB->exec("INSERT INTO {$table} (`content`,`date`,`addtime`,`active`) VALUES (:content,:date,NOW(),1)", array(':content' => $content, ':date' => $date));
		q8_toollog_clear_cache();
		return true;
	}
}

if (!function_exists('q8_toollog_clear_cache')) {
	function q8_toollog_clear_cache()
	{
		foreach (array(sys_get_temp_dir() . '/q8_toollogsgroup_*.json', sys_get_temp_dir() . '/q8_toollogsoffline_*.json') as $pattern) {
			foreach ((array)glob($pattern) as $file) {
				if (is_file($file)) @unlink($file);
			}
		}
	}
}

if (!function_exists('q8_get_recharge_rebate_rate')) {
	function q8_get_recharge_rebate_rate($money, $conf)
	{
		$money = floatval($money);
		if ($money <= 0 || empty($conf) || intval(isset($conf['recharge_rebate_enabled']) ? $conf['recharge_rebate_enabled'] : 0) != 1) {
			return 0;
		}

		$rules = trim((string)(isset($conf['recharge_rebate_rules']) ? $conf['recharge_rebate_rules'] : ''));
		if ($rules === '' && !empty($conf['fenzhan_gift'])) {
			$rules = trim((string)$conf['fenzhan_gift']);
		}
		if ($rules !== '') {
			$matchedRate = 0;
			foreach (explode('|', $rules) as $item) {
				$parts = explode(':', trim($item), 2);
				if (count($parts) != 2) continue;
				$threshold = floatval($parts[0]);
				$rate = floatval($parts[1]);
				if ($threshold > 0 && $rate > 0 && $money >= $threshold) {
					$matchedRate = $rate;
				}
			}
			return $matchedRate;
		}

		$min = floatval(isset($conf['recharge_rebate_min']) ? $conf['recharge_rebate_min'] : 0);
		if ($min > 0 && $money < $min) return 0;
		return max(0, floatval(isset($conf['recharge_rebate_rate']) ? $conf['recharge_rebate_rate'] : 0));
	}
}

if (!function_exists('q8_calc_online_recharge_bonus')) {
	function q8_calc_online_recharge_bonus($money, $conf)
	{
		$money = floatval($money);
		$rate = q8_get_recharge_rebate_rate($money, $conf);
		if ($money <= 0 || $rate <= 0) return 0;
		return round($money * $rate / 100, 2);
	}
}

if (!function_exists('q8_grant_recharge_rebate')) {
	function q8_grant_recharge_rebate($zid, $money, $source = 'online')
	{
		$zid = intval($zid);
		$money = floatval($money);
		if ($zid <= 0 || $money <= 0) return 0;
		global $conf;
		$bonus = q8_calc_online_recharge_bonus($money, $conf);
		if ($bonus <= 0) return 0;
		$rate = q8_get_recharge_rebate_rate($money, $conf);
		$action = hex2bin('e8b5a0e98081');
		$label = $source === 'admin' ? hex2bin('e5908ee58fb0e58aa0e6acbe') : ($source === 'card' ? hex2bin('e58aa0e6acbee58da1') : hex2bin('e59ca8e7babfe58585e580bc'));
		$bz = $label . $money . hex2bin('e58583e8b5a0e98081') . $rate . '%=' . $bonus . hex2bin('e58583');
		changeUserMoney($zid, $bonus, true, $action, $bz);
		return $bonus;
	}
}

if (!function_exists('q8_sitetask_ensure_log_table')) {
	function q8_sitetask_ensure_log_table()
	{
		global $DB;
		$DB->exec("CREATE TABLE IF NOT EXISTS `pre_sitetask_log` (
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
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	}
}

if (!function_exists('q8_sitetask_progress')) {
	function q8_sitetask_progress($task, $zid)
	{
		global $DB;
		$zid = intval($zid);
		$type = intval(isset($task['type']) ? $task['type'] : 1);
		$taskType = intval(isset($task['task']) ? $task['task'] : 0);
		$value = floatval(isset($task['value']) ? $task['value'] : 0);
		$whereDate = $type === 0 ? " AND addtime>='" . date('Y-m-d 00:00:00') . "'" : '';
		$progress = 0;

		if ($taskType === 1) {
			$progress = floatval($DB->getColumn("SELECT COALESCE(SUM(money),0) FROM pre_pay WHERE tid=-1 AND status=1 AND input=:zid{$whereDate}", array(':zid' => (string)$zid)));
		} elseif ($taskType === 2) {
			$progress = intval($DB->getColumn("SELECT COUNT(*) FROM pre_orders WHERE zid=:zid AND status IN (1,2){$whereDate}", array(':zid' => $zid)));
		} elseif ($taskType === 3) {
			$progress = floatval($DB->getColumn("SELECT COALESCE(SUM(money),0) FROM pre_orders WHERE zid=:zid AND status IN (1,2){$whereDate}", array(':zid' => $zid)));
		} elseif ($taskType === 4) {
			$progress = intval($DB->getColumn("SELECT COUNT(*) FROM pre_site WHERE upzid=:zid{$whereDate}", array(':zid' => $zid)));
		} elseif ($taskType === 5) {
			$progress = intval($DB->getColumn("SELECT COALESCE(MAX(`continue`),0) FROM pre_qiandao WHERE zid=:zid", array(':zid' => $zid)));
		} else {
			$tid = intval(isset($task['tid']) ? $task['tid'] : 0);
			$progress = intval($DB->getColumn("SELECT COUNT(*) FROM pre_orders WHERE zid=:zid AND tid=:tid AND status IN (1,2){$whereDate}", array(':zid' => $zid, ':tid' => $tid)));
		}

		return array(
			'progress' => $progress,
			'target' => $value,
			'done' => $value > 0 && $progress >= $value
		);
	}
}

if (!function_exists('q8_sitetask_status')) {
	function q8_sitetask_status($task, $userrow)
	{
		global $DB, $date;
		q8_sitetask_ensure_log_table();
		$zid = intval($userrow['zid']);
		$taskId = intval($task['id']);
		$status = q8_sitetask_progress($task, $zid);
		$status['claimed'] = false;
		$status['claim_text'] = '';
		$status['log'] = $DB->getRow("SELECT * FROM pre_sitetask_log WHERE userid=:userid AND taskid=:taskid AND status IN (0,1) ORDER BY id DESC LIMIT 1", array(':userid' => $zid, ':taskid' => $taskId));
		if ($status['log']) {
			$status['claimed'] = intval($status['log']['status']) === 1;
			$status['claim_text'] = $status['claimed'] ? 'rewarded' : 'pending';
			return $status;
		}
		if (!$status['done']) return $status;

		$quantity = intval(isset($task['quantity']) ? $task['quantity'] : 0);
		if ($quantity > 0) {
			$sent = intval($DB->getColumn("SELECT COUNT(*) FROM pre_sitetask_log WHERE taskid=:taskid AND status=1", array(':taskid' => $taskId)));
			if ($sent >= $quantity) {
				$status['claim_text'] = 'soldout';
				return $status;
			}
		}

		$money = round(floatval($task['money']), 2);
		if ($money > 0) {
			changeUserMoney($zid, $money, true, hex2bin('e8b5a0e98081'), 'Site task reward: ' . $task['name']);
		}
		$DB->exec("INSERT INTO pre_sitetask_log (`userid`,`taskid`,`taskname`,`money`,`addtime`,`status`,`remark`) VALUES (:userid,:taskid,:taskname,:money,:addtime,1,:remark)", array(
			':userid' => $zid,
			':taskid' => $taskId,
			':taskname' => $task['name'],
			':money' => $money,
			':addtime' => $date,
			':remark' => 'auto granted'
		));
		$status['claimed'] = true;
		$status['claim_text'] = 'rewarded';
		return $status;
	}
}

if (!function_exists('q8_visit_stats_ensure_site_columns')) {
	function q8_visit_stats_ensure_site_columns()
	{
		global $DB;
		static $done = false;
		if ($done || !$DB) return;
		$done = true;
		try {
			$tableExists = $DB->getColumn("SHOW TABLES LIKE 'shua_visit_ips'");
			if (!$tableExists) return;
			if (!$DB->getColumn("SHOW COLUMNS FROM shua_visit_ips LIKE 'username'")) $DB->query("ALTER TABLE shua_visit_ips ADD COLUMN username varchar(150) NOT NULL DEFAULT '' AFTER ip");
			if (!$DB->getColumn("SHOW COLUMNS FROM shua_visit_ips LIKE 'host'")) $DB->query("ALTER TABLE shua_visit_ips ADD COLUMN host varchar(191) NOT NULL DEFAULT '' AFTER username");
			if (!$DB->getColumn("SHOW COLUMNS FROM shua_visit_ips LIKE 'site_zid'")) $DB->query("ALTER TABLE shua_visit_ips ADD COLUMN site_zid int(11) NOT NULL DEFAULT 0 AFTER host");
			if (!$DB->getColumn("SHOW INDEX FROM shua_visit_ips WHERE Key_name='date_site_ip'")) $DB->query("ALTER TABLE shua_visit_ips ADD KEY date_site_ip (date, site_zid, ip)");
		} catch (Exception $e) {
			return;
		}
	}
}

if (!function_exists('q8_get_site_visit_dashboard')) {
	function q8_get_site_visit_dashboard($siteZid)
	{
		global $DB;
		$stats = array('ready' => false, 'today_visits' => 0, 'today_ips' => 0, 'week_visits' => 0, 'week_ips' => 0, 'chart_days' => array(), 'chart_max' => 1);
		$siteZid = intval($siteZid);
		if ($siteZid <= 0 || !$DB) return $stats;
		try {
			if (!$DB->getColumn("SHOW TABLES LIKE 'shua_visit_ips'")) return $stats;
			q8_visit_stats_ensure_site_columns();
			$today = date('Y-m-d');
			$weekStart = date('Y-m-d', strtotime('-6 day'));
			$stats['today_visits'] = intval($DB->getColumn("SELECT COALESCE(SUM(visits),0) FROM shua_visit_ips WHERE date=:date AND site_zid=:site_zid", array(':date' => $today, ':site_zid' => $siteZid)));
			$stats['today_ips'] = intval($DB->getColumn("SELECT COUNT(DISTINCT ip) FROM shua_visit_ips WHERE date=:date AND site_zid=:site_zid", array(':date' => $today, ':site_zid' => $siteZid)));
			$stats['week_visits'] = intval($DB->getColumn("SELECT COALESCE(SUM(visits),0) FROM shua_visit_ips WHERE date>=:start_date AND date<=:end_date AND site_zid=:site_zid", array(':start_date' => $weekStart, ':end_date' => $today, ':site_zid' => $siteZid)));
			$stats['week_ips'] = intval($DB->getColumn("SELECT COUNT(DISTINCT ip) FROM shua_visit_ips WHERE date>=:start_date AND date<=:end_date AND site_zid=:site_zid", array(':start_date' => $weekStart, ':end_date' => $today, ':site_zid' => $siteZid)));
			$rows = $DB->getAll("SELECT date, COALESCE(SUM(visits),0) AS visits, COUNT(DISTINCT ip) AS ip_count FROM shua_visit_ips WHERE date>=:start_date AND date<=:end_date AND site_zid=:site_zid GROUP BY date ORDER BY date ASC", array(':start_date' => $weekStart, ':end_date' => $today, ':site_zid' => $siteZid));
			$map = array();
			foreach ((array)$rows as $row) $map[$row['date']] = array('visits' => intval($row['visits']), 'ips' => intval($row['ip_count']));
			for ($i = 6; $i >= 0; $i--) {
				$day = date('Y-m-d', strtotime('-' . $i . ' day'));
				$dayVisits = isset($map[$day]) ? intval($map[$day]['visits']) : 0;
				$dayIps = isset($map[$day]) ? intval($map[$day]['ips']) : 0;
				if ($dayVisits > $stats['chart_max']) $stats['chart_max'] = $dayVisits;
				$stats['chart_days'][] = array('date' => $day, 'label' => date('m/d', strtotime($day)), 'visits' => $dayVisits, 'ips' => $dayIps);
			}
			$stats['ready'] = true;
		} catch (Exception $e) {
			return $stats;
		}
		return $stats;
	}
}

if (!function_exists('q8_record_front_visit')) {
	function q8_record_front_visit()
	{
		global $DB, $clientip, $islogin2, $userrow, $is_fenzhan, $siterow;
		if (!$DB || php_sapi_name() === 'cli') return;
		$path = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
		$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $path;
		if (strpos($path, '/admin/') !== false || strpos($path, '/api/') !== false || strpos($path, '/includes/') !== false || strpos($path, '/assets/') !== false || strpos($path, '/template/') !== false) return;
		if (preg_match('/\\.(?:css|js|png|jpg|jpeg|gif|ico|svg|webp|woff|woff2|ttf|map)(?:\\?|$)/i', $uri)) return;
		if (strpos($path, 'ajax.php') !== false || strpos($path, 'api.php') !== false || strpos($path, 'cron.php') !== false) return;
		try {
			$DB->query("CREATE TABLE IF NOT EXISTS shua_visit_statistics (id int(11) NOT NULL AUTO_INCREMENT,date date NOT NULL,visits int(11) NOT NULL DEFAULT 0,ip_count int(11) NOT NULL DEFAULT 0,created_at timestamp DEFAULT CURRENT_TIMESTAMP,updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,PRIMARY KEY (id),UNIQUE KEY date (date)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
			$DB->query("CREATE TABLE IF NOT EXISTS shua_visit_ips (id int(11) NOT NULL AUTO_INCREMENT,date date NOT NULL,ip varchar(45) NOT NULL,username varchar(150) NOT NULL DEFAULT '',host varchar(191) NOT NULL DEFAULT '',site_zid int(11) NOT NULL DEFAULT 0,url varchar(255) DEFAULT '-',user_agent text,region varchar(100) DEFAULT 'unknown',visits int(11) NOT NULL DEFAULT 1,created_at timestamp DEFAULT CURRENT_TIMESTAMP,updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,PRIMARY KEY (id),KEY date_ip (date, ip),KEY date_site_ip (date, site_zid, ip)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
			q8_visit_stats_ensure_site_columns();
			$today = date('Y-m-d');
			$ip = $clientip ?: (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
			if ($ip === '') return;
			$url = function_exists('mb_substr') ? mb_substr($uri, 0, 255, 'UTF-8') : substr($uri, 0, 255);
			$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
			$username = (!empty($islogin2) && $islogin2 == 1 && !empty($userrow['user'])) ? (function_exists('mb_substr') ? mb_substr($userrow['user'], 0, 150, 'UTF-8') : substr($userrow['user'], 0, 150)) : '';
			$host = isset($_SERVER['HTTP_HOST']) ? strtolower(preg_replace('/:\\d+$/', '', $_SERVER['HTTP_HOST'])) : '';
			$siteZid = (!empty($is_fenzhan) && !empty($siterow['zid'])) ? intval($siterow['zid']) : 0;
			$DB->query("INSERT INTO shua_visit_statistics (date, visits, ip_count) VALUES (:date, 1, 0) ON DUPLICATE KEY UPDATE visits = visits + 1", array(':date' => $today));
			$exists = $DB->getColumn("SELECT id FROM shua_visit_ips WHERE date=:date AND ip=:ip AND site_zid=:site_zid LIMIT 1", array(':date' => $today, ':ip' => $ip, ':site_zid' => $siteZid));
			if ($exists) {
				$DB->query("UPDATE shua_visit_ips SET visits=visits+1,url=:url,user_agent=:ua,host=:host,site_zid=:site_zid,username=CASE WHEN :username_set='' THEN username ELSE :username_value END WHERE id=:id", array(':url' => $url, ':ua' => $ua, ':host' => $host, ':site_zid' => $siteZid, ':username_set' => $username, ':username_value' => $username, ':id' => $exists));
			} else {
				$dateIpExists = $DB->getColumn("SELECT id FROM shua_visit_ips WHERE date=:date AND ip=:ip LIMIT 1", array(':date' => $today, ':ip' => $ip));
				$DB->query("INSERT INTO shua_visit_ips (date, ip, username, host, site_zid, url, user_agent) VALUES (:date,:ip,:username,:host,:site_zid,:url,:ua)", array(':date' => $today, ':ip' => $ip, ':username' => $username, ':host' => $host, ':site_zid' => $siteZid, ':url' => $url, ':ua' => $ua));
				if (!$dateIpExists) $DB->query("UPDATE shua_visit_statistics SET ip_count=ip_count+1 WHERE date=:date", array(':date' => $today));
			}
		} catch (Exception $e) {
			return;
		}
	}
}

if (!function_exists('q8_add_site_log')) {
	function q8_add_site_log($type, $action, $object_id, $summary, $detail = '', $operator = '')
	{
		global $DB, $clientip;
		if (!$DB) return false;
		try {
			$DB->exec("CREATE TABLE IF NOT EXISTS `pre_site_logs` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`type` varchar(32) NOT NULL DEFAULT '',`action` varchar(64) NOT NULL DEFAULT '',`object_id` varchar(64) DEFAULT '',`summary` varchar(255) NOT NULL DEFAULT '',`detail` text,`operator` varchar(64) DEFAULT 'system',`ip` varchar(45) DEFAULT '',`addtime` datetime DEFAULT NULL,PRIMARY KEY (`id`),KEY `type` (`type`),KEY `action` (`action`),KEY `addtime` (`addtime`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
			if ($operator === '') $operator = isset($_SESSION['admin_user']) ? $_SESSION['admin_user'] : 'admin';
			$ip = isset($clientip) ? $clientip : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
			$summary = function_exists('mb_substr') ? mb_substr((string)$summary, 0, 250, 'UTF-8') : substr((string)$summary, 0, 250);
			$DB->exec("INSERT INTO pre_site_logs (`type`,`action`,`object_id`,`summary`,`detail`,`operator`,`ip`,`addtime`) VALUES (:type,:action,:object_id,:summary,:detail,:operator,:ip,NOW())", array(':type' => substr((string)$type, 0, 32), ':action' => substr((string)$action, 0, 64), ':object_id' => substr((string)$object_id, 0, 64), ':summary' => $summary, ':detail' => is_scalar($detail) ? (string)$detail : json_encode($detail), ':operator' => substr((string)$operator, 0, 64), ':ip' => substr((string)$ip, 0, 45)));
			return true;
		} catch (Exception $e) {
			return false;
		}
	}
}
