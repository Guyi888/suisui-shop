<?php
/**
 * 商品管理
 */
include("../includes/common.php");
$title = '商品管理';

if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission('shop', 1);

if (!function_exists('q8_admin_shop_escape')) {
    function q8_admin_shop_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('q8_admin_shop_fetch_categories')) {
    function q8_admin_shop_fetch_categories($DB)
    {
        $rows = $DB->getAll("SELECT cid,pid,name,active,sort FROM pre_class ORDER BY sort ASC,cid ASC");
        $parents = array();
        $children = array();
        $map = array();

        foreach ($rows as $row) {
            $row['cid'] = intval($row['cid']);
            $row['pid'] = intval($row['pid']);
            $row['active'] = intval($row['active']);
            $map[$row['cid']] = $row;

            if ($row['pid'] === 0) {
                $parents[] = $row;
            } else {
                if (!isset($children[$row['pid']])) {
                    $children[$row['pid']] = array();
                }
                $children[$row['pid']][] = $row;
            }
        }

        return array($parents, $children, $map);
    }
}

if (!function_exists('q8_admin_shop_render_category_options')) {
    function q8_admin_shop_render_category_options($parents, $children, $selected = '', $allowAll = true, $allowNone = false)
    {
        $html = '';

        if ($allowAll) {
            $html .= '<option value="">全部分类</option>';
        }
        if ($allowNone) {
            $html .= '<option value="-1"' . ((string)$selected === '-1' ? ' selected' : '') . '>未分类</option>';
        }

        foreach ($parents as $parent) {
            $label = $parent['name'] . ($parent['active'] == 1 ? '' : ' [隐藏]');
            $html .= '<option value="' . intval($parent['cid']) . '"' . ((string)$selected === (string)$parent['cid'] ? ' selected' : '') . '>' . q8_admin_shop_escape($label) . '</option>';

            if (isset($children[$parent['cid']])) {
                foreach ($children[$parent['cid']] as $child) {
                    $childLabel = '-- ' . $child['name'] . ($child['active'] == 1 ? '' : ' [隐藏]');
                    $html .= '<option value="' . intval($child['cid']) . '"' . ((string)$selected === (string)$child['cid'] ? ' selected' : '') . '>' . q8_admin_shop_escape($childLabel) . '</option>';
                }
            }
        }

        foreach ($children as $pid => $orphanRows) {
            $exists = false;
            foreach ($parents as $parent) {
                if (intval($parent['cid']) === intval($pid)) {
                    $exists = true;
                    break;
                }
            }
            if ($exists) {
                continue;
            }

            foreach ($orphanRows as $child) {
                $label = '-- ' . $child['name'] . ' [孤立]';
                $html .= '<option value="' . intval($child['cid']) . '"' . ((string)$selected === (string)$child['cid'] ? ' selected' : '') . '>' . q8_admin_shop_escape($label) . '</option>';
            }
        }

        return $html;
    }
}

if (!function_exists('q8_admin_shop_fetch_price_rules')) {
    function q8_admin_shop_fetch_price_rules($DB)
    {
$rows = $DB->getAll("SELECT id,name,kind,p_0,p_1,p_2 FROM pre_price WHERE zid=0 ORDER BY id ASC");
        $legacySelect = '<option value="0">不使用加价模板</option>';
        $legacyMap = array(0 => '不加价');
        $rules = array();

        foreach ($rows as $row) {
            $row['id'] = intval($row['id']);
            $row['kind'] = intval($row['kind']);
            $row['p_0'] = (string)$row['p_0'];
            $row['p_1'] = (string)$row['p_1'];
            $row['p_2'] = (string)$row['p_2'];
            $unitLabel = $row['kind'] === 1 ? '元' : '倍';

            $legacySelect .= '<option value="' . $row['id'] . '" kind="' . $row['kind'] . '" p_2="' . q8_admin_shop_escape($row['p_2']) . '" p_1="' . q8_admin_shop_escape($row['p_1']) . '" p_0="' . q8_admin_shop_escape($row['p_0']) . '">' . q8_admin_shop_escape($row['name']) . '(' . q8_admin_shop_escape($row['p_2']) . $unitLabel . '|' . q8_admin_shop_escape($row['p_1']) . $unitLabel . '|' . q8_admin_shop_escape($row['p_0']) . $unitLabel . ')</option>';
            $legacyMap[$row['id']] = $row['name'];
            $rules[] = $row;
        }

        $_SESSION['priceselect'] = $legacySelect;
        $_SESSION['price_class'] = $legacyMap;

        return array($rules, $legacyMap);
    }
}

