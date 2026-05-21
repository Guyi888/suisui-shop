<?php
include "../includes/common.php";

$title = "在线更新";
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

if (!function_exists('q8_update_json')) {
    function q8_update_json($code, $msg, $data = array())
    {
        @header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(array('code' => $code, 'msg' => $msg), $data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('q8_update_workspace')) {
    function q8_update_workspace()
    {
        $dir = ROOT . 'cache/suisui_update/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }
}

if (!function_exists('q8_update_state_file')) {
    function q8_update_state_file()
    {
        return q8_update_workspace() . 'state_' . md5(session_id() . SYS_KEY) . '.json';
    }
}

if (!function_exists('q8_update_read_state')) {
    function q8_update_read_state()
    {
        $file = q8_update_state_file();
        if (!is_file($file)) return array();
        $state = json_decode((string)@file_get_contents($file), true);
        return is_array($state) ? $state : array();
    }
}

if (!function_exists('q8_update_save_state')) {
    function q8_update_save_state($state)
    {
        $state['updated_at'] = date('Y-m-d H:i:s');
        @file_put_contents(q8_update_state_file(), json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}

if (!function_exists('q8_update_local_version')) {
    function q8_update_local_version()
    {
        foreach (array(ROOT . 'CHANGELOG.md', ROOT . 'admin/changelog.php', ROOT . 'README.md') as $file) {
            if (!is_file($file)) continue;
            $content = (string)@file_get_contents($file);
            if (preg_match('/v\d+\.\d+\.\d+\.\d+/', $content, $m)) {
                return $m[0];
            }
        }
        return defined('VERSION') ? VERSION : 'unknown';
    }
}

if (!function_exists('q8_update_http_get')) {
    function q8_update_http_get($url, $timeout = 20)
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_USERAGENT, 'SuisuiShopUpdater/' . (defined('VERSION') ? VERSION : '1.0'));
            $body = curl_exec($ch);
            $err = curl_error($ch);
            $code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
            curl_close($ch);
            if ($body !== false && $code >= 200 && $code < 300) {
                return $body;
            }
            throw new Exception($err ? $err : ('HTTP ' . $code));
        }
        $context = stream_context_create(array('http' => array('timeout' => $timeout, 'header' => "User-Agent: SuisuiShopUpdater\r\n")));
        $body = @file_get_contents($url, false, $context);
        if ($body === false) {
            throw new Exception('无法连接远程更新源');
        }
        return $body;
    }
}

if (!function_exists('q8_update_remote_manifest')) {
    function q8_update_remote_manifest()
    {
        $manifestUrl = 'https://github.com/Guyi888/suisui-shop/releases/latest/download/update.json';
        try {
            $manifest = json_decode(q8_update_http_get($manifestUrl), true);
            if (is_array($manifest) && !empty($manifest['version'])) {
                $manifest['manifest_url'] = $manifestUrl;
                return $manifest;
            }
        } catch (Exception $e) {
            $manifestError = $e->getMessage();
        }

        $api = json_decode(q8_update_http_get('https://api.github.com/repos/Guyi888/suisui-shop/releases/latest'), true);
        if (!is_array($api) || empty($api['tag_name'])) {
            throw new Exception('GitHub Release 信息读取失败');
        }
        return array(
            'version' => $api['tag_name'],
            'name' => isset($api['name']) ? $api['name'] : $api['tag_name'],
            'body' => isset($api['body']) ? $api['body'] : '',
            'html_url' => isset($api['html_url']) ? $api['html_url'] : 'https://github.com/Guyi888/suisui-shop/releases',
            'package' => !empty($api['zipball_url']) ? $api['zipball_url'] : ('https://api.github.com/repos/Guyi888/suisui-shop/zipball/' . rawurlencode($api['tag_name'])),
            'sha256' => '',
            'manifest_error' => isset($manifestError) ? $manifestError : '',
        );
    }
}

if (!function_exists('q8_update_compare_version')) {
    function q8_update_compare_version($remote, $local)
    {
        $remote = preg_replace('/[^0-9.]/', '', (string)$remote);
        $local = preg_replace('/[^0-9.]/', '', (string)$local);
        if ($remote === '' || $local === '') return 1;
        return version_compare($remote, $local);
    }
}

if (!function_exists('q8_update_precheck')) {
    function q8_update_precheck()
    {
        $checks = array();
        $critical = false;
        $items = array(
            array('PHP 版本', version_compare(PHP_VERSION, '7.4.0', '>='), PHP_VERSION, true, '当前服务器 PHP 版本低于在线更新要求。', '请在宝塔面板把站点 PHP 版本切换到 7.4 或更高版本，然后重试。'),
            array('ZipArchive 扩展', class_exists('ZipArchive'), class_exists('ZipArchive') ? '可用' : '不可用', true, '服务器没有启用 ZipArchive，程序无法解压更新包。', '请在 PHP 扩展管理中安装或启用 zip 扩展，然后重启 PHP 服务。'),
            array('下载能力', function_exists('curl_init') || ini_get('allow_url_fopen'), (function_exists('curl_init') ? 'cURL' : (ini_get('allow_url_fopen') ? 'file_get_contents' : '不可用')), true, '服务器当前无法通过 PHP 下载远程更新包。', '请启用 PHP cURL 扩展，或开启 allow_url_fopen，并确认服务器能访问 GitHub。'),
            array('站点目录写入', is_writable(ROOT), ROOT, true, 'PHP 运行用户没有站点根目录写入权限，无法创建临时目录或覆盖程序文件。', '请把站点目录权限调整为 PHP 运行用户可写；宝塔常见做法是将站点目录所有者设为 www:www。'),
            array('临时目录写入', is_writable(q8_update_workspace()), q8_update_workspace(), true, '更新临时目录无法写入，更新包、解压文件和备份文件无法保存。', '请创建 cache 目录并给 PHP 运行用户写入权限，或修复站点根目录权限后重新检查。'),
            array('配置文件保护', is_file(ROOT . 'config.php'), 'config.php', true, '没有检测到 config.php，程序无法确认当前站点运行配置。', '请确认站点根目录存在 config.php；不要用示例配置文件替代正式配置。'),
            array('安装锁保护', is_file(ROOT . 'install/install.lock'), 'install/install.lock', false, '没有检测到 install/install.lock，站点可能存在重新安装风险。', '请确认站点已安装完成，并在 install 目录下保留 install.lock 文件。'),
        );
        foreach ($items as $item) {
            $ok = (bool)$item[1];
            if (!$ok && $item[3]) $critical = true;
            $checks[] = array(
                'name' => $item[0],
                'ok' => $ok,
                'detail' => $item[2],
                'critical' => (bool)$item[3],
                'reason' => $item[4],
                'fix' => $item[5],
            );
        }
        return array('checks' => $checks, 'critical' => $critical);
    }
}

if (!function_exists('q8_update_failed_check_names')) {
    function q8_update_failed_check_names($checks)
    {
        $names = array();
        foreach ($checks as $check) {
            if (empty($check['ok']) && !empty($check['critical'])) {
                $names[] = $check['name'];
            }
        }
        return $names;
    }
}

if (!function_exists('q8_update_excludes')) {
    function q8_update_excludes()
    {
        return array(
            'config.php',
            'install/install.lock',
            '.git',
            'cache',
            'logs',
            'runtime',
            'upload',
            'uploads',
            'assets/upload',
            'assets/uploads',
            '_codex_backup',
            'bak_sync',
        );
    }
}

if (!function_exists('q8_update_should_skip')) {
    function q8_update_should_skip($relative)
    {
        $relative = trim(str_replace('\\', '/', $relative), '/');
        if ($relative === '') return true;
        foreach (q8_update_excludes() as $skip) {
            $skip = trim($skip, '/');
            if ($relative === $skip || strpos($relative, $skip . '/') === 0) return true;
        }
        return (bool)preg_match('/(^|\/)(.+\.bak|.+\.tmp|.+~)$/i', $relative);
    }
}

if (!function_exists('q8_update_zip_dir')) {
    function q8_update_zip_dir($source, $zipFile)
    {
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('备份压缩包创建失败');
        }
        $source = rtrim(str_replace('\\', '/', realpath($source)), '/') . '/';
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile()) continue;
            $path = str_replace('\\', '/', $file->getPathname());
            $relative = substr($path, strlen($source));
            if (strpos($relative, 'cache/suisui_update/') === 0 || strpos($relative, '.git/') === 0) continue;
            $zip->addFile($path, $relative);
        }
        $zip->close();
    }
}

