<?php
/*
 * 自动补单设置页面
 * 功能：管理自动补单功能，设置补单参数、补单规则等
 */
include("../includes/common.php");

function createMissingTables() {
    global $DB;

    try {
        $goods_check = $DB->query("SELECT 1 FROM pre_goods LIMIT 1");
        if ($goods_check === false) {
            $DB->exec("CREATE TABLE IF NOT EXISTS `pre_goods` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `price` decimal(10,2) NOT NULL,
                `stock` int(11) NOT NULL DEFAULT '0',
                `sales` int(11) NOT NULL DEFAULT '0',
                `shequ_id` int(11) NOT NULL DEFAULT '0',
                `shequ_goods_id` varchar(255) NOT NULL DEFAULT '',
                `type` int(11) NOT NULL DEFAULT '0',
                `cid` int(11) NOT NULL DEFAULT '0',
                `active` int(11) NOT NULL DEFAULT '1',
                `addtime` datetime NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1");
        }

        $orders_check = $DB->query("SELECT reorder_times, last_reorder_time FROM pre_orders LIMIT 1");
        if ($orders_check === false) {
            $DB->exec("ALTER TABLE pre_orders ADD COLUMN reorder_times int(11) NOT NULL DEFAULT '0'");
            $DB->exec("ALTER TABLE pre_orders ADD COLUMN last_reorder_time datetime DEFAULT NULL");
        }
    } catch (Exception $e) {
        // 保持旧逻辑：表结构自动补齐失败时不阻断页面展示。
    }
}

if (!function_exists('q8_auto_reorder_escape')) {
    function q8_auto_reorder_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('q8_auto_reorder_int')) {
    function q8_auto_reorder_int($value, $min, $max)
    {
        $value = intval($value);
        if ($value < $min) {
            return $min;
        }
        if ($value > $max) {
            return $max;
        }
        return $value;
    }
}

if (!function_exists('q8_auto_reorder_json_response')) {
    function q8_auto_reorder_json_response($code, $msg, $extra = array())
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode(array_merge(array(
            'code' => intval($code),
            'msg' => $msg
        ), $extra), JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$is_ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || (isset($_POST['ajax']) && $_POST['ajax'] == 1);

$title = '自动补单设置';

if ($islogin != 1) {
    if ($is_ajax) {
        q8_auto_reorder_json_response(0, '请先登录');
    }
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission('order', $is_ajax ? 2 : 1);

createMissingTables();

if (!$is_ajax) {
    include './head.php';
}

$autoReorderAssetVersion = isset($adminAssetVersion) ? $adminAssetVersion : ((defined('VERSION') ? VERSION : '1.0.0') . '.20260426admin37');
$saveMessage = null;

$config_keys = apply_filters('admin_auto_reorder_config_keys', array(
    'autoreorder' => 0,
    'autoreorder_interval' => 5,
    'autoreorder_limit' => 50,
    'autoreorder_max_retries' => 3,
    'autoreorder_status' => '0,1,2',
    'autoreorder_after' => 10,
    'autoreorder_timeout' => 30
));

$autoreorder_config = array();
foreach ($config_keys as $key => $default) {
    $value = $DB->getColumn("SELECT v FROM pre_config WHERE k='{$key}'");
    $autoreorder_config[$key] = $value !== false ? $value : $default;
}

$statusOptions = apply_filters('admin_auto_reorder_status_options', array(
    '0' => array('label' => '待处理', 'desc' => '订单尚未开始处理，通常适合补单。', 'tone' => 'primary'),
    '2' => array('label' => '正在处理', 'desc' => '订单已进入处理流程，可按需复查补单。', 'tone' => 'accent'),
    '1' => array('label' => '已完成', 'desc' => '一般不建议补单，避免重复处理。', 'tone' => 'muted'),
    '3' => array('label' => '异常', 'desc' => '适合人工确认后再纳入自动补单。', 'tone' => 'danger'),
    '4' => array('label' => '已退单', 'desc' => '谨慎使用，可能影响售后记录。', 'tone' => 'warning'),
    '5' => array('label' => '退款中', 'desc' => '谨慎使用，避免和退款流程冲突。', 'tone' => 'warning'),
    'djzt2' => array('label' => '对接失败', 'desc' => '第三方下单失败时可尝试重新提交。', 'tone' => 'danger')
), $autoreorder_config);

if (!is_array($statusOptions)) {
    $statusOptions = array();
}

$validationRules = apply_filters('admin_auto_reorder_validation_rules', array(
    'autoreorder_interval' => array('min' => 1, 'max' => 1440, 'label' => '补单间隔'),
    'autoreorder_limit' => array('min' => 1, 'max' => 1000, 'label' => '每次补单数'),
    'autoreorder_max_retries' => array('min' => 1, 'max' => 10, 'label' => '最大重试次数'),
    'autoreorder_after' => array('min' => 0, 'max' => 1440, 'label' => '补单延迟'),
    'autoreorder_timeout' => array('min' => 5, 'max' => 1440, 'label' => '补单超时')
));

if (isset($_POST['submit'])) {
    $errors = array();
    $data = array(
        'autoreorder' => isset($_POST['autoreorder']) && (string)$_POST['autoreorder'] === '1' ? 1 : 0
    );

    foreach ($validationRules as $field => $rule) {
        $rawValue = isset($_POST[$field]) ? trim((string)$_POST[$field]) : '';
        if ($rawValue === '' || !preg_match('/^\d+$/', $rawValue)) {
            $errors[] = $rule['label'] . '必须填写整数';
            continue;
        }
        $value = intval($rawValue);
        if ($value < $rule['min'] || $value > $rule['max']) {
            $errors[] = $rule['label'] . '范围必须在 ' . $rule['min'] . ' 到 ' . $rule['max'] . ' 之间';
            continue;
        }
        $data[$field] = $value;
    }

    $postedStatuses = isset($_POST['autoreorder_status']) && is_array($_POST['autoreorder_status']) ? $_POST['autoreorder_status'] : array();
    $allowedStatuses = array_keys($statusOptions);
    $selectedStatuses = array();
    foreach ($postedStatuses as $status) {
        $status = (string)$status;
        if (in_array($status, $allowedStatuses, true) && !in_array($status, $selectedStatuses, true)) {
            $selectedStatuses[] = $status;
        }
    }

    if (empty($selectedStatuses)) {
        $errors[] = '至少选择一个补单订单状态';
    }

    $data['autoreorder_status'] = implode(',', $selectedStatuses);

    if (!empty($errors)) {
        $message = implode('；', $errors);
        if ($is_ajax) {
            q8_auto_reorder_json_response(0, $message);
        }
        $saveMessage = array('type' => 'danger', 'icon' => 'fa-exclamation-triangle', 'title' => '保存失败', 'text' => $message);
    } else {
        $error = '';
        $success = 0;
        foreach ($data as $key => $value) {
            if ($DB->exec("REPLACE INTO pre_config SET k='{$key}',v='{$value}'")) {
                $success++;
            } else {
                $error .= "保存{$key}失败 ";
            }
        }

        if ($error === '') {
            if (isset($CACHE)) {
                $CACHE->clear();
            }
            $autoreorder_config = array_merge($autoreorder_config, $data);
            do_action('admin_auto_reorder_settings_saved', $data);
            $message = "成功更新{$success}项配置";
            if ($is_ajax) {
                q8_auto_reorder_json_response(1, $message, array('config' => $data));
            }
            $saveMessage = array('type' => 'success', 'icon' => 'fa-check-circle', 'title' => '设置已保存', 'text' => $message);
        } else {
            if ($is_ajax) {
                q8_auto_reorder_json_response(0, $error);
            }
            $saveMessage = array('type' => 'danger', 'icon' => 'fa-exclamation-triangle', 'title' => '保存失败', 'text' => $error);
        }
    }
}

$last_reorder_time = $DB->getColumn("SELECT v FROM pre_config WHERE k='last_reorder_time'");
if (!$last_reorder_time) {
    $last_reorder_time = '从未运行';
}

$total_reordered = intval($DB->getColumn("SELECT COUNT(*) FROM pre_orders WHERE reorder_times > 0"));
$today_reordered = intval($DB->getColumn("SELECT COUNT(*) FROM pre_orders WHERE reorder_times > 0 AND addtime >= CURDATE()"));
$selectedStatusArray = array_filter(explode(',', (string)$autoreorder_config['autoreorder_status']), 'strlen');
$autoReorderEnabled = intval($autoreorder_config['autoreorder']) === 1;
$runUrl = apply_filters('admin_auto_reorder_run_url', './cron.php?action=reorder', $autoreorder_config);
$cronUrl = $siteurl . 'admin/cron.php';
$autoReorderContext = array(
    'config' => $autoreorder_config,
    'enabled' => $autoReorderEnabled,
    'last_reorder_time' => $last_reorder_time,
    'total_reordered' => $total_reordered,
    'today_reordered' => $today_reordered,
    'run_url' => $runUrl
);
$guideItems = apply_filters('admin_auto_reorder_guides', array(
    array('icon' => 'fa-clock-o', 'title' => '计划任务频率', 'text' => '建议服务器 cron 每 5 分钟调用一次，页面里的补单间隔负责控制是否真正执行。'),
    array('icon' => 'fa-random', 'title' => '状态范围要克制', 'text' => '优先选择待处理、正在处理或对接失败，已完成、退款类状态需要谨慎。'),
    array('icon' => 'fa-shield', 'title' => '防重复策略', 'text' => '最大重试次数和补单超时会一起限制重复提交，建议先保守设置。')
), $autoReorderContext);
?>
<link rel="stylesheet" href="./assets/css/admin-auto-reorder.css?v=<?php echo urlencode($autoReorderAssetVersion); ?>">

<div class="col-xs-12 admin-auto-reorder-page">
    <?php echo q8_render_action('admin_auto_reorder_page_before', $autoReorderContext); ?>

    <section class="admin-auto-reorder-hero">
        <div class="admin-auto-reorder-hero__content">
            <p class="admin-auto-reorder-hero__eyebrow">Auto Reorder</p>
            <h2>自动补单规则与手动触发</h2>
            <p>这一页只负责自动补单的开关、节奏和补单范围配置；实际补单仍由现有 <code>admin/cron.php</code> 执行，避免把设置页和业务执行逻辑混在一起。</p>
        </div>
        <div class="admin-auto-reorder-hero__status <?php echo $autoReorderEnabled ? 'is-on' : 'is-off'; ?>">
            <span class="admin-auto-reorder-hero__pulse"></span>
            <strong><?php echo $autoReorderEnabled ? '自动补单已开启' : '自动补单已关闭'; ?></strong>
            <small>间隔 <?php echo q8_auto_reorder_escape($autoreorder_config['autoreorder_interval']); ?> 分钟，每次最多 <?php echo q8_auto_reorder_escape($autoreorder_config['autoreorder_limit']); ?> 单</small>
        </div>
    </section>

    <div id="autoReorderMessage" class="admin-auto-reorder-alert<?php echo empty($saveMessage) ? ' is-hidden' : ' admin-auto-reorder-alert--' . q8_auto_reorder_escape($saveMessage['type']); ?>">
        <?php if (!empty($saveMessage)) { ?>
        <i class="fa <?php echo q8_auto_reorder_escape($saveMessage['icon']); ?>"></i>
        <div>
            <strong><?php echo q8_auto_reorder_escape($saveMessage['title']); ?></strong>
            <p><?php echo q8_auto_reorder_escape($saveMessage['text']); ?></p>
        </div>
        <?php } ?>
    </div>

    <section class="admin-auto-reorder-stats">
        <article class="admin-auto-reorder-stat">
            <span class="admin-auto-reorder-stat__icon admin-auto-reorder-stat__icon--primary"><i class="fa fa-history"></i></span>
            <div>
                <span>最近补单时间</span>
                <strong><?php echo q8_auto_reorder_escape($last_reorder_time); ?></strong>
            </div>
        </article>
        <article class="admin-auto-reorder-stat">
            <span class="admin-auto-reorder-stat__icon admin-auto-reorder-stat__icon--accent"><i class="fa fa-refresh"></i></span>
            <div>
                <span>总补单数</span>
                <strong><?php echo $total_reordered; ?></strong>
            </div>
        </article>
        <article class="admin-auto-reorder-stat">
            <span class="admin-auto-reorder-stat__icon admin-auto-reorder-stat__icon--warm"><i class="fa fa-calendar-check-o"></i></span>
            <div>
                <span>今日补单数</span>
                <strong><?php echo $today_reordered; ?></strong>
            </div>
        </article>
        <article class="admin-auto-reorder-stat admin-auto-reorder-stat--action">
            <button type="button" class="btn btn-primary" id="runAutoReorder" data-run-url="<?php echo q8_auto_reorder_escape($runUrl); ?>">
                <i class="fa fa-play-circle"></i> 立即补单
            </button>
            <small>手动触发会直接执行一次补单，请先确认配置。</small>
        </article>
    </section>

    <?php echo q8_render_action('admin_auto_reorder_stats_after', $autoReorderContext); ?>

    <div class="admin-auto-reorder-grid">
        <section class="block admin-auto-reorder-panel">
            <div class="block-title admin-auto-reorder-panel__title">
                <div>
                    <h3>补单参数</h3>
                    <p>参数范围保留原有执行逻辑，只把保存体验和状态选择整理得更清楚。</p>
                </div>
            </div>

            <?php echo q8_render_action('admin_auto_reorder_form_before', $autoReorderContext); ?>

            <form id="autoReorderForm" class="admin-auto-reorder-form" method="post" action="./set_autoreorder.php">
                <input type="hidden" name="submit" value="1">

                <div class="admin-auto-reorder-switch">
                    <span class="admin-auto-reorder-field__label">自动补单</span>
                    <label class="admin-auto-reorder-switch__option">
                        <input type="radio" name="autoreorder" value="1" <?php echo $autoReorderEnabled ? 'checked' : ''; ?>>
                        <span><i class="fa fa-toggle-on"></i> 开启</span>
                    </label>
                    <label class="admin-auto-reorder-switch__option">
                        <input type="radio" name="autoreorder" value="0" <?php echo !$autoReorderEnabled ? 'checked' : ''; ?>>
                        <span><i class="fa fa-toggle-off"></i> 关闭</span>
                    </label>
                </div>

                <div class="admin-auto-reorder-field-grid">
                    <label class="admin-auto-reorder-field">
                        <span class="admin-auto-reorder-field__label">补单间隔</span>
                        <span class="admin-auto-reorder-input">
                            <i class="fa fa-clock-o"></i>
                            <input type="number" name="autoreorder_interval" value="<?php echo q8_auto_reorder_escape($autoreorder_config['autoreorder_interval']); ?>" class="form-control" min="1" max="1440" required>
                            <em>分钟</em>
                        </span>
                        <small>两次自动补单之间的间隔，建议 5-15 分钟。</small>
                    </label>

                    <label class="admin-auto-reorder-field">
                        <span class="admin-auto-reorder-field__label">每次补单数</span>
                        <span class="admin-auto-reorder-input">
                            <i class="fa fa-list-ol"></i>
                            <input type="number" name="autoreorder_limit" value="<?php echo q8_auto_reorder_escape($autoreorder_config['autoreorder_limit']); ?>" class="form-control" min="1" max="1000" required>
                            <em>单</em>
                        </span>
                        <small>每轮最多处理多少订单，建议先保守设置。</small>
                    </label>

                    <label class="admin-auto-reorder-field">
                        <span class="admin-auto-reorder-field__label">最大重试次数</span>
                        <span class="admin-auto-reorder-input">
                            <i class="fa fa-repeat"></i>
                            <input type="number" name="autoreorder_max_retries" value="<?php echo q8_auto_reorder_escape($autoreorder_config['autoreorder_max_retries']); ?>" class="form-control" min="1" max="10" required>
                            <em>次</em>
                        </span>
                        <small>单个订单超过该次数后不再尝试补单。</small>
                    </label>

                    <label class="admin-auto-reorder-field">
                        <span class="admin-auto-reorder-field__label">补单延迟</span>
                        <span class="admin-auto-reorder-input">
                            <i class="fa fa-hourglass-half"></i>
                            <input type="number" name="autoreorder_after" value="<?php echo q8_auto_reorder_escape($autoreorder_config['autoreorder_after']); ?>" class="form-control" min="0" max="1440" required>
                            <em>分钟</em>
                        </span>
                        <small>订单创建多久后才允许进入补单范围。</small>
                    </label>

                    <label class="admin-auto-reorder-field">
                        <span class="admin-auto-reorder-field__label">补单超时</span>
                        <span class="admin-auto-reorder-input">
                            <i class="fa fa-ban"></i>
                            <input type="number" name="autoreorder_timeout" value="<?php echo q8_auto_reorder_escape($autoreorder_config['autoreorder_timeout']); ?>" class="form-control" min="5" max="1440" required>
                            <em>分钟</em>
                        </span>
                        <small>超过该时间范围的订单不再自动补单。</small>
                    </label>
                </div>

                <section class="admin-auto-reorder-status">
                    <div class="admin-auto-reorder-status__header">
                        <div>
                            <h3>补单订单状态</h3>
                            <p>这里的状态值已按订单列表实际含义重新标注。已完成、退款类状态建议谨慎选择。</p>
                        </div>
                    </div>
                    <div class="admin-auto-reorder-status__grid">
                        <?php foreach ($statusOptions as $statusValue => $statusMeta) {
                            $tone = isset($statusMeta['tone']) ? $statusMeta['tone'] : 'primary';
                            $label = isset($statusMeta['label']) ? $statusMeta['label'] : $statusValue;
                            $desc = isset($statusMeta['desc']) ? $statusMeta['desc'] : '';
                            $checked = in_array((string)$statusValue, $selectedStatusArray, true);
                        ?>
                        <label class="admin-auto-reorder-status-card admin-auto-reorder-status-card--<?php echo q8_auto_reorder_escape($tone); ?>">
                            <input type="checkbox" name="autoreorder_status[]" value="<?php echo q8_auto_reorder_escape($statusValue); ?>" <?php echo $checked ? 'checked' : ''; ?>>
                            <span class="admin-auto-reorder-status-card__check"><i class="fa fa-check"></i></span>
                            <span class="admin-auto-reorder-status-card__body">
                                <strong><?php echo q8_auto_reorder_escape($label); ?></strong>
                                <small><?php echo q8_auto_reorder_escape($desc); ?></small>
                            </span>
                        </label>
                        <?php } ?>
                    </div>
                </section>

                <section class="admin-auto-reorder-cron">
                    <div>
                        <h3><i class="fa fa-terminal"></i> 计划任务</h3>
                        <p>服务器计划任务建议每 5 分钟调用一次，真正是否补单由上方“补单间隔”控制。</p>
                    </div>
                    <code>*/5 * * * * curl --silent <?php echo q8_auto_reorder_escape($cronUrl); ?></code>
                </section>

                <div class="admin-auto-reorder-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa fa-save"></i> 保存配置
                    </button>
                    <a href="./list.php" class="btn btn-default btn-lg">
                        <i class="fa fa-list"></i> 查看订单列表
                    </a>
                </div>
            </form>

            <?php echo q8_render_action('admin_auto_reorder_form_after', $autoReorderContext); ?>
        </section>

        <aside class="admin-auto-reorder-guide">
            <div class="admin-auto-reorder-guide__header">
                <span><i class="fa fa-compass"></i></span>
                <div>
                    <h3>配置建议</h3>
                    <p>先用保守参数跑稳定，再逐步扩大处理范围。</p>
                </div>
            </div>
            <div class="admin-auto-reorder-guide__list">
                <?php foreach ($guideItems as $item) {
                    $icon = isset($item['icon']) ? $item['icon'] : 'fa-info-circle';
                    $title = isset($item['title']) ? $item['title'] : '提示';
                    $text = isset($item['text']) ? $item['text'] : '';
                ?>
                <article class="admin-auto-reorder-tip">
                    <i class="fa <?php echo q8_auto_reorder_escape($icon); ?>"></i>
                    <div>
                        <strong><?php echo q8_auto_reorder_escape($title); ?></strong>
                        <p><?php echo q8_auto_reorder_escape($text); ?></p>
                    </div>
                </article>
                <?php } ?>
            </div>
            <div id="autoReorderRunResult" class="admin-auto-reorder-run-result" hidden></div>
        </aside>
    </div>

    <?php echo q8_render_action('admin_auto_reorder_page_after', $autoReorderContext); ?>
</div>
</div>
</div>

<script src="assets/js/set_autoreorder.js?v=<?php echo urlencode($autoReorderAssetVersion); ?>"></script>
</body>
</html>
