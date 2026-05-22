<?php
/**
 * 自动同步设置
 */
include '../includes/common.php';
$title = '自动同步设置';

if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$adminSyncAction = isset($_REQUEST['action']) ? (string)$_REQUEST['action'] : '';
adminpermission('shequ', $adminSyncAction === 'run_sync_now' ? 2 : 1);

if (!function_exists('q8_admin_sync_escape')) {
    function q8_admin_sync_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('q8_admin_sync_json')) {
    function q8_admin_sync_json($value)
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
    }
}

if (!function_exists('q8_admin_sync_db_escape')) {
    function q8_admin_sync_db_escape($DB, $value)
    {
        if (is_object($DB) && method_exists($DB, 'escape')) {
            return $DB->escape($value);
        }

        return addslashes($value);
    }
}

if (!function_exists('q8_admin_sync_exec')) {
    function q8_admin_sync_exec($DB, $sql)
    {
        if (is_object($DB) && method_exists($DB, 'exec')) {
            return $DB->exec($sql);
        }

        return $DB->query($sql);
    }
}

if (!function_exists('q8_admin_sync_table_exists')) {
    function q8_admin_sync_table_exists($DB, $tableName)
    {
        $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
        $row = $DB->getRow("SHOW TABLES LIKE '{$safeTable}'");

        return $row ? true : false;
    }
}

if (!function_exists('q8_admin_sync_table_columns')) {
    function q8_admin_sync_table_columns($DB, $tableName)
    {
        $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
        $result = $DB->query("SHOW COLUMNS FROM `{$safeTable}`");
        $columns = array();

        while ($row = $result->fetch()) {
            if (isset($row['Field'])) {
                $columns[] = $row['Field'];
            } elseif (isset($row[0])) {
                $columns[] = $row[0];
            }
        }

        return $columns;
    }
}

if (!function_exists('q8_admin_sync_ensure_schema')) {
    function q8_admin_sync_ensure_schema($DB)
    {
        $tableName = 'pre_sync_config';

        if (!q8_admin_sync_table_exists($DB, $tableName)) {
            $createTableSql = "CREATE TABLE `pre_sync_config` (
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
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;";
            q8_admin_sync_exec($DB, $createTableSql);
        }

        $columns = q8_admin_sync_table_columns($DB, $tableName);
        $requiredColumns = array(
            'shequ_id' => "ALTER TABLE `pre_sync_config` ADD COLUMN `shequ_id` int(11) NOT NULL AFTER `id`",
            'sync_interval' => "ALTER TABLE `pre_sync_config` ADD COLUMN `sync_interval` int(11) NOT NULL DEFAULT '5' AFTER `shequ_id`",
            'sync_limit' => "ALTER TABLE `pre_sync_config` ADD COLUMN `sync_limit` int(11) NOT NULL DEFAULT '50' AFTER `sync_interval`",
            'auto_update' => "ALTER TABLE `pre_sync_config` ADD COLUMN `auto_update` tinyint(1) NOT NULL DEFAULT '1' AFTER `sync_limit`",
            'delete_rule' => "ALTER TABLE `pre_sync_config` ADD COLUMN `delete_rule` tinyint(1) NOT NULL DEFAULT '0' AFTER `auto_update`",
            'sync_class' => "ALTER TABLE `pre_sync_config` ADD COLUMN `sync_class` tinyint(1) NOT NULL DEFAULT '0' AFTER `delete_rule`",
            'sync_sort' => "ALTER TABLE `pre_sync_config` ADD COLUMN `sync_sort` tinyint(1) NOT NULL DEFAULT '0' AFTER `sync_class`",
            'sync_goods_sort' => "ALTER TABLE `pre_sync_config` ADD COLUMN `sync_goods_sort` tinyint(1) NOT NULL DEFAULT '0' AFTER `sync_sort`",
            'sync_log' => "ALTER TABLE `pre_sync_config` ADD COLUMN `sync_log` tinyint(1) NOT NULL DEFAULT '0' AFTER `sync_goods_sort`",
            'sync_name' => "ALTER TABLE `pre_sync_config` ADD COLUMN `sync_name` tinyint(1) NOT NULL DEFAULT '0' AFTER `sync_log`",
            'sync_price' => "ALTER TABLE `pre_sync_config` ADD COLUMN `sync_price` tinyint(1) NOT NULL DEFAULT '0' AFTER `sync_name`",
            'sync_cost' => "ALTER TABLE `pre_sync_config` ADD COLUMN `sync_cost` tinyint(1) NOT NULL DEFAULT '0' AFTER `sync_price`",
            'sync_desc' => "ALTER TABLE `pre_sync_config` ADD COLUMN `sync_desc` tinyint(1) NOT NULL DEFAULT '0' AFTER `sync_cost`",
            'sync_image' => "ALTER TABLE `pre_sync_config` ADD COLUMN `sync_image` tinyint(1) NOT NULL DEFAULT '0' AFTER `sync_desc`",
            'sync_workorder' => "ALTER TABLE `pre_sync_config` ADD COLUMN `sync_workorder` tinyint(1) NOT NULL DEFAULT '0' AFTER `sync_image`",
            'add_class' => "ALTER TABLE `pre_sync_config` ADD COLUMN `add_class` tinyint(1) NOT NULL DEFAULT '0' AFTER `sync_workorder`",
            'add_goods' => "ALTER TABLE `pre_sync_config` ADD COLUMN `add_goods` tinyint(1) NOT NULL DEFAULT '0' AFTER `add_class`",
            'markup_template' => "ALTER TABLE `pre_sync_config` ADD COLUMN `markup_template` int(11) NOT NULL DEFAULT '0' AFTER `add_goods`",
            'status' => "ALTER TABLE `pre_sync_config` ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT '0' AFTER `markup_template`",
            'addtime' => "ALTER TABLE `pre_sync_config` ADD COLUMN `addtime` datetime NOT NULL AFTER `status`"
        );

        foreach ($requiredColumns as $column => $sql) {
            if (!in_array($column, $columns, true)) {
                q8_admin_sync_exec($DB, $sql);
            }
        }

        $index = $DB->getRow("SHOW INDEX FROM `pre_sync_config` WHERE Key_name='shequ_id'");
        if (!$index) {
            q8_admin_sync_exec($DB, "ALTER TABLE `pre_sync_config` ADD UNIQUE KEY `shequ_id` (`shequ_id`)");
        }
    }
}

if (!function_exists('q8_admin_sync_render_select_options')) {
    function q8_admin_sync_render_select_options($options, $selected)
    {
        $html = '';

        foreach ($options as $value => $label) {
            $html .= '<option value="' . q8_admin_sync_escape($value) . '"' . ((string)$selected === (string)$value ? ' selected' : '') . '>' . q8_admin_sync_escape($label) . '</option>';
        }

        return $html;
    }
}

if (!function_exists('q8_admin_sync_fetch_price_rules')) {
    function q8_admin_sync_fetch_price_rules($DB)
    {
        $rows = array();
    $result = $DB->query("SELECT id,name FROM pre_price WHERE zid=0 ORDER BY id ASC");

        while ($row = $result->fetch()) {
            $rows[] = array(
                'id' => intval($row['id']),
                'name' => $row['name']
            );
        }

        return $rows;
    }
}

