<?php
if (defined('CHAT_WIDGET_LOADED')) {
    return;
}
define('CHAT_WIDGET_LOADED', 1);

if (!isset($conf) || empty($conf['chat_enable']) || intval($conf['chat_enable']) !== 1) {
    return;
}

function q8_chat_hex_color($value, $fallback)
{
    $value = trim((string)$value);
    return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? $value : $fallback;
}

function q8_chat_fa_icon($value)
{
    $value = trim((string)$value);
    if ($value === '') {
        return 'fa-comments';
    }
    $value = preg_replace('/[^a-zA-Z0-9_\\-\\s]/', '', $value);
    $parts = preg_split('/\\s+/', $value);
    foreach ($parts as $part) {
        if (strpos($part, 'fa-') === 0) {
            return $part;
        }
    }
    return 'fa-' . ltrim($parts[0], '-');
}

function q8_chat_url($value)
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }
    if (preg_match('/^https?:\\/\\//i', $value) || preg_match('/^tencent:\\/\\//i', $value)) {
        return $value;
    }
    return '';
}

$chatMode = isset($conf['chat_mode']) ? trim((string)$conf['chat_mode']) : 'link';
$allowedModes = array('link', 'qq', 'chatwoot', 'custom');
if (!in_array($chatMode, $allowedModes, true)) {
    return;
}

if ($chatMode === 'custom') {
    echo isset($conf['chat_custom_code']) ? $conf['chat_custom_code'] : '';
    return;
}

$buttonText = trim((string)($conf['chat_button_text'] ?? '在线客服'));
$buttonTitle = trim((string)($conf['chat_title'] ?? $buttonText));
$buttonIcon = q8_chat_fa_icon($conf['chat_button_icon'] ?? 'fa-comments');
$buttonColor = q8_chat_hex_color($conf['chat_btn_color'] ?? '#2196F3', '#2196F3');
$buttonTextColor = q8_chat_hex_color($conf['chat_button_text_color'] ?? '#FFFFFF', '#FFFFFF');
$buttonPosition = in_array(($conf['chat_position'] ?? 'right-bottom'), array('right-bottom', 'left-bottom', 'right-middle', 'left-middle'), true) ? $conf['chat_position'] : 'right-bottom';
$offsetX = max(0, intval($conf['chat_offset_x'] ?? 24));
$offsetY = max(0, intval($conf['chat_offset_y'] ?? 24));
$mobileShow = !isset($conf['chat_mobile_show']) || intval($conf['chat_mobile_show']) === 1;
$openNew = !isset($conf['chat_open_new']) || intval($conf['chat_open_new']) === 1;
$buttonUrl = '';
$chatwootBaseUrl = '';
$chatwootToken = '';

if ($chatMode === 'link') {
    $buttonUrl = q8_chat_url($conf['chat_link_url'] ?? '');
    if ($buttonUrl === '') {
        return;
    }
} elseif ($chatMode === 'qq') {
    $qq = preg_replace('/\\D+/', '', (string)($conf['chat_qq'] ?? ''));
    if ($qq === '') {
        return;
    }
    $buttonUrl = 'tencent://message/?uin=' . rawurlencode($qq) . '&Site=&Menu=yes';
} elseif ($chatMode === 'chatwoot') {
    $chatwootBaseUrl = rtrim(q8_chat_url($conf['chatwoot_base_url'] ?? ''), '/');
    $chatwootToken = trim((string)($conf['chatwoot_website_token'] ?? ''));
    if ($chatwootBaseUrl === '' || $chatwootToken === '') {
        return;
    }
}

$positionStyle = '';
if ($buttonPosition === 'left-bottom') {
    $positionStyle = 'left:' . $offsetX . 'px;bottom:' . $offsetY . 'px;';
} elseif ($buttonPosition === 'right-middle') {
    $positionStyle = 'right:' . $offsetX . 'px;top:50%;transform:translateY(-50%);';
} elseif ($buttonPosition === 'left-middle') {
    $positionStyle = 'left:' . $offsetX . 'px;top:50%;transform:translateY(-50%);';
} else {
    $positionStyle = 'right:' . $offsetX . 'px;bottom:' . $offsetY . 'px;';
}
?>
<style>
.q8-floating-service {
    position: fixed;
    z-index: 99999;
    <?php echo $positionStyle; ?>
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 46px;
    padding: 0 18px;
    border: 0;
    border-radius: 999px;
    background: <?php echo htmlspecialchars($buttonColor, ENT_QUOTES, 'UTF-8'); ?>;
    color: <?php echo htmlspecialchars($buttonTextColor, ENT_QUOTES, 'UTF-8'); ?> !important;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.18);
    font-size: 15px;
    font-weight: 700;
    line-height: 46px;
    text-decoration: none !important;
    cursor: pointer;
    transition: box-shadow 0.2s ease, filter 0.2s ease;
}
.q8-floating-service:hover,
.q8-floating-service:focus {
    color: <?php echo htmlspecialchars($buttonTextColor, ENT_QUOTES, 'UTF-8'); ?> !important;
    filter: brightness(0.94);
    box-shadow: 0 16px 34px rgba(15, 23, 42, 0.22);
    outline: none;
}
.q8-floating-service .fa {
    width: 1.1em;
    text-align: center;
    font-size: 18px;
}
@media (max-width: 767px) {
    <?php if (!$mobileShow) { ?>
    .q8-floating-service { display: none; }
    <?php } else { ?>
    .q8-floating-service {
        min-width: 46px;
        width: 46px;
        height: 46px;
        padding: 0;
        justify-content: center;
        line-height: 46px;
    }
    .q8-floating-service__text { display: none; }
    <?php } ?>
}
</style>

<?php if ($chatMode === 'chatwoot') { ?>
<button type="button" class="q8-floating-service" id="q8ChatwootLauncher" title="<?php echo htmlspecialchars($buttonTitle, ENT_QUOTES, 'UTF-8'); ?>">
    <i class="fa <?php echo htmlspecialchars($buttonIcon, ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
    <span class="q8-floating-service__text"><?php echo htmlspecialchars($buttonText, ENT_QUOTES, 'UTF-8'); ?></span>
</button>
<script>
(function(d, t) {
    window.chatwootSettings = { hideMessageBubble: true };
    var BASE_URL = <?php echo json_encode($chatwootBaseUrl, JSON_UNESCAPED_SLASHES); ?>;
    var g = d.createElement(t);
    var s = d.getElementsByTagName(t)[0];
    g.src = BASE_URL + "/packs/js/sdk.js";
    g.defer = true;
    g.async = true;
    s.parentNode.insertBefore(g, s);
    g.onload = function() {
        window.chatwootSDK.run({
            websiteToken: <?php echo json_encode($chatwootToken); ?>,
            baseUrl: BASE_URL
        });
    };
})(document, "script");
document.getElementById("q8ChatwootLauncher").addEventListener("click", function() {
    if (window.$chatwoot && typeof window.$chatwoot.toggle === "function") {
        window.$chatwoot.toggle("open");
    }
});
</script>
<?php } else { ?>
<a class="q8-floating-service" href="<?php echo htmlspecialchars($buttonUrl, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $openNew ? 'target="_blank" rel="noopener noreferrer"' : ''; ?> title="<?php echo htmlspecialchars($buttonTitle, ENT_QUOTES, 'UTF-8'); ?>">
    <i class="fa <?php echo htmlspecialchars($buttonIcon, ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
    <span class="q8-floating-service__text"><?php echo htmlspecialchars($buttonText, ENT_QUOTES, 'UTF-8'); ?></span>
</a>
<?php } ?>
