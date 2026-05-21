<?php
/*
 岁岁云商城 XHY-01 模板
 维护：岁岁 @qqfaka
*/
if(!defined('IN_CRONLITE'))exit();
$chdsn_cn_zuocew = $conf['chdsn_cn_zuocew']?$conf['chdsn_cn_zuocew']:'/template/XHY-01/bg-fallback.jpg';
$q8XhyFenzhanPricing = q8_get_fenzhan_price_context($conf, !empty($is_fenzhan), isset($siterow) ? $siterow : array());
$q8XhyFenzhanNormalPriceText = q8_format_currency_amount($q8XhyFenzhanPricing['normal_price']);
$q8XhyFenzhanProfessionalPriceText = q8_format_currency_amount($q8XhyFenzhanPricing['professional_price']);
$q8XhyWithdrawMinText = q8_format_currency_amount(isset($conf['tixian_min']) ? $conf['tixian_min'] : 0);
$q8XhyFaviconHref = function_exists('q8_brand_favicon_href') ? q8_brand_favicon_href() : '/assets/img/favicon/favicon.ico';
$q8XhyPopupNotice = isset($conf['modal']) ? trim((string)$conf['modal']) : '';
?>
<!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
    	<title><?php echo $conf['sitename'] ?> - <?php echo $conf['title'] ?></title>
    	<meta name="keywords" content="<?php echo $conf['keywords'] ?>">
    	<meta name="description" content="<?php echo $conf['description'] ?>">
		<link rel="icon" href="<?php echo htmlspecialchars($q8XhyFaviconHref, ENT_QUOTES, 'UTF-8'); ?>" type="image/x-icon" />
		<link rel="shortcut icon" href="<?php echo htmlspecialchars($q8XhyFaviconHref, ENT_QUOTES, 'UTF-8'); ?>" type="image/x-icon" />
		<link href="<?php echo $cdnpublic?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
    	<link href="<?php echo $cdnpublic?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    	<link rel="stylesheet" href="<?php echo $cdnserver?>assets/simple/css/oneui.css">
		<link rel="stylesheet" href="<?php echo $cdnserver?>assets/css/common.css?ver=<?php echo VERSION ?>">
		<link rel="stylesheet" href="/template/XHY-01/assets/css/xhy01.css?ver=<?php echo VERSION ?>-xhy01refactor04">
		<script src="/assets/vendor/jquery/3.5.1/jquery.min.js?v=suisuivendor1"></script>
		<script src="/assets/vendor/jquery-cookie/1.4.1/jquery.cookie.min.js?v=suisuivendor1"></script>
		<script src="<?php echo $cdnpublic?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<script src="<?php echo $cdnpublic?>modernizr/2.8.3/modernizr.min.js"></script>
		<script src="/assets/Agod/layer.js?v=suisuilocal1"></script>
		<script>
		var isModal = <?php echo $q8XhyPopupNotice === '' ? 'false' : 'true'; ?> ;
		var modalShowType = <?php echo isset($conf['modal_show_type']) ? intval($conf['modal_show_type']) : 0; ?>;
		var homepage = true;
		var hashsalt = <?php echo $addsalt_js ?> ;
		window.XHY01_CONTACT_URL = <?php echo json_encode(site_contact_url($conf['kfqq']), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
		</script>
		<script src="<?php echo $cdnserver ?>assets/js/main.js?ver=<?php echo VERSION ?>-xhy01refactor01"></script>
		<!--[if lt IE 9]>
	    <script src="<?php echo $cdnpublic?>html5shiv/3.7.3/html5shiv.min.js"></script>
	    <script src="<?php echo $cdnpublic?>respond.js/1.4.2/respond.min.js"></script>
	    <![endif]-->
    </head>
 <body class="xhy01-page">
<div id="anime-bg"></div>
<!--弹出公告-->
	<!--Announcement Modal-->
					<div class="modal fade" id="anounce" tabindex="-1" role="dialog" aria-labelledby="anounceLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="xhy-announce-head">
					<button type="button" class="xhy-announce-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="xhy-announce-title" id="anounceLabel">
						<i class="fa fa-bullhorn"></i>
						<span>&#24179;&#21488;&#20844;&#21578;</span>
					</h4>
					<div class="xhy-announce-subtitle">&#35831;&#20808;&#26597;&#30475;&#26368;&#26032;&#25552;&#37266;&#65292;&#20877;&#36827;&#34892;&#19979;&#21333;&#21672;&#35810;</div>
				</div>
				<div class="xhy-announce-body">
					<div class="xhy-announce-content">
						<?php echo $q8XhyPopupNotice?>
					</div>
				</div>
				<div class="xhy-announce-footer">
					<button type="button" class="xhy-announce-btn" data-dismiss="modal">
						<i class="fa fa-check-circle"></i> &#25105;&#30693;&#36947;&#20102;
					</button>
				</div>
			</div>
		</div>
	</div>
	<!--Announcement Modal End-->
	<!--Customer Service Modal-->
	<div id="lxkf" class="xhy-service-modal">
	<!-- 标题部分 -->
	<div class="xhy-service-head">
		<button class="xhy-service-close" type="button" onclick="closeModal()">
			<span aria-hidden="true">×</span>
		</button>
		<h4 class="xhy-service-heading">
			<i class="fa fa-headphones fa-fw"></i> 客服与帮助
		</h4>
	</div>

	<!-- 内容区域 -->
	<div class="xhy-service-body">
		<!-- 问题1 -->
		<div class="xhy-service-faq-card">
			<div class="xhy-service-faq-head">
				<h4 class="xhy-no-margin">
					<a class="xhy-service-faq-link" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true">
						<i class="fa fa-question-circle xhy-icon-gap-lg"></i>
						购买的辅助导致账号封禁？
					</a>
				</h4>
			</div>
			<div id="collapseOne" class="panel-collapse collapse in" aria-expanded="true">
				<div class="xhy-service-faq-body">
					<div class="xhy-service-text">
						<div class="xhy-flex-start xhy-mb-12">
							<i class="fa fa-info-circle fa-fw xhy-service-inline-icon"></i>
							<span>本站所有项目均由供货商提供，请按商品说明确认适用范围。</span>
						</div>
						<div class="xhy-flex-start">
							<i class="fa fa-exclamation-triangle fa-fw xhy-service-inline-icon"></i>
							<span>下单前请确认商品规则，风险提示类商品请谨慎购买。</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- 问题2 -->
		<div class="xhy-service-faq-card">
			<div class="xhy-service-faq-head">
				<h4 class="xhy-no-margin">
					<a data-toggle="collapse" data-parent="#accordion" href="#collapseThree" class="xhy-service-faq-link" aria-expanded="true">
						<i class="fa fa-question-circle xhy-icon-gap-lg"></i>
						买了的项目不会使用？
					</a>
				</h4>
			</div>
			<div id="collapseThree" class="panel-collapse collapse in" aria-expanded="true">
				<div class="xhy-service-faq-body">
					<div class="xhy-service-text">
						<div class="xhy-flex-start xhy-mb-12">
							<i class="fa fa-info-circle fa-fw xhy-service-inline-icon"></i>
							<span>确认下载地址可正常访问。</span>
						</div>
						<div class="xhy-flex-start">
							<i class="fa fa-exclamation-triangle fa-fw xhy-service-inline-icon"></i>
							<span>仔细阅读商品教程文档</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- 问题3 -->
		<div class="xhy-service-faq-card">
			<div class="xhy-service-faq-head">
				<h4 class="xhy-no-margin">
					<a data-toggle="collapse" data-parent="#accordion" href="#collapseFourth" class="xhy-service-faq-link" aria-expanded="true">
						<i class="fa fa-question-circle xhy-icon-gap-lg"></i>
						人工客服的售后范围？
					</a>
				</h4>
			</div>
			<div id="collapseFourth" class="panel-collapse collapse in" aria-expanded="true">
				<div class="xhy-service-faq-body">
					<div class="xhy-service-text">
						<i class="fa fa-check-circle xhy-service-inline-icon xhy-icon-gap"></i>
						处理项目无效果、下载地址失效等售后问题，并协助核对订单处理进度。
					</div>
				</div>
			</div>
		</div>

		<!-- 提示信息 -->
		<div class="xhy-service-alert">
			<i class="fa fa-exclamation-circle xhy-icon-gap"></i>
			如遇到站长无法处理的问题，请点击下方联系平台人工客服解决。
		</div>

		<!-- 客服联系区域 -->
		<div class="xhy-service-contact-card">
			<div class="xhy-flex-center">
				<!-- 客服头像 -->
				<div class="xhy-mr-15">
					<img src="/template/XHY-01/gif_lb.jpg" alt="客服头像" class="img-circle xhy-service-avatar">
				</div>

				<!-- 客服信息 -->
				<div class="xhy-flex-one">
					<div class="xhy-service-contact-title">
						客服中心
					</div>
					<div class="xhy-service-contact-time">
						<i class="fa fa-clock-o xhy-icon-gap-sm"></i>
						<b>在线时间：10:00 - 22:00</b>
					</div>
					<a class="xhy-service-doc-link" href="/template/XHY-01/content.html" target="_blank">
						<i class="fa fa-commenting"></i> 点击查看自助教程
					</a>
					<div class="xhy-service-actions">
						<a class="xhy-service-btn xhy-service-btn-online" href="javascript:void(0);" onclick="return openXhyOnlineChat(event);">
							<i class="fa fa-comments"></i> &#22312;&#32447;&#23458;&#26381;
						</a>
					</div>
				</div>


			</div>
		</div>
	</div>

	<!-- 底部 -->
	<div class="xhy-service-footer">
		<button class="xhy-service-footer-btn" type="button" onclick="closeModal()">
			<i class="fa fa-check-circle"></i> 知道了
		</button>
	</div>
</div>

<!-- 遮罩层 -->
<div id="modalOverlay" class="xhy-modal-overlay"></div>

<main class="xhy-home-shell">
		<section class="xhy-hero-card">
			<div class="xhy-hero-media">
				<img class="xhy-hero-avatar animated zoomInDown" src="/assets/simple/img/head3.jpg" alt="<?php echo htmlspecialchars($conf['sitename'], ENT_QUOTES, 'UTF-8'); ?>">
			</div>
			<div class="xhy-hero-body">
				<h3 class="xhy-title-wrap">
					<a href="javascript:void(alert('<?php echo $conf['sitename']?>&#65292;&#24314;&#35758;&#25910;&#34255;&#26412;&#31449;&#65292;&#36991;&#20813;&#20002;&#22833;&#35775;&#38382;&#22320;&#22336;&#12290;'));"><b><span class="xhy-brand-red"><?php echo $conf['sitename']?></span></b></a>
				</h3>
				<div class="xhy-site-slogan">&#27491;&#21697;&#26381;&#21153; &middot; &#31283;&#23450;&#22788;&#29702; &middot; &#21806;&#21518;&#26080;&#24551;</div>
			</div>
		</section>
		<section class="xhy-quick-panel" aria-label="&#39318;&#39029;&#24555;&#25463;&#20837;&#21475;">
			<div class="xhy-quick-grid xhy-quick-grid-main">
				<a class="xhy-quick-btn" href="javascript:void(0);" onclick="$('#anounce').modal('show');">
					<i class="fa fa-bullhorn"></i>
					<span>&#24179;&#21488;&#20844;&#21578;</span>
				</a>
				<a href="./sup" target="_blank" class="xhy-quick-btn">
					<i class="fa fa-shopping-cart"></i>
					<span>&#20379;&#36135;&#19978;&#26550;</span>
				</a>
				<a class="xhy-quick-btn" href="./user/login.php" target="_blank">
					<i class="fa fa-users"></i>
					<span>&#30331;&#24405;&#21518;&#21488;</span>
				</a>
			</div>
			<div class="xhy-quick-grid xhy-quick-grid-sub">
				<a class="xhy-quick-btn" href="/toollogs.php" target="_blank">
					<i class="fa fa-comment"></i>
					<span>&#20170;&#26085;&#26032;&#19978;&#26550;&#39033;&#30446;</span>
				</a>
				<a class="xhy-quick-btn" href="#lxkf" data-toggle="modal">
					<i class="fa fa-headphones"></i>
					<span>&#28857;&#25105;&#21672;&#35810;100%&#22788;&#29702;</span>
				</a>
			</div>
		</section>
<!--TAB标签-->

<!--TAB标签-->

				<!-- 查单说明开始 -->
										<div class="modal fade" align="left" id="cxsm" tabindex="-1" role="dialog"
				aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">
									<span aria-hidden="true">
										&times;
									</span>
									<span class="sr-only">
										Close
									</span>
								</button>
								<h4 class="modal-title" id="myModalLabel">
									查询内容是什么？该输入什么？
								</h4>
							</div>
							<li class="list-group-item">
								<span class="xhy-text-red">
									请在右侧的输入框内输入您下单时，在第一个输入框内填写的信息
								</span>
							</li>
							<li class="list-group-item">
								例如您购买的是QQ名片赞，输入下单的QQ账号即可查询订单
							</li>
							<li class="list-group-item">
								例如您购买的是邮箱类商品，需要输入您的邮箱号，输入 QQ 号通常查询不到。
							</li>
							<li class="list-group-item">
								例如您购买的是快手商品，需要输入作品链接里“userid=”后面的数字，输入快手号通常查询不到。
							</li>
							<li class="list-group-item">
								例如您购买的是全民 K 歌商品，需要输入歌曲链接里“shareuid=”后面、“&amp;”前面的一串英文数字，直接输入歌曲链接通常查询不到。
							</li>
							<li class="list-group-item">
								<span class="xhy-text-red">
									如果不清楚下单账号是什么，可以不填写，直接点击查询，则会根据浏览器缓存查询
								</span>
							</li>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">
									关闭
								</button>
							</div>
						</div>
					</div>
				</div>
				<!--查单说明结束-->
				<section class="xhy-module-card xhy-tabs-card">
					<ul class="nav nav-tabs q8-main-tabs" data-toggle="tabs">
						<li class="active q8-tab-shop xhy-tab-cell" align="center">
							<a href="#shop" data-toggle="tab"><i class="fa fa-shopping-bag fa-fw"></i>&#19979;&#21333;</a>
						</li>
						<li class="q8-tab-search xhy-tab-cell" align="center">
							<a href="#search" data-toggle="tab" id="tab-query"><i class="fa fa-search"></i>&#26597;&#21333;</a>
						</li>
						<li class="q8-tab-profit xhy-tab-cell" align="center">
							<a href="#ktfz" data-toggle="tab"><i class="fa fa-coffee fa-fw"></i>&#36186;&#38065;</a>
						</li>
						<li class="q8-tab-gift xhy-tab-cell" align="center">
							<a href="#gift" data-toggle="tab"><i class="fa fa-gift fa-fw"></i>&#25277;&#22870;</a>
						</li>
						<li class="q8-tab-more xhy-tab-cell" align="center">
							<a href="#more" data-toggle="tab"><i class="fa fa-folder-open"></i>&#26356;&#22810;</a>
						</li>
					</ul>
				<!-- 添加警告信息和下单步骤 -->

					<!--TAB-->
					<div class="xhy-tab-content tab-content">
						<!--在线下单-->
						<div class="tab-pane fade fade-up in active" id="shop">
							<?php include TEMPLATE_ROOT.'default/shop.inc.php'; ?>
						</div>
						<!--在线下单-->
						<!--查询订单-->
					<div class="tab-pane fade fade-up" id="search">
						<!-- 注文释意标题和按钮 -->
						<div class="xhy-query-help">
								<div class="xhy-query-help-head">
									<h4 class="xhy-query-help-title"><i class="fa fa-newspaper-o"></i><span>&#27880;&#25991;&#37322;&#20041;</span></h4>
								</div>
								<div class="xhy-query-help-actions">
									<a class="xhy-query-help-btn" href="javascript:void(alert('&#21487;&#33021;&#26159;&#24635;&#31449;&#25110;&#20379;&#36135;&#21830;&#24211;&#23384;&#19981;&#36275;&#20102;&#65292;&#31561;&#24453;&#21518;&#21488;&#34917;&#21345;&#25110;&#32852;&#31995;&#23458;&#26381;&#35299;&#20915;&#21363;&#21487;&#65281;'));"><i class="fa fa-clock-o"></i><span>&#24453;&#22788;&#29702;</span></a>
									<a class="xhy-query-help-btn refund" href="javascript:void(alert('&#26174;&#31034;&#24050;&#36864;&#27454;&#26159;&#36864;&#27454;&#21040;&#20320;&#20313;&#39069;&#65288;&#21487;&#25552;&#29616;&#65289;&#65292;&#22914;&#26524;&#27809;&#26377;&#27880;&#20876;&#21017;&#19981;&#21040;&#36134;&#65292;&#20808;&#27880;&#20876;&#36134;&#21495;&#20877;&#32852;&#31995;&#23458;&#26381;&#65281;'));"><i class="fa fa-undo"></i><span>&#24050;&#36864;&#27454;</span></a>
									<a class="xhy-query-help-btn refund" href="javascript:void(alert('&#22914;&#26524;&#20184;&#27454;&#20102;&#25214;&#19981;&#21040;&#35746;&#21333;&#35831;&#22312;&#19979;&#36793;&#25163;&#21160;&#26597;&#21333;&#65292;&#22914;&#26524;&#36824;&#26159;&#27809;&#26377;&#24102;&#19978;&#20184;&#27454;&#25130;&#22270;&#32852;&#31995;&#23458;&#26381;&#65281;'));"><i class="fa fa-search-minus"></i><span>&#26597;&#19981;&#21040;</span></a>
								</div>
							</div>

							<!-- 客服信息卡片 -->
							<div class="xhy-home-service-card">
							<div class="xhy-home-service-main">
								<!-- 客服头像 -->
								<div>
									<a class="xhy-home-avatar-link" href="#lxkf" data-toggle="modal">
										<img class="xhy-home-avatar" src="/template/XHY-01/gif_lb.jpg" alt="客服头像">
									</a>
								</div>

								<!-- 客服信息 -->
								<div class="xhy-flex-one">
								<h4 class="xhy-home-service-title">
									<i class="fa fa-home xhy-icon-gap"></i>
									有任何问题可在右下角联系客服
								</h4>
									<div class="xhy-service-text">
										<div class="xhy-flex-center">
									<i class="fa fa-comment xhy-icon-gap"></i>
									<span>
										<span class="xhy-fw-600">人工客服</span>
										<span class="xhy-brand-red"> - 24H在线随时咨询</span>
										<small class="xhy-home-service-note">点击头像查看教程</small>
									</span>
								</div>
									</div>
								</div>
							</div>
						</div>

						<!-- 查询订单教程 -->
						<div class="q8-query-card">
<h5 class="xhy-query-title">订单查询</h5>
<div class="input-group xhy-query-input"><div class="input-group-btn"><select class="form-control xhy-query-type" id="searchtype"><option value="0">下单账号</option><option value="1">订单号</option></select></div><input type="text" name="qq" id="qq3" value="" class="form-control" placeholder="输入下单信息" onkeydown="if(event.keyCode==13){submit_query.click()}" required><span class="input-group-btn"><a tabindex="0" class="btn btn-default" role="button" data-container="body" data-toggle="popover" data-trigger="focus" data-placement="top" data-content="输入下单时填写的信息；不记得可留空查询浏览器缓存。"><i class="fa fa-info-circle"></i></a></span></div>
<input type="submit" id="submit_query" class="btn btn-primary btn-block btn-rounded xhy-query-button" value="立即查询">
<div id="result2" class="q8-query-list xhy-query-result"><div id="list" class="q8-query-list"></div></div>
</div>
</div>
					<!--查询订单-->
						<!-- 开通分站 -->
				<div class="tab-pane fade fade-up" id="ktfz">
						<section class="xhy-earn-panel">
							<div class="xhy-earn-hero">
								<div class="xhy-earn-kicker"><i class="fa fa-line-chart"></i> <?php echo htmlspecialchars($q8XhyFenzhanNormalPriceText, ENT_QUOTES, 'UTF-8'); ?> &#20803;&#36215;&#24320;&#36890;&#20998;&#31449;</div>
								<h3 class="xhy-earn-title">&#25226;&#24179;&#21488;&#36164;&#28304;&#21464;&#25104;&#20320;&#33258;&#24049;&#30340;&#23567;&#24215;</h3>
								<p class="xhy-earn-sub">&#26080;&#38656;&#22788;&#29702;&#21457;&#36135;&#21644;&#24211;&#23384;&#65292;&#24320;&#22909;&#20998;&#31449;&#23601;&#33021;&#25512;&#24191;&#25509;&#21333;&#65292;&#20313;&#39069;&#25552;&#25104;&#21487;&#25552;&#29616;&#12290;</p>
								<div class="xhy-earn-stats">
									<div class="xhy-earn-stat"><strong>&#26222;&#21450;&#29256;</strong><span><?php echo htmlspecialchars($q8XhyFenzhanNormalPriceText, ENT_QUOTES, 'UTF-8'); ?> &#20803;&#20837;&#38376;</span></div>
									<div class="xhy-earn-stat"><strong>&#19987;&#19994;&#29256;</strong><span><?php echo htmlspecialchars($q8XhyFenzhanProfessionalPriceText, ENT_QUOTES, 'UTF-8'); ?> &#20803;&#21319;&#32423;</span></div>
									<div class="xhy-earn-stat"><strong><?php echo htmlspecialchars($q8XhyWithdrawMinText, ENT_QUOTES, 'UTF-8'); ?> &#20803;</strong><span>&#28385;&#39069;&#21487;&#25552;&#29616;</span></div>
								</div>
							</div>
							<div class="xhy-earn-body">
								<div class="xhy-earn-benefits">
									<div class="xhy-earn-benefit">
										<span class="xhy-earn-icon"><i class="fa fa-rocket"></i></span>
										<div><strong>&#19968;&#38190;&#24320;&#31449;</strong><span>&#33258;&#21161;&#24320;&#36890;&#65292;&#25317;&#26377;&#33258;&#24049;&#30340;&#21830;&#22478;&#38142;&#25509;&#12290;</span></div>
									</div>
									<div class="xhy-earn-benefit">
										<span class="xhy-earn-icon"><i class="fa fa-cubes"></i></span>
										<div><strong>&#24179;&#21488;&#36164;&#28304;&#20849;&#20139;</strong><span>&#19981;&#29992;&#22244;&#36135;&#65292;&#21830;&#21697;&#12289;&#25945;&#31243;&#12289;&#21457;&#36135;&#27969;&#31243;&#30452;&#25509;&#20351;&#29992;&#12290;</span></div>
									</div>
									<div class="xhy-earn-benefit">
										<span class="xhy-earn-icon"><i class="fa fa-money"></i></span>
										<div><strong>&#25552;&#29616;&#31616;&#21333;</strong><span>0 &#25163;&#32493;&#36153;&#65292;&#27599;&#26085;&#22266;&#23450;&#26102;&#38388;&#22788;&#29702;&#25171;&#27454;&#12290;</span></div>
									</div>
									<div class="xhy-earn-benefit">
										<span class="xhy-earn-icon"><i class="fa fa-users"></i></span>
										<div><strong>&#36866;&#21512;&#22810;&#31181;&#20154;&#32676;</strong><span>&#23398;&#29983;&#12289;&#19978;&#29677;&#26063;&#12289;&#21019;&#19994;&#32773;&#65292;&#37117;&#21487;&#20197;&#20316;&#20026;&#21103;&#19994;&#25512;&#24191;&#12290;</span></div>
									</div>
								</div>
								<div class="xhy-earn-highlight"><i class="fa fa-star"></i>&#29992;&#33258;&#24049;&#30340;&#20998;&#31449;&#25512;&#24191;&#25509;&#21333;&#65292;&#25104;&#20132;&#21518;&#33719;&#21462;&#20313;&#39069;&#25552;&#25104;&#65292;&#26085;&#31215;&#26376;&#32047;&#26356;&#31283;&#12290;</div>
								<div class="xhy-earn-actions">
									<a href="#userjs" data-toggle="modal" class="xhy-earn-btn xhy-earn-btn-info">
										<i class="fa fa-list-alt"></i> &#29256;&#26412;&#20171;&#32461;
									</a>
									<a href="user/regsite.php" target="_blank" class="xhy-earn-btn xhy-earn-btn-main">
										<i class="fa fa-arrow-right"></i> &#39532;&#19978;&#24320;&#36890;&#20998;&#31449;
									</a>
								</div>
							</div>
						</section>
					</div>
							<!-- gift module -->
														<div class="tab-pane fade fade-up" id="gift">
								<section class="xhy-gift-panel">
									<div class="xhy-gift-hero">
										<div class="xhy-gift-kicker"><i class="fa fa-gift"></i> &#27599;&#26085;&#22909;&#36816;&#25277;&#22870;</div>
										<h3 class="xhy-gift-title">&#35797;&#35797;&#20170;&#22825;&#30340;&#25163;&#27668;</h3>
										<p class="xhy-gift-sub">&#28857;&#20987;&#24320;&#22987;&#21518;&#20877;&#20572;&#27490;&#65292;&#20013;&#22870;&#32467;&#26524;&#20250;&#31435;&#21363;&#26174;&#31034;&#12290;&#31069;&#20320;&#25277;&#21040;&#22909;&#22870;&#21697;&#12290;</p>
									</div>
									<div class="xhy-gift-body">
										<div class="xhy-gift-stage">
											<div class="xhy-gift-icon"><i class="fa fa-diamond"></i></div>
											<div id="roll">&#28857;&#20987;&#19979;&#26041;&#25353;&#38062;&#24320;&#22987;&#25277;&#22870;</div>
											<div id="result" class="xhy-gift-result"></div>
										</div>
										<div class="xhy-gift-actions">
											<a class="xhy-gift-btn xhy-gift-btn-start xhy-is-visible" id="start"><i class="fa fa-play"></i> &#24320;&#22987;&#25277;&#22870;</a>
											<a class="xhy-gift-btn xhy-gift-btn-stop xhy-is-hidden" id="stop"><i class="fa fa-stop"></i> &#20572;&#27490;&#25277;&#22870;</a>
										</div>
										<div class="giftlist xhy-gift-history xhy-is-hidden">
											<div class="xhy-gift-history-title"><i class="fa fa-trophy"></i> &#26368;&#36817;&#20013;&#22870;&#35760;&#24405;</div>
											<ul id="pst_1"></ul>
										</div>
									</div>
								</section>
							</div>

							<!-- 更多服务 -->
							<div class="tab-pane fade fade-right" id="more">
								<section class="xhy-more-panel">
									<div class="xhy-more-head">
										<h3 class="xhy-more-title"><i class="fa fa-folder-open"></i> &#26356;&#22810;&#26381;&#21153;</h3>
										<p class="xhy-more-sub">&#24120;&#29992;&#20837;&#21475;&#25918;&#22312;&#36825;&#37324;&#65292;&#19979;&#21333;&#12289;&#21806;&#21518;&#12289;&#20998;&#31449;&#31649;&#29702;&#37117;&#26356;&#22909;&#25214;&#12290;</p>
									</div>
									<div class="xhy-more-grid">
										<?php if(!empty($conf['appurl'])){?>
										<a class="xhy-more-item" href="<?php echo $conf['appurl'] ?>" target="_blank">
											<span class="xhy-more-icon"><i class="fa fa-cloud-download"></i></span>
											<span><span class="xhy-more-name">APP&#19979;&#36733;</span><span class="xhy-more-desc">&#25163;&#26426;&#31471;&#24555;&#36895;&#35775;&#38382;&#21644;&#19979;&#21333;</span></span>
										</a>
										<?php }?>
										<?php if(!empty($conf['daiguaurl'])){?>
										<a class="xhy-more-item" href="./?mod=daigua">
											<span class="xhy-more-icon"><i class="fa fa-circle-o"></i></span>
											<span><span class="xhy-more-name">QQ&#20195;&#25346;</span><span class="xhy-more-desc">&#20195;&#25346;&#26381;&#21153;&#20837;&#21475;</span></span>
										</a>
										<?php }?>
										<?php if(!empty($conf['invite_tid'])){?>
										<a class="xhy-more-item" href="./?mod=invite" target="_blank">
											<span class="xhy-more-icon"><i class="fa fa-paper-plane-o"></i></span>
											<span><span class="xhy-more-name">&#20813;&#36153;&#39046;&#36190;</span><span class="xhy-more-desc">&#27963;&#21160;&#31119;&#21033;&#20837;&#21475;</span></span>
										</a>
										<?php }?>
										<a class="xhy-more-item" href="#lxkf" data-toggle="modal">
											<span class="xhy-more-icon"><i class="fa fa-headphones"></i></span>
											<span><span class="xhy-more-name">&#21806;&#21518;&#23458;&#26381;</span><span class="xhy-more-desc">&#38382;&#39064;&#21672;&#35810;&#21644;&#24037;&#21333;&#22788;&#29702;</span></span>
										</a>
										<a class="xhy-more-item" href="/user/findpwd.php">
											<span class="xhy-more-icon"><i class="fa fa-key"></i></span>
											<span><span class="xhy-more-name">&#25214;&#22238;&#23494;&#30721;</span><span class="xhy-more-desc">&#24536;&#35760;&#23494;&#30721;&#21487;&#20197;&#22312;&#36825;&#37324;&#25214;&#22238;</span></span>
										</a>
										<a class="xhy-more-item" href="./user" target="_blank">
											<span class="xhy-more-icon"><i class="fa fa-user-circle"></i></span>
											<span><span class="xhy-more-name">&#20998;&#31449;&#30331;&#24405;</span><span class="xhy-more-desc">&#31649;&#29702;&#20998;&#31449;&#12289;&#35746;&#21333;&#21644;&#21830;&#21697;</span></span>
										</a>
									</div>
								</section>
							</div>
										</div>
				</section>
				<div class="modal fade xhy-site-modal" id="userjs" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog modal-dialog-popin">
						<div class="modal-content">
							<div class="xhy-site-modal-head">
								<button class="xhy-site-modal-close" data-dismiss="modal" type="button" aria-label="Close">&times;</button>
								<h4 class="xhy-site-modal-title"><i class="fa fa-list-alt"></i> &#29256;&#26412;&#20171;&#32461;</h4>
								<p class="xhy-site-modal-sub">&#26222;&#21450;&#29256;&#21644;&#19987;&#19994;&#29256;&#37117;&#21487;&#24320;&#36890;&#20998;&#31449;&#65292;&#19987;&#19994;&#29256;&#25552;&#20379;&#26356;&#22810;&#31649;&#29702;&#21644;&#25512;&#24191;&#33021;&#21147;&#12290;</p>
							</div>
							<div class="xhy-site-modal-body">
								<div class="xhy-version-grid">
									<div class="xhy-version-row xhy-version-head">
										<div>&#21151;&#33021;</div>
										<div class="xhy-version-cell">&#26222;&#21450;&#29256;</div>
										<div class="xhy-version-cell">&#19987;&#19994;&#29256;</div>
									</div>
									<div class="xhy-version-row"><div class="xhy-version-name">&#19987;&#23646;&#20195;&#21047;&#24179;&#21488;</div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div></div>
									<div class="xhy-version-row"><div class="xhy-version-name">&#19977;&#31181;&#22312;&#32447;&#25903;&#20184;&#25509;&#21475;</div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div></div>
									<div class="xhy-version-row"><div class="xhy-version-name">&#19987;&#23646;&#32593;&#31449;&#22495;&#21517;</div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div></div>
									<div class="xhy-version-row"><div class="xhy-version-name">&#36186;&#21462;&#29992;&#25143;&#25552;&#25104;</div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div></div>
									<div class="xhy-version-row"><div class="xhy-version-name">&#36186;&#21462;&#19979;&#32423;&#20998;&#31449;&#25552;&#25104;</div><div class="xhy-version-cell"><span class="xhy-version-no"><i class="fa fa-times"></i></span></div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div></div>
									<div class="xhy-version-row"><div class="xhy-version-name">&#35774;&#32622;&#21830;&#21697;&#20215;&#26684;</div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div></div>
									<div class="xhy-version-row"><div class="xhy-version-name">&#35774;&#32622;&#19979;&#32423;&#20998;&#31449;&#21830;&#21697;&#20215;&#26684;</div><div class="xhy-version-cell"><span class="xhy-version-no"><i class="fa fa-times"></i></span></div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div></div>
									<div class="xhy-version-row"><div class="xhy-version-name">&#25645;&#24314;&#19979;&#32423;&#20998;&#31449;</div><div class="xhy-version-cell"><span class="xhy-version-no"><i class="fa fa-times"></i></span></div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div></div>
									<div class="xhy-version-row"><div class="xhy-version-name">&#36192;&#36865;&#19987;&#23646;&#31934;&#33268; APP</div><div class="xhy-version-cell"><span class="xhy-version-no"><i class="fa fa-times"></i></span></div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div></div>
								</div>
								<div class="xhy-site-modal-note"><i class="fa fa-lightbulb-o"></i> &#33258;&#24049;&#30340;&#25512;&#24191;&#33021;&#21147;&#20915;&#23450;&#25910;&#20837;&#65292;&#20808;&#24320;&#31449;&#65292;&#20877;&#24930;&#24930;&#31215;&#32047;&#23458;&#25143;&#12290;</div>
							</div>
							<div class="xhy-site-modal-footer">
								<button type="button" class="xhy-site-modal-btn xhy-site-modal-btn-close" data-dismiss="modal">&#20851;&#38381;</button>
								<a href="user/regsite.php" target="_blank" class="xhy-site-modal-btn xhy-site-modal-btn-main xhy-center-link">&#39532;&#19978;&#24320;&#36890;</a>
							</div>
						</div>
					</div>
				</div>
				<!--鐗堟湰浠嬬粛-->
														<div class="modal fade" align="left" id="about" tabindex="-1" role="dialog"
				aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">
									<span aria-hidden="true">
										&times;
									</span>
									<span class="sr-only">
										Close
									</span>
								</button>
								<h4 class="modal-title" id="myModalLabel">
									新手下单帮助
								</h4>
							</div>
							<div class="modal-body">
								<a href="javascript:void(0)" class="widget">
									<center>
										<strong>
											<span class="xhy-text-larger">
												客服 TG：
												<a href="<?php echo site_contact_url($conf['kfqq'])?>" target="_blank">
													@qqfaka
												</a>
											</span>
										</strong>
										<br />
										<strong>
											<span class="xhy-text-small">
												本站域名：<?php echo $_SERVER['HTTP_HOST']; ?>
											</span>
										</strong>
									</center>
									<center>
										<div id="demo-acc-faq" class="panel-group accordion">
											<div class="panel panel-trans pad-top">
												<a href="#demo-acc-faq1" class="text-semibold text-lg text-main collapsed"
												data-toggle="collapse" data-parent="#demo-acc-faq" aria-expanded="false">
													下单很久了都没有开始刷呢？
												</a>
												<div id="demo-acc-faq1" class="mar-ver collapse xhy-collapse-empty" aria-expanded="false"
											>
													本站采用自动订单处理，部分商品会因供货接口或库存波动延迟处理。超过 24 小时未处理请联系客服核查。
												</div>
											</div>
											<div class="panel panel-trans pad-top">
												<a href="#demo-acc-faq2" class="text-semibold text-lg text-main collapsed"
												data-toggle="collapse" data-parent="#demo-acc-faq" aria-expanded="false">
													空间类业务下单方法说明
												</a>
												<div id="demo-acc-faq2" class="mar-ver collapse" aria-expanded="false">
													1.下单前：空间必须允许访问，并按商品说明准备内容。
													<br>
													2.处理期间请勿关闭访问权限或删除内容，否则可能影响订单处理。
												</div>
											</div>
											<div class="panel panel-trans pad-top">
												<a href="#demo-acc-faq3" class="text-semibold text-lg text-main collapsed"
												data-toggle="collapse" data-parent="#demo-acc-faq" aria-expanded="false">
													空间说说赞相关下单方法说明
												</a>
												<div id="demo-acc-faq3" class="mar-ver collapse" aria-expanded="false">
													1.下单前：空间必须允许访问，并按商品说明准备内容。
													<br>
													2.在指定栏目填写下单账号或内容 ID，按页面提示提交即可。
													<br>
													3.处理期间请勿关闭访问权限或删除内容，否则可能影响订单处理。
												</div>
											</div>
											<div class="panel panel-trans pad-top">
												<a href="#demo-acc-faq4" class="text-semibold text-lg text-main collapsed"
												data-toggle="collapse" data-parent="#demo-acc-faq" aria-expanded="false">
													全民 K 歌业务类下单方法说明
												</a>
												<div id="demo-acc-faq4" class="mar-ver collapse" aria-expanded="false">
													1.打开全民 K 歌。
													<br>
													2.复制需要提交的歌曲链接。
													<br>
													3.例如：你歌曲链接是：
													<span class="xhy-text-red">
														https://kg.qq.com/node/play?s=
														<span class="xhy-text-green">
															881Zbk8aCfIwA8U3
														</span>
														&amp;g_f=personal
													</span>
													<br>
													4.然后把 s= 后面的
													<span class="xhy-text-green">
														881Zbk8aCfIwA8U3
													</span>
													链接填入到歌曲 ID 里面，然后提交购买。
												</div>
											</div>
											<div class="panel panel-trans pad-top">
												<a href="#demo-acc-faq5" class="text-semibold text-lg text-main collapsed"
												data-toggle="collapse" data-parent="#demo-acc-faq" aria-expanded="false">
													快手业务类下单方法说明
												</a>
												<div id="demo-acc-faq5" class="mar-ver collapse" aria-expanded="false">
													1.需要填写用户 ID 和作品 ID，例如：
													<span class="xhy-text-red">
														http://www.kuaishou.com/i/photo/lwx?userId=
														<span class="xhy-text-green">
															294200023
														</span>
														&amp;photoId=
														<span class="xhy-text-green">
															1071823418
														</span>
													</span>
													(分享作品就可以看到”复制链接”了)
													<br>
													2.用户ID就是
													<span class="xhy-text-green">
														294200023
													</span>
													作品ID就是
													<span class="xhy-text-green">
														1071823418
													</span>
													，然后在分别把用户ID和作品ID填上，请勿把两个选项填反了，不给予补单！
												</div>
											</div>
											<div class="panel panel-trans pad-top">
												<a href="#demo-acc-faq6" class="text-semibold text-lg text-main collapsed"
												data-toggle="collapse" data-parent="#demo-acc-faq" aria-expanded="false">
													会员类商品下单方法说明
												</a>
												<div id="demo-acc-faq6" class="mar-ver collapse" aria-expanded="false">
													1.下单之前，先确认输的信息是不是正确的!
													<br>
													2.Q会员/钻因为需要人工处理，所以每天不定时开刷，24小时-48小时内到账！
												</div>
											</div>
										</div>
									</center>
								</a>
							</div>
						</div>
					</div>
				</div>



				<?php
			// 确保文章列表总是显示
			$limit = intval($conf['articlenum']) > 0 ? intval($conf['articlenum']) : 5;
			$rs=$DB->query("SELECT id,title FROM " . DBQZ . "article WHERE active=1 ORDER BY top DESC,id DESC LIMIT {$limit}");
			$msgrow=array();
			while($res = $rs->fetch()){
				$msgrow[]=$res;
			}
			$class_arr = ['danger','warning','primary','success','info'];
			$i=0;
			// 如果没有文章，添加一个默认文章
	if(empty($msgrow)){
				$msgrow[] = array(
					'id' => 1,
					'title' => '平台购前必看教程 - 注意事项 - 新手必读 - 协议条约'
				);
			}
			?>
				<!--文章列表-->
				<div class="xhy-article-panel">
					<div class="xhy-article-head">
						<h4 class="xhy-article-title"><i class="fa fa-newspaper-o"></i>&#25991;&#31456;&#21015;&#34920;</h4>
						<div class="xhy-article-sub">&#19979;&#21333;&#21069;&#24314;&#35758;&#20808;&#30475;&#65292;&#35268;&#21017;&#21644;&#21806;&#21518;&#35828;&#26126;&#37117;&#22312;&#36825;&#37324;&#12290;</div>
					</div>
					<div class="xhy-article-list">
						<?php foreach($msgrow as $row){
							$title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
							echo '<a target="_blank" class="xhy-article-item" href="'.article_url($row['id']).'">
								<span class="xhy-article-num">'.($i+1).'</span>
								<span class="xhy-article-text">'.$title.'</span>
								<i class="fa fa-angle-right xhy-article-arrow"></i>
							</a>';
							$i++;
						}?>
					</div>
				</div>
				<div class="xhy-fav-card">
					<div class="xhy-fav-main">
						<div class="xhy-fav-icon"><i class="fa fa-heart"></i></div>
						<div>
							<div class="xhy-fav-title">&#26412;&#31449;&#32593;&#22336;&#65306;<?php echo $_SERVER['HTTP_HOST'];?></div>
							<div class="xhy-fav-host">&#24314;&#35758;&#25910;&#34255;&#65292;&#26041;&#20415;&#19979;&#27425;&#26597;&#35810;&#35746;&#21333;&#21644;&#25552;&#20132;&#21806;&#21518;&#12290;</div>
						</div>
					</div>
					<a class="xhy-fav-btn" href="javascript:void(0);" onclick="AddFavorite('\u8d27\u6e90\u603b\u7ad9',location.href)">
						<i class="fa fa-star"></i> &#31435;&#21363;&#25910;&#34255;
					</a>
				</div>
				<div class="text-center xhy-footer-text"><?php echo $conf['footer']?></div>
			</div>
		</div>
	<script src="/assets/vendor/jquery.lazyload/1.9.1/jquery.lazyload.min.js?v=suisuivendor1"></script>
<!-- remove invalid music player script -->
<!-- 收藏代码结束-->

	</main>
	<!--音乐代码-->
	<!--音乐代码-->
	<div id="audio-play" class="<?php echo empty($conf['musicurl']) ? 'xhy-is-hidden' : ''; ?>">
	  <div id="audio-btn" class="on" onclick="audio_init.changeClass(this,'media')">
	    <audio loop="loop" src="<?php echo $conf['musicurl']?>" id="media" preload="preload"> </audio>
	  </div>
	</div>
	<script src="<?php echo $cdnserver ?>assets/appui/js/app.js?v=<?php echo VERSION; ?>"></script>
	<script src="/template/XHY-01/assets/js/xhy01.js?ver=<?php echo VERSION; ?>-xhy01refactor04"></script>
</body>
</html>
