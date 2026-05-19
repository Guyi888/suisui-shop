<?php
/**
 * 批量对接商品
 * 结构已重构为控制台风格：删除 inline style / onclick / glyphicon / 冗余 layer.js 加载，
 * 表现层交互逻辑迁移到 assets/js/batchgoods.js，样式迁移到 assets/css/admin-batchgoods.css。
 * 对接业务逻辑（AJAX 端点、字段名、数据结构）保持不变。
 */
include '../includes/common.php';
$title = '批量对接商品';

if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission('shop', 1);

if (!function_exists('q8_admin_batchgoods_escape')) {
    function q8_admin_batchgoods_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('q8_admin_batchgoods_json')) {
    function q8_admin_batchgoods_json($value)
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
    }
}

$act = isset($_GET['act']) ? trim((string)$_GET['act']) : '';
if ($act !== 'data') {
    $act = 'pick';
}

$rs = $DB->query('SELECT cid,name FROM pre_class WHERE active=1 ORDER BY sort ASC');
$classselect = '<option value="0">未分类</option>';
while ($res = $rs->fetch()) {
    $classselect .= '<option value="' . intval($res['cid']) . '">' . q8_admin_batchgoods_escape($res['name']) . '</option>';
}

$TRADITIONAL_TYPES = array('jiuwu', 'yile', 'zhike', 'shangzhanwl');
$TRADITIONAL_LABELS = array(
    'jiuwu' => '玖伍',
    'yile' => '亿乐',
    'zhike' => '直客',
    'shangzhanwl' => '商战网'
);

$shequOptions = array();
$rs = $DB->query('SELECT id,type,url,remark FROM pre_shequ ORDER BY id ASC');
while ($res = $rs->fetch()) {
    $pluginConfig = \lib\Plugin::getConfig('third_' . $res['type']);
    $supportsBatch = false;
    if (isset($pluginConfig['batchgoods']) && $pluginConfig['batchgoods'] === true) {
        $supportsBatch = true;
    } elseif (in_array($res['type'], $TRADITIONAL_TYPES, true)) {
        $supportsBatch = true;
    }
    if (!$supportsBatch) {
        continue;
    }

    $titleText = '';
    if (in_array($res['type'], $TRADITIONAL_TYPES, true)) {
        $titleText = isset($TRADITIONAL_LABELS[$res['type']]) ? $TRADITIONAL_LABELS[$res['type']] : '未知';
    } else {
        $titleText = isset($pluginConfig['title']) ? $pluginConfig['title'] : '';
    }

    $shequOptions[] = array(
        'id' => intval($res['id']),
        'type' => $res['type'],
        'url' => $res['url'],
        'remark' => $res['remark'],
        'title' => $titleText
    );
}

$shequselect = '';
foreach ($shequOptions as $opt) {
    $remarkSuffix = $opt['remark'] !== '' ? ' (' . $opt['remark'] . ')' : '';
    $shequselect .= '<option value="' . $opt['id'] . '"'
        . ' data-type="' . q8_admin_batchgoods_escape($opt['type']) . '"'
        . ' data-domain="' . q8_admin_batchgoods_escape($opt['url']) . '">'
        . '[' . q8_admin_batchgoods_escape($opt['title']) . '] '
        . q8_admin_batchgoods_escape($opt['url'] . $remarkSuffix)
        . '</option>';
}

$priceselect = '<option value="0">不使用加价模板</option>';
$rs = $DB->query('SELECT id,name,kind,p_0,p_1,p_2 FROM pre_price WHERE zid=0 ORDER BY id ASC');
while ($res = $rs->fetch()) {
    $unitLabel = intval($res['kind']) === 1 ? '元' : '倍';
    $priceselect .= '<option value="' . intval($res['id']) . '"'
        . ' kind="' . intval($res['kind']) . '"'
        . ' p_2="' . q8_admin_batchgoods_escape($res['p_2']) . '"'
        . ' p_1="' . q8_admin_batchgoods_escape($res['p_1']) . '"'
        . ' p_0="' . q8_admin_batchgoods_escape($res['p_0']) . '">'
        . q8_admin_batchgoods_escape($res['name'])
        . '(' . q8_admin_batchgoods_escape($res['p_2']) . $unitLabel
        . '|' . q8_admin_batchgoods_escape($res['p_1']) . $unitLabel
        . '|' . q8_admin_batchgoods_escape($res['p_0']) . $unitLabel . ')</option>';
}

