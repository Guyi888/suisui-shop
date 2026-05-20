<?php
if (!defined('IN_CRONLITE')) exit();

function suisui_conf($key, $default = '') {
	global $conf;
	return isset($conf[$key]) && $conf[$key] !== '' ? $conf[$key] : $default;
}

function suisui_h($value) {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function suisui_on($key, $default = '1') {
	return suisui_conf($key, $default) !== '0';
}

function suisui_clean_theme($theme) {
	$theme = preg_replace('/[^a-z0-9_-]/i', '', (string)$theme);
	return in_array($theme, array('fresh', 'coral', 'graphite'), true) ? $theme : 'fresh';
}

function suisui_url($value, $default = '#') {
	$value = trim((string)$value);
	return $value === '' ? $default : $value;
}

function suisui_render_notice_modal($id, $title, $content, $enabled = true) {
	if (trim((string)$content) === '') return;
	?>
	<div class="modal fade suisui-modal" id="<?php echo suisui_h($id); ?>" tabindex="-1" role="dialog" aria-hidden="true" data-enabled="<?php echo $enabled ? 1 : 0; ?>">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-bullhorn"></i> <?php echo $title; ?></h4>
				</div>
				<div class="suisui-modal-body"><?php echo $content; ?></div>
				<div class="modal-footer">
					<button type="button" class="suisui-btn suisui-btn-primary" data-dismiss="modal"><i class="fa fa-check-circle"></i> 我知道了</button>
				</div>
			</div>
		</div>
	</div>
	<?php
}

$suisuiVariant = isset($suisuiTemplateVariant) ? $suisuiTemplateVariant : 'suisui';
$suisuiVariant = $suisuiVariant === 'xhy01' ? 'xhy01' : 'suisui';
$suisuiTheme = $suisuiVariant === 'xhy01' ? 'coral' : suisui_clean_theme(suisui_conf('suisui_template_theme', 'fresh'));
$suisuiFavicon = function_exists('q8_brand_favicon_href') ? q8_brand_favicon_href() : '/assets/img/favicon/favicon.ico';
$suisuiLogo = isset($logo) && $logo ? $logo : 'assets/img/logo.png';
$suisuiDefaultClassImg = suisui_url(suisui_conf('suisui_template_default_class_img', '/assets/img/Product/default.png'), '/assets/img/Product/default.png');
$suisuiKicker = suisui_conf('suisui_template_kicker', '正版服务 / 稳定处理 / 售后无忧');
$suisuiSlogan = suisui_conf('suisui_template_slogan', '选择商品后填写下单联系方式，付款前请认真核对商品说明。');
$suisuiHomeNotice = trim((string)(isset($conf['anounce']) ? $conf['anounce'] : ''));
$suisuiPopupNotice = trim((string)(isset($conf['modal']) ? $conf['modal'] : ''));
$suisuiSafeTips = preg_split('/\r\n|\r|\n/', suisui_conf('suisui_template_safe_tips', "请按商品要求填写下单账号、邮箱或联系方式。\n付款前确认商品介绍、库存和售后规则。\n查单请使用下单时填写的第一项信息或订单号。"));
$suisuiServiceUrl = site_contact_url(isset($conf['kfqq']) ? $conf['kfqq'] : '');
$suisuiSupplyUrl = suisui_url(suisui_conf('suisui_template_supply_url', './sup'), './sup');
$suisuiGroupUrl = trim((string)suisui_conf('suisui_template_group_url', 'https://t.me/suisuishop'));
$suisuiChannelUrl = trim((string)suisui_conf('suisui_template_channel_url', 'https://t.me/suisuifaka'));
$suisuiModalShowType = isset($conf['modal_show_type']) ? intval($conf['modal_show_type']) : 0;
$suisuiInitialCid = isset($_GET['cid']) ? intval($_GET['cid']) : 0;
$suisuiInitialTid = isset($_GET['tid']) ? intval($_GET['tid']) : 0;
$classhide = isset($siterow['class']) ? explode(',', $siterow['class']) : array();

$classRows = array();
$classMap = array();
$primaryRows = array();
$childrenRows = array();
try {
	$rs = $DB->query("SELECT cid,pid,name,shopimg,notice FROM pre_class WHERE active=1 ORDER BY sort ASC,cid ASC");
	while ($row = $rs->fetch(PDO::FETCH_ASSOC)) {
		if ($is_fenzhan && in_array($row['cid'], $classhide)) continue;
		$row['cid'] = intval($row['cid']);
		$row['pid'] = intval($row['pid']);
		$classRows[] = $row;
		$classMap[$row['cid']] = $row;
		if ($row['pid'] > 0) {
			if (!isset($childrenRows[$row['pid']])) $childrenRows[$row['pid']] = array();
			$childrenRows[$row['pid']][] = $row;
		} else {
			$primaryRows[] = $row;
		}
	}
} catch (Exception $e) {}

$recommendRows = array();
$recommendTids = trim((string)suisui_conf('suisui_template_recommend_tids', ''));
try {
	if ($recommendTids !== '') {
		$tids = array();
		foreach (explode(',', $recommendTids) as $tid) {
			$tid = intval($tid);
			if ($tid > 0) $tids[] = $tid;
		}
		$tids = array_values(array_unique($tids));
		if ($tids) {
			$tidSql = implode(',', $tids);
			$rs = $DB->query("SELECT tid,cid,name,price,sales FROM pre_tools WHERE active=1 AND tid IN ($tidSql) ORDER BY FIELD(tid,$tidSql)");
			while ($row = $rs->fetch(PDO::FETCH_ASSOC)) $recommendRows[] = $row;
		}
	}
	if (!$recommendRows) {
		$rs = $DB->query("SELECT tid,cid,name,price,sales FROM pre_tools WHERE active=1 AND cid>0 ORDER BY close ASC,sales DESC,sort ASC,tid DESC LIMIT 8");
		while ($row = $rs->fetch(PDO::FETCH_ASSOC)) $recommendRows[] = $row;
	}
} catch (Exception $e) {}

$classConfig = array(
	'classes' => array_values($classRows),
	'children' => $childrenRows,
	'initialCid' => $suisuiInitialCid,
	'initialTid' => $suisuiInitialTid,
	'popupEnabled' => suisui_on('suisui_template_popup_enable') && $suisuiPopupNotice !== '',
	'modalShowType' => $suisuiModalShowType,
	'defaultClassImg' => $suisuiDefaultClassImg,
	'version' => VERSION,
);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<title><?php echo suisui_h($hometitle); ?></title>
	<meta name="keywords" content="<?php echo suisui_h(isset($conf['keywords']) ? $conf['keywords'] : ''); ?>">
	<meta name="description" content="<?php echo suisui_h(isset($conf['description']) ? $conf['description'] : ''); ?>">
	<link rel="icon" href="<?php echo suisui_h($suisuiFavicon); ?>" type="image/x-icon">
	<link rel="stylesheet" href="/assets/vendor/twitter-bootstrap/3.4.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="/assets/vendor/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="/assets/vendor/layer/3.1.1/theme/default/layer.css">
	<link rel="stylesheet" href="/template/suisui/assets/css/suisui.css?ver=<?php echo VERSION; ?>-suisui-clean1">
	<?php if ($suisuiVariant === 'xhy01') { ?><link rel="stylesheet" href="/template/XHY-01/assets/css/xhy01.css"><?php } ?>
	<script>
		var isModal = false;
		var modalShowType = <?php echo $suisuiModalShowType; ?>;
		var homepage = true;
		var hashsalt = <?php echo $addsalt_js ?>;
		window.SUISUI_TEMPLATE = <?php echo json_encode($classConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
	</script>
</head>
<body class="suisui-page suisui-theme-<?php echo suisui_h($suisuiTheme); ?> suisui-variant-<?php echo suisui_h($suisuiVariant); ?>">
	<div class="suisui-shell">
		<header class="suisui-topbar">
			<a class="suisui-brand" href="./" aria-label="<?php echo suisui_h($conf['sitename']); ?>">
				<img class="suisui-logo" src="/<?php echo ltrim(suisui_h($suisuiLogo), '/'); ?>" alt="<?php echo suisui_h($conf['sitename']); ?>">
				<span class="suisui-brand-text">
					<span class="suisui-brand-title"><?php echo suisui_h($conf['sitename']); ?></span>
					<span class="suisui-brand-sub">岁岁云商城 / 岁岁 @qqfaka</span>
				</span>
			</a>
			<nav class="suisui-actions">
				<?php if (suisui_on('suisui_template_notice_enable') && $suisuiHomeNotice !== '') { ?>
				<button type="button" class="suisui-btn" data-toggle="modal" data-target="#suisuiHomeNotice"><i class="fa fa-bullhorn"></i> 公告</button>
				<?php } ?>
				<a class="suisui-btn" href="#queryPane" id="tab-query" data-toggle="tab"><i class="fa fa-search"></i> 查订单</a>
				<a class="suisui-btn" href="./user/login.php"><i class="fa fa-user-circle"></i> 用户中心</a>
				<?php if (suisui_on('suisui_template_service_enable')) { ?>
				<a class="suisui-btn suisui-btn-primary" href="<?php echo suisui_h($suisuiServiceUrl); ?>" target="_blank"><i class="fa fa-comments"></i> <?php echo suisui_h(suisui_conf('suisui_template_service_text', '联系客服')); ?></a>
				<?php } ?>
			</nav>
		</header>

		<main class="suisui-workspace">
			<section class="suisui-order-card">
				<div class="suisui-card-head">
					<div>
						<div class="suisui-kicker"><i class="fa fa-shield"></i><?php echo suisui_h($suisuiKicker); ?></div>
						<h1><?php echo suisui_h($conf['sitename']); ?></h1>
						<p><?php echo nl2br(suisui_h($suisuiSlogan)); ?></p>
					</div>
				</div>

				<section class="suisui-order-alert">
					<div class="suisui-order-alert-title"><i class="fa fa-check-circle"></i> 下单提醒</div>
					<ul class="suisui-tips">
						<?php foreach ($suisuiSafeTips as $tip) { if (trim($tip) === '') continue; ?>
						<li><i class="fa fa-check"></i><span><?php echo suisui_h($tip); ?></span></li>
						<?php } ?>
					</ul>
					<div class="suisui-quicklinks">
						<?php if (suisui_on('suisui_template_supply_enable')) { ?><a class="suisui-btn" href="<?php echo suisui_h($suisuiSupplyUrl); ?>"><i class="fa fa-truck"></i> 供货入口</a><?php } ?>
						<?php if ($suisuiGroupUrl !== '') { ?><a class="suisui-btn" href="<?php echo suisui_h($suisuiGroupUrl); ?>" target="_blank"><i class="fa fa-telegram"></i> 交流群</a><?php } ?>
						<?php if ($suisuiChannelUrl !== '') { ?><a class="suisui-btn" href="<?php echo suisui_h($suisuiChannelUrl); ?>" target="_blank"><i class="fa fa-bullhorn"></i> 频道</a><?php } ?>
					</div>
				</section>

				<ul class="nav nav-tabs suisui-tabs" role="tablist">
					<li class="active"><a href="#orderPane" data-toggle="tab"><i class="fa fa-shopping-bag"></i> 我要下单</a></li>
					<li><a href="#queryPane" data-toggle="tab"><i class="fa fa-search"></i> 查询订单</a></li>
				</ul>

				<div class="tab-content">
					<div class="tab-pane active" id="orderPane">
						<div class="suisui-steps" aria-label="下单流程">
							<span class="is-active" data-step="0">选分类</span>
							<span data-step="1">选商品</span>
							<span data-step="2">填信息</span>
							<span data-step="3">付款</span>
						</div>

						<?php if (isset($conf['search_open']) && $conf['search_open'] == 1) { ?>
						<div class="suisui-field">
							<label for="searchkw">搜索商品</label>
							<div class="suisui-search">
								<input type="text" id="searchkw" class="form-control" placeholder="输入关键词快速搜索商品">
								<button type="button" id="doSearch" class="suisui-icon-btn" aria-label="搜索"><i class="fa fa-search"></i></button>
							</div>
						</div>
						<?php } ?>

						<div class="suisui-form-grid">
							<div class="suisui-field">
								<label for="primary_cid">一级分类</label>
								<select id="primary_cid" class="form-control">
									<option value="0">请选择一级分类</option>
									<?php foreach ($primaryRows as $row) { ?>
									<option value="<?php echo intval($row['cid']); ?>"><?php echo suisui_h($row['name']); ?></option>
									<?php } ?>
								</select>
							</div>
							<div class="suisui-field" id="display_selectsubclass">
								<label for="subcid">二级分类</label>
								<select id="subcid" class="form-control" disabled><option value="0">请先选择一级分类</option></select>
							</div>
						</div>

						<input type="hidden" name="cid" id="cid" value="0">
						<div class="suisui-field">
							<label for="tid">商品</label>
							<select name="tid" id="tid" class="form-control"><option value="0">请选择商品</option></select>
						</div>

						<div class="suisui-form-grid">
							<div class="suisui-field" id="display_price">
								<label for="need">商品价格</label>
								<input type="text" name="need" id="need" class="form-control" readonly placeholder="选择商品后自动显示">
							</div>
							<div class="suisui-field" id="display_left">
								<label for="leftcount">库存数量</label>
								<input type="text" name="leftcount" id="leftcount" class="form-control" readonly>
							</div>
						</div>

						<div class="suisui-field" id="display_num">
							<label for="num">购买数量</label>
							<div class="suisui-qty">
								<button id="num_min" type="button" class="suisui-icon-btn" aria-label="减少"><i class="fa fa-minus"></i></button>
								<input id="num" name="num" class="form-control" type="number" min="1" value="1" inputmode="numeric">
								<button id="num_add" type="button" class="suisui-icon-btn" aria-label="增加"><i class="fa fa-plus"></i></button>
							</div>
						</div>
						<div class="suisui-inline-note" id="display_num_note"><i class="fa fa-info-circle"></i> 该商品固定按 1 份下单，无需选择数量。</div>

						<div class="suisui-field">
							<label>下单联系方式 / 账号信息</label>
							<div class="suisui-input-placeholder" id="suisuiInputPlaceholder">选择商品后，这里会显示需要填写的账号、邮箱、链接或联系方式。</div>
							<div id="inputsname" class="suisui-dynamic-fields"></div>
						</div>

						<button type="button" id="submit_buy" class="suisui-submit"><i class="fa fa-credit-card"></i> 立即购买</button>
					</div>

					<div class="tab-pane" id="queryPane">
						<div class="suisui-query">
							<div class="suisui-form-grid">
								<div class="suisui-field">
									<label for="searchtype">查询方式</label>
									<select class="form-control" id="searchtype"><option value="0">下单账号</option><option value="1">订单号</option></select>
								</div>
								<div class="suisui-field">
									<label for="qq3">查询内容</label>
									<input type="text" name="qq" id="qq3" class="form-control" placeholder="输入下单账号或订单号">
								</div>
							</div>
							<button type="button" id="submit_query" class="suisui-submit suisui-submit-muted"><i class="fa fa-search"></i> 立即查询</button>
							<div id="result2" class="suisui-result"><div id="list"></div></div>
						</div>
					</div>
				</div>
			</section>

			<aside class="suisui-side">
				<section class="suisui-panel suisui-product-panel">
					<div class="suisui-panel-title"><i class="fa fa-cube"></i> 商品信息</div>
					<div class="suisui-class-line">
						<img id="classImg" src="<?php echo suisui_h($suisuiDefaultClassImg); ?>" alt="">
						<div>
							<span>当前分类</span>
							<strong id="className">等待选择分类</strong>
						</div>
					</div>
					<div class="suisui-selected-product" id="suisuiSelectedProduct">
						<strong>请先选择商品</strong>
						<span>选择后会显示价格、库存、商品介绍和填写要求。</span>
					</div>
					<div id="alert_frame" class="suisui-desc"></div>
				</section>

				<?php if (suisui_on('suisui_template_recommend_enable') && $recommendRows) { ?>
				<section class="suisui-panel">
					<div class="suisui-panel-title"><i class="fa fa-star"></i> <?php echo suisui_h(suisui_conf('suisui_template_recommend_title', '推荐商品')); ?></div>
					<div class="suisui-products" id="suisuiAjaxRecommend">
						<?php foreach ($recommendRows as $row) { ?>
						<button type="button" class="suisui-product" data-suisui-tool data-cid="<?php echo intval($row['cid']); ?>" data-tid="<?php echo intval($row['tid']); ?>">
							<span class="suisui-product-name"><?php echo suisui_h($row['name']); ?></span>
							<span class="suisui-product-meta"><b>¥<?php echo suisui_h($row['price']); ?></b><em>选择</em></span>
						</button>
						<?php } ?>
					</div>
				</section>
				<?php } ?>

			</aside>
		</main>

		<footer class="suisui-footer">
			<?php echo isset($conf['footer']) && $conf['footer'] ? $conf['footer'] : 'Copyright © ' . suisui_h($conf['sitename']); ?>
		</footer>
	</div>

	<?php if (suisui_on('suisui_template_notice_enable')) suisui_render_notice_modal('suisuiHomeNotice', '首页公告', $suisuiHomeNotice, true); ?>
	<?php if (suisui_on('suisui_template_popup_enable')) suisui_render_notice_modal('suisuiPopupNotice', '平台公告', $suisuiPopupNotice, true); ?>

	<script src="/assets/vendor/jquery/3.5.1/jquery.min.js"></script>
	<script src="/assets/vendor/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
	<script src="/assets/vendor/twitter-bootstrap/3.4.1/js/bootstrap.min.js"></script>
	<script src="/assets/vendor/layer/3.1.1/layer.js"></script>
	<script src="/assets/js/main.js?ver=<?php echo VERSION; ?>-suisui-clean1"></script>
	<script src="/template/suisui/assets/js/suisui.js?ver=<?php echo VERSION; ?>-suisui-clean1"></script>
</body>
</html>
