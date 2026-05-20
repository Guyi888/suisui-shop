<?php
include("../includes/common.php");
$title = '&#25968;&#25454;&#36801;&#31227;&#20013;&#24515;';
include './head.php';
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

class MigrationPreflight
{
    private $sourceDb;
    private $targetDb;
    private $sourceConfig;
    private $targetConfig;
    private $coreBaseTables = array(
        'config', 'class', 'tools', 'orders', 'faka', 'pay', 'site', 'shequ',
        'points', 'kms', 'cart', 'price', 'supplier', 'suppoints', 'suptixian',
        'tixian', 'workorder', 'message', 'article', 'toollogs'
    );
    private $highValueColumns = array(
        'tid', 'cid', 'zid', 'sid', 'money', 'price', 'cost', 'sup_price',
        'upstream_cost', 'min_price', 'manual_price_lock', 'goods_id',
        'shequ', 'is_curl', 'desc', 'input', 'address', 'km', 'pw', 'kid',
        'orderid', 'trade_no', 'djorder', 'kminfo', 'result', 'status',
        'template', 'template_m', 'rmb', 'point'
    );

    public function __construct($sourceConfig, $targetConfig)
    {
        $this->sourceConfig = $sourceConfig;
        $this->targetConfig = $targetConfig;
    }

    public function run()
    {
        $this->sourceDb = $this->connect($this->sourceConfig);
        $this->targetDb = $this->connect($this->targetConfig);

        $sourceTables = $this->getTables($this->sourceDb);
        $targetTables = $this->getTables($this->targetDb);
        $sourceProfile = $this->profileDatabase($this->sourceDb, $sourceTables);
        $targetProfile = $this->profileDatabase($this->targetDb, $targetTables);
        $tableDiff = $this->compareTables($sourceTables, $targetTables, $sourceProfile, $targetProfile);
        $columnDiff = $this->compareColumns($sourceTables, $targetTables);
        $businessChecks = $this->businessChecks($sourceTables, $targetTables, $sourceProfile, $targetProfile, $columnDiff);
        $risk = $this->riskSummary($tableDiff, $columnDiff, $businessChecks);

        return array(
            'generated_at' => date('Y-m-d H:i:s'),
            'source_profile' => $sourceProfile,
            'target_profile' => $targetProfile,
            'table_diff' => $tableDiff,
            'column_diff' => $columnDiff,
            'business_checks' => $businessChecks,
            'risk' => $risk,
        );
    }