$currentShequ = null;
$isTraditionalSystem = false;
$third_classlist = '';

if ($act === 'data') {
    $shequId = isset($_GET['shequ']) ? intval($_GET['shequ']) : 0;
    $currentShequ = $DB->getRow('SELECT * FROM pre_shequ WHERE id=:id LIMIT 1', array(':id' => $shequId));
    if (!$currentShequ) {
        header('Location: ./batchgoods.php');
        exit;
    }
    $isTraditionalSystem = in_array($currentShequ['type'], $TRADITIONAL_TYPES, true);

    if (!$isTraditionalSystem) {
        $result = third_call($currentShequ['type'], $currentShequ, 'class_list');
        if (is_array($result)) {
            foreach ($result as $res) {
                $third_classlist .= '<option value="' . q8_admin_batchgoods_escape($res['cid']) . '">'
                    . q8_admin_batchgoods_escape($res['name']) . '</option>';
            }
        }
    } else {
        $third_classlist = '<option value="-1">--所有商品--</option>';
    }
}

$batchGoodsContext = apply_filters('admin_batchgoods_context', array(
    'act' => $act,
    'shequ' => $currentShequ ? array(
        'id' => intval($currentShequ['id']),
        'type' => $currentShequ['type'],
        'url' => $currentShequ['url'],
        'remark' => $currentShequ['remark']
    ) : null,
    'is_traditional' => $isTraditionalSystem,
    'shequ_total' => count($shequOptions)
));

include './head.php';
$batchGoodsAssetVersion = isset($adminAssetVersion) ? $adminAssetVersion : ((defined('VERSION') ? VERSION : '1.0.0') . '.20260426admin37');
?>
<link rel="stylesheet" href="./assets/css/admin-batchgoods.css?v=<?php echo urlencode($batchGoodsAssetVersion); ?>">