if (!function_exists('q8_admin_shop_normalize_filters')) {
    function q8_admin_shop_normalize_filters($typeOptions, $statusOptions, $pageSizeOptions)
    {
        $status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';
        if ($status === '1') {
            $status = 'show';
        } elseif ($status === '0') {
            $status = 'hide';
        }
        if (!isset($statusOptions[$status])) {
            $status = '';
        }

        $type = isset($_GET['type']) ? trim((string)$_GET['type']) : '';
        if (!isset($typeOptions[$type])) {
            $type = '';
        }

        $pageSize = isset($_GET['num']) ? intval($_GET['num']) : 30;
        if (!in_array($pageSize, $pageSizeOptions, true)) {
            $pageSize = 30;
        }

        return array(
            'kw' => isset($_GET['kw']) ? trim((string)$_GET['kw']) : '',
            'cid' => isset($_GET['cid']) ? intval($_GET['cid']) : 0,
            'prid' => isset($_GET['prid']) ? intval($_GET['prid']) : 0,
            'tid' => isset($_GET['tid']) ? intval($_GET['tid']) : 0,
            'type' => $type,
            'status' => $status,
            'num' => $pageSize
        );
    }
}

$my = isset($_GET['my']) ? trim((string)$_GET['my']) : '';
if ($my === 'qk2') {
    adminpermission('shop', 2);
    $ok = $DB->exec("TRUNCATE TABLE `pre_tools`") !== false;
    header('Location: shoplist.php?notice=' . ($ok ? 'clear_success' : 'clear_failed'));
    exit;
}

list($categoryParents, $categoryChildren, $categoryMap) = q8_admin_shop_fetch_categories($DB);
list($priceRules, $priceRuleMap) = q8_admin_shop_fetch_price_rules($DB);

$typeOptions = apply_filters('admin_shop_list_type_options', array(
    '' => '全部来源',
    '1' => '对接商品',
    '4' => '发卡商品',
    'other' => '自营商品'
));
$statusOptions = apply_filters('admin_shop_list_status_options', array(
    '' => '全部状态',
    'show' => '前台显示',
    'hide' => '前台隐藏',
    'up' => '上架中',
    'down' => '已下架'
));
$pageSizeOptions = apply_filters('admin_shop_list_page_size_options', array(30, 50, 60, 80, 100));
$filters = q8_admin_shop_normalize_filters($typeOptions, $statusOptions, $pageSizeOptions);