if (!function_exists('q8_update_backup_database')) {
    function q8_update_backup_database($sqlFile)
    {
        global $dbconfig;
        if (empty($dbconfig['host']) || empty($dbconfig['user']) || empty($dbconfig['dbname'])) {
            throw new Exception('数据库配置不完整');
        }
        $pdo = new PDO("mysql:host={$dbconfig['host']};dbname={$dbconfig['dbname']};port={$dbconfig['port']};charset=utf8mb4", $dbconfig['user'], $dbconfig['pwd']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $fp = fopen($sqlFile, 'wb');
        if (!$fp) throw new Exception('数据库备份文件创建失败');
        fwrite($fp, "-- Suisui Shop database backup\n-- " . date('Y-m-d H:i:s') . "\n\nSET NAMES utf8mb4;\n\n");
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $create = $pdo->query('SHOW CREATE TABLE `' . str_replace('`', '``', $table) . '`')->fetch(PDO::FETCH_ASSOC);
            fwrite($fp, "DROP TABLE IF EXISTS `" . str_replace('`', '``', $table) . "`;\n" . array_values($create)[1] . ";\n\n");
            $stmt = $pdo->query('SELECT * FROM `' . str_replace('`', '``', $table) . '`');
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cols = array_map(function ($col) { return '`' . str_replace('`', '``', $col) . '`'; }, array_keys($row));
                $vals = array_map(function ($val) use ($pdo) { return $val === null ? 'NULL' : $pdo->quote($val); }, array_values($row));
                fwrite($fp, 'INSERT INTO `' . str_replace('`', '``', $table) . '` (' . implode(',', $cols) . ') VALUES (' . implode(',', $vals) . ");\n");
            }
            fwrite($fp, "\n");
        }
        fclose($fp);
    }
}