<div class="col-xs-12 admin-batchgoods-page">
    <?php echo q8_render_action('admin_batchgoods_page_before', $batchGoodsContext); ?>

    <section class="admin-batchgoods-hero">
        <div class="admin-batchgoods-hero__content">
            <p class="admin-batchgoods-hero__eyebrow"><?php echo html_entity_decode('&#25209;&#37327;&#23545;&#25509;', ENT_QUOTES, 'UTF-8'); ?></p>
            <h2>一次性把对接站的商品拉进本站</h2>
            <p>支持标准插件站点和玖伍 / 亿乐 / 直客 / 商战网等传统站点。先选站点，再拉分类和商品，最后统一落到本站分类并套用加价模板。</p>
        </div>
        <div class="admin-batchgoods-hero__aside">
            <a href="./shoplist.php" class="admin-batchgoods-hero__chip"><i class="fa fa-cubes"></i> 商品管理</a>
            <a href="./price.php" class="admin-batchgoods-hero__chip"><i class="fa fa-sliders"></i> 加价模板</a>
            <a href="./classlist.php" class="admin-batchgoods-hero__chip"><i class="fa fa-sitemap"></i> 商品分类</a>
        </div>
    </section>

    <?php echo q8_render_action('admin_batchgoods_hero_after', $batchGoodsContext); ?>

    <?php if ($act === 'pick') { ?>
    <section class="block admin-batchgoods-panel">
        <div class="block-title admin-batchgoods-panel__title">
            <div>
                <h3>选择对接站点</h3>
                <p>只会列出已开启 batchgoods 能力的插件站点和传统站点。选中后进入下一步拉取分类和商品。</p>
            </div>
        </div>

        <?php if (count($shequOptions) === 0) { ?>
        <div class="admin-batchgoods-empty">
            <i class="fa fa-plug"></i>
            <div>
                <strong>暂无可对接的站点</strong>
                <p>请先到「对接站点」页添加站点并启用 batchgoods 能力，或使用玖伍 / 亿乐 / 直客 / 商战网等传统站点。</p>
            </div>
        </div>
        <?php } else { ?>
        <form class="admin-batchgoods-form" method="get" action="./batchgoods.php">
            <input type="hidden" name="act" value="data">

            <label class="admin-batchgoods-field">
                <span class="admin-batchgoods-field__label">选择对接站点</span>
                <span class="admin-batchgoods-field__control">
                    <select class="form-control" name="shequ" required><?php echo $shequselect; ?></select>
                </span>
            </label>

            <div class="admin-batchgoods-form-actions">
                <button type="submit" class="btn btn-primary admin-batchgoods-submit">
                    <i class="fa fa-arrow-right"></i> 获取商品分类
                </button>
            </div>
        </form>
        <?php } ?>
    </section>
    <?php } else { ?>

    <section class="block admin-batchgoods-panel">
        <div class="block-title admin-batchgoods-panel__title">
            <div>
                <h3>批量对接商品</h3>
                <p>选择对接站的商品分类后，下方会加载对应商品；勾选后统一保存到本站分类并套用加价模板。</p>
            </div>
            <div class="admin-batchgoods-panel__actions">
                <a href="./batchgoods.php" class="btn btn-default">
                    <i class="fa fa-refresh"></i> 重新选择站点
                </a>
            </div>
        </div>

        <form class="admin-batchgoods-form" id="batchGoodsForm" action="?" role="form">
            <input type="hidden" name="shequ" value="<?php echo intval($currentShequ['id']); ?>">
            <input type="hidden" name="type" value="<?php echo q8_admin_batchgoods_escape($currentShequ['type']); ?>">

            <label class="admin-batchgoods-field">
                <span class="admin-batchgoods-field__label">当前对接站点</span>
                <span class="admin-batchgoods-field__control">
                    <input class="form-control" value="<?php echo q8_admin_batchgoods_escape($currentShequ['url']); ?>" disabled>
                </span>
            </label>

            <?php if (!$isTraditionalSystem) { ?>
            <div class="admin-batchgoods-field admin-batchgoods-field--stack">
                <span class="admin-batchgoods-field__label">选择对接站点商品分类</span>
                <span class="admin-batchgoods-field__control">
                    <select class="form-control admin-batchgoods-multi" id="cid" multiple="multiple" size="10">
                        <option value="-1">--请选择分类（可多选）--</option>
                        <?php echo $third_classlist; ?>
                    </select>
                </span>
                <span class="admin-batchgoods-field__hint">按住 Ctrl 或 Shift 可以同时选中多个分类</span>
            </div>
            <?php } else { ?>
            <label class="admin-batchgoods-field">
                <span class="admin-batchgoods-field__label">获取方式</span>
                <span class="admin-batchgoods-field__control">
                    <select class="form-control" id="is">
                        <option value="0">获取 社区 所有的商品</option>
                        <option value="1">只获取 不存在本系统 的社区商品</option>
                    </select>
                </span>
            </label>

            <div class="admin-batchgoods-field admin-batchgoods-field--stack" id="categoryGroup">
                <span class="admin-batchgoods-field__label">选择对接站点商品分类</span>
                <span class="admin-batchgoods-field__control">
                    <select class="form-control admin-batchgoods-multi" id="category_id" multiple="multiple" size="10">
                        <option value="-1">--请选择分类（可多选）--</option>
                    </select>
                </span>
                <span class="admin-batchgoods-field__hint">按住 Ctrl 或 Shift 可以同时选中多个分类；选中后会自动拉取商品</span>
            </div>

            <?php if ($currentShequ['type'] === 'shangzhanwl') { ?>
            <label class="admin-batchgoods-field" id="showcid">
                <span class="admin-batchgoods-field__label">商品目录</span>
                <span class="admin-batchgoods-field__control">
                    <input type="text" class="form-control" id="goodscid" placeholder="输入 全部 就获取全部">
                </span>
            </label>
            <?php } ?>
            <?php } ?>

            <div class="admin-batchgoods-table-shell">
                <div class="admin-batchgoods-table-toolbar">
                    <span class="admin-batchgoods-table-title"><i class="fa fa-list-alt"></i> 商品列表</span>
                    <span class="admin-batchgoods-table-meta" id="batchGoodsMeta">等待加载</span>
                </div>
                <div class="admin-batchgoods-table-scroll">
                    <table class="table table-bordered table-vcenter table-hover admin-batchgoods-table" id="shoptable">
                        <thead>
                            <tr id="shopHeaderDefault">
                                <th class="admin-batchgoods-col-check">
                                    <label class="admin-batchgoods-check">
                                        <input type="checkbox" id="batchGoodsSelectAll">
                                        <span>全选</span>
                                    </label>
                                </th>
                                <th>商品</th>
                                <th>成本价</th>
                                <th>状态</th>
                            </tr>
                        </thead>
                        <tbody id="shoplist">
                            <tr class="admin-batchgoods-placeholder">
                                <td colspan="<?php echo $isTraditionalSystem ? 8 : 5; ?>">
                                    <?php if ($isTraditionalSystem) { ?>
                                    请先选择上方的商品分类，系统会自动拉取商品列表。
                                    <?php } else { ?>
                                    请先选择上方的商品分类（可多选），系统会自动拉取商品列表。
                                    <?php } ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <label class="admin-batchgoods-field">
                <span class="admin-batchgoods-field__label">保存到本站的分类</span>
                <span class="admin-batchgoods-field__control">
                    <select class="form-control" id="mcid">
                        <option value="-1">--请选择分类--</option>
                        <option value="new">新建同名分类</option>
                        <?php echo $classselect; ?>
                    </select>
                </span>
            </label>

            <div class="admin-batchgoods-field" id="parentClassGroup" hidden>
                <span class="admin-batchgoods-field__label">父级分类</span>
                <span class="admin-batchgoods-field__control">
                    <select class="form-control" id="parent_cid">
                        <option value="0">作为一级分类</option>
                        <?php echo $classselect; ?>
                    </select>
                </span>
            </div>

            <div class="admin-batchgoods-field admin-batchgoods-field--with-action">
                <span class="admin-batchgoods-field__label">使用的加价模板</span>
                <span class="admin-batchgoods-field__control">
                    <select class="form-control" id="prid">
                        <option value="-1">--请选择加价模板--</option>
                        <?php echo $priceselect; ?>
                    </select>
                    <a class="btn btn-default" href="./price.php">
                        <i class="fa fa-sliders"></i> 加价模板管理
                    </a>
                </span>
            </div>

            <?php if ($isTraditionalSystem) { ?>
            <div class="admin-batchgoods-form-actions">
                <button type="button" class="btn btn-default" data-batchgoods-action="fetch-goods">
                    <i class="fa fa-cloud-download"></i> 手动刷新商品列表
                </button>
            </div>
            <?php } ?>

            <div class="admin-batchgoods-form-actions admin-batchgoods-form-actions--primary">
                <button type="button" class="btn btn-primary admin-batchgoods-submit" id="batchGoodsSubmit">
                    <i class="fa fa-check"></i> 确定添加 / 更新选中商品
                </button>
            </div>
        </form>
    </section>
    <?php } ?>

    <?php echo q8_render_action('admin_batchgoods_page_after', $batchGoodsContext); ?>
</div>

<script>
window.batchGoodsConfig = <?php echo q8_admin_batchgoods_json(array(
    'act' => $act,
    'shequ' => $currentShequ ? intval($currentShequ['id']) : 0,
    'type' => $currentShequ ? $currentShequ['type'] : '',
    'isTraditional' => (bool)$isTraditionalSystem,
    'traditionalTypes' => $TRADITIONAL_TYPES,
    'endpoints' => array(
        'batchDocking' => './ajax_batch_docking.php',
        'shopAjax' => './ajax_shop.php'
    )
)); ?>;
</script>
<script src="./assets/js/batchgoods.js?v=<?php echo urlencode($batchGoodsAssetVersion); ?>"></script>
</body>
</html>
