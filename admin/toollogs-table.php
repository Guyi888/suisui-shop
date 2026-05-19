<?php
include '../includes/common.php';
if ($islogin != 1) {
    exit('<script language="javascript">window.location.href="./login.php";</script>');
}
adminpermission('shop', 1);

if (!function_exists('q8_admin_toollogs_table_escape')) {
    function q8_admin_toollogs_table_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('q8_admin_toollogs_table_ensure_schema')) {
    function q8_admin_toollogs_table_ensure_schema($DB)
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

if (!function_exists('q8_admin_toollogs_table_type_meta')) {
    function q8_admin_toollogs_table_type_meta($type)
    {
        if ($type === 'offline') {
            return array(
                'table' => 'pre_toollogs_offline',
                'label' => '下架日志',
                'tag' => '下架',
                'empty_icon' => 'fa-arrow-down',
                'row_class' => 'admin-toollogs-row--offline'
            );
        }

        return array(
            'table' => 'pre_toollogs',
            'label' => '上架日志',
            'tag' => '上架',
            'empty_icon' => 'fa-arrow-up',
            'row_class' => 'admin-toollogs-row--online'
        );
    }
}

if (!function_exists('q8_admin_toollogs_table_parse_entries')) {
    function q8_admin_toollogs_table_parse_entries($content)
    {
        if (function_exists('q8_toollog_parse_content')) {
            return q8_toollog_parse_content($content);
        }

        return array();
    }
}

q8_admin_toollogs_table_ensure_schema($DB);

$logType = isset($_GET['log_type']) && $_GET['log_type'] === 'offline' ? 'offline' : 'online';
$keyword = isset($_GET['keyword']) ? trim((string)$_GET['keyword']) : '';
$pageSize = isset($_GET['num']) ? intval($_GET['num']) : 30;
$pageSize = max(20, min(100, $pageSize));
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$page = max(1, $page);

$meta = q8_admin_toollogs_table_type_meta($logType);
$where = 'active=1';
$params = array();

if ($keyword !== '') {
    $where .= ' AND content LIKE :keyword';
    $params[':keyword'] = '%' . $keyword . '%';
}

$totalRows = intval($DB->getColumn("SELECT COUNT(*) FROM {$meta['table']} WHERE {$where}", $params));
$todayParams = $params;
$todayRows = intval($DB->getColumn("SELECT COUNT(*) FROM {$meta['table']} WHERE {$where} AND date=CURDATE()", $todayParams));
$pages = $totalRows > 0 ? intval(ceil($totalRows / $pageSize)) : 1;
if ($page > $pages) {
    $page = $pages;
}
$offset = max(0, ($page - 1) * $pageSize);

$query = "SELECT id,content,date,addtime,active FROM {$meta['table']} WHERE {$where} ORDER BY date DESC,id DESC LIMIT {$offset},{$pageSize}";
$rows = array();
$rs = $DB->query($query, $params);
while ($row = $rs->fetch()) {
    $row['id'] = intval($row['id']);
    $row['entries'] = q8_admin_toollogs_table_parse_entries($row['content']);
    $row['items'] = array();
    foreach ($row['entries'] as $entry) {
        $row['items'][] = $entry['name'];
    }
    $row['item_total'] = count($row['entries']);
    $rows[] = $row;
}

$currentItems = 0;
foreach ($rows as $row) {
    $currentItems += intval($row['item_total']);
}

$tableContext = apply_filters('admin_toollogs_table_context', array(
    'log_type' => $logType,
    'keyword' => $keyword,
    'page' => $page,
    'pages' => $pages,
    'page_size' => $pageSize,
    'total_rows' => $totalRows,
    'today_rows' => $todayRows,
    'current_items' => $currentItems
));

echo q8_render_action('admin_toollogs_table_before', $tableContext);
?>
<div class="admin-toollogs-summary">
    <div class="admin-toollogs-summary__meta">
        <strong><?php echo q8_admin_toollogs_table_escape($meta['label']); ?></strong>
        <span>共 <?php echo intval($totalRows); ?> 组日志</span>
        <span>今日 <?php echo intval($todayRows); ?> 组</span>
        <span>当前页 <?php echo intval($currentItems); ?> 个商品动态</span>
    </div>
    <div class="admin-toollogs-summary__side">
        <?php if ($keyword !== '') { ?>
        <span class="admin-toollogs-summary__keyword">关键词：<?php echo q8_admin_toollogs_table_escape($keyword); ?></span>
        <?php } ?>
        <span>第 <?php echo intval($page); ?> / <?php echo intval(max($pages, 1)); ?> 页</span>
    </div>
</div>

<?php if (empty($rows)) { ?>
<div class="admin-toollogs-empty">
    <i class="fa <?php echo q8_admin_toollogs_table_escape($meta['empty_icon']); ?>"></i>
    <strong>当前没有<?php echo q8_admin_toollogs_table_escape($meta['label']); ?></strong>
    <p><?php echo $keyword !== '' ? '换个关键词再试，或者清空筛选后重新查看。' : '同步、手动上下架或公开页日志生成后，会在这里汇总展示。'; ?></p>
</div>
<?php } else { ?>
<form id="toolLogListForm" class="admin-toollogs-list-form">
    <input type="hidden" name="log_type" value="<?php echo q8_admin_toollogs_table_escape($logType); ?>">
    <div class="admin-toollogs-table-wrap">
        <table class="table admin-toollogs-table">
            <thead>
            <tr>
                <th class="admin-toollogs-table__check">
                    <label class="admin-toollogs-check">
                        <input type="checkbox" data-toollog-check-all>
                        <span></span>
                    </label>
                </th>
                <th>ID</th>
                <th>类型</th>
                <th>日期</th>
                <th>商品数</th>
                <th>最后更新时间</th>
                <th>内容预览</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row) { ?>
            <?php
            $previewItems = array_slice($row['entries'], 0, 4);
            $remaining = max(0, intval($row['item_total']) - count($previewItems));
            ?>
            <tr class="admin-toollogs-row <?php echo q8_admin_toollogs_table_escape($meta['row_class']); ?>" data-toollog-row data-id="<?php echo intval($row['id']); ?>">
                <td data-label="选择">
                    <label class="admin-toollogs-check">
                        <input type="checkbox" name="checkbox[]" value="<?php echo intval($row['id']); ?>" data-toollog-checkbox>
                        <span></span>
                    </label>
                </td>
                <td data-label="ID">
                    <strong>#<?php echo intval($row['id']); ?></strong>
                </td>
                <td data-label="类型">
                    <span class="admin-toollogs-badge admin-toollogs-badge--<?php echo q8_admin_toollogs_table_escape($logType); ?>">
                        <i class="fa <?php echo $logType === 'offline' ? 'fa-arrow-down' : 'fa-arrow-up'; ?>"></i>
                        <?php echo q8_admin_toollogs_table_escape($meta['tag']); ?>
                    </span>
                </td>
                <td data-label="日期">
                    <div class="admin-toollogs-date">
                        <strong><?php echo q8_admin_toollogs_table_escape($row['date']); ?></strong>
                        <?php if ($row['date'] === date('Y-m-d')) { ?>
                        <span>今天</span>
                        <?php } ?>
                    </div>
                </td>
                <td data-label="商品数">
                    <strong><?php echo intval($row['item_total']); ?></strong>
                </td>
                <td data-label="最后更新时间">
                    <span><?php echo q8_admin_toollogs_table_escape($row['addtime']); ?></span>
                </td>
                <td data-label="内容预览">
                    <textarea class="admin-toollogs-row__items-json" hidden><?php echo q8_admin_toollogs_table_escape(json_encode($row['entries'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></textarea>
                    <div class="admin-toollogs-preview">
                        <?php foreach ($previewItems as $entry) { ?>
                        <span class="admin-toollogs-preview__item">
                            <?php echo q8_admin_toollogs_table_escape($entry['name']); ?>
                            <?php if (!empty($entry['tid'])) { ?>
                            <em>#<?php echo intval($entry['tid']); ?></em>
                            <?php } ?>
                        </span>
                        <?php } ?>
                        <?php if ($remaining > 0) { ?>
                        <span class="admin-toollogs-preview__more">还有 <?php echo intval($remaining); ?> 个商品</span>
                        <?php } ?>
                    </div>
                </td>
                <td data-label="操作">
                    <div class="admin-toollogs-actions">
                        <button type="button"
                                class="btn btn-primary btn-sm"
                                data-toollog-action="edit"
                                data-id="<?php echo intval($row['id']); ?>"
                                data-date="<?php echo q8_admin_toollogs_table_escape($row['date']); ?>"
                                data-log-type="<?php echo q8_admin_toollogs_table_escape($logType); ?>">
                            <i class="fa fa-pencil"></i> 编辑
                        </button>
                        <button type="button"
                                class="btn btn-default btn-sm"
                                data-toollog-action="copy"
                                data-content="<?php echo q8_admin_toollogs_table_escape(implode("\n", $row['items'])); ?>">
                            <i class="fa fa-copy"></i> 复制
                        </button>
                        <button type="button"
                                class="btn btn-danger btn-sm"
                                data-toollog-action="delete"
                                data-id="<?php echo intval($row['id']); ?>"
                                data-log-type="<?php echo q8_admin_toollogs_table_escape($logType); ?>">
                            <i class="fa fa-trash"></i> 删除
                        </button>
                    </div>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="admin-toollogs-footer">
        <div class="admin-toollogs-footer__bulk">
            <span>已选 <strong data-toollog-selected-count>0</strong> 条</span>
            <button type="button" class="btn btn-default" data-toollog-action="clear-selection">
                <i class="fa fa-eraser"></i> 清空选择
            </button>
        </div>
        <div class="admin-toollogs-pagination">
            <?php if ($page <= 1) { ?>
            <span class="admin-toollogs-pagination__button admin-toollogs-pagination__button--disabled">首页</span>
            <span class="admin-toollogs-pagination__button admin-toollogs-pagination__button--disabled">上一页</span>
            <?php } else { ?>
            <button type="button" class="admin-toollogs-pagination__button" data-toollog-page="1">首页</button>
            <button type="button" class="admin-toollogs-pagination__button" data-toollog-page="<?php echo intval($page - 1); ?>">上一页</button>
            <?php } ?>

            <span class="admin-toollogs-pagination__button admin-toollogs-pagination__button--current"><?php echo intval($page); ?> / <?php echo intval(max($pages, 1)); ?></span>

            <?php if ($page >= $pages) { ?>
            <span class="admin-toollogs-pagination__button admin-toollogs-pagination__button--disabled">下一页</span>
            <span class="admin-toollogs-pagination__button admin-toollogs-pagination__button--disabled">尾页</span>
            <?php } else { ?>
            <button type="button" class="admin-toollogs-pagination__button" data-toollog-page="<?php echo intval($page + 1); ?>">下一页</button>
            <button type="button" class="admin-toollogs-pagination__button" data-toollog-page="<?php echo intval($pages); ?>">尾页</button>
            <?php } ?>
        </div>
    </div>
</form>
<?php } ?>

<?php echo q8_render_action('admin_toollogs_table_after', $tableContext); ?>
