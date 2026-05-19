<?php
include '../includes/common.php';
$title = html_entity_decode('&#28040;&#24687;&#32676;&#21457;', ENT_QUOTES, 'UTF-8');

if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission('site', 1);

if (!function_exists('q8_admin_message_escape')) {
    function q8_admin_message_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('q8_admin_message_json')) {
    function q8_admin_message_json($value)
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
    }
}

if (!function_exists('q8_admin_message_scope_labels_page')) {
    function q8_admin_message_scope_labels_page()
    {
        if (function_exists('q8_get_message_scope_labels')) {
            return q8_get_message_scope_labels();
        }
        return array(
            0 => html_entity_decode('&#20840;&#37096;&#29992;&#25143;', ENT_QUOTES, 'UTF-8'),
            1 => html_entity_decode('&#20840;&#37096;&#26222;&#36890;&#29992;&#25143;', ENT_QUOTES, 'UTF-8'),
            2 => html_entity_decode('&#20840;&#37096;&#20998;&#31449;&#31449;&#38271;', ENT_QUOTES, 'UTF-8'),
            3 => html_entity_decode('&#26222;&#21450;&#29256;&#20998;&#31449;&#31449;&#38271;', ENT_QUOTES, 'UTF-8'),
            4 => html_entity_decode('&#19987;&#19994;&#29256;&#20998;&#31449;&#31449;&#38271;', ENT_QUOTES, 'UTF-8'),
            5 => html_entity_decode('&#20027;&#31449;&#26222;&#36890;&#29992;&#25143;', ENT_QUOTES, 'UTF-8'),
            6 => html_entity_decode('&#20998;&#31449;&#19979;&#32423;&#26222;&#36890;&#29992;&#25143;', ENT_QUOTES, 'UTF-8'),
        );
    }
}

if (!function_exists('q8_admin_message_scope_text_page')) {
    function q8_admin_message_scope_text_page($scope)
    {
        $labels = q8_admin_message_scope_labels_page();
        $scope = intval($scope);
        return isset($labels[$scope]) ? $labels[$scope] : $labels[0];
    }
}