if (!function_exists('q8_update_package_root')) {
    function q8_update_package_root($extractDir)
    {
        $items = array_values(array_filter(scandir($extractDir), function ($item) {
            return $item !== '.' && $item !== '..' && $item !== '__MACOSX';
        }));
        if (count($items) === 1 && is_dir($extractDir . '/' . $items[0])) {
            $candidate = $extractDir . '/' . $items[0];
            if (is_dir($candidate . '/admin') || is_file($candidate . '/includes/common.php')) {
                return $candidate;
            }
        }
        return $extractDir;
    }
}

if (!function_exists('q8_update_copy_package')) {
    function q8_update_copy_package($source, $target)
    {
        $source = rtrim(str_replace('\\', '/', realpath($source)), '/') . '/';
        $target = rtrim(str_replace('\\', '/', realpath($target)), '/') . '/';
        $count = 0;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $file) {
            $path = str_replace('\\', '/', $file->getPathname());
            $relative = substr($path, strlen($source));
            if (q8_update_should_skip($relative)) continue;
            $dest = $target . $relative;
            if ($file->isDir()) {
                if (!is_dir($dest)) @mkdir($dest, 0755, true);
                continue;
            }
            if (!is_dir(dirname($dest))) @mkdir(dirname($dest), 0755, true);
            if (!@copy($path, $dest)) {
                throw new Exception('文件覆盖失败：' . $relative);
            }
            $count++;
        }
        return $count;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['act']) && $_POST['act'] === 'online_update_step') {
    if (!q8_admin_check_csrf()) {
        q8_update_json(0, '表单已失效，请刷新页面后重试');
    }
    $step = isset($_POST['step']) ? trim($_POST['step']) : '';
    $state = q8_update_read_state();
    try {
        if ($step === 'precheck') {
            $precheck = q8_update_precheck();
            $state = array('started_at' => date('Y-m-d H:i:s'), 'local_version' => q8_update_local_version(), 'cache_version' => defined('VERSION') ? VERSION : '');
            $state['precheck'] = $precheck;
            q8_update_save_state($state);
            $failedNames = q8_update_failed_check_names($precheck['checks']);
            $message = $precheck['critical'] ? ('环境预检未通过：' . implode('、', $failedNames)) : '环境预检通过';
            q8_update_json($precheck['critical'] ? 0 : 1, $message, array('checks' => $precheck['checks'], 'state' => $state));
        }
        if ($step === 'remote') {
            $manifest = q8_update_remote_manifest();
            $local = isset($state['local_version']) ? $state['local_version'] : q8_update_local_version();
            $state['remote'] = $manifest;
            $state['has_update'] = q8_update_compare_version($manifest['version'], $local) > 0;
            q8_update_save_state($state);
            q8_update_json(1, $state['has_update'] ? '发现可更新版本' : '当前已经是最新版本', array('remote' => $manifest, 'has_update' => $state['has_update'], 'local_version' => $local));
        }
        if ($step === 'download') {
            if (empty($state['remote']['package'])) {
                if (!empty($state['remote']['version'])) {
                    $state['remote']['package'] = 'https://api.github.com/repos/Guyi888/suisui-shop/zipball/' . rawurlencode($state['remote']['version']);
                } else {
                    throw new Exception('最新 Release 未配置更新包地址，也无法识别远程版本');
                }
            }
            $packageDir = q8_update_workspace() . 'packages/';
            if (!is_dir($packageDir)) @mkdir($packageDir, 0755, true);
            $packageFile = $packageDir . 'suisui-shop-' . preg_replace('/[^a-zA-Z0-9_.-]/', '', $state['remote']['version']) . '.zip';
            $body = q8_update_http_get($state['remote']['package'], 180);
            if (@file_put_contents($packageFile, $body) === false) {
                throw new Exception('更新包保存失败');
            }
            $state['package_file'] = $packageFile;
            $state['package_size'] = filesize($packageFile);
            q8_update_save_state($state);
            q8_update_json(1, '更新包下载完成', array('size' => $state['package_size']));
        }
        if ($step === 'verify') {
            if (empty($state['package_file']) || !is_file($state['package_file'])) throw new Exception('更新包不存在');
            $sha256 = hash_file('sha256', $state['package_file']);
            $expected = isset($state['remote']['sha256']) ? strtolower(trim($state['remote']['sha256'])) : '';
            if ($expected !== '' && !hash_equals($expected, strtolower($sha256))) {
                throw new Exception('更新包校验失败');
            }
            $state['package_sha256'] = $sha256;
            q8_update_save_state($state);
            q8_update_json(1, $expected === '' ? '更新包已校验，Release 未提供 SHA256' : '更新包校验通过', array('sha256' => $sha256, 'warning' => $expected === ''));
        }
        if ($step === 'backup') {
            $backupDir = q8_update_workspace() . 'backups/' . date('Ymd_His') . '/';
            if (!is_dir($backupDir)) @mkdir($backupDir, 0755, true);
            $codeZip = $backupDir . 'site-files.zip';
            $dbSql = $backupDir . 'database.sql';
            q8_update_zip_dir(ROOT, $codeZip);
            q8_update_backup_database($dbSql);
            $state['backup_dir'] = $backupDir;
            $state['backup_code'] = $codeZip;
            $state['backup_db'] = $dbSql;
            q8_update_save_state($state);
            q8_update_json(1, '网站文件和数据库已备份', array('backup_dir' => $backupDir));
        }
        if ($step === 'extract') {
            if (empty($state['package_file']) || !is_file($state['package_file'])) throw new Exception('更新包不存在');
            $extractDir = q8_update_workspace() . 'extract_' . date('Ymd_His');
            if (!is_dir($extractDir)) @mkdir($extractDir, 0755, true);
            $zip = new ZipArchive();
            if ($zip->open($state['package_file']) !== true) throw new Exception('更新包无法打开');
            $zip->extractTo($extractDir);
            $zip->close();
            $state['extract_dir'] = $extractDir;
            $state['package_root'] = q8_update_package_root($extractDir);
            q8_update_save_state($state);
            q8_update_json(1, '更新包解压完成');
        }
        if ($step === 'apply') {
            if (empty($state['package_root']) || !is_dir($state['package_root'])) throw new Exception('解压目录不存在');
            @file_put_contents(ROOT . 'cache/update.lock', date('Y-m-d H:i:s'));
            $count = q8_update_copy_package($state['package_root'], ROOT);
            $state['applied_files'] = $count;
            q8_update_save_state($state);
            q8_update_json(1, '程序文件覆盖完成', array('count' => $count));
        }
        if ($step === 'dryrun') {
            if (empty($state['package_root']) || !is_dir($state['package_root'])) throw new Exception('解压目录不存在');
            $source = rtrim(str_replace('\\', '/', realpath($state['package_root'])), '/') . '/';
            $scan = array('copy' => 0, 'skip' => 0);
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($iterator as $file) {
                if (!$file->isFile()) continue;
                $path = str_replace('\\', '/', $file->getPathname());
                $relative = substr($path, strlen($source));
                if (q8_update_should_skip($relative)) {
                    $scan['skip']++;
                } else {
                    $scan['copy']++;
                }
            }
            $state['dryrun'] = $scan;
            q8_update_save_state($state);
            q8_update_json(1, '流程演练完成，未覆盖任何文件', array('scan' => $scan));
        }
        if ($step === 'migrate') {
            $version = isset($state['remote']['version']) ? preg_replace('/[^a-zA-Z0-9_.-]/', '', $state['remote']['version']) : '';
            $migrationFiles = array(
                isset($state['package_root']) ? $state['package_root'] . '/install/update/' . $version . '.php' : '',
                ROOT . 'install/update/' . $version . '.php',
            );
            $ran = false;
            foreach ($migrationFiles as $file) {
                if ($file && is_file($file)) {
                    include $file;
                    $ran = true;
                    break;
                }
            }
            $state['migration_ran'] = $ran;
            q8_update_save_state($state);
            q8_update_json(1, $ran ? '数据库升级脚本执行完成' : '没有发现数据库升级脚本，已跳过');
        }
        if ($step === 'cleanup') {
            global $CACHE;
            if (isset($CACHE) && is_object($CACHE) && method_exists($CACHE, 'clear')) {
                @$CACHE->clear();
            }
            @unlink(ROOT . 'cache/update.lock');
            $state['cleaned_at'] = date('Y-m-d H:i:s');
            q8_update_save_state($state);
            q8_update_json(1, '缓存已清理');
        }
        if ($step === 'selfcheck') {
            $ok = is_file(ROOT . 'includes/common.php') && is_file(ROOT . 'admin/update.php') && is_dir(ROOT . 'assets');
            $state['finished_at'] = date('Y-m-d H:i:s');
            $state['final_cache_version'] = defined('VERSION') ? VERSION : '';
            q8_update_save_state($state);
            q8_update_json($ok ? 1 : 0, $ok ? '更新完成，自检通过' : '更新完成但自检发现异常', array('state' => $state));
        }
        q8_update_json(0, '未知更新步骤');
    } catch (Exception $e) {
        $state['failed_step'] = $step;
        $state['failed_message'] = $e->getMessage();
        q8_update_save_state($state);
        @unlink(ROOT . 'cache/update.lock');
        q8_update_json(0, $e->getMessage(), array('state' => $state));
    }
}