$globalStats = $DB->getRow("SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN active=1 THEN 1 ELSE 0 END) AS visible_total,
    SUM(CASE WHEN active=0 THEN 1 ELSE 0 END) AS hidden_total,
    SUM(CASE WHEN close=0 THEN 1 ELSE 0 END) AS onsale_total,
    SUM(CASE WHEN close=1 THEN 1 ELSE 0 END) AS down_total,
    SUM(CASE WHEN is_curl IN (1,2) THEN 1 ELSE 0 END) AS docking_total,
    SUM(CASE WHEN is_curl=4 THEN 1 ELSE 0 END) AS card_total,
    SUM(CASE WHEN is_curl NOT IN (1,2,4) THEN 1 ELSE 0 END) AS self_total
FROM pre_tools");

$noticeMap = array(
    'clear_success' => array('type' => 'success', 'icon' => 'fa-check-circle', 'text' => '商品表已清空。'),
    'clear_failed' => array('type' => 'danger', 'icon' => 'fa-exclamation-triangle', 'text' => '商品表清空失败，请稍后再试。')
);
$notice = isset($_GET['notice']) && isset($noticeMap[$_GET['notice']]) ? $noticeMap[$_GET['notice']] : null;

$currentCategoryName = '';
if ($filters['cid'] > 0 && isset($categoryMap[$filters['cid']])) {
    $currentCategoryName = $categoryMap[$filters['cid']]['name'];
} elseif ($filters['cid'] === -1) {
    $currentCategoryName = '未分类';
}

$shopListAssetVersion = isset($adminAssetVersion) ? $adminAssetVersion : ((defined('VERSION') ? VERSION : '1.0.0') . '.20260426admin37');
$shopListContext = apply_filters('admin_shop_list_context', array(
    'filters' => $filters,
    'stats' => array(
        'total' => intval($globalStats['total']),
        'visible' => intval($globalStats['visible_total']),
        'hidden' => intval($globalStats['hidden_total']),
        'onsale' => intval($globalStats['onsale_total']),
        'down' => intval($globalStats['down_total']),
        'docking' => intval($globalStats['docking_total']),
        'card' => intval($globalStats['card_total']),
        'self' => intval($globalStats['self_total'])
    ),
    'categories' => count($categoryMap),
    'price_rules' => count($priceRules),
    'current_category' => $currentCategoryName,
    'notice' => $notice
));

include './head.php';
$shopListAssetVersion = isset($adminAssetVersion) ? $adminAssetVersion : ((defined('VERSION') ? VERSION : '1.0.0') . '.20260426admin40');
?>
<link rel="stylesheet" href="./assets/css/admin-shop-list.css?v=<?php echo urlencode($shopListAssetVersion); ?>">

<div class="col-xs-12 admin-shop-page">
    <?php echo q8_render_action('admin_shop_list_page_before', $shopListContext); ?>

    <?php if ($notice) { ?>
    <div class="alert alert-<?php echo q8_admin_shop_escape($notice['type']); ?> admin-shop-notice">
        <i class="fa <?php echo q8_admin_shop_escape($notice['icon']); ?>"></i>
        <span><?php echo q8_admin_shop_escape($notice['text']); ?></span>
    </div>
    <?php } ?>

    <section class="admin-shop-hero">
        <div class="admin-shop-hero__content">
            <p class="admin-shop-hero__eyebrow"><?php echo html_entity_decode('&#21830;&#21697;&#20013;&#24515;', ENT_QUOTES, 'UTF-8'); ?></p>
            <h2>统一管理商品、库存、价格模板、上下架与前台展示联动</h2>
            <p>这一页只处理后台商品维度的维护工作，不改动下单业务逻辑。分类筛选、价格调整、库存处理、批量上下架、移动分类和前台预览入口都会集中在这里完成。</p>
        </div>
        <div class="admin-shop-hero__aside">
            <a href="./shopedit.php?my=add<?php echo $filters['cid'] > 0 ? '&amp;cid=' . intval($filters['cid']) : ''; ?>" class="admin-shop-hero__chip"><i class="fa fa-plus-circle"></i> 新增商品</a>
            <a href="./classlist.php" class="admin-shop-hero__chip"><i class="fa fa-sitemap"></i> 分类管理</a>
            <a href="./batchgoods.php" class="admin-shop-hero__chip"><i class="fa fa-cloud-download"></i> 批量对接</a>
        </div>
    </section>

    <section class="admin-shop-stats">
        <a class="admin-shop-stat admin-ui-stat" href="./shoplist.php">
            <span class="admin-shop-stat__icon admin-shop-stat__icon--primary admin-ui-stat__icon"><i class="fa fa-cubes"></i></span>
            <div>
                <span>商品总数</span>
                <strong><?php echo intval($shopListContext['stats']['total']); ?></strong>
            </div>
        </a>
        <a class="admin-shop-stat admin-ui-stat" href="./shoplist.php?status=up">
            <span class="admin-shop-stat__icon admin-shop-stat__icon--success admin-ui-stat__icon"><i class="fa fa-arrow-circle-up"></i></span>
            <div>
                <span>上架中</span>
                <strong><?php echo intval($shopListContext['stats']['onsale']); ?></strong>
            </div>
        </a>
        <a class="admin-shop-stat admin-ui-stat" href="./shoplist.php?status=down">
            <span class="admin-shop-stat__icon admin-shop-stat__icon--warning admin-ui-stat__icon"><i class="fa fa-arrow-circle-down"></i></span>
            <div>
                <span>已下架</span>
                <strong><?php echo intval($shopListContext['stats']['down']); ?></strong>
            </div>
        </a>
        <a class="admin-shop-stat admin-ui-stat" href="./shoplist.php?status=hide">
            <span class="admin-shop-stat__icon admin-shop-stat__icon--accent admin-ui-stat__icon"><i class="fa fa-eye-slash"></i></span>
            <div>
                <span>前台隐藏</span>
                <strong><?php echo intval($shopListContext['stats']['hidden']); ?></strong>
            </div>
        </a>
    </section>

    <?php echo q8_render_action('admin_shop_list_stats_after', $shopListContext); ?>

    <section class="admin-shop-map">
        <article>
            <i class="fa fa-desktop"></i>
            <div>
                <strong>前台联动</strong>
                <p>商品显隐、上下架、前台预览和分类归属都会从这里衔接，改动会直接影响首页与下单页的展示结果。</p>
            </div>
        </article>
        <article>
            <i class="fa fa-tags"></i>
            <div>
                <strong>分类联动</strong>
                <p>支持按分类查看、按分类重排和批量移动分类，方便和前台首页、用户中心分类结构保持一致。</p>
            </div>
        </article>
        <article>
            <i class="fa fa-plug"></i>
            <div>
                <strong>对接联动</strong>
                <p>对接商品、发卡商品和自营商品在这里统一落表，后续同步、克隆、发卡库存也都以这里为基准入口。</p>
            </div>
        </article>
    </section>

    <section class="block admin-shop-panel">
        <div class="block-title admin-shop-panel__title">
            <div>
                <h3>商品列表</h3>
                <p><?php echo $currentCategoryName !== '' ? '当前正在查看分类：' . q8_admin_shop_escape($currentCategoryName) . '，可以直接调整该分类内商品排序。' : '支持关键词、分类、来源、状态和加价模板多条件筛选。'; ?></p>
            </div>
            <div class="admin-shop-panel__actions">
                <a href="./shoprank.php" class="btn btn-default"><i class="fa fa-bar-chart"></i> 销量排行</a>
                <button type="button" class="btn btn-default" data-shop-action="replace-name"><i class="fa fa-font"></i> 批量替换商品名</button>
                <button type="button" class="btn btn-default" data-shop-action="replace-inputs"><i class="fa fa-keyboard-o"></i> 批量替换输入框标题</button>
                <?php if ($filters['cid'] > 0) { ?>
                <button type="button" class="btn btn-default" data-shop-action="reset-sort" data-cid="<?php echo intval($filters['cid']); ?>"><i class="fa fa-sort"></i> 重置当前分类排序</button>
                <?php } ?>
                <button type="button" class="btn btn-default" data-shop-action="refresh"><i class="fa fa-refresh"></i> 刷新</button>
            </div>
        </div>

        <?php echo q8_render_action('admin_shop_list_filters_before', $shopListContext); ?>

        <form id="shopFilterForm" class="admin-shop-filter-form" method="get" action="./shoplist.php">
            <input type="hidden" name="tid" value="<?php echo intval($filters['tid']); ?>">

            <div class="admin-shop-filter-grid">
                <label class="admin-shop-field admin-shop-field--wide">
                    <span class="admin-shop-field__label">关键词</span>
                    <span class="admin-shop-field__control admin-shop-field__control--icon">
                        <i class="fa fa-search"></i>
                        <input type="text" class="form-control" name="kw" value="<?php echo q8_admin_shop_escape($filters['kw']); ?>" placeholder="输入商品名称或商品 ID">
                    </span>
                </label>
                <label class="admin-shop-field">
                    <span class="admin-shop-field__label">分类</span>
                    <select class="form-control" name="cid">
                        <?php echo q8_admin_shop_render_category_options($categoryParents, $categoryChildren, $filters['cid'], true, true); ?>
                    </select>
                </label>
                <label class="admin-shop-field">
                    <span class="admin-shop-field__label">来源</span>
                    <select class="form-control" name="type">
                        <?php foreach ($typeOptions as $value => $label) { ?>
                        <option value="<?php echo q8_admin_shop_escape($value); ?>"<?php echo $filters['type'] === (string)$value ? ' selected' : ''; ?>><?php echo q8_admin_shop_escape($label); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label class="admin-shop-field">
                    <span class="admin-shop-field__label">状态</span>
                    <select class="form-control" name="status">
                        <?php foreach ($statusOptions as $value => $label) { ?>
                        <option value="<?php echo q8_admin_shop_escape($value); ?>"<?php echo $filters['status'] === (string)$value ? ' selected' : ''; ?>><?php echo q8_admin_shop_escape($label); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label class="admin-shop-field">
                    <span class="admin-shop-field__label">加价模板</span>
                    <select class="form-control" name="prid">
                        <option value="0">全部模板</option>
                        <?php foreach ($priceRules as $rule) { ?>
                        <option value="<?php echo intval($rule['id']); ?>"<?php echo $filters['prid'] === intval($rule['id']) ? ' selected' : ''; ?>><?php echo q8_admin_shop_escape($rule['name']); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label class="admin-shop-field">
                    <span class="admin-shop-field__label">每页数量</span>
                    <select class="form-control" name="num" id="shopPageSize">
                        <?php foreach ($pageSizeOptions as $pageSize) { ?>
                        <option value="<?php echo intval($pageSize); ?>"<?php echo $filters['num'] === intval($pageSize) ? ' selected' : ''; ?>><?php echo intval($pageSize); ?></option>
                        <?php } ?>
                    </select>
                </label>
            </div>

            <div class="admin-shop-filter-actions">
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 应用筛选</button>
                <a href="./shoplist.php" class="btn btn-default"><i class="fa fa-undo"></i> 重置筛选</a>
            </div>
        </form>

        <?php echo q8_render_action('admin_shop_list_toolbar_after', $shopListContext); ?>

        <div id="shopListTable" class="admin-shop-table-shell" data-source="./shoplist-table.php">
            <div class="admin-shop-loading">
                <i class="fa fa-spinner fa-spin"></i>
                <span>正在加载商品列表</span>
            </div>
        </div>
    </section>

    <?php echo q8_render_action('admin_shop_list_page_after', $shopListContext); ?>
</div>

<script>
window.adminShopListConfig = <?php echo json_encode(array(
    'priceRules' => $priceRules,
    'defaultPageSize' => $filters['num'],
    'currentCategoryId' => $filters['cid'],
    'endpoints' => array(
        'table' => './shoplist-table.php',
        'ajax' => './ajax_shop.php'
    )
)); ?>;
</script>
<script src="./assets/js/shoplist.js?v=<?php echo urlencode($shopListAssetVersion); ?>"></script>
</body>
</html>
