<?php
/**
 * 商品分类管理
 */
include("../includes/common.php");
$title = '分类管理';
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission('shop', 1);

if (!function_exists('q8_admin_class_escape')) {
    function q8_admin_class_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('q8_admin_class_ensure_schema')) {
    function q8_admin_class_ensure_schema($DB)
    {
        $pidColumn = $DB->getColumn("SHOW COLUMNS FROM shua_class LIKE 'pid'");
        if (!$pidColumn) {
            $DB->exec("ALTER TABLE shua_class ADD COLUMN pid int(11) unsigned NOT NULL DEFAULT '0' COMMENT '父分类ID，0表示一级分类' AFTER `zid`");
        }

        $noticeColumn = $DB->getColumn("SHOW COLUMNS FROM shua_class LIKE 'notice'");
        if (!$noticeColumn) {
            $DB->exec("ALTER TABLE shua_class ADD COLUMN notice text COMMENT '分类提示语，前台选中时弹窗显示' AFTER `blockpay`");
        }
    }
}

q8_admin_class_ensure_schema($DB);

$my = isset($_GET['my']) ? trim((string)$_GET['my']) : null;

if ($my === 'qk2') {
    adminpermission('shop', 2);
    $ok = $DB->exec("TRUNCATE TABLE `pre_class`") !== false;
    header('Location: classlist.php?notice=' . ($ok ? 'clear_success' : 'clear_failed'));
    exit;
}

$classListAssetVersion = isset($adminAssetVersion) ? $adminAssetVersion : ((defined('VERSION') ? VERSION : '1.0.0') . '.20260426admin37');
$classStats = array(
    'total' => intval($DB->getColumn("SELECT COUNT(*) FROM pre_class")),
    'visible' => intval($DB->getColumn("SELECT COUNT(*) FROM pre_class WHERE active=1")),
    'hidden' => intval($DB->getColumn("SELECT COUNT(*) FROM pre_class WHERE active=0")),
    'parents' => intval($DB->getColumn("SELECT COUNT(*) FROM pre_class WHERE pid=0")),
    'children' => intval($DB->getColumn("SELECT COUNT(*) FROM pre_class WHERE pid>0")),
    'goods' => intval($DB->getColumn("SELECT COUNT(*) FROM pre_tools")),
    'onsale_goods' => intval($DB->getColumn("SELECT COUNT(*) FROM pre_tools WHERE active=1 AND close=0"))
);
$classContext = apply_filters('admin_class_list_context', array(
    'stats' => $classStats,
    'mode' => $my === 'classimg' ? 'image' : 'list'
));

include './head.php';
$classListAssetVersion = isset($adminAssetVersion) ? $adminAssetVersion : ((defined('VERSION') ? VERSION : '1.0.0') . '.20260426admin40');
?>
<link rel="stylesheet" href="./assets/css/admin-class-list.css?v=<?php echo urlencode($classListAssetVersion); ?>">