if (!function_exists('q8_admin_sync_render_price_options')) {
    function q8_admin_sync_render_price_options($rules, $selected)
    {
        $html = '<option value="0"' . ((string)$selected === '0' ? ' selected' : '') . '>不使用加价模板</option>';

        foreach ($rules as $rule) {
            $html .= '<option value="' . intval($rule['id']) . '"' . ((string)$selected === (string)$rule['id'] ? ' selected' : '') . '>' . q8_admin_sync_escape($rule['name']) . '</option>';
        }

        return $html;
    }
}

if (!function_exists('q8_admin_sync_delete_rule_text')) {
    function q8_admin_sync_delete_rule_text($value)
    {
        $value = intval($value);
        if ($value === 2) {
            return '对方删除商品时，本站也删除';
        }
        if ($value === 1) {
            return '对方删除商品时，本站下架';
        }

        return '保留本站商品';
    }
}

if (!function_exists('q8_admin_sync_make_site_url')) {
    function q8_admin_sync_make_site_url($row)
    {
        $url = isset($row['url']) ? trim((string)$row['url']) : '';
        if ($url === '') {
            return '';
        }
        if (preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }

        $protocol = isset($row['protocol']) && intval($row['protocol']) === 1 ? 'https://' : 'http://';

        return $protocol . $url;
    }
}

if (!function_exists('q8_admin_sync_fetch_shequ_rows')) {
    function q8_admin_sync_fetch_shequ_rows($DB)
    {
        $rows = array();
        $map = array();
        $result = $DB->query("SELECT * FROM pre_shequ ORDER BY id ASC");

        while ($row = $result->fetch()) {
            $row['id'] = intval($row['id']);
            $pluginConfig = \lib\Plugin::getConfig('third_' . $row['type']);
            $row['plugin_title'] = isset($pluginConfig['title']) ? $pluginConfig['title'] : strtoupper($row['type']);
            $row['display_url'] = q8_admin_sync_make_site_url($row);
            $row['remark'] = isset($row['remark']) ? (string)$row['remark'] : '';
            $row['status'] = isset($row['status']) ? intval($row['status']) : 1;
            $rows[] = $row;
            $map[$row['id']] = $row;
        }

        return array($rows, $map);
    }
}

if (!function_exists('q8_admin_sync_admin_base_url')) {
    function q8_admin_sync_admin_base_url()
    {
        $forwardedProto = '';
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO']);
            $forwardedProto = strtolower(trim($parts[0]));
        }

        $requestScheme = !empty($_SERVER['REQUEST_SCHEME']) ? strtolower((string)$_SERVER['REQUEST_SCHEME']) : '';
        $frontEndHttps = !empty($_SERVER['HTTP_FRONT_END_HTTPS']) ? strtolower((string)$_SERVER['HTTP_FRONT_END_HTTPS']) : '';
        $forwardedSsl = !empty($_SERVER['HTTP_X_FORWARDED_SSL']) ? strtolower((string)$_SERVER['HTTP_X_FORWARDED_SSL']) : '';
        $host = isset($_SERVER['HTTP_HOST']) ? (string)$_SERVER['HTTP_HOST'] : '';

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && intval($_SERVER['SERVER_PORT']) === 443)
            || $forwardedProto === 'https'
            || $requestScheme === 'https'
            || $frontEndHttps === 'on'
            || $forwardedSsl === 'on';

        $scheme = $isHttps ? 'https://' : 'http://';
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $scriptDir = rtrim($scriptDir, '/');

        return $scheme . $host . $scriptDir . '/';
    }
}

if (!function_exists('q8_admin_sync_default_config')) {
    function q8_admin_sync_default_config()
    {
        return array(
            'shequ_id' => 0,
            'sync_interval' => 5,
            'sync_limit' => 50,
            'auto_update' => 1,
            'delete_rule' => 0,
            'sync_class' => 0,
            'sync_sort' => 0,
            'sync_goods_sort' => 0,
            'sync_log' => 0,
            'sync_name' => 0,
            'sync_price' => 0,
            'sync_cost' => 0,
            'sync_desc' => 0,
            'sync_image' => 0,
            'sync_workorder' => 0,
            'add_class' => 0,
            'add_goods' => 0,
            'markup_template' => 0,
            'status' => 0
        );
    }
}

if (!function_exists('q8_admin_sync_merge_config')) {
    function q8_admin_sync_merge_config($config)
    {
        $defaults = q8_admin_sync_default_config();

        foreach ($defaults as $key => $value) {
            if (isset($config[$key])) {
                $defaults[$key] = is_numeric($value) ? intval($config[$key]) : $config[$key];
            }
        }

        return $defaults;
    }
}

if (!function_exists('q8_admin_sync_cron_schedule')) {
    function q8_admin_sync_cron_schedule($minutes)
    {
        $minutes = intval($minutes);

        if ($minutes <= 1) {
            return '* * * * *';
        }
        if ($minutes >= 60) {
            return '0 * * * *';
        }

        return '*/' . $minutes . ' * * * *';
    }
}

if (!function_exists('q8_admin_sync_start_monitor_request')) {
    function q8_admin_sync_start_monitor_request($url, $host)
    {
        $parts = parse_url($url);
        if (!$parts || empty($parts['path'])) {
            return false;
        }

        $path = $parts['path'] . (!empty($parts['query']) ? '?' . $parts['query'] : '');
        $host = $host !== '' ? $host : (!empty($parts['host']) ? $parts['host'] : 'localhost');
        $request = "GET {$path} HTTP/1.1\r\n";
        $request .= "Host: {$host}\r\n";
        $request .= "User-Agent: SuiSuiSync/1.0\r\n";
        $request .= "Connection: Close\r\n\r\n";

        foreach (array('127.0.0.1:80', 'ssl://127.0.0.1:443') as $target) {
            $targetParts = explode(':', $target);
            $scheme = count($targetParts) > 2 ? $targetParts[0] : '';
            $port = intval($targetParts[count($targetParts) - 1]);
            $address = $scheme === 'ssl' ? 'ssl://127.0.0.1' : '127.0.0.1';
            $errno = 0;
            $errstr = '';
            $fp = @fsockopen($address, $port, $errno, $errstr, 2);
            if (!$fp) {
                continue;
            }
            stream_set_timeout($fp, 2);
            fwrite($fp, $request);
            fclose($fp);
            return true;
        }

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_exec($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            return $errno === 0 || $errno === 28;
        }

        return false;
    }
}

if (!function_exists('q8_admin_sync_start_cli_task')) {
    function q8_admin_sync_start_cli_task($taskKey, $monitorKey)
    {
        if (!function_exists('exec')) {
            return false;
        }

        $phpBin = defined('PHP_BINDIR') ? rtrim(PHP_BINDIR, '/\\') . '/php' : '';
        if ($phpBin === '' || !is_file($phpBin)) {
            $phpBin = '/www/server/php/74/bin/php';
        }
        if (!is_file($phpBin)) {
            return false;
        }

        $script = __DIR__ . '/cx-api-synchronization.php';
        $command = 'cd ' . escapeshellarg(dirname(__DIR__)) . ' && nohup ' . escapeshellarg($phpBin) . ' ' . escapeshellarg($script)
            . ' key=' . escapeshellarg($monitorKey)
            . ' task_id=' . escapeshellarg($taskKey)
            . ' test=1 > /dev/null 2>&1 & echo $!';
        @exec($command, $output, $code);

        if ($code !== 0 || empty($output)) {
            return false;
        }

        $pid = preg_replace('/\D+/', '', (string)$output[0]);
        return $pid !== '' ? $pid : true;
    }
}

