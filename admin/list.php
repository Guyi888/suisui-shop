<?php
include("../includes/common.php");
$title = '订单管理';
include './head.php';
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission('order', 1);

$orderListAssetVersion = (isset($adminAssetVersion) ? $adminAssetVersion : ((defined('VERSION') ? VERSION : '1.0.0') . '.20260426admin37')) . '-owner01';
$statusOptions = apply_filters('admin_order_list_status_options', array(
    -1 => '全部状态',
    0 => '待处理',
    2 => '正在处理',
    1 => '已完成',
    3 => '异常',
    4 => '已退单'
), 'search');
$pageSizeOptions = array(30, 50, 60, 80, 100);
$currentPageSize = isset($_GET['num']) ? intval($_GET['num']) : 30;
if (!in_array($currentPageSize, $pageSizeOptions, true)) {
    $currentPageSize = 30;
}
$filters = array(
    'id' => isset($_GET['id']) ? intval($_GET['id']) : 0,
    'kw' => isset($_GET['kw']) ? trim((string)$_GET['kw']) : '',
    'starttime' => isset($_GET['starttime']) ? trim((string)$_GET['starttime']) : '',
    'endtime' => isset($_GET['endtime']) ? trim((string)$_GET['endtime']) : '',
    'type' => isset($_GET['type']) ? intval($_GET['type']) : -1,
    'tid' => isset($_GET['tid']) ? trim((string)$_GET['tid']) : '',
    'cid' => isset($_GET['cid']) ? trim((string)$_GET['cid']) : '',
    'zid' => isset($_GET['zid']) ? trim((string)$_GET['zid']) : '',
    'uid' => isset($_GET['uid']) ? trim((string)$_GET['uid']) : ''
);
$keywordValue = $filters['kw'] !== '' ? $filters['kw'] : ($filters['id'] > 0 ? (string)$filters['id'] : '');
$filters['kw'] = $keywordValue;
?>
<link rel="stylesheet" href="../assets/appui/css/datepicker.css">
<link rel="stylesheet" href="./assets/css/admin-order-list.css?v=<?php echo urlencode($orderListAssetVersion); ?>">
<script src="<?php echo $cdnpublic ?>bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="<?php echo $cdnpublic ?>bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.zh-CN.min.js"></script>