include "./head.php";
$githubUrl = defined('PROJECT_REPOSITORY_URL') ? PROJECT_REPOSITORY_URL : 'https://github.com/Guyi888/suisui-shop';
$localVersion = q8_update_local_version();
?>
<div class="col-xs-12 admin-update-page">
    <section class="admin-update-hero">
        <div>
            <p class="admin-update-hero__eyebrow">UPDATE CENTER</p>
            <h2><i class="fa fa-cloud-download"></i> 在线更新中心</h2>
            <p>一键更新会自动完成环境检查、版本检查、下载校验、文件和数据库备份、程序覆盖、数据库升级、缓存清理和最终自检。</p>
        </div>
        <div class="admin-update-hero__actions">
            <a href="./changelog.php" class="admin-update-chip"><i class="fa fa-list-alt"></i> 更新日志</a>
            <a href="<?php echo htmlspecialchars($githubUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noreferrer" class="admin-update-chip"><i class="fa fa-github"></i> GitHub</a>
        </div>
    </section>

    <section class="admin-update-grid">
        <div class="admin-update-stat">
            <span class="admin-update-stat__icon admin-update-stat__icon--primary"><i class="fa fa-code-fork"></i></span>
            <div><b>当前版本</b><strong id="localVersion"><?php echo htmlspecialchars($localVersion, ENT_QUOTES, 'UTF-8'); ?></strong><small>本地程序版本</small></div>
        </div>
        <div class="admin-update-stat">
            <span class="admin-update-stat__icon admin-update-stat__icon--success"><i class="fa fa-refresh"></i></span>
            <div><b>最新版本</b><strong id="remoteVersion">待检查</strong><small id="remoteHint">读取 GitHub Release</small></div>
        </div>
        <div class="admin-update-stat">
            <span class="admin-update-stat__icon admin-update-stat__icon--warning"><i class="fa fa-bolt"></i></span>
            <div><b>缓存版本</b><strong><?php echo defined('VERSION') ? VERSION : 'unknown'; ?></strong><small>静态资源缓存号</small></div>
        </div>
        <div class="admin-update-stat">
            <span class="admin-update-stat__icon admin-update-stat__icon--accent"><i class="fa fa-shield"></i></span>
            <div><b>更新状态</b><strong id="updateStatus">未开始</strong><small id="updateStatusHint">点击按钮开始检查</small></div>
        </div>
    </section>

    <div class="admin-update-layout">
        <section class="block admin-update-panel">
            <div class="block-title">
                <h3 class="panel-title"><i class="fa fa-tasks"></i> 一键更新进度</h3>
            </div>
            <div class="panel-body">
                <div class="admin-update-progress">
                    <div class="admin-update-progress__bar" id="updateProgressBar"></div>
                </div>
                <div class="admin-update-actions">
                    <button type="button" class="btn btn-primary" id="startOnlineUpdate"><i class="fa fa-cloud-download"></i> 立即更新</button>
                    <button type="button" class="btn btn-info" id="dryRunOnlineUpdate"><i class="fa fa-play-circle"></i> 流程演练</button>
                    <button type="button" class="btn btn-default" id="checkOnlineUpdate"><i class="fa fa-search"></i> 仅检查更新</button>
                    <a class="btn btn-default" href="./changelog.php"><i class="fa fa-list-alt"></i> 查看日志</a>
                </div>
                <ol class="admin-update-steps" id="updateSteps">
                    <li data-step="precheck"><span><i class="fa fa-circle-o"></i></span><div><b>环境预检</b><p>检查 PHP、ZipArchive、下载能力、目录权限和运行保护文件。</p></div></li>
                    <li data-step="remote"><span><i class="fa fa-circle-o"></i></span><div><b>版本检查</b><p>读取 GitHub Release / update.json，判断是否有新版。</p></div></li>
                    <li data-step="download"><span><i class="fa fa-circle-o"></i></span><div><b>下载更新包</b><p>下载正式 Release 中配置的更新包。</p></div></li>
                    <li data-step="verify"><span><i class="fa fa-circle-o"></i></span><div><b>完整性校验</b><p>校验 SHA256，防止更新包损坏或被替换。</p></div></li>
                    <li data-step="backup"><span><i class="fa fa-circle-o"></i></span><div><b>自动备份</b><p>备份当前网站文件和数据库，保留回滚依据。</p></div></li>
                    <li data-step="extract"><span><i class="fa fa-circle-o"></i></span><div><b>解压更新包</b><p>解压到临时目录，不直接在站点目录内展开。</p></div></li>
                    <li data-step="dryrun"><span><i class="fa fa-circle-o"></i></span><div><b>流程演练</b><p>扫描将要覆盖和跳过的文件，不修改站点程序。</p></div></li>
                    <li data-step="apply"><span><i class="fa fa-circle-o"></i></span><div><b>覆盖程序文件</b><p>自动跳过 config.php、上传目录、备份目录和 install.lock。</p></div></li>
                    <li data-step="migrate"><span><i class="fa fa-circle-o"></i></span><div><b>数据库升级</b><p>如果更新包包含迁移脚本，则自动执行。</p></div></li>
                    <li data-step="cleanup"><span><i class="fa fa-circle-o"></i></span><div><b>清理缓存</b><p>清理配置缓存和更新锁。</p></div></li>
                    <li data-step="selfcheck"><span><i class="fa fa-circle-o"></i></span><div><b>最终自检</b><p>检查关键文件和版本状态，返回成功或失败原因。</p></div></li>
                </ol>
            </div>
        </section>

        <aside class="admin-update-side">
            <section class="block admin-update-panel">
                <div class="block-title">
                    <h3 class="panel-title"><i class="fa fa-check-square-o"></i> 环境检查</h3>
                </div>
                <div class="panel-body">
                    <div class="admin-update-checks" id="updateChecks">
                        <div class="admin-update-empty"><i class="fa fa-info-circle"></i> 尚未开始检查</div>
                    </div>
                </div>
            </section>
            <section class="block admin-update-panel">
                <div class="block-title">
                    <h3 class="panel-title"><i class="fa fa-file-text-o"></i> 更新说明</h3>
                </div>
                <div class="panel-body">
                    <div class="admin-update-release" id="releaseNotes">检查远程版本后显示最新 Release 说明。</div>
                </div>
            </section>
        </aside>
    </div>
</div>

<script>
window.adminUpdateConfig = <?php echo json_encode(array(
    'csrfToken' => q8_admin_csrf_token(),
    'endpoint' => './update.php',
), JSON_UNESCAPED_SLASHES); ?>;
</script>
<script src="./assets/js/admin-update.js?v=<?php echo urlencode($adminAssetVersion); ?>"></script>
</body>
</html>
