<?php
include "../includes/common.php";
$title = "订单状态监控";
include "./head.php";
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission("shequ", 1);

$orderMonitorAssetVersion = isset($adminAssetVersion) ? $adminAssetVersion : ((defined('VERSION') ? VERSION : '1.0.0') . '.20260426admin37');
$saveMessage = null;

if (isset($_POST["do"]) && $_POST["do"] == "submit") {
    $updatestatus = isset($_POST["updatestatus"]) && (string)$_POST["updatestatus"] === "1" ? 1 : 0;
    $updatestatus_interval = isset($_POST["updatestatus_interval"]) ? trim((string)$_POST["updatestatus_interval"]) : '';
    $updatestatus_interval_unit = isset($_POST["updatestatus_interval_unit"]) && $_POST["updatestatus_interval_unit"] === "minute" ? "minute" : "hour";
    $updatestatus_interval = str_replace(',', '.', $updatestatus_interval);
    $updatestatus_interval_value = intval($updatestatus_interval);
    $minInterval = intval(apply_filters('admin_order_monitor_min_interval', 1, $updatestatus_interval_unit));
    if ($minInterval < 1) {
        $minInterval = 1;
    }

    if ($updatestatus_interval === '' || !preg_match('/^\d+$/', $updatestatus_interval) || $updatestatus_interval_value < $minInterval) {
        $saveMessage = array(
            'type' => 'danger',
            'icon' => 'fa-exclamation-triangle',
            'title' => '保存失败',
            'text' => '检测间隔必须是大于等于 ' . $minInterval . ' 的整数。'
        );
    } else {
        saveSetting("updatestatus", $updatestatus);
        saveSetting("updatestatus_interval", $updatestatus_interval_value);
        saveSetting("updatestatus_interval_unit", $updatestatus_interval_unit);
        $ad = $CACHE->clear();
        if ($ad) {
            $conf["updatestatus"] = $updatestatus;
            $conf["updatestatus_interval"] = $updatestatus_interval_value;
            $conf["updatestatus_interval_unit"] = $updatestatus_interval_unit;
            do_action('admin_order_monitor_settings_saved', array(
                'enabled' => $updatestatus == 1,
                'interval' => $updatestatus_interval_value,
                'unit' => $updatestatus_interval_unit
            ));
            $saveMessage = array(
                'type' => 'success',
                'icon' => 'fa-check-circle',
                'title' => '设置已保存',
                'text' => '订单状态监控设置已更新，新的检测间隔会在下次 cron 运行时生效。'
            );
        } else {
            $saveMessage = array(
                'type' => 'danger',
                'icon' => 'fa-exclamation-triangle',
                'title' => '保存失败',
                'text' => '缓存刷新失败：' . $DB->error()
            );
        }
    }
}

$monitorEnabled = isset($conf["updatestatus"]) && (string)$conf["updatestatus"] === "1";
$monitorInterval = isset($conf["updatestatus_interval"]) && $conf["updatestatus_interval"] !== '' ? $conf["updatestatus_interval"] : '1';
$monitorIntervalUnit = isset($conf["updatestatus_interval_unit"]) && $conf["updatestatus_interval_unit"] === "minute" ? "minute" : "hour";
$monitorIntervalLabel = $monitorInterval . ($monitorIntervalUnit === "minute" ? " 分钟" : " 小时");
$lastRunTime = !empty($conf["updatestatus_lasttime"]) ? $conf["updatestatus_lasttime"] : '暂未运行';
$monitorCronUrl = apply_filters('admin_order_monitor_cron_url', '../cron.php?do=updatestatus&key=' . $conf["cronkey"] . '&test=1');
$monitorContext = array(
    'enabled' => $monitorEnabled,
    'interval' => $monitorInterval,
    'unit' => $monitorIntervalUnit,
    'interval_label' => $monitorIntervalLabel,
    'last_run_time' => $lastRunTime,
    'cron_url' => $monitorCronUrl
);
$monitorTips = apply_filters('admin_order_monitor_tips', array(
    array(
        'icon' => 'fa-clock-o',
        'title' => '按时间间隔复查',
        'text' => '系统会把正在处理的订单按设定间隔重新检测，减少人工重复刷新。'
    ),
    array(
        'icon' => 'fa-plug',
        'title' => '适配对接站点',
        'text' => '适用于对接站点返回“正在处理”的订单，保留原有 cron 调度逻辑。'
    ),
    array(
        'icon' => 'fa-user-circle-o',
        'title' => '前台仍可触发更新',
        'text' => '用户在前台查询订单时也会尝试刷新状态，监控不是强制依赖项。'
    )
), $monitorContext);
if (!is_array($monitorTips)) {
    $monitorTips = array();
}
?>
<link rel="stylesheet" href="./assets/css/admin-order-monitor.css?v=<?php echo urlencode($orderMonitorAssetVersion); ?>">

