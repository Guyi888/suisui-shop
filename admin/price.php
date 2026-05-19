<?php
include "../includes/common.php";
$title = '加价模板';

if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission('price', 1);

if (!function_exists('q8_admin_price_escape')) {
    function q8_admin_price_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('q8_admin_price_fetch_rules')) {
    function q8_admin_price_fetch_rules($DB)
    {
        $rows = $DB->getAll("SELECT id,name,kind,p_0,p_1,p_2 FROM pre_price WHERE zid=0 ORDER BY id DESC");
        $usageRows = $DB->getAll("SELECT prid, COUNT(*) AS goods_total FROM pre_tools WHERE prid>0 GROUP BY prid");
        $usageMap = array();

        foreach ($usageRows as $usageRow) {
            $usageMap[intval($usageRow['prid'])] = intval($usageRow['goods_total']);
        }

        foreach ($rows as &$row) {
            $row['id'] = intval($row['id']);
            $row['kind'] = intval($row['kind']);
            $row['p_0'] = (string)$row['p_0'];
            $row['p_1'] = (string)$row['p_1'];
            $row['p_2'] = (string)$row['p_2'];
            $row['usage_total'] = isset($usageMap[$row['id']]) ? intval($usageMap[$row['id']]) : 0;
        }
        unset($row);

        return array($rows, $usageMap);
    }
}

if (!function_exists('q8_admin_price_fetch_categories')) {
    function q8_admin_price_fetch_categories($DB)
    {
        $rows = $DB->getAll("SELECT cid,pid,name,active,sort FROM pre_class WHERE active=1 ORDER BY sort ASC,cid ASC");
        $parents = array();
        $children = array();

        foreach ($rows as $row) {
            $row['cid'] = intval($row['cid']);
            $row['pid'] = intval($row['pid']);

            if ($row['pid'] === 0) {
                $parents[] = $row;
            } else {
                if (!isset($children[$row['pid']])) {
                    $children[$row['pid']] = array();
                }
                $children[$row['pid']][] = $row;
            }
        }

        return array($parents, $children);
    }
}

if (!function_exists('q8_admin_price_render_category_options')) {
    function q8_admin_price_render_category_options($parents, $children)
    {
        $html = '';

        foreach ($parents as $parent) {
            $html .= '<option value="' . intval($parent['cid']) . '">' . q8_admin_price_escape($parent['name']) . '</option>';

            if (!empty($children[$parent['cid']])) {
                foreach ($children[$parent['cid']] as $child) {
                    $html .= '<option value="' . intval($child['cid']) . '">-- ' . q8_admin_price_escape($child['name']) . '</option>';
                }
            }
        }

        return $html;
    }
}

if (!function_exists('q8_admin_price_kind_text')) {
    function q8_admin_price_kind_text($kind)
    {
        return intval($kind) === 1 ? '固定金额' : '倍数加价';
    }
}

if (!function_exists('q8_admin_price_kind_class')) {
    function q8_admin_price_kind_class($kind)
    {
        return intval($kind) === 1 ? 'admin-price-kind--fixed' : 'admin-price-kind--ratio';
    }
}

if (!function_exists('q8_admin_price_rule_formula')) {
    function q8_admin_price_rule_formula($row)
    {
        $unit = intval($row['kind']) === 1 ? '元' : '倍';
        return '专业版 ' . $row['p_2'] . $unit . ' / 普及版 ' . $row['p_1'] . $unit . ' / 普通用户 ' . $row['p_0'] . $unit;
    }
}

list($priceRows, $usageMap) = q8_admin_price_fetch_rules($DB);
list($categoryParents, $categoryChildren) = q8_admin_price_fetch_categories($DB);

$totalRules = count($priceRows);
$usedRuleCount = 0;
$unusedRuleCount = max($totalRules - $usedRuleCount, 0);
$boundGoodsTotal = 0;
$ratioRuleCount = 0;
$fixedRuleCount = 0;

foreach ($priceRows as $priceRow) {
    $boundGoodsTotal += intval($priceRow['usage_total']);
    if (intval($priceRow['usage_total']) > 0) {
        $usedRuleCount++;
    }
    if (intval($priceRow['kind']) === 1) {
        $fixedRuleCount++;
    } else {
        $ratioRuleCount++;
    }
}