<div class="col-xs-12 admin-class-page" data-class-page="<?php echo q8_admin_class_escape($classContext['mode']); ?>">
    <?php echo q8_render_action('admin_class_list_page_before', $classContext); ?>

    <section class="admin-class-hero">
        <div class="admin-class-hero__content">
            <p class="admin-class-hero__eyebrow"><?php echo html_entity_decode('&#20998;&#31867;&#20013;&#24515;', ENT_QUOTES, 'UTF-8'); ?></p>
            <h2>商品分类、前台展示、用户中心与对接导入统一管理</h2>
            <p>分类是商品和发卡的地基：前台首页按这里的显示状态与排序展示，用户中心可以在此基础上隐藏分类，对接同步也会把商品落到这里的分类结构里。</p>
        </div>
        <div class="admin-class-hero__aside">
            <a href="./batchgoods.php" class="admin-class-hero__chip"><i class="fa fa-cloud-download"></i> 批量对接</a>
            <a href="./cx-synchronization.php" class="admin-class-hero__chip"><i class="fa fa-refresh"></i> 自动同步</a>
            <a href="./shopedit.php?my=add" class="admin-class-hero__chip"><i class="fa fa-plus-circle"></i> 新增商品</a>
        </div>
    </section>

    <section class="admin-class-stats">
        <article class="admin-class-stat admin-ui-stat">
            <span class="admin-class-stat__icon admin-class-stat__icon--primary admin-ui-stat__icon"><i class="fa fa-sitemap"></i></span>
            <div><span>分类总数</span><strong><?php echo $classStats['total']; ?></strong></div>
        </article>
        <article class="admin-class-stat admin-ui-stat">
            <span class="admin-class-stat__icon admin-class-stat__icon--success admin-ui-stat__icon"><i class="fa fa-eye"></i></span>
            <div><span>前台可见</span><strong><?php echo $classStats['visible']; ?></strong></div>
        </article>
        <article class="admin-class-stat admin-ui-stat">
            <span class="admin-class-stat__icon admin-class-stat__icon--warning admin-ui-stat__icon"><i class="fa fa-code-fork"></i></span>
            <div><span>一级 / 二级</span><strong><?php echo $classStats['parents']; ?> / <?php echo $classStats['children']; ?></strong></div>
        </article>
        <article class="admin-class-stat admin-ui-stat">
            <span class="admin-class-stat__icon admin-class-stat__icon--accent admin-ui-stat__icon"><i class="fa fa-cubes"></i></span>
            <div><span>上架商品 / 全部</span><strong><?php echo $classStats['onsale_goods']; ?> / <?php echo $classStats['goods']; ?></strong></div>
        </article>
    </section>

    <?php echo q8_render_action('admin_class_list_stats_after', $classContext); ?>

    <section class="admin-class-map">
        <article>
            <i class="fa fa-home"></i>
            <div>
                <strong>前台首页</strong>
                <p>显示状态、排序、分类图片会影响首页和商品选择体验。</p>
            </div>
        </article>
        <article>
            <i class="fa fa-user-circle"></i>
            <div>
                <strong>用户中心</strong>
                <p>用户侧可在已启用分类里做隐藏，后台下架会直接影响所有用户。</p>
            </div>
        </article>
        <article>
            <i class="fa fa-plug"></i>
            <div>
                <strong>对接同步</strong>
                <p>批量对接支持保存到指定分类或新建同名分类，后续同步页会继续接这里。</p>
            </div>
        </article>
    </section>

    <?php if ($my === 'classimg') { ?>
    <section class="block admin-class-panel admin-class-image-panel">
        <div class="block-title admin-class-panel__title">
            <div>
                <h3>分类图片管理</h3>
                <p>分类图主要供部分首页模板使用，支持上传、从分类商品中自动提取、预览和批量保存。</p>
            </div>
            <div class="admin-class-panel__actions">
                <a href="./classlist.php" class="btn btn-default"><i class="fa fa-arrow-left"></i> 返回分类列表</a>
                <button type="button" class="btn btn-primary" data-class-action="save-all-images"><i class="fa fa-floppy-o"></i> 保存全部图片</button>
            </div>
        </div>
        <?php echo q8_render_action('admin_class_image_before', $classContext); ?>
        <form id="classImageForm" class="admin-class-image-grid">
            <?php
            $rs = $DB->query("SELECT cid,pid,name,shopimg,active FROM pre_class ORDER BY sort ASC,cid ASC");
            while ($res = $rs->fetch()) {
                $img = isset($res['shopimg']) ? (string)$res['shopimg'] : '';
                $preview = $img !== '' && stripos($img, 'http') !== 0 ? '../' . $img : $img;
            ?>
            <article class="admin-class-image-card" data-class-card data-cid="<?php echo intval($res['cid']); ?>" data-img="<?php echo q8_admin_class_escape($img); ?>">
                <div class="admin-class-image-card__preview">
                    <?php if ($preview !== '') { ?>
                    <img src="<?php echo q8_admin_class_escape($preview); ?>" alt="<?php echo q8_admin_class_escape($res['name']); ?>">
                    <?php } else { ?>
                    <span><i class="fa fa-picture-o"></i></span>
                    <?php } ?>
                </div>
                <div class="admin-class-image-card__body">
                    <strong><?php echo q8_admin_class_escape($res['name']); ?></strong>
                    <small><?php echo intval($res['pid']) > 0 ? '二级分类' : '一级分类'; ?> · ID <?php echo intval($res['cid']); ?></small>
                    <input type="file" class="admin-class-file-input" data-cid="<?php echo intval($res['cid']); ?>" accept="image/*">
                    <input type="text" class="form-control" name="img[<?php echo intval($res['cid']); ?>]" data-class-image-url="<?php echo intval($res['cid']); ?>" value="<?php echo q8_admin_class_escape($img); ?>" placeholder="填写图片 URL 或上传图片">
                    <div class="admin-class-image-card__actions">
                        <button type="button" class="btn btn-default" data-class-action="image-upload" data-cid="<?php echo intval($res['cid']); ?>"><i class="fa fa-upload"></i> 上传</button>
                        <button type="button" class="btn btn-default" data-class-action="image-auto" data-cid="<?php echo intval($res['cid']); ?>"><i class="fa fa-magic"></i> 提取</button>
                        <button type="button" class="btn btn-default" data-class-action="image-preview" data-cid="<?php echo intval($res['cid']); ?>"><i class="fa fa-eye"></i> 预览</button>
                    </div>
                </div>
            </article>
            <?php } ?>
        </form>
        <?php echo q8_render_action('admin_class_image_after', $classContext); ?>
    </section>
    <?php } else { ?>
    <section class="block admin-class-panel">
        <div class="block-title admin-class-panel__title">
            <div>
                <h3>分类列表</h3>
                <p>支持一级/二级分类、前台显示开关、排序、分类图片、支付限制、地区限制、提示语和批量操作。</p>
            </div>
            <div class="admin-class-panel__actions">
                <a href="./classlist.php?my=classimg" class="btn btn-default"><i class="fa fa-picture-o"></i> 分类图片</a>
                <button type="button" class="btn btn-default" data-class-action="refresh"><i class="fa fa-refresh"></i> 刷新</button>
            </div>
        </div>

        <div class="admin-class-toolbar">
            <label class="admin-class-search">
                <i class="fa fa-search"></i>
                <input type="text" class="form-control" id="classSearch" placeholder="搜索分类名称或 ID">
            </label>
            <div class="admin-class-add">
                <input type="text" class="form-control" id="newClassName" placeholder="输入一级分类名称">
                <button type="button" class="btn btn-primary" data-class-action="add-parent"><i class="fa fa-plus"></i> 新增一级分类</button>
            </div>
        </div>

        <?php echo q8_render_action('admin_class_list_toolbar_after', $classContext); ?>

        <div id="listTable" class="admin-class-table-shell" data-source="./classlist-table.php">
            <div class="admin-class-loading"><i class="fa fa-spinner fa-spin"></i><span>正在加载分类列表</span></div>
        </div>
    </section>
    <?php } ?>

    <?php echo q8_render_action('admin_class_list_page_after', $classContext); ?>
</div>

<script src="assets/js/classlist.js?v=<?php echo urlencode($classListAssetVersion); ?>"></script>
</body>
</html>
