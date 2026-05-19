<?php
include("../includes/common.php");
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission('super', 1);
$title = '自定义 CSS';
include './head.php';

$scopes = q8_get_custom_css_scopes();
$saveMessage = '';
$saveState = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (function_exists('checkRefererHost') && !checkRefererHost()) {
        $saveState = 'danger';
        $saveMessage = '请求来源异常，请刷新页面后重试。';
    } else {
        q8_save_custom_css_settings($_POST);
        $saveState = 'success';
        $saveMessage = '自定义 CSS 已保存，配置缓存也已经同步刷新。';
    }
}
?>
<div class="col-xs-12 admin-custom-css-page">
    <?php if ($saveMessage !== '') { ?>
    <div class="alert alert-<?php echo $saveState === 'success' ? 'success' : 'danger'; ?> admin-custom-css-alert">
        <i class="fa <?php echo $saveState === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
        <span><?php echo htmlspecialchars($saveMessage, ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
    <?php } ?>

    <section class="admin-custom-css-hero">
        <div>
            <p class="admin-custom-css-hero__eyebrow"><?php echo html_entity_decode('&#26679;&#24335;&#25511;&#21046;&#20013;&#24515;', ENT_QUOTES, 'UTF-8'); ?></p>
            <h2>集中管理前台、用户中心和后台样式</h2>
            <p>这里保存的是站点级自定义样式，不会往各个模板里叠加零散代码。保存后会统一刷新配置缓存，三端共用同一套输出能力。</p>
        </div>
        <div class="admin-custom-css-hero__meta">
            <div class="admin-custom-css-pill"><i class="fa fa-magic"></i> 统一输出</div>
            <div class="admin-custom-css-pill"><i class="fa fa-plug"></i> 预留插件钩子</div>
            <div class="admin-custom-css-pill"><i class="fa fa-shield"></i> 仅超级管理员可改</div>
        </div>
    </section>

    <?php echo q8_render_action('admin_custom_css_form_before', array('scopes' => $scopes)); ?>

    <form method="post" class="admin-custom-css-form">
        <div class="admin-custom-css-grid">
            <?php foreach ($scopes as $scope => $meta) {
                $keys = q8_get_custom_css_keys($scope);
                $state = q8_get_custom_css_state($scope);
                $icon = !empty($meta['icon']) ? $meta['icon'] : 'fa-code';
            ?>
            <section class="admin-custom-css-card">
                <?php echo q8_render_action('admin_custom_css_scope_before', array('scope' => $scope, 'meta' => $meta, 'state' => $state)); ?>
                <div class="admin-custom-css-card__header">
                    <div class="admin-custom-css-card__title-wrap">
                        <span class="admin-custom-css-card__icon"><i class="fa <?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>"></i></span>
                        <div>
                            <h3><?php echo htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p><?php echo htmlspecialchars($meta['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </div>
                    <label class="admin-custom-css-switch">
                        <input type="checkbox" name="<?php echo htmlspecialchars($keys['enabled'], ENT_QUOTES, 'UTF-8'); ?>" value="1" <?php echo $state['enabled'] ? 'checked' : ''; ?>>
                        <span class="admin-custom-css-switch__slider"></span>
                        <span class="admin-custom-css-switch__text"><?php echo $state['enabled'] ? '已启用' : '未启用'; ?></span>
                    </label>
                </div>

                <div class="admin-custom-css-card__meta">
                    <span><i class="fa fa-key"></i> 配置键：<?php echo htmlspecialchars($keys['content'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><i class="fa fa-plug"></i> 钩子：<?php echo htmlspecialchars($meta['hook'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>

                <label class="admin-custom-css-card__label" for="scope-<?php echo htmlspecialchars($scope, ENT_QUOTES, 'UTF-8'); ?>">CSS 内容</label>
                <textarea
                    id="scope-<?php echo htmlspecialchars($scope, ENT_QUOTES, 'UTF-8'); ?>"
                    name="<?php echo htmlspecialchars($keys['content'], ENT_QUOTES, 'UTF-8'); ?>"
                    class="admin-custom-css-textarea"
                    spellcheck="false"
                    placeholder="/* 这里写 <?php echo htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8'); ?> 的自定义样式 */"><?php echo htmlspecialchars($state['content'], ENT_QUOTES, 'UTF-8'); ?></textarea>

                <p class="admin-custom-css-card__hint">建议只写当前作用域需要的样式，尽量用明确类名，不要依赖大面积强制覆盖。</p>
                <?php echo q8_render_action('admin_custom_css_scope_after', array('scope' => $scope, 'meta' => $meta, 'state' => $state)); ?>
            </section>
            <?php } ?>
        </div>

        <section class="admin-custom-css-devnote">
            <h3><i class="fa fa-code-fork"></i> 开发预留</h3>
            <div class="admin-custom-css-devnote__grid">
                <div>
                    <strong>内容过滤</strong>
                    <p><code>suisui_custom_css_content</code>、<code>suisui_custom_css_content_site</code>、<code>suisui_custom_css_content_user</code>、<code>suisui_custom_css_content_admin</code></p>
                </div>
                <div>
                    <strong>输出过滤</strong>
                    <p><code>suisui_custom_css_markup</code>、<code>suisui_custom_css_markup_site</code>、<code>suisui_custom_css_markup_user</code>、<code>suisui_custom_css_markup_admin</code></p>
                </div>
                <div>
                    <strong>保存事件</strong>
                    <p><code>suisui_custom_css_save_payload</code>、<code>suisui_custom_css_saved</code>、<code>admin_custom_css_form_before</code>、<code>admin_custom_css_scope_after</code></p>
                </div>
            </div>
        </section>

        <div class="admin-custom-css-actions">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fa fa-save"></i> 保存自定义 CSS
            </button>
            <a href="./index.php" class="btn btn-default btn-lg">
                <i class="fa fa-arrow-left"></i> 返回后台首页
            </a>
        </div>
    </form>

    <?php echo q8_render_action('admin_custom_css_form_after', array('scopes' => $scopes)); ?>
</div>
</div>
</div>
</body>
</html>
