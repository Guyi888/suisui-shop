<?php
include "../includes/common.php";
$title = "支付记录";
include "./head.php";
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission("shop", 1);

$payOrderAssetVersion = isset($adminAssetVersion) ? $adminAssetVersion : ((defined('VERSION') ? VERSION : '1.0.0') . '.20260426admin37');
$payOrderColumns = apply_filters('admin_payorder_search_columns', array(
    'trade_no' => '支付订单号',
    'api_trade_no' => '接口订单号',
    'zid' => '站点ID',
    'userid' => '用户ID',
    'tid' => '商品ID',
    'name' => '订单名称',
    'money' => '支付金额',
    'input' => '下单内容',
    'ip' => '用户IP'
));
$payOrderTypes = apply_filters('admin_payorder_type_options', array(
    'all' => '所有支付方式',
    'alipay' => '支付宝',
    'wxpay' => '微信支付',
    'qqpay' => 'QQ钱包',
    'tenpay' => '财付通',
    'bank' => '银联支付',
    'jdpay' => '京东支付',
    'rmb' => '余额支付'
));
$payOrderPageSizes = array(30, 50, 60, 80, 100);
$payOrderColumn = isset($_GET['column']) && isset($payOrderColumns[$_GET['column']]) ? $_GET['column'] : 'trade_no';
$payOrderType = isset($_GET['type']) && isset($payOrderTypes[$_GET['type']]) ? $_GET['type'] : 'all';
$payOrderStatus = isset($_GET['dstatus']) ? intval($_GET['dstatus']) : 0;
if (!in_array($payOrderStatus, array(0, 1, 2), true)) {
    $payOrderStatus = 0;
}
$payOrderPageSize = isset($_GET['num']) ? intval($_GET['num']) : 30;
if (!in_array($payOrderPageSize, $payOrderPageSizes, true)) {
    $payOrderPageSize = 30;
}
$payOrderFilters = array(
    'column' => $payOrderColumn,
    'kw' => isset($_GET['kw']) ? trim((string)$_GET['kw']) : '',
    'type' => $payOrderType,
    'dstatus' => $payOrderStatus,
    'starttime' => isset($_GET['starttime']) ? trim((string)$_GET['starttime']) : '',
    'endtime' => isset($_GET['endtime']) ? trim((string)$_GET['endtime']) : ''
);
?>
<link rel="stylesheet" href="../assets/appui/css/datepicker.css">
<link rel="stylesheet" href="./assets/css/admin-payorder.css?v=<?php echo urlencode($payOrderAssetVersion); ?>">
<script src="<?php echo $cdnpublic ?>bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="<?php echo $cdnpublic ?>bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.zh-CN.min.js"></script>