    private function connect($config)
    {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        return new PDO($dsn, $config['username'], $config['password'], array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        ));
    }

    private function getTables($pdo)
    {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = array();
        while ($row = $stmt->fetch()) {
            $tables[] = array_values($row)[0];
        }
        sort($tables);
        return $tables;
    }

    private function getColumns($pdo, $table)
    {
        $columns = array();
        $stmt = $pdo->query("SHOW COLUMNS FROM `" . str_replace('`', '``', $table) . "`");
        while ($row = $stmt->fetch()) {
            $columns[$row['Field']] = array(
                'type' => $row['Type'],
                'null' => $row['Null'],
                'default' => $row['Default'],
                'extra' => $row['Extra'],
            );
        }
        return $columns;
    }

    private function profileDatabase($pdo, $tables)
    {
        $prefixes = array();
        $counts = array();
        $coreCount = 0;
        foreach ($tables as $table) {
            $prefix = $this->tablePrefix($table);
            if (!isset($prefixes[$prefix])) {
                $prefixes[$prefix] = 0;
            }
            $prefixes[$prefix]++;

            $base = $this->baseTableName($table);
            if (in_array($base, $this->coreBaseTables, true)) {
                $coreCount++;
                $counts[$table] = $this->safeCount($pdo, $table);
            }
        }
        arsort($prefixes);

        return array(
            'table_count' => count($tables),
            'prefixes' => $prefixes,
            'primary_prefix' => count($prefixes) ? key($prefixes) : '',
            'system_family' => $this->detectFamily($tables),
            'core_table_count' => $coreCount,
            'core_counts' => $counts,
        );
    }

    private function safeCount($pdo, $table)
    {
        try {
            return (int)$pdo->query("SELECT COUNT(*) FROM `" . str_replace('`', '``', $table) . "`")->fetchColumn();
        } catch (Exception $e) {
            return null;
        }
    }

    private function tablePrefix($table)
    {
        $pos = strpos($table, '_');
        return $pos === false ? '' : substr($table, 0, $pos);
    }

    private function baseTableName($table)
    {
        return preg_replace('/^[a-z0-9]+_/i', '', $table);
    }

    private function detectFamily($tables)
    {
        $bases = array_map(array($this, 'baseTableName'), $tables);
        $hasMallCore = count(array_intersect(array('config', 'class', 'tools', 'orders', 'faka', 'pay'), $bases));
        $hasSiteCore = count(array_intersect(array('site', 'shequ', 'points'), $bases));
        if ($hasMallCore >= 5 && $hasSiteCore >= 2) {
            return '彩虹发卡 / 岁岁云商城同系';
        }
        if ($hasMallCore >= 4) {
            return '彩虹商城同系';
        }
        return '未识别或非标准同系';
    }

    private function compareTables($sourceTables, $targetTables, $sourceProfile, $targetProfile)
    {
        $missingInTarget = array_values(array_diff($sourceTables, $targetTables));
        $extraInTarget = array_values(array_diff($targetTables, $sourceTables));
        sort($missingInTarget);
        sort($extraInTarget);

        return array(
            'missing_in_target' => $this->annotateTables($missingInTarget),
            'extra_in_target' => $this->annotateTables($extraInTarget),
            'source_primary_prefix' => $sourceProfile['primary_prefix'],
            'target_primary_prefix' => $targetProfile['primary_prefix'],
            'prefix_match' => $sourceProfile['primary_prefix'] === $targetProfile['primary_prefix'],
        );
    }

    private function annotateTables($tables)
    {
        $items = array();
        foreach ($tables as $table) {
            $base = $this->baseTableName($table);
            $items[] = array(
                'name' => $table,
                'base' => $base,
                'level' => in_array($base, $this->coreBaseTables, true) ? 'danger' : ($this->looksLikeBackupTable($table) ? 'muted' : 'warning'),
                'note' => $this->looksLikeBackupTable($table) ? '疑似备份或临时表' : (in_array($base, $this->coreBaseTables, true) ? '核心业务表' : '扩展或自定义表'),
            );
        }
        return $items;
    }

    private function looksLikeBackupTable($table)
    {
        return preg_match('/(_bak_|_backup_|backup_|_bak$|_\d{8,})/i', $table) === 1;
    }

    private function compareColumns($sourceTables, $targetTables)
    {
        $result = array();
        $commonTables = array_values(array_intersect($sourceTables, $targetTables));
        sort($commonTables);

        foreach ($commonTables as $table) {
            $sourceColumns = $this->getColumns($this->sourceDb, $table);
            $targetColumns = $this->getColumns($this->targetDb, $table);
            $missingColumns = array_values(array_diff(array_keys($sourceColumns), array_keys($targetColumns)));
            $extraColumns = array_values(array_diff(array_keys($targetColumns), array_keys($sourceColumns)));
            sort($missingColumns);
            sort($extraColumns);

            if (!$missingColumns && !$extraColumns && !in_array($this->baseTableName($table), $this->coreBaseTables, true)) {
                continue;
            }

            $result[] = array(
                'table' => $table,
                'base' => $this->baseTableName($table),
                'source_count' => count($sourceColumns),
                'target_count' => count($targetColumns),
                'missing_columns' => $this->annotateColumns($missingColumns, $sourceColumns, 'missing'),
                'extra_columns' => $this->annotateColumns($extraColumns, $targetColumns, 'extra'),
            );
        }

        return $result;
    }

    private function annotateColumns($columns, $info, $direction)
    {
        $items = array();
        foreach ($columns as $column) {
            $items[] = array(
                'name' => $column,
                'type' => isset($info[$column]['type']) ? $info[$column]['type'] : '',
                'null' => isset($info[$column]['null']) ? $info[$column]['null'] : '',
                'default' => isset($info[$column]['default']) ? $info[$column]['default'] : null,
                'extra' => isset($info[$column]['extra']) ? $info[$column]['extra'] : '',
                'level' => $this->columnRiskLevel($column, $direction),
            );
        }
        return $items;
    }

    private function columnRiskLevel($column, $direction)
    {
        if ($direction === 'missing' && in_array($column, $this->highValueColumns, true)) {
            return 'danger';
        }
        if ($direction === 'missing') {
            return 'warning';
        }
        return 'info';
    }

    private function businessChecks($sourceTables, $targetTables, $sourceProfile, $targetProfile, $columnDiff)
    {
        $checks = array();
        $coreMissing = array();
        foreach ($this->coreBaseTables as $base) {
            $sourceTable = $this->findTableByBase($sourceTables, $base);
            $targetTable = $this->findTableByBase($targetTables, $base);
            if ($sourceTable && !$targetTable) {
                $coreMissing[] = $sourceTable;
            }
        }

        $checks[] = array(
            'level' => $sourceProfile['system_family'] === '未识别或非标准同系' ? 'danger' : 'ok',
            'title' => '系统族识别',
            'message' => $sourceProfile['system_family'],
        );
        $checks[] = array(
            'level' => $sourceProfile['primary_prefix'] === $targetProfile['primary_prefix'] ? 'ok' : 'warning',
            'title' => '表前缀',
            'message' => '源库 ' . $sourceProfile['primary_prefix'] . ' / 目标库 ' . $targetProfile['primary_prefix'],
        );
        $checks[] = array(
            'level' => empty($coreMissing) ? 'ok' : 'danger',
            'title' => '核心表覆盖',
            'message' => empty($coreMissing) ? '核心业务表在目标库中有对应结构' : implode(', ', $coreMissing),
        );

        $missingHighValue = array();
        foreach ($columnDiff as $table) {
            foreach ($table['missing_columns'] as $column) {
                if ($column['level'] === 'danger') {
                    $missingHighValue[] = $table['table'] . '.' . $column['name'];
                }
            }
        }
        $checks[] = array(
            'level' => empty($missingHighValue) ? 'ok' : 'danger',
            'title' => '高价值字段',
            'message' => empty($missingHighValue) ? '未发现高价值字段会被丢弃' : implode(', ', $missingHighValue),
        );

        return $checks;
    }

    private function findTableByBase($tables, $base)
    {
        foreach ($tables as $table) {
            if ($this->baseTableName($table) === $base) {
                return $table;
            }
        }
        return null;
    }

    private function riskSummary($tableDiff, $columnDiff, $businessChecks)
    {
        $danger = 0;
        $warning = 0;
        foreach ($tableDiff['missing_in_target'] as $table) {
            if ($table['level'] === 'danger') {
                $danger++;
            } elseif ($table['level'] === 'warning') {
                $warning++;
            }
        }
        foreach ($columnDiff as $table) {
            foreach ($table['missing_columns'] as $column) {
                if ($column['level'] === 'danger') {
                    $danger++;
                } elseif ($column['level'] === 'warning') {
                    $warning++;
                }
            }
        }
        foreach ($businessChecks as $check) {
            if ($check['level'] === 'danger') {
                $danger++;
            } elseif ($check['level'] === 'warning') {
                $warning++;
            }
        }

        if ($danger > 0) {
            $level = 'danger';
            $label = '不建议直接迁移';
            $message = '存在核心表或高价值字段风险，需要先补结构或制定字段映射。';
        } elseif ($warning > 0) {
            $level = 'warning';
            $label = '需确认后迁移';
            $message = '存在扩展表或字段差异，建议先备份并明确是否保留。';
        } else {
            $level = 'ok';
            $label = '预检通过';
            $message = '未发现明显结构阻断，可进入下一步迁移测试。';
        }

        return array(
            'level' => $level,
            'label' => $label,
            'message' => $message,
            'danger_count' => $danger,
            'warning_count' => $warning,
        );
    }
}