$unusedRuleCount = max($totalRules - $usedRuleCount, 0);

$priceContext = apply_filters('admin_price_context', array(
    'stats' => array(
        'total' => $totalRules,
        'used' => $usedRuleCount,
        'unused' => $unusedRuleCount,
        'bound_goods' => $boundGoodsTotal,
        'ratio' => $ratioRuleCount,
        'fixed' => $fixedRuleCount
    ),
    'rows' => $priceRows,
    'categories' => count($categoryParents)
));

include './head.php';
$priceAssetVersion = isset($adminAssetVersion) ? $adminAssetVersion : ((defined('VERSION') ? VERSION : '1.0.0') . '.20260426admin37');
?>
<link rel="stylesheet" href="./assets/css/admin-price.css?v=<?php echo urlencode($priceAssetVersion); ?>">

<div class="col-xs-12 admin-price-page">
    <?php echo q8_render_action('admin_price_page_before', $priceContext); ?>

    <section class="admin-price-hero">
        <div class="admin-price-hero__content">
            <p class="admin-price-hero__eyebrow"><?php echo html_entity_decode('&#21152;&#20215;&#27169;&#26495;&#20013;&#24515;', ENT_QUOTES, 'UTF-8'); ?></p>
            <h2>统一管理加价模板、预览规则、批量绑定分类与商品价格策略</h2>
            <p>这一页只处理加价模板维度的维护，不改动商品业务逻辑。新增模板、修改规则、查看使用中的商品数量，以及把模板批量应用到分类，都集中在这里完成。</p>
        </div>
        <div class="admin-price-hero__aside">
            <button type="button" class="admin-price-hero__chip" data-price-action="add">
                <i class="fa fa-plus-circle"></i>
                <span>新增模板</span>
            </button>
            <a href="./shoplist.php" class="admin-price-hero__chip">
                <i class="fa fa-cubes"></i>
                <span>商品列表</span>
            </a>
            <a href="./cx-synchronization.php" class="admin-price-hero__chip">
                <i class="fa fa-refresh"></i>
                <span>自动同步</span>
            </a>
        </div>
    </section>

    <section class="admin-price-stats">
        <article class="admin-price-stat admin-ui-stat">
            <span class="admin-price-stat__icon admin-price-stat__icon--primary admin-ui-stat__icon"><i class="fa fa-sliders"></i></span>
            <div>
                <span>模板总数</span>
                <strong><?php echo intval($totalRules); ?></strong>
            </div>
        </article>
        <article class="admin-price-stat admin-ui-stat">
            <span class="admin-price-stat__icon admin-price-stat__icon--success admin-ui-stat__icon"><i class="fa fa-link"></i></span>
            <div>
                <span>使用中模板</span>
                <strong><?php echo intval($usedRuleCount); ?></strong>
            </div>
        </article>
        <article class="admin-price-stat admin-ui-stat">
            <span class="admin-price-stat__icon admin-price-stat__icon--accent admin-ui-stat__icon"><i class="fa fa-cube"></i></span>
            <div>
                <span>已绑定商品</span>
                <strong><?php echo intval($boundGoodsTotal); ?></strong>
            </div>
        </article>
        <article class="admin-price-stat admin-ui-stat">
            <span class="admin-price-stat__icon admin-price-stat__icon--warning admin-ui-stat__icon"><i class="fa fa-folder-open"></i></span>
            <div>
                <span>未使用模板</span>
                <strong><?php echo intval($unusedRuleCount); ?></strong>
            </div>
        </article>
    </section>

    <section class="admin-price-map">
        <article>
            <i class="fa fa-lightbulb-o"></i>
            <div>
                <strong>规则顺序要稳定</strong>
                <p>系统仍按原逻辑校验“专业版 ≤ 普及版 ≤ 普通用户”，这里只重构界面与弹窗体验，不改原有价格规则校验。</p>
            </div>
        </article>
        <article>
            <i class="fa fa-sitemap"></i>
            <div>
                <strong>分类批量应用保留</strong>
                <p>模板可以继续批量应用到分类下商品，适合和商品同步、批量对接、商品编辑配合使用。</p>
            </div>
        </article>
        <article>
            <i class="fa fa-mobile"></i>
            <div>
                <strong>弹窗改为独立面板</strong>
                <p>不再混用旧 Bootstrap Modal 和旧 Layer 版本，避免遮罩不退、手机端滚动锁死和关闭按钮失效。</p>
            </div>
        </article>
    </section>

    <section class="admin-price-panel">
        <div class="admin-price-panel__title">
            <div>
                <h3>加价模板清单</h3>
                <p>支持搜索模板名、按类型筛选、批量删除，以及查看某个模板当前绑定的商品数量与规则预览。</p>
            </div>
            <div class="admin-price-panel__meta" id="priceRuleSummary">
                当前共 <?php echo intval($totalRules); ?> 个模板
            </div>
        </div>

        <div class="admin-price-toolbar" id="priceRuleToolbar">
            <label class="admin-price-toolbar__search">
                <i class="fa fa-search"></i>
                <input type="text" class="form-control" id="priceRuleSearch" placeholder="搜索模板名或规则">
            </label>
            <label class="admin-price-toolbar__select">
                <select class="form-control" id="priceRuleKindFilter">
                    <option value="">全部类型</option>
                    <option value="0">倍数加价</option>
                    <option value="1">固定金额</option>
                </select>
            </label>
            <div class="admin-price-toolbar__actions">
                <button type="button" class="admin-price-button admin-price-button--primary" data-price-action="add">
                    <i class="fa fa-plus"></i> 新增模板
                </button>
                <button type="button" class="admin-price-button admin-price-button--light" data-price-action="reload">
                    <i class="fa fa-refresh"></i> 刷新页面
                </button>
                <button type="button" class="admin-price-button admin-price-button--danger" data-price-action="batch-delete">
                    <i class="fa fa-trash"></i> 删除选中
                </button>
            </div>
        </div>

        <?php echo q8_render_action('admin_price_toolbar_after', $priceContext); ?>

        <?php if (!empty($priceRows)) { ?>
        <div class="admin-price-table-wrap">
            <table class="table admin-price-table" id="priceRuleTable">
                <thead>
                <tr>
                    <th class="admin-price-table__check">
                        <label class="admin-price-check">
                            <input type="checkbox" id="priceRuleSelectAll">
                            <span></span>
                        </label>
                    </th>
                    <th>ID</th>
                    <th>模板信息</th>
                    <th>规则预览</th>
                    <th>使用情况</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($priceRows as $row) { ?>
                <tr
                    data-rule-row
                    data-rule-id="<?php echo intval($row['id']); ?>"
                    data-kind="<?php echo intval($row['kind']); ?>"
                    data-name="<?php echo q8_admin_price_escape($row['name']); ?>"
                    data-formula="<?php echo q8_admin_price_escape(q8_admin_price_rule_formula($row)); ?>"
                >
                    <td class="admin-price-table__check">
                        <label class="admin-price-check">
                            <input type="checkbox" name="checkbox[]" value="<?php echo intval($row['id']); ?>" data-price-select>
                            <span></span>
                        </label>
                    </td>
                    <td>
                        <span class="admin-price-id">#<?php echo intval($row['id']); ?></span>
                    </td>
                    <td>
                        <div class="admin-price-name">
                            <strong><?php echo q8_admin_price_escape($row['name']); ?></strong>
                            <span class="admin-price-kind <?php echo q8_admin_price_kind_class($row['kind']); ?>">
                                <?php echo q8_admin_price_escape(q8_admin_price_kind_text($row['kind'])); ?>
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="admin-price-rule">
                            <span><em>专业版</em><strong><?php echo q8_admin_price_escape($row['p_2']); ?><?php echo intval($row['kind']) === 1 ? '元' : '倍'; ?></strong></span>
                            <span><em>普及版</em><strong><?php echo q8_admin_price_escape($row['p_1']); ?><?php echo intval($row['kind']) === 1 ? '元' : '倍'; ?></strong></span>
                            <span><em>普通用户</em><strong><?php echo q8_admin_price_escape($row['p_0']); ?><?php echo intval($row['kind']) === 1 ? '元' : '倍'; ?></strong></span>
                        </div>
                    </td>
                    <td>
                        <div class="admin-price-usage">
                            <strong><?php echo intval($row['usage_total']); ?></strong>
                            <span>个商品正在使用</span>
                        </div>
                    </td>
                    <td>
                        <div class="admin-price-actions">
                            <a href="./shoplist.php?prid=<?php echo intval($row['id']); ?>" target="_blank" class="admin-price-action admin-price-action--success">
                                <i class="fa fa-cube"></i> 商品
                            </a>
                            <button type="button" class="admin-price-action admin-price-action--primary" data-price-action="edit" data-rule-id="<?php echo intval($row['id']); ?>">
                                <i class="fa fa-pencil"></i> 编辑
                            </button>
                            <button type="button" class="admin-price-action admin-price-action--light" data-price-action="assign" data-rule-id="<?php echo intval($row['id']); ?>" data-rule-name="<?php echo q8_admin_price_escape($row['name']); ?>">
                                <i class="fa fa-sitemap"></i> 应用分类
                            </button>
                            <button type="button" class="admin-price-action admin-price-action--danger" data-price-action="delete" data-rule-id="<?php echo intval($row['id']); ?>">
                                <i class="fa fa-trash"></i> 删除
                            </button>
                        </div>
                    </td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } else { ?>
        <div class="admin-price-empty" id="priceRuleEmpty">
            <i class="fa fa-sliders"></i>
            <h4>还没有加价模板</h4>
            <p>可以先新增一个模板，再在商品编辑、商品同步或批量对接里直接套用。</p>
            <button type="button" class="admin-price-button admin-price-button--primary" data-price-action="add">
                <i class="fa fa-plus"></i> 立即创建
            </button>
        </div>
        <?php } ?>
    </section>

    <?php echo q8_render_action('admin_price_page_after', $priceContext); ?>
</div>

<div class="admin-price-modal" id="priceRuleModal" aria-hidden="true">
    <div class="admin-price-modal__dialog admin-price-modal__dialog--wide" role="dialog" aria-modal="true" aria-labelledby="priceRuleModalTitle">
        <div class="admin-price-modal__header">
            <div>
                <p class="admin-price-modal__eyebrow"><?php echo html_entity_decode('&#27169;&#26495;&#32534;&#36753;', ENT_QUOTES, 'UTF-8'); ?></p>
                <h3 id="priceRuleModalTitle">新增加价模板</h3>
            </div>
            <button type="button" class="admin-price-modal__close" data-price-modal-close>
                <i class="fa fa-times"></i>
            </button>
        </div>
        <div class="admin-price-modal__body">
            <form id="priceRuleForm" class="admin-price-form" novalidate>
                <input type="hidden" id="priceRuleAction" value="add">
                <input type="hidden" name="prid" id="priceRuleId">

                <div class="admin-price-form__notice">
                    <i class="fa fa-info-circle"></i>
                    <div>
                        <strong>规则校验保持不变</strong>
                        <p>保存时仍按原系统逻辑校验：专业版加价不能高于普及版，普及版不能高于普通用户。</p>
                    </div>
                </div>

                <div class="admin-price-form__grid">
                    <label class="admin-price-field">
                        <span>模板名称</span>
                        <input type="text" class="form-control" name="name" id="priceRuleName" placeholder="输入模板名称">
                    </label>
                    <label class="admin-price-field">
                        <span>加价方式</span>
                        <select name="kind" id="priceRuleKind" class="form-control">
                            <option value="0">倍数加价</option>
                            <option value="1">固定金额加价</option>
                        </select>
                    </label>
                    <label class="admin-price-field admin-price-field--full">
                        <span>测试成本价</span>
                        <input type="text" class="form-control" id="priceRuleTestPrice" value="100" placeholder="仅用于预览，不会写入数据库">
                        <small>这里只用于计算下方预览价格，方便确认模板设置是否符合预期。</small>
                    </label>
                </div>

                <div class="admin-price-tiers">
                    <label class="admin-price-tier">
                        <span>专业版加价</span>
                        <input type="text" class="form-control" name="p_2" id="priceRuleP2" placeholder="输入加价值">
                        <div class="admin-price-tier__preview">
                            <em>预览价格</em>
                            <strong id="priceRulePreviewP2">0.00</strong>
                        </div>
                    </label>
                    <label class="admin-price-tier">
                        <span>普及版加价</span>
                        <input type="text" class="form-control" name="p_1" id="priceRuleP1" placeholder="输入加价值">
                        <div class="admin-price-tier__preview">
                            <em>预览价格</em>
                            <strong id="priceRulePreviewP1">0.00</strong>
                        </div>
                    </label>
                    <label class="admin-price-tier">
                        <span>普通用户加价</span>
                        <input type="text" class="form-control" name="p_0" id="priceRuleP0" placeholder="输入加价值">
                        <div class="admin-price-tier__preview">
                            <em>预览价格</em>
                            <strong id="priceRulePreviewP0">0.00</strong>
                        </div>
                    </label>
                </div>
            </form>
        </div>
        <div class="admin-price-modal__footer">
            <button type="button" class="admin-price-button admin-price-button--light" data-price-modal-close>取消</button>
            <button type="button" class="admin-price-button admin-price-button--primary" id="priceRuleSaveButton">
                <i class="fa fa-save"></i> 保存模板
            </button>
        </div>
    </div>
</div>

<div class="admin-price-modal" id="priceCategoryModal" aria-hidden="true">
    <div class="admin-price-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="priceCategoryModalTitle">
        <div class="admin-price-modal__header">
            <div>
                <p class="admin-price-modal__eyebrow"><?php echo html_entity_decode('&#20998;&#31867;&#32465;&#23450;', ENT_QUOTES, 'UTF-8'); ?></p>
                <h3 id="priceCategoryModalTitle">批量应用模板到分类</h3>
            </div>
            <button type="button" class="admin-price-modal__close" data-price-modal-close>
                <i class="fa fa-times"></i>
            </button>
        </div>
        <div class="admin-price-modal__body">
            <input type="hidden" id="priceCategoryRuleId">
            <div class="admin-price-form__notice">
                <i class="fa fa-sitemap"></i>
                <div>
                    <div id="priceCategoryRuleInfo">
                        <strong>当前模板</strong>
                        <p>选择一个或多个分类后，系统会按原接口逻辑把该模板应用到这些分类下的商品。</p>
                    </div>
                </div>
            </div>
            <label class="admin-price-field admin-price-field--full">
                <span>选择分类</span>
                <div class="admin-price-select-tools">
                    <button type="button" class="admin-price-button admin-price-button--light admin-price-button--small" data-price-action="select-all-categories">
                        <i class="fa fa-check-square-o"></i> 全选分类
                    </button>
                    <button type="button" class="admin-price-button admin-price-button--light admin-price-button--small" data-price-action="clear-categories">
                        <i class="fa fa-square-o"></i> 清空选择
                    </button>
                </div>
                <select class="form-control" name="cids[]" id="priceCategorySelect" multiple>
                    <?php echo q8_admin_price_render_category_options($categoryParents, $categoryChildren); ?>
                </select>
                <small>如果你要给整站商品统一套模板，不用一页页改，直接点下方“应用到全站商品”即可。分类多选仍支持 Ctrl / Shift 和手机端滚动点选。</small>
            </label>
        </div>
        <div class="admin-price-modal__footer">
            <button type="button" class="admin-price-button admin-price-button--light" data-price-modal-close>取消</button>
            <button type="button" class="admin-price-button admin-price-button--primary" id="priceCategoryApplyButton">
                <i class="fa fa-check-circle"></i> 应用到分类
            </button>
            <button type="button" class="admin-price-button admin-price-button--danger" id="priceCategoryApplyAllButton">
                <i class="fa fa-globe"></i> 应用到全站商品
            </button>
        </div>
    </div>
</div>

<script>
window.pricePageConfig = <?php echo json_encode(array(
    'endpoints' => array(
        'get' => 'ajax_shop.php?act=getPriceRule&id=',
        'add' => 'ajax_shop.php?act=addPriceRule',
        'edit' => 'ajax_shop.php?act=editPriceRule',
        'delete' => 'ajax_shop.php?act=delPriceRule&id=',
        'batch' => 'ajax_shop.php?act=batchPriceOperation',
        'assign' => 'ajax_shop.php?act=changePriceRule'
    )
), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>
<script src="./assets/js/price.js?v=<?php echo urlencode($priceAssetVersion); ?>"></script>
</body>
</html>