if (!function_exists('q8_admin_sync_ensure_task_schema')) {
    function q8_admin_sync_ensure_task_schema($DB)
    {
        q8_admin_sync_exec($DB, "CREATE TABLE IF NOT EXISTS `pre_sync_tasks` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `task_key` varchar(40) NOT NULL DEFAULT '',
            `trigger_type` varchar(32) NOT NULL DEFAULT 'manual',
            `status` varchar(16) NOT NULL DEFAULT 'queued',
            `progress` tinyint(3) unsigned NOT NULL DEFAULT 0,
            `summary` varchar(255) NOT NULL DEFAULT '',
            `error_reason` varchar(255) NOT NULL DEFAULT '',
            `upstream_summary` text,
            `output_tail` text,
            `detail` mediumtext,
            `started_at` datetime DEFAULT NULL,
            `finished_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            `addtime` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `task_key` (`task_key`),
            KEY `status` (`status`),
            KEY `addtime` (`addtime`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}

if (!function_exists('q8_admin_sync_create_task')) {
    function q8_admin_sync_create_task($DB, $triggerType, $monitorUrl)
    {
        $taskKey = date('YmdHis') . substr(md5(uniqid('', true)), 0, 10);
        $detail = q8_admin_sync_json(array(
            'monitor_url' => $monitorUrl,
            'created_from' => 'admin'
        ));
        $DB->exec("INSERT INTO `pre_sync_tasks` (`task_key`,`trigger_type`,`status`,`progress`,`summary`,`detail`,`addtime`,`updated_at`) VALUES (:task_key,:trigger_type,'queued',0,:summary,:detail,NOW(),NOW())", array(
            ':task_key' => $taskKey,
            ':trigger_type' => $triggerType,
            ':summary' => '任务已创建，等待后台接口接手执行',
            ':detail' => $detail
        ));

        return $taskKey;
    }
}

if (!function_exists('q8_admin_sync_update_task_status')) {
    function q8_admin_sync_update_task_status($DB, $taskKey, $status, $summary, $errorReason = '')
    {
        if ($taskKey === '') {
            return;
        }

        $progress = $status === 'failed' ? 100 : 0;
        $DB->exec("UPDATE `pre_sync_tasks` SET `status`=:status,`progress`=:progress,`summary`=:summary,`error_reason`=:error_reason,`finished_at`=NOW(),`updated_at`=NOW() WHERE `task_key`=:task_key", array(
            ':status' => $status,
            ':progress' => $progress,
            ':summary' => $summary,
            ':error_reason' => $errorReason,
            ':task_key' => $taskKey
        ));
    }
}

if (!function_exists('q8_admin_sync_update_task_detail')) {
    function q8_admin_sync_update_task_detail($DB, $taskKey, $detail)
    {
        if ($taskKey === '') {
            return;
        }

        $DB->exec("UPDATE `pre_sync_tasks` SET `detail`=:detail,`updated_at`=NOW() WHERE `task_key`=:task_key", array(
            ':detail' => q8_admin_sync_json($detail),
            ':task_key' => $taskKey
        ));
    }
}

if (!function_exists('q8_admin_sync_process_alive')) {
    function q8_admin_sync_process_alive($pid)
    {
        $pid = intval($pid);
        if ($pid <= 0 || !function_exists('exec')) {
            return null;
        }

        @exec('ps -p ' . intval($pid) . ' -o pid=', $output, $code);
        return $code === 0 && !empty($output);
    }
}

if (!function_exists('q8_admin_sync_task_status_text')) {
    function q8_admin_sync_task_status_text($status)
    {
        $map = array(
            'queued' => '排队中',
            'running' => '运行中',
            'success' => '已完成',
            'failed' => '失败'
        );

        return isset($map[$status]) ? $map[$status] : $status;
    }
}

if (!function_exists('q8_admin_sync_format_task')) {
    function q8_admin_sync_format_task($row)
    {
        if (!$row) {
            return null;
        }

        return array(
            'task_key' => $row['task_key'],
            'status' => $row['status'],
            'status_text' => q8_admin_sync_task_status_text($row['status']),
            'progress' => intval($row['progress']),
            'summary' => (string)$row['summary'],
            'error_reason' => (string)$row['error_reason'],
            'upstream_summary' => (string)$row['upstream_summary'],
            'output_tail' => (string)$row['output_tail'],
            'started_at' => (string)$row['started_at'],
            'finished_at' => (string)$row['finished_at'],
            'updated_at' => (string)$row['updated_at'],
            'addtime' => (string)$row['addtime']
        );
    }
}

if (!function_exists('q8_admin_sync_mark_stale_tasks')) {
    function q8_admin_sync_mark_stale_tasks($DB)
    {
        q8_admin_sync_ensure_task_schema($DB);
        $result = $DB->query("SELECT `task_key`,`detail`,`updated_at` FROM `pre_sync_tasks` WHERE `status`='running' AND `updated_at` IS NOT NULL AND `updated_at` < DATE_SUB(NOW(), INTERVAL 10 MINUTE) ORDER BY `id` ASC LIMIT 20");
        while ($row = $result->fetch()) {
            $detail = json_decode((string)$row['detail'], true);
            $pid = is_array($detail) && !empty($detail['pid']) ? intval($detail['pid']) : 0;
            $alive = $pid > 0 ? q8_admin_sync_process_alive($pid) : null;
            if ($alive === true) {
                continue;
            }

            $reason = $pid > 0
                ? '后台 CLI 进程已退出，且任务超过 10 分钟没有写入新进度'
                : '后台任务超过 10 分钟没有写入新进度，可能已异常退出';
            $DB->exec("UPDATE `pre_sync_tasks` SET `status`='failed',`progress`=100,`summary`='同步进程已停止',`error_reason`=:error_reason,`finished_at`=NOW(),`updated_at`=NOW() WHERE `task_key`=:task_key AND `status`='running'", array(
                ':error_reason' => $reason,
                ':task_key' => $row['task_key']
            ));
        }
    }
}

if (!function_exists('q8_admin_sync_fetch_recent_tasks')) {
    function q8_admin_sync_fetch_recent_tasks($DB, $limit = 5)
    {
        q8_admin_sync_ensure_task_schema($DB);
        $rows = array();
        $limit = max(1, min(20, intval($limit)));
        $result = $DB->query("SELECT * FROM `pre_sync_tasks` ORDER BY `id` DESC LIMIT {$limit}");

        while ($row = $result->fetch()) {
            $rows[] = q8_admin_sync_format_task($row);
        }

        return $rows;
    }
}

if (!function_exists('q8_admin_sync_json_response')) {
    function q8_admin_sync_json_response($response)
    {
        @ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        echo q8_admin_sync_json($response);
        exit;
    }
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'sync_task_status') {
    q8_admin_sync_ensure_task_schema($DB);
    q8_admin_sync_mark_stale_tasks($DB);
    $taskKey = isset($_REQUEST['task_id']) ? preg_replace('/[^a-zA-Z0-9]/', '', (string)$_REQUEST['task_id']) : '';

    if ($taskKey !== '') {
        $row = $DB->getRow("SELECT * FROM `pre_sync_tasks` WHERE `task_key`=:task_key LIMIT 1", array(':task_key' => $taskKey));
        q8_admin_sync_json_response(array('code' => $row ? 1 : 0, 'task' => q8_admin_sync_format_task($row), 'msg' => $row ? 'ok' : '任务记录不存在'));
    }

    q8_admin_sync_json_response(array('code' => 1, 'tasks' => q8_admin_sync_fetch_recent_tasks($DB, 5)));
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'run_sync_now') {
    q8_admin_sync_ensure_task_schema($DB);

    $scheme = is_https() ? 'https://' : 'http://';
    $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $scriptBase = !empty($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])) : '/admin';
    if ($scriptBase === '.' || $scriptBase === '/') {
        $scriptBase = '/admin';
    }
    $monitorKey = (string)$DB->getColumn("SELECT `v` FROM `pre_config` WHERE `k`='monitor_key'");
    $taskKey = q8_admin_sync_create_task($DB, 'manual', '');
    $monitorUrl = $scheme . $host . rtrim($scriptBase, '/') . '/cx-api-synchronization.php?key=' . rawurlencode($monitorKey) . '&task_id=' . rawurlencode($taskKey) . '&test=1&__cv=' . time();

    $pid = q8_admin_sync_start_cli_task($taskKey, $monitorKey);
    if ($pid !== false) {
        q8_admin_sync_update_task_detail($DB, $taskKey, array(
            'monitor_url' => '',
            'created_from' => 'admin',
            'runner' => 'cli',
            'pid' => is_scalar($pid) ? (string)$pid : ''
        ));
        q8_admin_sync_json_response(array(
            'code' => 1,
            'msg' => '同步任务已创建，后台 CLI 正在执行，可在任务记录里查看进度。',
            'task_id' => $taskKey
        ));
    }

    if (q8_admin_sync_start_monitor_request($monitorUrl, $host)) {
        q8_admin_sync_update_task_detail($DB, $taskKey, array(
            'monitor_url' => $monitorUrl,
            'created_from' => 'admin',
            'runner' => 'http'
        ));
        q8_admin_sync_json_response(array(
            'code' => 1,
            'msg' => '同步任务已创建，后台正在执行，可在任务记录里查看进度。',
            'task_id' => $taskKey
        ));
    }

    q8_admin_sync_update_task_status($DB, $taskKey, 'failed', '同步请求未能送达后台接口', '本机 HTTP、curl 或站点访问配置不可用');
    q8_admin_sync_json_response(array('code' => 0, 'msg' => '同步请求失败，请检查本机 HTTP、curl 或站点访问配置', 'task_id' => $taskKey));
}

if (isset($_POST['submit'])) {
    $response = array('code' => 0, 'msg' => '保存失败，请稍后重试');

    try {
        q8_admin_sync_ensure_schema($DB);

        $selectedIds = array();
        if (isset($_POST['shequ_ids']) && is_array($_POST['shequ_ids'])) {
            foreach ($_POST['shequ_ids'] as $siteId) {
                $siteId = intval($siteId);
                if ($siteId > 0) {
                    $selectedIds[] = $siteId;
                }
            }
        }
        $selectedIds = array_values(array_unique($selectedIds));

        $syncInterval = isset($_POST['sync_interval']) ? intval($_POST['sync_interval']) : 5;
        if ($syncInterval < 0) {
            $syncInterval = 0;
        }

        $syncLimit = isset($_POST['sync_limit']) ? intval($_POST['sync_limit']) : 50;
        if ($syncLimit < 1) {
            $syncLimit = 1;
        }

        $monitorKey = isset($_POST['monitor_key']) ? trim((string)$_POST['monitor_key']) : '';
        $monitorKeySql = q8_admin_sync_db_escape($DB, $monitorKey);
        $now = date('Y-m-d H:i:s');
        $nowSql = q8_admin_sync_db_escape($DB, $now);
        $errors = array();
        $successCount = 0;

        q8_admin_sync_exec($DB, "UPDATE `pre_sync_config` SET `status`=0");

        foreach ($selectedIds as $siteId) {
            $data = array(
                'shequ_id' => $siteId,
                'sync_interval' => $syncInterval,
                'sync_limit' => $syncLimit,
                'auto_update' => isset($_POST['auto_update_' . $siteId]) ? intval($_POST['auto_update_' . $siteId]) : 1,
                'delete_rule' => isset($_POST['delete_rule_' . $siteId]) ? intval($_POST['delete_rule_' . $siteId]) : 0,
                'sync_class' => isset($_POST['sync_class_' . $siteId]) ? 1 : 0,
                'sync_sort' => isset($_POST['sync_sort_' . $siteId]) ? 1 : 0,
                'sync_goods_sort' => isset($_POST['sync_goods_sort_' . $siteId]) ? 1 : 0,
                'sync_log' => isset($_POST['sync_log_' . $siteId]) ? 1 : 0,
                'sync_name' => isset($_POST['sync_name_' . $siteId]) ? 1 : 0,
                'sync_price' => isset($_POST['sync_price_' . $siteId]) ? 1 : 0,
                'sync_cost' => isset($_POST['sync_cost_' . $siteId]) ? 1 : 0,
                'sync_desc' => isset($_POST['sync_desc_' . $siteId]) ? 1 : 0,
                'sync_image' => isset($_POST['sync_image_' . $siteId]) ? 1 : 0,
                'sync_workorder' => isset($_POST['sync_workorder_' . $siteId]) ? 1 : 0,
                'add_class' => isset($_POST['add_class_' . $siteId]) ? 1 : 0,
                'add_goods' => isset($_POST['add_goods_' . $siteId]) ? 1 : 0,
                'markup_template' => isset($_POST['markup_template_' . $siteId]) ? intval($_POST['markup_template_' . $siteId]) : 0,
                'status' => 1,
                'addtime' => $now
            );

            $exists = $DB->getRow("SELECT `id` FROM `pre_sync_config` WHERE `shequ_id`='" . intval($siteId) . "' LIMIT 1");
            if ($exists) {
                $sql = "UPDATE `pre_sync_config` SET "
                    . "`sync_interval`='" . intval($data['sync_interval']) . "',"
                    . "`sync_limit`='" . intval($data['sync_limit']) . "',"
                    . "`auto_update`='" . intval($data['auto_update']) . "',"
                    . "`delete_rule`='" . intval($data['delete_rule']) . "',"
                    . "`sync_class`='" . intval($data['sync_class']) . "',"
                    . "`sync_sort`='" . intval($data['sync_sort']) . "',"
                    . "`sync_goods_sort`='" . intval($data['sync_goods_sort']) . "',"
                    . "`sync_log`='" . intval($data['sync_log']) . "',"
                    . "`sync_name`='" . intval($data['sync_name']) . "',"
                    . "`sync_price`='" . intval($data['sync_price']) . "',"
                    . "`sync_cost`='" . intval($data['sync_cost']) . "',"
                    . "`sync_desc`='" . intval($data['sync_desc']) . "',"
                    . "`sync_image`='" . intval($data['sync_image']) . "',"
                    . "`sync_workorder`='" . intval($data['sync_workorder']) . "',"
                    . "`add_class`='" . intval($data['add_class']) . "',"
                    . "`add_goods`='" . intval($data['add_goods']) . "',"
                    . "`markup_template`='" . intval($data['markup_template']) . "',"
                    . "`status`='1',"
                    . "`addtime`='" . $nowSql . "' "
                    . "WHERE `shequ_id`='" . intval($siteId) . "'";
            } else {
                $sql = "INSERT INTO `pre_sync_config` SET "
                    . "`shequ_id`='" . intval($data['shequ_id']) . "',"
                    . "`sync_interval`='" . intval($data['sync_interval']) . "',"
                    . "`sync_limit`='" . intval($data['sync_limit']) . "',"
                    . "`auto_update`='" . intval($data['auto_update']) . "',"
                    . "`delete_rule`='" . intval($data['delete_rule']) . "',"
                    . "`sync_class`='" . intval($data['sync_class']) . "',"
                    . "`sync_sort`='" . intval($data['sync_sort']) . "',"
                    . "`sync_goods_sort`='" . intval($data['sync_goods_sort']) . "',"
                    . "`sync_log`='" . intval($data['sync_log']) . "',"
                    . "`sync_name`='" . intval($data['sync_name']) . "',"
                    . "`sync_price`='" . intval($data['sync_price']) . "',"
                    . "`sync_cost`='" . intval($data['sync_cost']) . "',"
                    . "`sync_desc`='" . intval($data['sync_desc']) . "',"
                    . "`sync_image`='" . intval($data['sync_image']) . "',"
                    . "`sync_workorder`='" . intval($data['sync_workorder']) . "',"
                    . "`add_class`='" . intval($data['add_class']) . "',"
                    . "`add_goods`='" . intval($data['add_goods']) . "',"
                    . "`markup_template`='" . intval($data['markup_template']) . "',"
                    . "`status`='1',"
                    . "`addtime`='" . $nowSql . "'";
            }

            $writeResult = q8_admin_sync_exec($DB, $sql);
            if ($writeResult === false) {
                $errors[] = '站点 ID ' . $siteId . ' 保存失败';
            } else {
                $successCount++;
            }
        }

        q8_admin_sync_exec($DB, "REPLACE INTO `pre_config` SET `k`='monitor_key', `v`='" . $monitorKeySql . "'");

        if (!empty($errors)) {
            $response = array('code' => 0, 'msg' => implode("\n", $errors));
        } else {
            $response = array(
                'code' => 1,
                'msg' => $successCount > 0 ? '已保存 ' . $successCount . ' 个站点的同步配置' : '已保存设置，当前没有启用同步站点'
            );
        }
    } catch (Exception $e) {
        $response = array('code' => 0, 'msg' => '服务器处理失败：' . $e->getMessage());
    }

    @ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');
    echo q8_admin_sync_json($response);
    exit;
}

q8_admin_sync_ensure_schema($DB);
q8_admin_sync_ensure_task_schema($DB);

list($shequRows, $shequMap) = q8_admin_sync_fetch_shequ_rows($DB);
$priceRules = q8_admin_sync_fetch_price_rules($DB);
$enabledConfigs = array();
$enabledResult = $DB->query("SELECT * FROM `pre_sync_config` WHERE `status`=1 ORDER BY `shequ_id` ASC");
while ($row = $enabledResult->fetch()) {
    $enabledConfigs[intval($row['shequ_id'])] = q8_admin_sync_merge_config($row);
}

$monitorKey = (string)$DB->getColumn("SELECT `v` FROM `pre_config` WHERE `k`='monitor_key'");
$monitorBasePath = '/admin/cx-api-synchronization.php';
$monitorBaseUrl = q8_admin_sync_admin_base_url() . 'cx-api-synchronization.php';
$monitorSecureUrl = preg_replace('/^http:\/\//i', 'https://', $monitorBaseUrl);
$monitorUrl = $monitorSecureUrl . ($monitorKey !== '' ? '?key=' . urlencode($monitorKey) : '');
$monitorClientUrl = $monitorUrl;
$lastRunTime = (string)$DB->getColumn("SELECT `v` FROM `pre_config` WHERE `k`='last_sync_time'");
if ($lastRunTime === '') {
    $lastRunTime = '从未运行';
}
$recentSyncTasks = q8_admin_sync_fetch_recent_tasks($DB, 5);

$globalConfig = array(
    'sync_interval' => 5,
    'sync_limit' => 50
);
if (!empty($enabledConfigs)) {
    $firstConfig = reset($enabledConfigs);
    $globalConfig['sync_interval'] = isset($firstConfig['sync_interval']) ? intval($firstConfig['sync_interval']) : 5;
    $globalConfig['sync_limit'] = isset($firstConfig['sync_limit']) ? intval($firstConfig['sync_limit']) : 50;
}

$intervalOptions = array(
    '0' => '0 分钟（调试）',
    '1' => '1 分钟',
    '3' => '3 分钟',
    '5' => '5 分钟',
    '10' => '10 分钟',
    '15' => '15 分钟',
    '30' => '30 分钟',
    '60' => '1 小时'
);
$limitOptions = array(
    '10' => '10 个 / 次',
    '30' => '30 个 / 次',
    '50' => '50 个 / 次',
    '100' => '100 个 / 次',
    '200' => '200 个 / 次',
    '500' => '500 个 / 次',
    '1000' => '1000 个 / 次'
);
$baseOptions = array(
    'auto_update' => array(
        'label' => '自动更新',
        'options' => array('1' => '开启', '0' => '关闭'),
        'help' => '开启后，命中同步规则时会更新已有商品。'
    ),
    'delete_rule' => array(
        'label' => '下架 / 删除规则',
        'options' => array('0' => '保留本站商品', '1' => '下架商品', '2' => '删除商品'),
        'help' => '决定对方商品下架或删除后，本站怎样处理对应商品。'
    )
);
$syncOptionGroups = array(
    array('key' => 'sync_class', 'label' => '分类', 'desc' => '更新商品所属分类，仅影响已有商品。'),
    array('key' => 'sync_sort', 'label' => '分类排序', 'desc' => '同步分类在后台和前台中的顺序。'),
    array('key' => 'sync_goods_sort', 'label' => '商品排序', 'desc' => '同步商品在所属分类中的排序。'),
    array('key' => 'sync_log', 'label' => '上架日志', 'desc' => '新增商品时记录上架日志，方便回溯。'),
    array('key' => 'sync_name', 'label' => '名称', 'desc' => '同步商品标题和展示名称。'),
    array('key' => 'sync_price', 'label' => '价格', 'desc' => '同步销售价格。'),
    array('key' => 'sync_cost', 'label' => '成本', 'desc' => '同步供货成本价。'),
    array('key' => 'sync_desc', 'label' => '描述', 'desc' => '同步商品详情和说明。'),
    array('key' => 'sync_image', 'label' => '图片', 'desc' => '同步封面图和展示图资源。'),
    array('key' => 'sync_workorder', 'label' => '网盘投诉', 'desc' => '同步并开启商品的网盘投诉能力。')
);
$addOptionGroups = array(
    array('key' => 'add_class', 'label' => '允许新增分类', 'desc' => '关闭后，没有对应分类的商品将不会新增到本站。'),
    array('key' => 'add_goods', 'label' => '允许新增商品', 'desc' => '控制同步过程中是否允许创建新商品。')
);

$enabledAutoUpdate = 0;
$enabledAddGoods = 0;
$activeSummaries = array();
foreach ($enabledConfigs as $siteId => $config) {
    if (intval($config['auto_update']) === 1) {
        $enabledAutoUpdate++;
    }
    if (intval($config['add_goods']) === 1) {
        $enabledAddGoods++;
    }
    if (isset($shequMap[$siteId])) {
        $siteRow = $shequMap[$siteId];
        $activeSummaries[] = array(
            'site_id' => $siteId,
            'title' => $siteRow['plugin_title'],
            'url' => $siteRow['display_url'],
            'delete_rule' => q8_admin_sync_delete_rule_text($config['delete_rule']),
            'add_goods' => intval($config['add_goods']) === 1,
            'sync_sort' => intval($config['sync_sort']) === 1 && intval($config['sync_goods_sort']) === 1
        );
    }
}

$syncStats = array(
    'total_sites' => count($shequRows),
    'enabled_sites' => count($enabledConfigs),
    'auto_update' => $enabledAutoUpdate,
    'add_goods' => $enabledAddGoods,
    'last_run_time' => $lastRunTime
);
$cronSchedule = q8_admin_sync_cron_schedule($globalConfig['sync_interval']);
$cronCommand = $cronSchedule . ' curl -m 120 --silent ' . $monitorClientUrl;

$pageContext = apply_filters('admin_sync_context', array(
    'stats' => $syncStats,
    'monitor_url' => $monitorUrl,
    'last_run_time' => $lastRunTime,
    'recent_tasks' => $recentSyncTasks,
    'enabled_sites' => $enabledConfigs,
    'site_total' => count($shequRows)
));

include './head.php';
$syncAssetVersion = isset($adminAssetVersion) ? $adminAssetVersion : ((defined('VERSION') ? VERSION : '1.0.0') . '.20260426admin37');
?>
<link rel="stylesheet" href="./assets/css/admin-cx-synchronization.css?v=<?php echo urlencode($syncAssetVersion); ?>">

<div class="col-xs-12 admin-sync-page">
    <?php echo q8_render_action('admin_sync_page_before', $pageContext); ?>

    <section class="admin-sync-hero">
        <div class="admin-sync-hero__content">
            <p class="admin-sync-hero__eyebrow"><?php echo html_entity_decode('&#21516;&#27493;&#25511;&#21046;&#21488;', ENT_QUOTES, 'UTF-8'); ?></p>
            <h2>统一管理供货站点的自动同步规则、任务入口和新增商品策略</h2>
            <p>这个页面只负责保存同步规则和调度入口，不改动原有供货业务逻辑。保存后可以交给计划任务按周期访问监控地址，也可以在这里手动触发一次同步，便于调试和排查。</p>
        </div>
        <div class="admin-sync-hero__aside">
            <a href="./shequlist.php" class="admin-sync-hero__chip"><i class="fa fa-plug"></i> 对接站点</a>
            <a href="./batchgoods.php" class="admin-sync-hero__chip"><i class="fa fa-cloud-download"></i> 批量对接</a>
            <a href="./price.php" class="admin-sync-hero__chip"><i class="fa fa-sliders"></i> 加价模板</a>
        </div>
    </section>

    <?php echo q8_render_action('admin_sync_hero_after', $pageContext); ?>

    <section class="admin-sync-stats">
        <article class="admin-sync-stat admin-ui-stat">
            <span class="admin-sync-stat__icon admin-sync-stat__icon--primary admin-ui-stat__icon"><i class="fa fa-plug"></i></span>
            <div><span>已接入站点</span><strong><?php echo intval($syncStats['total_sites']); ?></strong></div>
        </article>
        <article class="admin-sync-stat admin-ui-stat">
            <span class="admin-sync-stat__icon admin-sync-stat__icon--success admin-ui-stat__icon"><i class="fa fa-refresh"></i></span>
            <div><span>已启用同步</span><strong id="syncEnabledCount"><?php echo intval($syncStats['enabled_sites']); ?></strong></div>
        </article>
        <article class="admin-sync-stat admin-ui-stat">
            <span class="admin-sync-stat__icon admin-sync-stat__icon--warning admin-ui-stat__icon"><i class="fa fa-bolt"></i></span>
            <div><span>自动更新开启</span><strong><?php echo intval($syncStats['auto_update']); ?></strong></div>
        </article>
        <article class="admin-sync-stat admin-ui-stat">
            <span class="admin-sync-stat__icon admin-sync-stat__icon--accent admin-ui-stat__icon"><i class="fa fa-clock-o"></i></span>
            <div><span>最近运行</span><strong class="admin-sync-stat__time"><?php echo q8_admin_sync_escape($syncStats['last_run_time']); ?></strong></div>
        </article>
    </section>

    <section class="admin-sync-map">
        <article>
            <i class="fa fa-save"></i>
            <div>
                <strong>先保存规则</strong>
                <p>页面保存的是站点级同步规则，决定哪些字段更新、哪些商品可新增。</p>
            </div>
        </article>
        <article>
            <i class="fa fa-terminal"></i>
            <div>
                <strong>再交给计划任务</strong>
                <p>计划任务只需要访问监控地址，间隔多久跑一次取决于你在这里选择的时间。</p>
            </div>
        </article>
        <article>
            <i class="fa fa-play-circle"></i>
            <div>
                <strong>需要时手动执行</strong>
                <p>刚接入新站点或排查同步异常时，可以直接手动跑一轮，不必等计划任务。</p>
            </div>
        </article>
    </section>

    <section class="block admin-sync-panel">
        <div class="block-title admin-sync-panel__title">
            <div>
                <h3>自动同步设置</h3>
                <p>监控地址、全局频率、批量上限与每个站点的同步项都集中在这里配置。页面只保存规则，真正执行同步仍由监控地址触发。</p>
            </div>
            <div class="admin-sync-panel__actions">
                <button type="button" class="btn btn-default" id="syncCopyMonitor"><i class="fa fa-copy"></i> 复制监控地址</button>
                <button type="button" class="btn btn-primary" id="syncRunNow"><i class="fa fa-play"></i> 立即执行同步</button>
            </div>
        </div>

        <?php if (!empty($activeSummaries)) { ?>
        <div class="admin-sync-summary">
            <?php foreach ($activeSummaries as $summary) { ?>
            <article class="admin-sync-summary__item">
                <strong><?php echo q8_admin_sync_escape($summary['title']); ?> · 站点 ID <?php echo intval($summary['site_id']); ?></strong>
                <p><?php echo q8_admin_sync_escape($summary['url']); ?></p>
                <div class="admin-sync-summary__meta">
                    <span><?php echo q8_admin_sync_escape($summary['delete_rule']); ?></span>
                    <span><?php echo $summary['add_goods'] ? '允许新增商品' : '不新增商品'; ?></span>
                    <span><?php echo $summary['sync_sort'] ? '分类与商品排序同步' : '排序不同步'; ?></span>
                </div>
            </article>
            <?php } ?>
        </div>
        <?php } else { ?>
        <div class="admin-sync-empty">
            <i class="fa fa-info-circle"></i>
            <div>
                <strong>当前还没有启用中的自动同步站点</strong>
                <p>先在下面勾选站点并保存同步规则，再配合计划任务或手动执行同步即可生效。</p>
            </div>
        </div>
        <?php } ?>

        <div class="admin-sync-task-panel" id="syncTaskPanel">
            <div class="admin-sync-task-panel__header">
                <div>
                    <h4><i class="fa fa-tasks"></i> 后台任务记录</h4>
                    <p>手动同步会先创建任务记录，再由后台接口持续写入进度、异常原因和上游接口摘要。</p>
                </div>
                <button type="button" class="btn btn-default" id="syncRefreshTasks"><i class="fa fa-refresh"></i> 刷新进度</button>
            </div>
            <div class="admin-sync-task-list" id="syncTaskList">
                <?php if (!empty($recentSyncTasks)) { ?>
                <?php foreach ($recentSyncTasks as $taskRow) { ?>
                <article class="admin-sync-task is-<?php echo q8_admin_sync_escape($taskRow['status']); ?>" data-sync-task="<?php echo q8_admin_sync_escape($taskRow['task_key']); ?>">
                    <div class="admin-sync-task__main">
                        <strong><?php echo q8_admin_sync_escape($taskRow['status_text']); ?> · <?php echo q8_admin_sync_escape($taskRow['task_key']); ?></strong>
                        <p><?php echo q8_admin_sync_escape($taskRow['summary']); ?></p>
                        <?php if ($taskRow['error_reason'] !== '') { ?><small><?php echo q8_admin_sync_escape($taskRow['error_reason']); ?></small><?php } ?>
                        <?php if ($taskRow['upstream_summary'] !== '') { ?><small><?php echo q8_admin_sync_escape($taskRow['upstream_summary']); ?></small><?php } ?>
                    </div>
                    <div class="admin-sync-task__side">
                        <span><?php echo intval($taskRow['progress']); ?>%</span>
                        <time><?php echo q8_admin_sync_escape($taskRow['updated_at'] !== '' ? $taskRow['updated_at'] : $taskRow['addtime']); ?></time>
                    </div>
                </article>
                <?php } ?>
                <?php } else { ?>
                <div class="admin-sync-task-empty">暂无同步任务记录</div>
                <?php } ?>
            </div>
        </div>

        <?php echo q8_render_action('admin_sync_panel_before', $pageContext); ?>

        <form id="syncConfigForm" class="admin-sync-form" method="post" action="./cx-synchronization.php">
            <div class="admin-sync-overview">
                <article class="admin-sync-card">
                    <div class="admin-sync-card__header">
                        <h4><i class="fa fa-link"></i> 监控地址</h4>
                        <span class="admin-sync-card__badge"><?php echo $monitorKey !== '' ? '已加密钥' : '公开访问'; ?></span>
                    </div>
                    <label class="admin-sync-field">
                        <span class="admin-sync-field__label">计划任务访问地址</span>
                        <span class="admin-sync-field__control admin-sync-field__control--inline">
                            <input type="text" class="form-control" id="syncMonitorUrl" value="<?php echo q8_admin_sync_escape($monitorClientUrl); ?>" readonly>
                            <button type="button" class="btn btn-default" data-sync-copy="monitor-url"><i class="fa fa-copy"></i> 复制</button>
                        </span>
                    </label>
                    <p class="admin-sync-note">建议把这个地址加入服务器计划任务。是否需要密钥，由下面的“监控密钥”控制。</p>
                </article>

                <article class="admin-sync-card">
                    <div class="admin-sync-card__header">
                        <h4><i class="fa fa-clock-o"></i> 执行状态</h4>
                        <span class="admin-sync-card__badge admin-sync-card__badge--muted">最近运行</span>
                    </div>
                    <label class="admin-sync-field">
                        <span class="admin-sync-field__label">最近一次同步时间</span>
                        <span class="admin-sync-field__control">
                            <input type="text" class="form-control" value="<?php echo q8_admin_sync_escape($lastRunTime); ?>" readonly>
                        </span>
                    </label>
                    <div class="admin-sync-note">手动执行适合调试和排查问题；正式运行建议交给计划任务，避免人工忘记处理。</div>
                </article>

                <article class="admin-sync-card">
                    <div class="admin-sync-card__header">
                        <h4><i class="fa fa-terminal"></i> 计划任务示例</h4>
                        <span class="admin-sync-card__badge admin-sync-card__badge--accent" id="syncCronSchedule"><?php echo q8_admin_sync_escape($cronSchedule); ?></span>
                    </div>
                    <label class="admin-sync-field">
                        <span class="admin-sync-field__label">Cron 命令</span>
                        <span class="admin-sync-field__control admin-sync-field__control--stack">
                            <input type="text" class="form-control" id="syncCronCommand" value="<?php echo q8_admin_sync_escape($cronCommand); ?>" readonly>
                        </span>
                    </label>
                    <p class="admin-sync-note">如果你设置成 10 分钟，这里会自动显示 `*/10 * * * *` 这种计划任务写法，方便直接复制。</p>
                </article>

                <article class="admin-sync-card admin-sync-card--settings">
                    <div class="admin-sync-card__header">
                        <h4><i class="fa fa-sliders"></i> 全局节奏</h4>
                        <span class="admin-sync-card__badge admin-sync-card__badge--success">规则保存区</span>
                    </div>
                    <div class="admin-sync-settings-grid">
                        <label class="admin-sync-field">
                            <span class="admin-sync-field__label">监控密钥</span>
                            <span class="admin-sync-field__control admin-sync-field__control--inline">
                                <input type="text" class="form-control" name="monitor_key" id="syncMonitorKey" value="<?php echo q8_admin_sync_escape($monitorKey); ?>" placeholder="留空则不限制访问">
                                <button type="button" class="btn btn-default" id="syncGenerateKey"><i class="fa fa-random"></i> 生成</button>
                            </span>
                        </label>

                        <label class="admin-sync-field">
                            <span class="admin-sync-field__label">同步间隔</span>
                            <span class="admin-sync-field__control">
                                <select name="sync_interval" id="syncInterval" class="form-control" required>
                                    <?php echo q8_admin_sync_render_select_options($intervalOptions, $globalConfig['sync_interval']); ?>
                                </select>
                            </span>
                        </label>

                        <label class="admin-sync-field">
                            <span class="admin-sync-field__label">单次同步数量</span>
                            <span class="admin-sync-field__control">
                                <select name="sync_limit" id="syncLimit" class="form-control" required>
                                    <?php echo q8_admin_sync_render_select_options($limitOptions, $globalConfig['sync_limit']); ?>
                                </select>
                            </span>
                        </label>
                    </div>
                    <p class="admin-sync-note">正式运行建议间隔 5 分钟以上。数量越大，同步越快，但越容易受接口耗时和服务器性能影响。</p>
                </article>
            </div>

            <div class="admin-sync-toolbar">
                <label class="admin-sync-search">
                    <i class="fa fa-search"></i>
                    <input type="text" class="form-control" id="syncSiteSearch" placeholder="搜索站点标题、域名、备注或 ID">
                </label>
                <div class="admin-sync-toolbar__actions">
                    <span class="admin-sync-toolbar__count">已选 <b id="selected_count"><?php echo intval($syncStats['enabled_sites']); ?></b> 个站点</span>
                    <label class="admin-sync-check admin-sync-check--master">
                        <input type="checkbox" id="syncSelectAll">
                        <span>全选当前站点</span>
                    </label>
                    <a href="./shequlist.php" class="btn btn-default"><i class="fa fa-plus"></i> 添加站点</a>
                </div>
            </div>

            <?php echo q8_render_action('admin_sync_sites_before', $pageContext); ?>

            <?php if (empty($shequRows)) { ?>
            <div class="admin-sync-empty admin-sync-empty--panel">
                <i class="fa fa-plug"></i>
                <div>
                    <strong>暂无可配置的供货站点</strong>
                    <p>请先到“对接站点”添加供货站点，再回到这里设置自动同步规则。</p>
                </div>
            </div>
            <?php } else { ?>
            <div class="admin-sync-site-grid" id="syncSiteGrid">
                <?php foreach ($shequRows as $siteRow) {
                    $siteId = intval($siteRow['id']);
                    $config = isset($enabledConfigs[$siteId]) ? q8_admin_sync_merge_config($enabledConfigs[$siteId]) : q8_admin_sync_default_config();
                    $isEnabled = isset($enabledConfigs[$siteId]);
                    $displayUrl = $siteRow['display_url'] !== '' ? $siteRow['display_url'] : $siteRow['url'];
                    $searchText = strtolower(trim($siteRow['plugin_title'] . ' ' . $displayUrl . ' ' . $siteRow['remark'] . ' ' . $siteId));
                ?>
                <article class="admin-sync-site<?php echo $isEnabled ? ' is-active' : ''; ?>" data-sync-site data-sync-site-id="<?php echo $siteId; ?>" data-sync-search="<?php echo q8_admin_sync_escape($searchText); ?>">
                    <div class="admin-sync-site__header">
                        <div class="admin-sync-site__main">
                            <label class="admin-sync-check admin-sync-check--site">
                                <input type="checkbox" name="shequ_ids[]" value="<?php echo $siteId; ?>"<?php echo $isEnabled ? ' checked' : ''; ?>>
                                <span></span>
                            </label>
                            <div class="admin-sync-site__identity">
                                <strong><?php echo q8_admin_sync_escape($siteRow['plugin_title']); ?></strong>
                                <p><?php echo q8_admin_sync_escape($displayUrl); ?></p>
                                <div class="admin-sync-site__meta">
                                    <span>站点 ID <?php echo $siteId; ?></span>
                                    <span>类型 <?php echo q8_admin_sync_escape($siteRow['type']); ?></span>
                                    <?php if ($siteRow['remark'] !== '') { ?><span><?php echo q8_admin_sync_escape($siteRow['remark']); ?></span><?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="admin-sync-site__actions">
                            <span class="admin-sync-status<?php echo $isEnabled ? ' admin-sync-status--success' : ''; ?>"><?php echo $isEnabled ? '已启用' : '未启用'; ?></span>
                            <button type="button" class="btn btn-default admin-sync-site__toggle" data-sync-site-toggle="<?php echo $siteId; ?>">
                                <i class="fa <?php echo $isEnabled ? 'fa-angle-up' : 'fa-angle-down'; ?>"></i>
                                <span><?php echo $isEnabled ? '收起设置' : '展开设置'; ?></span>
                            </button>
                        </div>
                    </div>

                    <div class="admin-sync-site__config" id="config_<?php echo $siteId; ?>"<?php echo $isEnabled ? '' : ' hidden'; ?>>
                        <div class="admin-sync-site__grid">
                            <section class="admin-sync-section">
                                <div class="admin-sync-section__title">基础设置</div>
                                <div class="admin-sync-section__fields">
                                    <?php foreach ($baseOptions as $key => $meta) { ?>
                                    <label class="admin-sync-field">
                                        <span class="admin-sync-field__label"><?php echo q8_admin_sync_escape($meta['label']); ?></span>
                                        <span class="admin-sync-field__control">
                                            <select name="<?php echo q8_admin_sync_escape($key . '_' . $siteId); ?>" class="form-control">
                                                <?php echo q8_admin_sync_render_select_options($meta['options'], $config[$key]); ?>
                                            </select>
                                        </span>
                                        <span class="admin-sync-field__hint"><?php echo q8_admin_sync_escape($meta['help']); ?></span>
                                    </label>
                                    <?php } ?>

                                    <label class="admin-sync-field">
                                        <span class="admin-sync-field__label">新增商品加价模板</span>
                                        <span class="admin-sync-field__control">
                                            <select name="markup_template_<?php echo $siteId; ?>" class="form-control">
                                                <?php echo q8_admin_sync_render_price_options($priceRules, $config['markup_template']); ?>
                                            </select>
                                        </span>
                                        <span class="admin-sync-field__hint">只影响同步过程中新增到本站的商品，不会回改已有商品价格。</span>
                                    </label>
                                </div>
                            </section>

                            <section class="admin-sync-section">
                                <div class="admin-sync-section__title">同步项</div>
                                <div class="admin-sync-option-grid">
                                    <?php foreach ($syncOptionGroups as $option) { ?>
                                    <label class="admin-sync-option">
                                        <input type="checkbox" name="<?php echo q8_admin_sync_escape($option['key'] . '_' . $siteId); ?>" value="1"<?php echo intval($config[$option['key']]) === 1 ? ' checked' : ''; ?>>
                                        <span class="admin-sync-option__content">
                                            <strong><?php echo q8_admin_sync_escape($option['label']); ?></strong>
                                            <small><?php echo q8_admin_sync_escape($option['desc']); ?></small>
                                        </span>
                                    </label>
                                    <?php } ?>
                                </div>
                            </section>

                            <section class="admin-sync-section">
                                <div class="admin-sync-section__title">新增策略</div>
                                <div class="admin-sync-option-grid admin-sync-option-grid--compact">
                                    <?php foreach ($addOptionGroups as $option) { ?>
                                    <label class="admin-sync-option">
                                        <input type="checkbox" name="<?php echo q8_admin_sync_escape($option['key'] . '_' . $siteId); ?>" value="1"<?php echo intval($config[$option['key']]) === 1 ? ' checked' : ''; ?>>
                                        <span class="admin-sync-option__content">
                                            <strong><?php echo q8_admin_sync_escape($option['label']); ?></strong>
                                            <small><?php echo q8_admin_sync_escape($option['desc']); ?></small>
                                        </span>
                                    </label>
                                    <?php } ?>
                                </div>
                            </section>
                        </div>

                        <div class="admin-sync-site__footer">
                            <span><?php echo q8_admin_sync_escape(q8_admin_sync_delete_rule_text($config['delete_rule'])); ?></span>
                            <span><?php echo intval($config['auto_update']) === 1 ? '自动更新已开启' : '自动更新已关闭'; ?></span>
                            <span><?php echo intval($config['add_goods']) === 1 ? '允许新增商品' : '不新增商品'; ?></span>
                        </div>
                    </div>
                </article>
                <?php } ?>
            </div>
            <div class="admin-sync-empty-search" id="syncSearchEmpty">没有匹配的站点，请换个关键词再试</div>
            <?php } ?>

            <?php echo q8_render_action('admin_sync_sites_after', $pageContext); ?>

            <div class="admin-sync-submitbar">
                <p>保存后只更新同步规则，不会立刻改动商品；真正执行同步需要访问监控地址，或者点上方“立即执行同步”。</p>
                <button type="submit" class="btn btn-primary admin-sync-submit" id="syncConfigSubmit"><i class="fa fa-check"></i> 保存同步设置</button>
            </div>
        </form>

        <?php echo q8_render_action('admin_sync_panel_after', $pageContext); ?>
    </section>

    <?php echo q8_render_action('admin_sync_page_after', $pageContext); ?>
</div>

<script>
window.adminSyncConfig = <?php echo q8_admin_sync_json(array(
    'monitorUrl' => $monitorUrl,
    'monitorBaseUrl' => $monitorBaseUrl,
    'monitorPath' => $monitorBasePath,
    'pageUrl' => './cx-synchronization.php'
)); ?>;
</script>
<script src="./assets/js/cx-synchronization.js?v=<?php echo urlencode($syncAssetVersion); ?>"></script>
</body>
</html>