function migration_post_config($prefix)
{
    return array(
        'host' => trim($_POST[$prefix . '_host'] ?? '127.0.0.1'),
        'port' => trim($_POST[$prefix . '_port'] ?? '3306'),
        'database' => trim($_POST[$prefix . '_database'] ?? ''),
        'username' => trim($_POST[$prefix . '_username'] ?? ''),
        'password' => (string)($_POST[$prefix . '_password'] ?? ''),
    );
}

function migration_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function migration_level_class($level)
{
    if ($level === 'danger') {
        return 'label-danger';
    }
    if ($level === 'warning') {
        return 'label-warning';
    }
    if ($level === 'ok') {
        return 'label-success';
    }
    if ($level === 'muted') {
        return 'label-default';
    }
    return 'label-info';
}

$report = null;
$errorMessage = '';
$sourceConfig = migration_post_config('source');
$targetConfig = migration_post_config('target');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $preflight = new MigrationPreflight($sourceConfig, $targetConfig);
        $report = $preflight->run();
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}
?>
<style>
.migration-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.migration-card{border:1px solid #e6e8ef;border-radius:6px;background:#fff;padding:16px;margin-bottom:16px}
.migration-card h4{margin:0 0 14px;font-size:16px;font-weight:700}
.migration-card .form-group{margin-bottom:12px}
.migration-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px}
.migration-stat{border:1px solid #e6e8ef;border-radius:6px;padding:14px;background:#fff}
.migration-stat span{display:block;color:#6b7280;font-size:12px;margin-bottom:6px}
.migration-stat strong{display:block;font-size:20px;color:#1f2937;word-break:break-all}
.migration-risk{border-radius:6px;padding:16px;margin-bottom:16px;border:1px solid transparent}
.migration-risk--ok{background:#eefaf0;border-color:#cbeed2;color:#276738}
.migration-risk--warning{background:#fff8e6;border-color:#ffe1a8;color:#8a5a00}
.migration-risk--danger{background:#fff0f0;border-color:#f3c0c0;color:#9f2a2a}
.migration-table{font-size:13px}
.migration-table td,.migration-table th{vertical-align:middle!important}
.migration-scroll{max-height:360px;overflow:auto;border:1px solid #eee;border-radius:6px}
.migration-empty{padding:20px;text-align:center;color:#8a8f98}
@media (max-width: 767px){.migration-grid,.migration-summary{grid-template-columns:1fr}.migration-card{padding:12px}}
</style>

<div class="col-xs-12 admin-ops-page admin-migration-page">
    <section class="admin-ops-hero">
        <div>
            <p class="admin-ops-hero__eyebrow">&#36801;&#31227;&#39044;&#26816;</p>
            <h2>&#25968;&#25454;&#36801;&#31227;&#20013;&#24515;</h2>
            <p>&#38754;&#21521;&#24425;&#34425;&#21457;&#21345;&#12289;&#21516;&#31995;&#20108;&#24320;&#31449;&#21644;&#23681;&#23681;&#20113;&#21830;&#22478;&#26087;&#29256;&#31449;&#30340;&#36890;&#29992;&#32467;&#26500;&#39044;&#26816;&#12290;&#24403;&#21069;&#20165;&#20570;&#35835;&#21462;&#26816;&#26597;&#65292;&#19981;&#20250;&#28165;&#31354;&#25110;&#20889;&#20837;&#20219;&#20309;&#25968;&#25454;&#12290;</p>
        </div>
        <div class="admin-ops-hero__actions">
            <span class="admin-ops-chip"><i class="fa fa-search"></i> Dry Run</span>
            <span class="admin-ops-chip"><i class="fa fa-shield"></i> &#19981;&#20889;&#20837;</span>
            <span class="admin-ops-chip"><i class="fa fa-database"></i> Schema Diff</span>
        </div>
    </section>

    <?php if ($errorMessage !== '') { ?>
    <div class="alert alert-danger">
        <i class="fa fa-warning"></i> &#39044;&#26816;&#22833;&#36133;&#65306;<?php echo migration_h($errorMessage); ?>
    </div>
    <?php } ?>

    <form method="POST" autocomplete="off">
        <div class="migration-grid">
            <div class="migration-card">
                <h4><i class="fa fa-database"></i> &#28304;&#31449;&#25968;&#25454;&#24211;</h4>
                <div class="row">
                    <div class="col-sm-8 form-group">
                        <label>&#20027;&#26426;&#22320;&#22336;</label>
                        <input type="text" class="form-control" name="source_host" value="<?php echo migration_h($sourceConfig['host']); ?>" required>
                    </div>
                    <div class="col-sm-4 form-group">
                        <label>&#31471;&#21475;</label>
                        <input type="text" class="form-control" name="source_port" value="<?php echo migration_h($sourceConfig['port']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>&#25968;&#25454;&#24211;&#21517;</label>
                    <input type="text" class="form-control" name="source_database" value="<?php echo migration_h($sourceConfig['database']); ?>" required>
                </div>
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label>&#29992;&#25143;&#21517;</label>
                        <input type="text" class="form-control" name="source_username" value="<?php echo migration_h($sourceConfig['username']); ?>" required>
                    </div>
                    <div class="col-sm-6 form-group">
                        <label>&#23494;&#30721;</label>
                        <input type="password" class="form-control" name="source_password" value="">
                    </div>
                </div>
            </div>

            <div class="migration-card">
                <h4><i class="fa fa-server"></i> &#30446;&#26631;&#31449;&#25968;&#25454;&#24211;</h4>
                <div class="row">
                    <div class="col-sm-8 form-group">
                        <label>&#20027;&#26426;&#22320;&#22336;</label>
                        <input type="text" class="form-control" name="target_host" value="<?php echo migration_h($targetConfig['host']); ?>" required>
                    </div>
                    <div class="col-sm-4 form-group">
                        <label>&#31471;&#21475;</label>
                        <input type="text" class="form-control" name="target_port" value="<?php echo migration_h($targetConfig['port']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>&#25968;&#25454;&#24211;&#21517;</label>
                    <input type="text" class="form-control" name="target_database" value="<?php echo migration_h($targetConfig['database']); ?>" required>
                </div>
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label>&#29992;&#25143;&#21517;</label>
                        <input type="text" class="form-control" name="target_username" value="<?php echo migration_h($targetConfig['username']); ?>" required>
                    </div>
                    <div class="col-sm-6 form-group">
                        <label>&#23494;&#30721;</label>
                        <input type="password" class="form-control" name="target_password" value="">
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            &#39044;&#26816;&#21482;&#35835;&#21462; <code>SHOW TABLES</code>&#12289;<code>SHOW COLUMNS</code> &#21644;&#26680;&#24515;&#34920; <code>COUNT(*)</code>&#65292;&#19981;&#25191;&#34892; <code>TRUNCATE</code>&#12289;<code>INSERT</code>&#12289;<code>UPDATE</code> &#25110;&#32467;&#26500;&#21464;&#26356;&#12290;
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block">
            <i class="fa fa-search"></i> &#24320;&#22987;&#39044;&#26816;
        </button>
    </form>

    <?php if ($report) { ?>
    <hr>
    <div class="migration-risk migration-risk--<?php echo migration_h($report['risk']['level']); ?>">
                <h4 style="margin-top:0;"><?php echo migration_h($report['risk']['label']); ?></h4>
                <p style="margin-bottom:0;"><?php echo migration_h($report['risk']['message']); ?></p>
    </div>

    <div class="migration-summary">
        <div class="migration-stat"><span>&#28304;&#24211;&#34920;&#25968;</span><strong><?php echo (int)$report['source_profile']['table_count']; ?></strong></div>
        <div class="migration-stat"><span>&#30446;&#26631;&#24211;&#34920;&#25968;</span><strong><?php echo (int)$report['target_profile']['table_count']; ?></strong></div>
        <div class="migration-stat"><span>&#39640;&#39118;&#38505;</span><strong><?php echo (int)$report['risk']['danger_count']; ?></strong></div>
        <div class="migration-stat"><span>&#38656;&#30830;&#35748;</span><strong><?php echo (int)$report['risk']['warning_count']; ?></strong></div>
    </div>

    <div class="migration-grid">
        <div class="migration-card">
            <h4>&#28304;&#31449;&#35782;&#21035;</h4>
            <p><strong>&#31995;&#32479;&#26063;&#65306;</strong><?php echo migration_h($report['source_profile']['system_family']); ?></p>
            <p><strong>&#20027;&#34920;&#21069;&#32512;&#65306;</strong><?php echo migration_h($report['source_profile']['primary_prefix']); ?></p>
            <p><strong>&#26680;&#24515;&#34920;&#25968;&#65306;</strong><?php echo (int)$report['source_profile']['core_table_count']; ?></p>
        </div>
        <div class="migration-card">
            <h4>&#30446;&#26631;&#31449;&#35782;&#21035;</h4>
            <p><strong>&#31995;&#32479;&#26063;&#65306;</strong><?php echo migration_h($report['target_profile']['system_family']); ?></p>
            <p><strong>&#20027;&#34920;&#21069;&#32512;&#65306;</strong><?php echo migration_h($report['target_profile']['primary_prefix']); ?></p>
            <p><strong>&#26680;&#24515;&#34920;&#25968;&#65306;</strong><?php echo (int)$report['target_profile']['core_table_count']; ?></p>
        </div>
    </div>

    <div class="migration-card">
        <h4>&#19994;&#21153;&#26816;&#26597;</h4>
        <div class="table-responsive">
            <table class="table table-striped migration-table">
                <thead><tr><th>&#39033;&#30446;</th><th>&#32467;&#35770;</th><th>&#29366;&#24577;</th></tr></thead>
                <tbody>
                <?php foreach ($report['business_checks'] as $check) { ?>
                <tr>
                    <td><?php echo migration_h($check['title']); ?></td>
                    <td><?php echo migration_h($check['message']); ?></td>
                    <td><span class="label <?php echo migration_level_class($check['level']); ?>"><?php echo migration_h($check['level']); ?></span></td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="migration-grid">
        <div class="migration-card">
            <h4>&#30446;&#26631;&#24211;&#32570;&#23569;&#30340;&#28304;&#31449;&#34920;</h4>
            <div class="migration-scroll">
                <?php if (empty($report['table_diff']['missing_in_target'])) { ?>
                    <div class="migration-empty">&#26080;&#24046;&#24322;</div>
                <?php } else { ?>
                <table class="table table-striped migration-table">
                    <tbody>
                    <?php foreach ($report['table_diff']['missing_in_target'] as $table) { ?>
                    <tr>
                        <td><code><?php echo migration_h($table['name']); ?></code></td>
                        <td><?php echo migration_h($table['note']); ?></td>
                        <td><span class="label <?php echo migration_level_class($table['level']); ?>"><?php echo migration_h($table['level']); ?></span></td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <?php } ?>
            </div>
        </div>

        <div class="migration-card">
            <h4>&#30446;&#26631;&#24211;&#39069;&#22806;&#34920;</h4>
            <div class="migration-scroll">
                <?php if (empty($report['table_diff']['extra_in_target'])) { ?>
                    <div class="migration-empty">&#26080;&#24046;&#24322;</div>
                <?php } else { ?>
                <table class="table table-striped migration-table">
                    <tbody>
                    <?php foreach ($report['table_diff']['extra_in_target'] as $table) { ?>
                    <tr>
                        <td><code><?php echo migration_h($table['name']); ?></code></td>
                        <td><?php echo migration_h($table['note']); ?></td>
                        <td><span class="label <?php echo migration_level_class($table['level']); ?>"><?php echo migration_h($table['level']); ?></span></td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="migration-card">
        <h4>&#23383;&#27573;&#24046;&#24322;</h4>
        <div class="migration-scroll">
            <?php if (empty($report['column_diff'])) { ?>
                <div class="migration-empty">&#26080;&#24046;&#24322;</div>
            <?php } else { ?>
            <table class="table table-striped migration-table">
                <thead><tr><th>&#34920;</th><th>&#28304;&#24211;&#26377;&#12289;&#30446;&#26631;&#24211;&#26080;</th><th>&#30446;&#26631;&#24211;&#39069;&#22806;&#23383;&#27573;</th></tr></thead>
                <tbody>
                <?php foreach ($report['column_diff'] as $table) { ?>
                <tr>
                    <td><code><?php echo migration_h($table['table']); ?></code><br><small><?php echo (int)$table['source_count']; ?> / <?php echo (int)$table['target_count']; ?></small></td>
                    <td>
                        <?php if (empty($table['missing_columns'])) { echo '<span class="text-muted">&#26080;</span>'; } ?>
                        <?php foreach ($table['missing_columns'] as $column) { ?>
                            <div><span class="label <?php echo migration_level_class($column['level']); ?>"><?php echo migration_h($column['level']); ?></span> <code><?php echo migration_h($column['name']); ?></code> <small><?php echo migration_h($column['type']); ?></small></div>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if (empty($table['extra_columns'])) { echo '<span class="text-muted">&#26080;</span>'; } ?>
                        <?php foreach ($table['extra_columns'] as $column) { ?>
                            <div><span class="label <?php echo migration_level_class($column['level']); ?>"><?php echo migration_h($column['level']); ?></span> <code><?php echo migration_h($column['name']); ?></code> <small><?php echo migration_h($column['type']); ?></small></div>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
</div>
<?php include './foot.php'; ?>
