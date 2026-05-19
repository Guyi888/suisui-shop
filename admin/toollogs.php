<?php
include "../includes/common.php";
$title = '商品动态';

if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission('shop', 1);

if (!function_exists('q8_admin_toollogs_escape')) {
    function q8_admin_toollogs_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('q8_admin_toollogs_ensure_schema')) {
    function q8_admin_toollogs_ensure_schema($DB)
    {
        $DB->exec("CREATE TABLE IF NOT EXISTS `pre_toollogs_offline` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `content` text,
            `date` date DEFAULT NULL,
            `addtime` datetime DEFAULT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`id`),
            KEY `date` (`date`),
            KEY `active` (`active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}

if (!function_exists('q8_admin_toollogs_fetch_stats')) {
    function q8_admin_toollogs_fetch_stats($DB)
    {
        $stats = array(
            'online_total' => intval($DB->getColumn("SELECT COUNT(*) FROM pre_toollogs WHERE active=1")),
            'offline_total' => intval($DB->getColumn("SELECT COUNT(*) FROM pre_toollogs_offline WHERE active=1")),
            'online_today' => intval($DB->getColumn("SELECT COUNT(*) FROM pre_toollogs WHERE active=1 AND date=CURDATE()")),
            'offline_today' => intval($DB->getColumn("SELECT COUNT(*) FROM pre_toollogs_offline WHERE active=1 AND date=CURDATE()")),
            'online_latest' => (string)$DB->getColumn("SELECT addtime FROM pre_toollogs WHERE active=1 ORDER BY id DESC LIMIT 1"),
            'offline_latest' => (string)$DB->getColumn("SELECT addtime FROM pre_toollogs_offline WHERE active=1 ORDER BY id DESC LIMIT 1")
        );

        $stats['total_groups'] = $stats['online_total'] + $stats['offline_total'];
        $stats['today_groups'] = $stats['online_today'] + $stats['offline_today'];

        return $stats;
    }
}

q8_admin_toollogs_ensure_schema($DB);

$initialType = isset($_GET['log_type']) && $_GET['log_type'] === 'offline' ? 'offline' : 'online';
$toolLogStats = q8_admin_toollogs_fetch_stats($DB);
$toolLogContext = apply_filters('admin_toollogs_context', array(
    'stats' => $toolLogStats,
    'initial_type' => $initialType
));

include './head.php';
$toolLogAssetVersion = isset($adminAssetVersion) ? $adminAssetVersion : ((defined('VERSION') ? VERSION : '1.0.0') . '.20260426admin37');
?>
<link rel="stylesheet" href="./assets/css/admin-tool-logs.css?v=<?php echo urlencode($toolLogAssetVersion); ?>">

<div class="col-xs-12 admin-toollogs-page">
    <?php echo q8_render_action('admin_toollogs_page_before', $toolLogContext); ?>

    <section class="admin-toollogs-hero">
        <div class="admin-toollogs-hero__content">
            <p class="admin-toollogs-hero__eyebrow"><?php echo html_entity_decode('&#21830;&#21697;&#21160;&#24577;&#20013;&#24515;', ENT_QUOTES, 'UTF-8'); ?></p>
            <h2>统一查看商品上架、下架与自动同步留下的动态记录</h2>
            <p>这一页只管理商品动态日志，不改动商品业务和同步规则。后台删除、公开页展示、同步留痕会共用同一套日志表，避免前后台看到两套结果。</p>
        </div>
        <div class="admin-toollogs-hero__aside">
            <a href="/toollogs.php" target="_blank" rel="noopener" class="admin-toollogs-hero__chip">
                <i class="fa fa-external-link"></i>
                <span>查看公开页</span>
            </a>
            <a href="./cx-synchronization.php" class="admin-toollogs-hero__chip">
                <i class="fa fa-refresh"></i>
                <span>自动同步</span>
            </a>
            <a href="./shoplist.php?status=down" class="admin-toollogs-hero__chip">
                <i class="fa fa-cubes"></i>
                <span>下架商品</span>
            </a>
        </div>
    </section>

    <section class="admin-toollogs-stats">
        <article class="admin-toollogs-stat">
            <span class="admin-toollogs-stat__icon admin-toollogs-stat__icon--primary"><i class="fa fa-history"></i></span>
            <div>
                <span>动态分组总数</span>
                <strong><?php echo intval($toolLogStats['total_groups']); ?></strong>
            </div>
        </article>
        <article class="admin-toollogs-stat">
            <span class="admin-toollogs-stat__icon admin-toollogs-stat__icon--success"><i class="fa fa-arrow-up"></i></span>
            <div>
                <span>今日上架记录</span>
                <strong><?php echo intval($toolLogStats['online_today']); ?></strong>
            </div>
        </article>
        <article class="admin-toollogs-stat">
            <span class="admin-toollogs-stat__icon admin-toollogs-stat__icon--warning"><i class="fa fa-arrow-down"></i></span>
            <div>
                <span>今日下架记录</span>
                <strong><?php echo intval($toolLogStats['offline_today']); ?></strong>
            </div>
        </article>
        <article class="admin-toollogs-stat">
            <span class="admin-toollogs-stat__icon admin-toollogs-stat__icon--accent"><i class="fa fa-calendar"></i></span>
            <div>
                <span>今日动态分组</span>
                <strong><?php echo intval($toolLogStats['today_groups']); ?></strong>
            </div>
        </article>
    </section>

    <section class="admin-toollogs-map">
        <article>
            <i class="fa fa-link"></i>
            <div>
                <strong>前后台共用同一套数据</strong>
                <p>公开页 `/toollogs.php` 与后台商品动态都会分别读取 `pre_toollogs` 和 `pre_toollogs_offline`，后续不再只盯着上架表。</p>
            </div>
        </article>
        <article>
            <i class="fa fa-trash"></i>
            <div>
                <strong>同步删除也会补下架动态</strong>
                <p>同系统同步删商品时，现在会统一写下架日志，不再要求商品之前必须是上架状态，减少“删了但没动态”的漏记。</p>
            </div>
        </article>
        <article>
            <i class="fa fa-mobile"></i>
            <div>
                <strong>批量删除与手机端一起收口</strong>
                <p>这里统一做日志筛选、批量删除和公开页跳转，避免旧页按钮错位、表格难看和手机端难点的问题继续留着。</p>
            </div>
        </article>
    </section>

    <section class="admin-toollogs-panel">
        <div class="admin-toollogs-panel__title">
            <div>
                <h3>商品动态列表</h3>
                <p>支持切换查看上架日志和下架日志，按商品名关键字筛选，并对某一天的动态分组做单条或批量清理。</p>
            </div>
            <div class="admin-toollogs-panel__meta" id="toolLogSummary">准备加载日志...</div>
        </div>

        <div class="admin-toollogs-toolbar">
            <div class="admin-toollogs-tabs" role="tablist" aria-label="日志类型">
                <button type="button" class="admin-toollogs-tab<?php echo $initialType === 'online' ? ' is-active' : ''; ?>" data-toollog-type="online">
                    <i class="fa fa-arrow-up"></i>
                    <span>上架日志</span>
                </button>
                <button type="button" class="admin-toollogs-tab admin-toollogs-tab--offline<?php echo $initialType === 'offline' ? ' is-active' : ''; ?>" data-toollog-type="offline">
                    <i class="fa fa-arrow-down"></i>
                    <span>下架日志</span>
                </button>
            </div>
            <label class="admin-toollogs-search">
                <i class="fa fa-search"></i>
                <input type="text" class="form-control" id="toolLogKeyword" placeholder="搜索商品名或日志内容">
            </label>
            <label class="admin-toollogs-pagesize">
                <select class="form-control" id="toolLogPageSize">
                    <option value="20">20 条</option>
                    <option value="30" selected>30 条</option>
                    <option value="50">50 条</option>
                    <option value="80">80 条</option>
                    <option value="100">100 条</option>
                </select>
            </label>
            <div class="admin-toollogs-toolbar__actions">
                <button type="button" class="admin-toollogs-button admin-toollogs-button--light" data-toollog-action="search">
                    <i class="fa fa-filter"></i> 筛选
                </button>
                <button type="button" class="admin-toollogs-button admin-toollogs-button--light" data-toollog-action="reset">
                    <i class="fa fa-undo"></i> 重置
                </button>
                <button type="button" class="admin-toollogs-button admin-toollogs-button--danger" data-toollog-action="batch-delete">
                    <i class="fa fa-trash"></i> 删除选中
                </button>
            </div>
        </div>

        <div id="toolLogTable" class="admin-toollogs-table-host" aria-live="polite"></div>
    </section>

    <?php echo q8_render_action('admin_toollogs_page_after', $toolLogContext); ?>
</div>

<script>
window.toolLogsPageConfig = <?php echo json_encode(array(
    'initialType' => $initialType,
    'endpoints' => array(
        'table' => './toollogs-table.php',
        'save' => './ajax.php?act=saveToolLog',
        'lookup' => './ajax_shop.php?act=getTool',
        'delete' => './ajax.php?act=delToolLog',
        'batch' => './ajax.php?act=batchToolLogOperation'
    )
), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES); ?>;
</script>
<script src="./assets/js/toollogs.js?v=<?php echo urlencode($toolLogAssetVersion); ?>"></script>