if (!function_exists('q8_admin_message_status_text_page')) {
    function q8_admin_message_status_text_page($status)
    {
        $status = intval($status);
        if ($status === 1) {
            return html_entity_decode('&#21457;&#36865;&#20013;', ENT_QUOTES, 'UTF-8');
        }
        if ($status === 2) {
            return html_entity_decode('&#24050;&#23436;&#25104;', ENT_QUOTES, 'UTF-8');
        }
        return html_entity_decode('&#24453;&#21457;&#36865;', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('q8_admin_message_status_class_page')) {
    function q8_admin_message_status_class_page($task)
    {
        $status = intval($task['status']);
        $failCount = intval($task['fail_count']);
        $pendingCount = max(0, intval($task['total_count']) - intval($task['success_count']) - $failCount);

        if ($status === 1 || $pendingCount > 0) {
            return 'is-running';
        }
        if ($failCount > 0) {
            return 'is-warning';
        }
        return 'is-success';
    }
}

if (!function_exists('q8_admin_message_notice_status_text_page')) {
    function q8_admin_message_notice_status_text_page($active)
    {
        return intval($active) === 1 ? html_entity_decode('&#26174;&#31034;&#20013;', ENT_QUOTES, 'UTF-8') : html_entity_decode('&#24050;&#38544;&#34255;', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('q8_admin_message_mail_ready_page')) {
    function q8_admin_message_mail_ready_page()
    {
        global $conf;
        if (intval($conf['mail_cloud']) === 1 || intval($conf['mail_cloud']) === 2) {
            return !empty($conf['mail_apiuser']) && !empty($conf['mail_apikey']);
        }
        return !empty($conf['mail_name']) && !empty($conf['mail_port']) && !empty($conf['mail_smtp']) && !empty($conf['mail_pwd']);
    }
}

if (!function_exists('q8_admin_message_mail_channel_text_page')) {
    function q8_admin_message_mail_channel_text_page()
    {
        global $conf;
        $channel = intval($conf['mail_cloud']);
        if ($channel === 1) {
            return html_entity_decode('SendCloud &#25509;&#21475;', ENT_QUOTES, 'UTF-8');
        }
        if ($channel === 2) {
            return html_entity_decode('&#37038;&#20214;&#25509;&#21475;', ENT_QUOTES, 'UTF-8');
        }
        return html_entity_decode('SMTP &#36890;&#36947;', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('q8_admin_message_fetch_scope_counts_page')) {
    function q8_admin_message_fetch_scope_counts_page()
    {
        if (function_exists('q8_mail_center_scope_counts')) {
            return q8_mail_center_scope_counts();
        }

        global $DB;
        $counts = array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0);
        $baseWhere = "status=1 AND qq REGEXP '^[1-9][0-9]{4,12}$'";
        $counts[0] = intval($DB->getColumn("SELECT COUNT(*) FROM pre_site WHERE {$baseWhere}"));
        $counts[1] = intval($DB->getColumn("SELECT COUNT(*) FROM pre_site WHERE {$baseWhere} AND power=0"));
        $counts[2] = intval($DB->getColumn("SELECT COUNT(*) FROM pre_site WHERE {$baseWhere} AND power>0"));
        $counts[3] = intval($DB->getColumn("SELECT COUNT(*) FROM pre_site WHERE {$baseWhere} AND power=1"));
        $counts[4] = intval($DB->getColumn("SELECT COUNT(*) FROM pre_site WHERE {$baseWhere} AND power>1"));
        $counts[5] = intval($DB->getColumn("SELECT COUNT(*) FROM pre_site WHERE {$baseWhere} AND power=0 AND upzid<=0"));
        $counts[6] = intval($DB->getColumn("SELECT COUNT(*) FROM pre_site WHERE {$baseWhere} AND power=0 AND upzid>0"));
        return $counts;
    }
}

if (!function_exists('q8_admin_message_fetch_stats_page')) {
    function q8_admin_message_fetch_stats_page($DB, $scopeCounts)
    {
        $taskTable = 'pre_mail_task';
        $itemTable = 'pre_mail_task_item';
        return array(
            'valid_total' => intval(isset($scopeCounts[0]) ? $scopeCounts[0] : 0),
            'valid_users' => intval(isset($scopeCounts[1]) ? $scopeCounts[1] : 0),
            'valid_sites' => intval(isset($scopeCounts[2]) ? $scopeCounts[2] : 0),
            'task_total' => intval($DB->getColumn("SELECT COUNT(*) FROM `{$taskTable}`")),
            'task_today' => intval($DB->getColumn("SELECT COUNT(*) FROM `{$taskTable}` WHERE DATE(addtime)=CURDATE()")),
            'success_today' => intval($DB->getColumn("SELECT COUNT(*) FROM `{$itemTable}` WHERE status=1 AND DATE(sent_at)=CURDATE()")),
            'pending_total' => intval($DB->getColumn("SELECT COUNT(*) FROM `{$itemTable}` WHERE status=0")),
            'fail_total' => intval($DB->getColumn("SELECT COUNT(*) FROM `{$itemTable}` WHERE status=2")),
            'notice_total' => intval($DB->getColumn("SELECT COUNT(*) FROM pre_message")),
            'notice_active' => intval($DB->getColumn("SELECT COUNT(*) FROM pre_message WHERE active=1")),
        );
    }
}

if (!function_exists('q8_admin_message_fetch_tasks_page')) {
    function q8_admin_message_fetch_tasks_page($DB, $limit = 12)
    {
        $taskTable = 'pre_mail_task';
        return $DB->getAll("SELECT * FROM `{$taskTable}` ORDER BY id DESC LIMIT " . intval($limit));
    }
}

if (!function_exists('q8_admin_message_fetch_notices_page')) {
    function q8_admin_message_fetch_notices_page($DB, $limit = 20)
    {
        return $DB->getAll("SELECT id,type,title,content,addtime,count,active FROM pre_message ORDER BY id DESC LIMIT " . intval($limit));
    }
}

if (!function_exists('q8_admin_message_notice_summary_page')) {
    function q8_admin_message_notice_summary_page($content)
    {
        $content = trim(preg_replace('/\s+/', ' ', strip_tags((string)$content)));
        if ($content === '') {
            return html_entity_decode('&#26242;&#26080;&#20869;&#23481;', ENT_QUOTES, 'UTF-8');
        }
        if (function_exists('mb_substr')) {
            $summary = mb_substr($content, 0, 120, 'UTF-8');
            return $summary !== $content ? $summary . '...' : $summary;
        }
        $summary = substr($content, 0, 120);
        return strlen($content) > 120 ? $summary . '...' : $summary;
    }
}

if (!function_exists('q8_admin_message_task_payload_page')) {
    function q8_admin_message_task_payload_page($task)
    {
        if (!$task) {
            return null;
        }

        $total = intval($task['total_count']);
        $success = intval($task['success_count']);
        $fail = intval($task['fail_count']);
        $pending = max(0, $total - $success - $fail);
        $targetMode = intval(isset($task['target_mode']) ? $task['target_mode'] : 0);
        $targetValue = trim((string)(isset($task['target_value']) ? $task['target_value'] : ''));
        $targetLabel = trim((string)(isset($task['target_label']) ? $task['target_label'] : ''));
        $scopeText = q8_admin_message_scope_text_page($task['scope']);

        if ($targetMode === 1) {
            $specificScope = html_entity_decode('&#25351;&#23450;&#29992;&#25143;', ENT_QUOTES, 'UTF-8');
            if ($targetLabel !== '') {
                $scopeText = $specificScope . ' | ' . $targetLabel;
            } elseif ($targetValue !== '') {
                $scopeText = $specificScope . ' | ' . $targetValue;
            } else {
                $scopeText = $specificScope;
            }
        }

        return array(
            'id' => intval($task['id']),
            'scope' => intval($task['scope']),
            'scope_text' => $scopeText,
            'target_mode' => $targetMode,
            'target_value' => $targetValue,
            'target_label' => $targetLabel,
            'subject' => (string)$task['subject'],
            'sync_notice' => intval($task['sync_notice']),
            'notice_id' => intval($task['notice_id']),
            'total_count' => $total,
            'success_count' => $success,
            'fail_count' => $fail,
            'pending_count' => $pending,
            'status' => intval($task['status']),
            'status_text' => q8_admin_message_status_text_page($task['status']),
            'last_error' => (string)$task['last_error'],
            'creator' => (string)$task['creator'],
            'addtime' => (string)$task['addtime'],
            'starttime' => (string)$task['starttime'],
            'endtime' => (string)$task['endtime'],
        );
    }
}

if (!function_exists('q8_admin_message_progress_percent_page')) {
    function q8_admin_message_progress_percent_page($task)
    {
        $total = max(0, intval($task['total_count']));
        if ($total < 1) {
            return 0;
        }
        $finished = intval($task['success_count']) + intval($task['fail_count']);
        $percent = (int)round(($finished / $total) * 100);
        if ($percent < 0) {
            return 0;
        }
        if ($percent > 100) {
            return 100;
        }
        return $percent;
    }
}

q8_mail_center_ensure_tables();

$scopeLabels = q8_admin_message_scope_labels_page();
$scopeCounts = q8_admin_message_fetch_scope_counts_page();
$messageStats = q8_admin_message_fetch_stats_page($DB, $scopeCounts);
$taskRows = q8_admin_message_fetch_tasks_page($DB, 12);
$recentTasks = array();
foreach ($taskRows as $taskRow) {
    $recentTasks[] = q8_admin_message_task_payload_page($taskRow);
}
$recentNotices = q8_admin_message_fetch_notices_page($DB, 20);
$mailReady = q8_admin_message_mail_ready_page();
$senderName = q8_mail_sender_name();
$mailChannelText = q8_admin_message_mail_channel_text_page();

$messageContext = apply_filters('admin_message_center_context', array(
    'stats' => $messageStats,
    'scope_counts' => $scopeCounts,
    'mail_ready' => $mailReady,
    'sender_name' => $senderName,
    'channel' => $mailChannelText,
    'tasks' => $recentTasks,
    'notices' => $recentNotices,
));

include './head.php';
$messageAssetVersion = isset($adminAssetVersion) ? $adminAssetVersion : ((defined('VERSION') ? VERSION : '1.0.0') . '.20260428admin47');
?>
<link rel="stylesheet" href="./assets/css/admin-message-center.css?v=<?php echo urlencode($messageAssetVersion); ?>">

<div class="col-xs-12 admin-message-page">
    <?php echo q8_render_action('admin_message_center_page_before', $messageContext); ?>

    <section class="admin-message-hero">
        <div class="admin-message-hero__content">
            <p class="admin-message-hero__eyebrow"><?php echo html_entity_decode('&#28040;&#24687;&#20013;&#24515;', ENT_QUOTES, 'UTF-8'); ?></p>
            <h2>统一管理邮件群发、指定用户提醒和站内通知归档</h2>
            <p>这一页只处理消息触达链路，不改用户业务数据。收件地址默认按用户填写的 QQ 自动拼成 <code>QQ@qq.com</code>，并记录每位收件人的发送结果。</p>
        </div>
        <div class="admin-message-hero__aside">
            <div class="admin-message-hero__chip">
                <i class="fa fa-envelope"></i>
                <span><?php echo q8_admin_message_escape($mailChannelText); ?></span>
            </div>
            <div class="admin-message-hero__chip">
                <i class="fa fa-id-badge"></i>
                <span><?php echo q8_admin_message_escape($senderName); ?></span>
            </div>
            <a href="./set.php?mod=mail" class="admin-message-hero__chip admin-message-hero__chip--link">
                <i class="fa fa-cog"></i>
                <span>邮箱与提醒配置</span>
            </a>
            <a href="./userlist.php" class="admin-message-hero__chip admin-message-hero__chip--link">
                <i class="fa fa-users"></i>
                <span>用户与分站列表</span>
            </a>
        </div>
    </section>

    <?php if (!$mailReady) { ?>
    <section class="admin-message-alert">
        <i class="fa fa-warning"></i>
        <div>
            <strong>当前邮箱配置还不完整</strong>
            <p>请先到“邮箱与提醒配置”补齐 SMTP 或邮件推送参数，完成后这里就可以直接创建群发任务。</p>
        </div>
        <a href="./set.php?mod=mail" class="admin-message-alert__link">去完成配置</a>
    </section>
    <?php } ?>

    <section class="admin-message-stats">
        <article class="admin-message-stat">
            <span class="admin-message-stat__icon admin-message-stat__icon--primary"><i class="fa fa-at"></i></span>
            <div>
                <span>可用 QQ 邮箱收件人</span>
                <strong><?php echo intval($messageStats['valid_total']); ?></strong>
                <small>主站普通用户 <?php echo intval(isset($scopeCounts[5]) ? $scopeCounts[5] : 0); ?> · 分站下级普通用户 <?php echo intval(isset($scopeCounts[6]) ? $scopeCounts[6] : 0); ?></small>
            </div>
        </article>
        <article class="admin-message-stat">
            <span class="admin-message-stat__icon admin-message-stat__icon--success"><i class="fa fa-paper-plane"></i></span>
            <div>
                <span>今日发送成功</span>
                <strong><?php echo intval($messageStats['success_today']); ?></strong>
                <small>今日新建任务 <?php echo intval($messageStats['task_today']); ?> 个</small>
            </div>
        </article>
        <article class="admin-message-stat">
            <span class="admin-message-stat__icon admin-message-stat__icon--warning"><i class="fa fa-hourglass-half"></i></span>
            <div>
                <span>待发送队列</span>
                <strong><?php echo intval($messageStats['pending_total']); ?></strong>
                <small>失败项支持重试，不影响已成功部分</small>
            </div>
        </article>
        <article class="admin-message-stat">
            <span class="admin-message-stat__icon admin-message-stat__icon--danger"><i class="fa fa-bell-o"></i></span>
            <div>
                <span>站内通知</span>
                <strong><?php echo intval($messageStats['notice_total']); ?></strong>
                <small>当前显示 <?php echo intval($messageStats['notice_active']); ?> 条</small>
            </div>
        </article>
    </section>

    <section class="admin-message-layout">
        <div class="admin-message-compose">
            <div class="admin-message-panel__title">
                <div>
                    <h3>创建邮件任务</h3>
                    <p>支持按范围群发，也支持按用户 UID、账号或 QQ 精准发送。指定单个用户发送时，不会同步成全站站内通知。</p>
                </div>
                <div class="admin-message-panel__meta" id="messageComposeMeta"><?php echo $mailReady ? '邮箱通道可用，可直接创建并发送任务。' : '邮箱配置不完整，暂时只能查看历史任务。'; ?></div>
            </div>

            <form id="messageComposeForm" class="admin-message-form">
                <label class="admin-message-field">
                    <span>邮件主题</span>
                    <input type="text" class="form-control" name="subject" maxlength="255" placeholder="例如：活动通知、维护提醒、库存更新提醒">
                </label>

                <div class="admin-message-recipient-mode" role="tablist" aria-label="发送模式">
                    <button type="button" class="admin-message-recipient-mode__button is-active" data-message-mode="scope">
                        <i class="fa fa-sitemap"></i> 按范围群发
                    </button>
                    <button type="button" class="admin-message-recipient-mode__button" data-message-mode="single">
                        <i class="fa fa-user"></i> 指定单个用户
                    </button>
                </div>
                <input type="hidden" name="recipient_mode" value="scope" id="messageRecipientModeInput">

                <div class="admin-message-field-grid">
                    <label class="admin-message-field" id="messageScopeWrap">
                        <span>发送范围</span>
                        <select class="form-control" name="scope" id="messageScope">
                            <?php foreach ($scopeLabels as $scopeValue => $scopeLabel) { ?>
                            <option value="<?php echo intval($scopeValue); ?>"><?php echo q8_admin_message_escape($scopeLabel); ?>（<?php echo intval(isset($scopeCounts[$scopeValue]) ? $scopeCounts[$scopeValue] : 0); ?>）</option>
                            <?php } ?>
                        </select>
                        <em class="admin-message-field__hint">系统按当前有效 QQ 自动生成收件地址，不会给无效 QQ 发信。</em>
                    </label>

                    <label class="admin-message-field admin-message-field--hidden" id="messageTargetWrap">
                        <span>指定用户</span>
                        <input type="text" class="form-control" name="target_value" id="messageTargetValue" placeholder="输入用户 UID、账号或 QQ">
                        <em class="admin-message-field__hint">支持精确匹配 UID、账号或 QQ。指定单人发送时会自动关闭站内通知同步。</em>
                    </label>

                    <label class="admin-message-field admin-message-field--checkbox">
                        <span>同步到站内通知</span>
                        <input type="checkbox" name="sync_notice" value="1" checked id="messageSyncNotice">
                        <em id="messageSyncNoticeHint">勾选后会同步生成一条同范围的站内通知，主站普通用户与分站下级用户范围已拆分，避免串线。</em>
                    </label>
                </div>

                <div class="admin-message-target-preview" id="messageTargetPreview" hidden>
                    <div class="admin-message-target-preview__icon"><i class="fa fa-search"></i></div>
                    <div class="admin-message-target-preview__body">
                        <strong>输入用户 UID、账号或 QQ 后会在这里显示匹配结果</strong>
                        <p>例如：206554、wx1337、2081779218</p>
                    </div>
                </div>

                <label class="admin-message-field">
                    <span>邮件内容</span>
                    <textarea class="form-control admin-message-field__textarea" name="content" rows="12" placeholder="支持普通文本和基础 HTML 内容。若只输入纯文本，系统会自动保留换行。"></textarea>
                </label>

                <div class="admin-message-form__footer">
                    <div class="admin-message-form__tips">
                        <p><i class="fa fa-info-circle"></i> 发件人名称：<?php echo q8_admin_message_escape($senderName); ?></p>
                        <p><i class="fa fa-info-circle"></i> 收件地址规则：用户 QQ + <code>@qq.com</code></p>
                    </div>
                    <div class="admin-message-form__actions">
                        <button type="button" class="admin-message-button admin-message-button--primary" data-message-submit="send" <?php echo $mailReady ? '' : 'disabled'; ?>>
                            <i class="fa fa-paper-plane"></i> 创建并开始发送
                        </button>
                        <button type="button" class="admin-message-button admin-message-button--light" data-message-submit="queue" <?php echo $mailReady ? '' : 'disabled'; ?>>
                            <i class="fa fa-save"></i> 仅创建任务
                        </button>
                        <button type="reset" class="admin-message-button admin-message-button--ghost" id="messageComposeReset">
                            <i class="fa fa-undo"></i> 清空内容
                        </button>
                    </div>
                </div>
            </form>

            <div class="admin-message-scope-board">
                <?php foreach ($scopeLabels as $scopeValue => $scopeLabel) { ?>
                <article class="admin-message-scope-board__item">
                    <strong><?php echo q8_admin_message_escape($scopeLabel); ?></strong>
                    <span><?php echo intval(isset($scopeCounts[$scopeValue]) ? $scopeCounts[$scopeValue] : 0); ?> 个可发用户</span>
                </article>
                <?php } ?>
            </div>
        </div>

        <div class="admin-message-tasks">
            <div class="admin-message-panel__title">
                <div>
                    <h3>发送任务</h3>
                    <p>创建后会记录所有收件人的发送状态，支持继续发送、失败重试和查看前 200 条明细。</p>
                </div>
                <div class="admin-message-panel__meta" id="messageTaskMeta">最近保留 12 个任务概览</div>
            </div>

            <div class="admin-message-task-table-wrap">
                <table class="table admin-message-task-table">
                    <thead>
                        <tr>
                            <th>任务</th>
                            <th>进度</th>
                            <th>状态</th>
                            <th>时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="messageTaskTableBody">
                        <?php if (empty($recentTasks)) { ?>
                        <tr class="admin-message-empty-row" data-message-empty-row>
                            <td colspan="5">
                                <div class="admin-message-empty">
                                    <i class="fa fa-inbox"></i>
                                    <strong>还没有创建过邮件任务</strong>
                                    <p>先在左侧填好主题和内容，系统会自动生成发送明细并记录结果。</p>
                                </div>
                            </td>
                        </tr>
                        <?php } else { ?>
                        <?php foreach ($recentTasks as $task) { ?>
                        <?php
                            $progressPercent = q8_admin_message_progress_percent_page($task);
                            $statusClass = q8_admin_message_status_class_page($task);
                            $timeText = '尚未开始';
                            if (!empty($task['endtime'])) {
                                $timeText = '完成：' . $task['endtime'];
                            } elseif (!empty($task['starttime'])) {
                                $timeText = '开始：' . $task['starttime'];
                            }
                        ?>
                        <tr class="admin-message-task-row" data-mail-task-id="<?php echo intval($task['id']); ?>">
                            <td>
                                <div class="admin-message-task__subject">
                                    <strong><?php echo q8_admin_message_escape($task['subject']); ?></strong>
                                    <div class="admin-message-task__subline">
                                        <span><?php echo q8_admin_message_escape($task['scope_text']); ?></span>
                                        <span><?php echo intval($task['total_count']); ?> 人</span>
                                        <?php if (intval($task['sync_notice']) === 1) { ?><span>已同步通知</span><?php } ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="admin-message-task__progress">
                                    <div class="admin-message-task__progress-track">
                                        <div class="admin-message-task__progress-fill" data-progress-fill="<?php echo intval($progressPercent); ?>"></div>
                                    </div>
                                    <div class="admin-message-task__progress-meta">
                                        <span>成功 <?php echo intval($task['success_count']); ?></span>
                                        <span>失败 <?php echo intval($task['fail_count']); ?></span>
                                        <span>待发 <?php echo intval($task['pending_count']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="admin-message-task__status <?php echo q8_admin_message_escape($statusClass); ?>"><?php echo q8_admin_message_escape($task['status_text']); ?></span>
                                <?php if (!empty($task['last_error'])) { ?>
                                <div class="admin-message-task__error"><?php echo q8_admin_message_escape($task['last_error']); ?></div>
                                <?php } ?>
                            </td>
                            <td>
                                <div class="admin-message-task__time">
                                    <span>创建：<?php echo q8_admin_message_escape($task['addtime']); ?></span>
                                    <span><?php echo q8_admin_message_escape($timeText); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="admin-message-task__actions">
                                    <button type="button" class="admin-message-mini-button" data-message-action="view" data-task-id="<?php echo intval($task['id']); ?>">
                                        <i class="fa fa-eye"></i> 明细
                                    </button>
                                    <?php if (intval($task['pending_count']) > 0) { ?>
                                    <button type="button" class="admin-message-mini-button admin-message-mini-button--primary" data-message-action="run" data-task-id="<?php echo intval($task['id']); ?>">
                                        <i class="fa fa-play"></i> 继续发送
                                    </button>
                                    <?php } ?>
                                    <?php if (intval($task['fail_count']) > 0) { ?>
                                    <button type="button" class="admin-message-mini-button admin-message-mini-button--warning" data-message-action="retry" data-task-id="<?php echo intval($task['id']); ?>">
                                        <i class="fa fa-refresh"></i> 重试失败
                                    </button>
                                    <?php } ?>
                                    <?php if (intval($task['notice_id']) > 0) { ?>
                                    <button type="button" class="admin-message-mini-button" data-message-action="notice" data-notice-id="<?php echo intval($task['notice_id']); ?>">
                                        <i class="fa fa-commenting"></i> 通知
                                    </button>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="admin-message-notices">
        <div class="admin-message-panel__title">
            <div>
                <h3>站内通知归档</h3>
                <p>这里会显示系统当前已有的站内通知。无论是邮件任务同步生成的，还是你之前单独发过的通知，都会集中显示在这里。</p>
            </div>
            <div class="admin-message-panel__meta">当前共 <?php echo intval($messageStats['notice_total']); ?> 条通知</div>
        </div>

        <div class="admin-message-notices__list" id="messageNoticeList">
            <?php if (empty($recentNotices)) { ?>
            <div class="admin-message-empty">
                <i class="fa fa-bell-o"></i>
                <strong>暂时还没有通知记录</strong>
                <p>勾选“同步到站内通知”后，新建邮件任务时就会自动在这里留下对应通知。</p>
            </div>
            <?php } else { ?>
            <?php foreach ($recentNotices as $notice) { ?>
            <?php
                $noticeScope = q8_admin_message_scope_text_page($notice['type']);
                $noticeActive = intval($notice['active']) === 1;
            ?>
            <article class="admin-message-notice-card" data-message-notice-id="<?php echo intval($notice['id']); ?>" data-message-notice-active="<?php echo $noticeActive ? '1' : '0'; ?>">
                <div class="admin-message-notice-card__head">
                    <div>
                        <h4><?php echo q8_admin_message_escape($notice['title']); ?></h4>
                        <p><?php echo q8_admin_message_escape($noticeScope); ?> · 已读 <?php echo intval($notice['count']); ?> · <?php echo q8_admin_message_escape($notice['addtime']); ?></p>
                    </div>
                    <span class="admin-message-notice-card__status <?php echo $noticeActive ? 'is-success' : 'is-muted'; ?>" data-message-notice-status><?php echo q8_admin_message_escape(q8_admin_message_notice_status_text_page($notice['active'])); ?></span>
                </div>
                <div class="admin-message-notice-card__summary"><?php echo q8_admin_message_escape(q8_admin_message_notice_summary_page($notice['content'])); ?></div>
                <div class="admin-message-notice-card__actions">
                    <button type="button" class="admin-message-mini-button" data-message-action="notice-view" data-notice-id="<?php echo intval($notice['id']); ?>">
                        <i class="fa fa-eye"></i> 查看
                    </button>
                    <button type="button" class="admin-message-mini-button admin-message-mini-button--primary" data-message-action="notice-edit" data-notice-id="<?php echo intval($notice['id']); ?>">
                        <i class="fa fa-pencil"></i> 编辑
                    </button>
                    <button type="button" class="admin-message-mini-button <?php echo $noticeActive ? 'admin-message-mini-button--warning' : 'admin-message-mini-button--primary'; ?>" data-message-action="notice-toggle" data-notice-id="<?php echo intval($notice['id']); ?>" data-next-active="<?php echo $noticeActive ? '0' : '1'; ?>">
                        <i class="fa <?php echo $noticeActive ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                        <?php echo $noticeActive ? '隐藏' : '显示'; ?>
                    </button>
                </div>
            </article>
            <?php } ?>
            <?php } ?>
        </div>
    </section>

    <?php echo q8_render_action('admin_message_center_page_after', $messageContext); ?>
</div>

<script>
window.messageCenterConfig = <?php echo q8_admin_message_json(array(
    'mailReady' => $mailReady ? 1 : 0,
    'scopeCounts' => $scopeCounts,
    'endpoints' => array(
        'create' => './ajax.php?act=messageTaskCreate',
        'run' => './ajax.php?act=messageTaskRun',
        'items' => './ajax.php?act=messageTaskItems',
        'retry' => './ajax.php?act=messageTaskRetry',
        'getNotice' => './ajax.php?act=messageNoticeDetail',
        'saveNotice' => './ajax.php?act=messageNoticeSave',
        'toggleNotice' => './ajax.php?act=messageNoticeToggle',
        'lookup' => './ajax.php?act=messageRecipientLookup',
    ),
)); ?>;
</script>
<script src="./assets/js/message-center.js?v=<?php echo urlencode($messageAssetVersion); ?>"></script>