<div class="col-xs-12 admin-order-monitor-page">
    <?php echo q8_render_action('admin_order_monitor_page_before', $monitorContext); ?>

    <section class="admin-order-monitor-hero">
        <div class="admin-order-monitor-hero__content">
            <p class="admin-order-monitor-hero__eyebrow">Order Watcher</p>
            <h2>订单状态监控与自动复查设置</h2>
            <p>这里负责控制“正在处理”订单的定时复查，不改变下单、补单和订单状态判断逻辑。手动执行会调用现有 cron 检测入口，方便排查对接站点返回结果。</p>
        </div>
        <div class="admin-order-monitor-hero__status <?php echo $monitorEnabled ? 'is-on' : 'is-off'; ?>">
            <span class="admin-order-monitor-hero__pulse"></span>
            <strong><?php echo $monitorEnabled ? '监控已开启' : '监控已关闭'; ?></strong>
            <small>当前检测间隔：<?php echo htmlspecialchars($monitorIntervalLabel, ENT_QUOTES, 'UTF-8'); ?></small>
        </div>
    </section>

    <?php if (!empty($saveMessage)) { ?>
    <div class="admin-order-monitor-alert admin-order-monitor-alert--<?php echo htmlspecialchars($saveMessage['type'], ENT_QUOTES, 'UTF-8'); ?>">
        <i class="fa <?php echo htmlspecialchars($saveMessage['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
        <div>
            <strong><?php echo htmlspecialchars($saveMessage['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
            <p><?php echo htmlspecialchars($saveMessage['text'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    </div>
    <?php } ?>

    <section class="admin-order-monitor-stats">
        <article class="admin-order-monitor-stat admin-ui-stat">
            <span class="admin-order-monitor-stat__icon admin-order-monitor-stat__icon--primary admin-ui-stat__icon"><i class="fa fa-power-off"></i></span>
            <div>
                <span>运行状态</span>
                <strong><?php echo $monitorEnabled ? '开启' : '关闭'; ?></strong>
            </div>
        </article>
        <article class="admin-order-monitor-stat admin-ui-stat">
            <span class="admin-order-monitor-stat__icon admin-order-monitor-stat__icon--accent admin-ui-stat__icon"><i class="fa fa-repeat"></i></span>
            <div>
                <span>检测间隔</span>
                <strong><?php echo htmlspecialchars($monitorIntervalLabel, ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
        </article>
        <article class="admin-order-monitor-stat admin-ui-stat">
            <span class="admin-order-monitor-stat__icon admin-order-monitor-stat__icon--warm admin-ui-stat__icon"><i class="fa fa-history"></i></span>
            <div>
                <span>上次运行</span>
                <strong><?php echo htmlspecialchars($lastRunTime, ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
        </article>
    </section>

    <?php echo q8_render_action('admin_order_monitor_summary_after', $monitorContext); ?>

    <div class="admin-order-monitor-grid">
        <section class="block admin-order-monitor-panel">
            <div class="block-title admin-order-monitor-panel__title">
                <div>
                    <h3>监控设置</h3>
                    <p>只控制检测频率和是否启用，不改动订单表结构与对接站点处理规则。</p>
                </div>
            </div>

            <?php echo q8_render_action('admin_order_monitor_form_before', $monitorContext); ?>

            <form action="./orderjk.php" method="post" role="form" class="admin-order-monitor-form">
                <input type="hidden" name="do" value="submit">

                <label class="admin-order-monitor-field">
                    <span class="admin-order-monitor-field__label">是否开启订单状态监控</span>
                    <span class="admin-order-monitor-select">
                        <i class="fa <?php echo $monitorEnabled ? 'fa-toggle-on' : 'fa-toggle-off'; ?> admin-order-monitor-select__icon<?php echo $monitorEnabled ? ' is-on' : ' is-off'; ?>"></i>
                        <select class="form-control" name="updatestatus" id="orderMonitorStatus">
                            <option value="0" <?php echo !$monitorEnabled ? 'selected' : ''; ?>>关闭</option>
                            <option value="1" <?php echo $monitorEnabled ? 'selected' : ''; ?>>开启</option>
                        </select>
                    </span>
                </label>

                <label class="admin-order-monitor-field">
                    <span class="admin-order-monitor-field__label">订单状态检测时间间隔</span>
                    <span class="admin-order-monitor-interval">
                        <span class="admin-order-monitor-interval__input">
                            <i class="fa fa-clock-o"></i>
                            <input type="number" name="updatestatus_interval" value="<?php echo htmlspecialchars($monitorInterval, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" min="1" step="1" inputmode="numeric" autocomplete="off">
                        </span>
                        <select class="form-control admin-order-monitor-interval__unit" name="updatestatus_interval_unit">
                            <option value="minute" <?php echo $monitorIntervalUnit === "minute" ? 'selected' : ''; ?>>分钟</option>
                            <option value="hour" <?php echo $monitorIntervalUnit === "hour" ? 'selected' : ''; ?>>小时</option>
                        </select>
                    </span>
                    <em>例如 10 分钟检测一次：填 10，并选择“分钟”。实际触发频率还取决于计划任务调用 cron 的频率。</em>
                </label>

                <div class="admin-order-monitor-actions">
                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> 保存设置
                    </button>
                    <button type="button" class="btn btn-default" id="runOrderMonitor" data-monitor-url="<?php echo htmlspecialchars($monitorCronUrl, ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="fa fa-play-circle"></i> 手动执行一次
                    </button>
                    <a href="./set.php?mod=cron" class="btn btn-link">
                        <i class="fa fa-link"></i> 查看监控地址
                    </a>
                </div>
            </form>

            <?php echo q8_render_action('admin_order_monitor_form_after', $monitorContext); ?>
        </section>

        <aside class="admin-order-monitor-guide">
            <div class="admin-order-monitor-guide__header">
                <span><i class="fa fa-shield"></i></span>
                <div>
                    <h3>使用说明</h3>
                    <p>这块是给以后自动化、插件和更多对接玩法留的控制面板。</p>
                </div>
            </div>

            <div class="admin-order-monitor-guide__list">
                <?php foreach ($monitorTips as $tip) {
                    $tipIcon = isset($tip['icon']) ? $tip['icon'] : 'fa-info-circle';
                    $tipTitle = isset($tip['title']) ? $tip['title'] : '扩展提示';
                    $tipText = isset($tip['text']) ? $tip['text'] : '';
                ?>
                <article class="admin-order-monitor-tip">
                    <i class="fa <?php echo htmlspecialchars($tipIcon, ENT_QUOTES, 'UTF-8'); ?>"></i>
                    <div>
                        <strong><?php echo htmlspecialchars($tipTitle, ENT_QUOTES, 'UTF-8'); ?></strong>
                        <p><?php echo htmlspecialchars($tipText, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </article>
                <?php } ?>
            </div>
        </aside>
    </div>

    <div class="modal fade admin-order-monitor-modal" id="showresult" tabindex="-1" role="dialog" aria-labelledby="orderMonitorModalTitle" aria-hidden="true">
        <div class="modal-dialog admin-order-monitor-modal__dialog">
            <div class="modal-content admin-order-monitor-modal__content">
                <div class="modal-header admin-order-monitor-modal__header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="关闭"><span aria-hidden="true">&times;</span></button>
                    <p>Manual Check</p>
                    <h4 class="modal-title" id="orderMonitorModalTitle">手动同步订单状态</h4>
                </div>
                <div class="modal-body admin-order-monitor-modal__body" id="result_content">
                    <div class="admin-order-monitor-modal__empty">
                        <i class="fa fa-play-circle"></i>
                        <span>点击“手动执行一次”后会在这里显示 cron 返回内容。</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php echo q8_render_action('admin_order_monitor_page_after', $monitorContext); ?>
</div>
</div>

<script src="assets/js/orderjk.js?v=<?php echo urlencode($orderMonitorAssetVersion); ?>"></script>
</body>
</html>