<div class="col-xs-12 admin-order-page">
    <?php echo q8_render_action('admin_order_list_page_before', array('filters' => $filters)); ?>

    <section class="admin-order-hero">
        <div class="admin-order-hero__content">
            <p class="admin-order-hero__eyebrow"><?php echo html_entity_decode('&#35746;&#21333;&#20013;&#24515;', ENT_QUOTES, 'UTF-8'); ?></p>
            <h2>统一管理订单筛选、补单、退款和结果备注</h2>
            <p>这一页只处理订单维度的操作，不改动业务下单逻辑。筛选、状态处理、异常备注和对接进度都集中在这里完成。</p>
        </div>
        <div class="admin-order-hero__aside">
            <div class="admin-order-hero__chip"><i class="fa fa-search"></i> 多条件筛选</div>
            <div class="admin-order-hero__chip"><i class="fa fa-refresh"></i> 一键补单</div>
            <div class="admin-order-hero__chip"><i class="fa fa-credit-card"></i> 退款处理</div>
        </div>
    </section>

    <section class="block admin-order-panel">
        <div class="block-title admin-order-panel__title">
            <div>
                <h3>订单检索</h3>
                <p>支持关键词、时间范围和状态联动筛选，保留 tid、zid、uid 上下文跳转。</p>
            </div>
        </div>

        <?php echo q8_render_action('admin_order_list_filters_before', array('filters' => $filters)); ?>

        <form id="orderFilterForm" class="admin-order-filter-form" method="get" action="./list.php">
            <input type="hidden" name="tid" value="<?php echo htmlspecialchars($filters['tid'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="cid" value="<?php echo htmlspecialchars($filters['cid'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="zid" value="<?php echo htmlspecialchars($filters['zid'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="uid" value="<?php echo htmlspecialchars($filters['uid'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="admin-order-filter-grid">
                <label class="admin-order-field admin-order-field--wide">
                    <span class="admin-order-field__label">关键词</span>
                    <span class="admin-order-field__control admin-order-field__control--icon">
                        <i class="fa fa-search"></i>
                        <input type="text" class="form-control" name="kw" value="<?php echo htmlspecialchars($filters['kw'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="输入下单账号、订单号或支付单号">
                    </span>
                </label>

                <label class="admin-order-field">
                    <span class="admin-order-field__label">开始日期</span>
                    <span class="admin-order-field__control admin-order-field__control--icon">
                        <i class="fa fa-calendar"></i>
                        <input type="text" id="starttime" name="starttime" class="form-control input-datepicker" value="<?php echo htmlspecialchars($filters['starttime'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="YYYY-MM-DD" autocomplete="off">
                    </span>
                </label>

                <label class="admin-order-field">
                    <span class="admin-order-field__label">结束日期</span>
                    <span class="admin-order-field__control admin-order-field__control--icon">
                        <i class="fa fa-calendar-check-o"></i>
                        <input type="text" id="endtime" name="endtime" class="form-control input-datepicker" value="<?php echo htmlspecialchars($filters['endtime'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="YYYY-MM-DD" autocomplete="off">
                    </span>
                </label>

                <label class="admin-order-field">
                    <span class="admin-order-field__label">订单状态</span>
                    <span class="admin-order-field__control">
                        <select name="type" class="form-control">
                            <?php foreach ($statusOptions as $statusValue => $statusLabel) { ?>
                            <option value="<?php echo (int)$statusValue; ?>" <?php echo $filters['type'] === (int)$statusValue ? 'selected' : ''; ?>><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php } ?>
                        </select>
                    </span>
                </label>

                <label class="admin-order-field">
                    <span class="admin-order-field__label">每页显示</span>
                    <span class="admin-order-field__control">
                        <select id="pagesize" class="form-control">
                            <?php foreach ($pageSizeOptions as $pageSizeOption) { ?>
                            <option value="<?php echo $pageSizeOption; ?>" <?php echo $currentPageSize === $pageSizeOption ? 'selected' : ''; ?>><?php echo $pageSizeOption; ?></option>
                            <?php } ?>
                        </select>
                    </span>
                </label>
            </div>

            <div class="admin-order-toolbar">
                <div class="admin-order-toolbar__group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> 搜索订单
                    </button>
                    <button type="button" class="btn btn-default" id="resetOrderFilters">
                        <i class="fa fa-undo"></i> 重置筛选
                    </button>
                    <button type="button" class="btn btn-default" id="refreshOrderList">
                        <i class="fa fa-refresh"></i> 刷新列表
                    </button>
                </div>

                <div class="admin-order-toolbar__group">
                    <a href="./export.php" class="btn btn-success">
                        <i class="fa fa-download"></i> 导出订单
                    </a>
                    <a href="./log.php" class="btn btn-warning" target="_blank" rel="noreferrer">
                        <i class="fa fa-file-text-o"></i> 对接日志
                    </a>
                    <button type="button" class="btn btn-default" id="onekeyResubmit">
                        <i class="fa fa-repeat"></i> 一键补单
                    </button>
                </div>
            </div>
        </form>

        <?php echo q8_render_action('admin_order_list_filters_after', array('filters' => $filters)); ?>
    </section>

    <div id="listTable" class="admin-order-table-shell">
        <div class="admin-order-loading-state">
            <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
            <p>正在加载订单列表...</p>
        </div>
    </div>

    <?php echo q8_render_action('admin_order_list_page_after', array('filters' => $filters)); ?>
</div>
</div>

<script src="<?php echo $cdnpublic ?>layer/2.3/layer.js"></script>
<script src="assets/js/list.js?v=<?php echo urlencode($orderListAssetVersion); ?>"></script>
</body>
</html>