<div class="col-xs-12 admin-payorder-page">
    <?php echo q8_render_action('admin_payorder_page_before', array('filters' => $payOrderFilters)); ?>

    <section class="admin-payorder-hero">
        <div class="admin-payorder-hero__content">
            <p class="admin-payorder-hero__eyebrow">Payment Console</p>
            <h2>支付订单、补单与支付流水统一管理</h2>
            <p>这里处理支付维度的记录查询、未支付补单和流水删除；业务订单状态仍在订单列表里处理，避免支付流水和商品订单逻辑混在一起。</p>
        </div>
        <div class="admin-payorder-hero__aside">
            <div class="admin-payorder-hero__chip"><i class="fa fa-filter"></i> 日期与状态筛选</div>
            <div class="admin-payorder-hero__chip"><i class="fa fa-credit-card"></i> 支付方式归类</div>
            <div class="admin-payorder-hero__chip"><i class="fa fa-magic"></i> 未支付补单</div>
        </div>
    </section>

    <section class="block admin-payorder-panel">
        <div class="block-title admin-payorder-panel__title">
            <div>
                <h3>支付记录检索</h3>
                <p>支持订单号、接口单号、站点、用户、商品、金额、下单内容、IP 和时间范围组合筛选。</p>
            </div>
        </div>

        <?php echo q8_render_action('admin_payorder_filters_before', array('filters' => $payOrderFilters)); ?>

        <form id="payOrderFilterForm" class="admin-payorder-filter-form" method="get" action="./payorder.php">
            <div class="admin-payorder-filter-grid">
                <label class="admin-payorder-field">
                    <span class="admin-payorder-field__label">搜索字段</span>
                    <span class="admin-payorder-field__control">
                        <select class="form-control" name="column">
                            <?php foreach ($payOrderColumns as $columnKey => $columnLabel) { ?>
                            <option value="<?php echo htmlspecialchars($columnKey, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $payOrderFilters['column'] === $columnKey ? 'selected' : ''; ?>><?php echo htmlspecialchars($columnLabel, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php } ?>
                        </select>
                    </span>
                </label>

                <label class="admin-payorder-field admin-payorder-field--wide">
                    <span class="admin-payorder-field__label">关键词</span>
                    <span class="admin-payorder-field__control admin-payorder-field__control--icon">
                        <i class="fa fa-search"></i>
                        <input type="text" class="form-control" name="kw" value="<?php echo htmlspecialchars($payOrderFilters['kw'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="输入支付订单号、接口单号、用户ID或下单内容">
                    </span>
                </label>

                <label class="admin-payorder-field">
                    <span class="admin-payorder-field__label">支付方式</span>
                    <span class="admin-payorder-field__control">
                        <select name="type" class="form-control">
                            <?php foreach ($payOrderTypes as $typeKey => $typeLabel) { ?>
                            <option value="<?php echo htmlspecialchars($typeKey, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $payOrderFilters['type'] === $typeKey ? 'selected' : ''; ?>><?php echo htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php } ?>
                        </select>
                    </span>
                </label>

                <label class="admin-payorder-field">
                    <span class="admin-payorder-field__label">支付状态</span>
                    <span class="admin-payorder-field__control">
                        <select id="dstatus" name="dstatus" class="form-control">
                            <option value="0" <?php echo $payOrderFilters['dstatus'] === 0 ? 'selected' : ''; ?>>显示全部</option>
                            <option value="2" <?php echo $payOrderFilters['dstatus'] === 2 ? 'selected' : ''; ?>>只显示已支付</option>
                            <option value="1" <?php echo $payOrderFilters['dstatus'] === 1 ? 'selected' : ''; ?>>只显示未支付</option>
                        </select>
                    </span>
                </label>

                <label class="admin-payorder-field">
                    <span class="admin-payorder-field__label">开始日期</span>
                    <span class="admin-payorder-field__control admin-payorder-field__control--icon">
                        <i class="fa fa-calendar"></i>
                        <input type="text" name="starttime" class="form-control input-datepicker" value="<?php echo htmlspecialchars($payOrderFilters['starttime'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="YYYY-MM-DD" autocomplete="off">
                    </span>
                </label>

                <label class="admin-payorder-field">
                    <span class="admin-payorder-field__label">结束日期</span>
                    <span class="admin-payorder-field__control admin-payorder-field__control--icon">
                        <i class="fa fa-calendar-check-o"></i>
                        <input type="text" name="endtime" class="form-control input-datepicker" value="<?php echo htmlspecialchars($payOrderFilters['endtime'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="YYYY-MM-DD" autocomplete="off">
                    </span>
                </label>

                <label class="admin-payorder-field">
                    <span class="admin-payorder-field__label">每页显示</span>
                    <span class="admin-payorder-field__control">
                        <select id="pagesize" class="form-control">
                            <?php foreach ($payOrderPageSizes as $pageSizeOption) { ?>
                            <option value="<?php echo $pageSizeOption; ?>" <?php echo $payOrderPageSize === $pageSizeOption ? 'selected' : ''; ?>><?php echo $pageSizeOption; ?></option>
                            <?php } ?>
                        </select>
                    </span>
                </label>
            </div>

            <div class="admin-payorder-toolbar">
                <div class="admin-payorder-toolbar__group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> 搜索记录
                    </button>
                    <button type="button" class="btn btn-default" id="resetPayOrderFilters">
                        <i class="fa fa-undo"></i> 重置筛选
                    </button>
                    <button type="button" class="btn btn-default" id="refreshPayOrderList">
                        <i class="fa fa-refresh"></i> 刷新列表
                    </button>
                </div>
            </div>
        </form>

        <?php echo q8_render_action('admin_payorder_filters_after', array('filters' => $payOrderFilters)); ?>
    </section>

    <div id="listTable" class="admin-payorder-table-shell">
        <div class="admin-payorder-loading-state">
            <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
            <p>正在加载支付记录...</p>
        </div>
    </div>

    <?php echo q8_render_action('admin_payorder_page_after', array('filters' => $payOrderFilters)); ?>
</div>
</div>

<script src="assets/js/payorder.js?v=<?php echo urlencode($payOrderAssetVersion); ?>"></script>
</body>
</html>
