<?php
if (!defined('IN_CRONLITE')) exit();

function suisui_article_h($value) {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$suisuiVariant = isset($suisuiTemplateVariant) && $suisuiTemplateVariant === 'xhy01' ? 'xhy01' : 'suisui';
$suisuiTheme = $suisuiVariant === 'xhy01' ? 'coral' : 'fresh';
$suisuiFavicon = function_exists('q8_brand_favicon_href') ? q8_brand_favicon_href() : (!empty($conf['favicon']) ? $conf['favicon'] : '/assets/img/favicon/favicon.ico');
$suisuiLogo = isset($logo) && $logo ? $logo : 'assets/img/logo.png';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) sysmsg('文章ID不存在');
$row = $DB->getRow("SELECT * FROM " . DBQZ . "article WHERE id='$id' AND active=1 LIMIT 1");
if (!$row) sysmsg('当前文章不存在！');
$downResult = $DB->getRow("SELECT id,title FROM " . DBQZ . "article WHERE id<'$id' AND active=1 ORDER BY id DESC LIMIT 1");
$upResult = $DB->getRow("SELECT id,title FROM " . DBQZ . "article WHERE id>'$id' AND active=1 ORDER BY id ASC LIMIT 1");
$DB->exec("UPDATE `" . DBQZ . "article` SET `count`=`count`+1 WHERE id='$id'");
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<title><?php echo suisui_article_h($row['title']); ?> - <?php echo suisui_article_h($conf['sitename']); ?></title>
	<meta name="description" content="<?php echo suisui_article_h(isset($row['description']) ? $row['description'] : ''); ?>">
	<meta name="keywords" content="<?php echo suisui_article_h(isset($row['keywords']) ? $row['keywords'] : ''); ?>">
	<link rel="icon" href="<?php echo suisui_article_h($suisuiFavicon); ?>" type="image/x-icon">
	<link rel="stylesheet" href="/assets/vendor/twitter-bootstrap/3.4.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="/assets/vendor/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="/template/suisui/assets/css/suisui.css?ver=<?php echo VERSION; ?>.logic2">
	<?php if ($suisuiVariant === 'xhy01') { ?><link rel="stylesheet" href="/template/XHY-01/assets/css/xhy01.css?ver=<?php echo VERSION; ?>.logic2"><?php } ?>
</head>
<body class="suisui-page suisui-theme-<?php echo suisui_article_h($suisuiTheme); ?> suisui-variant-<?php echo suisui_article_h($suisuiVariant); ?>">
	<div class="suisui-shell">
		<header class="suisui-topbar">
			<a class="suisui-brand" href="./" aria-label="<?php echo suisui_article_h($conf['sitename']); ?>">
				<img class="suisui-logo" src="/<?php echo ltrim(suisui_article_h($suisuiLogo), '/'); ?>" alt="<?php echo suisui_article_h($conf['sitename']); ?>">
				<span class="suisui-brand-text">
					<span class="suisui-brand-title"><?php echo suisui_article_h($conf['sitename']); ?></span>
					<span class="suisui-brand-sub">岁岁云商城 / 岁岁 @qqfaka</span>
				</span>
			</a>
			<nav class="suisui-actions">
				<a class="suisui-btn" href="./"><i class="fa fa-home"></i> 首页</a>
				<a class="suisui-btn" href="<?php echo article_url(); ?>"><i class="fa fa-list"></i> 文章列表</a>
				<a class="suisui-btn suisui-btn-primary" href="./user/login.php"><i class="fa fa-user-circle"></i> 用户中心</a>
			</nav>
		</header>

		<main class="suisui-article-layout">
			<article class="suisui-panel suisui-article">
				<div class="suisui-article-head">
					<a class="suisui-crumb" href="<?php echo article_url(); ?>"><i class="fa fa-angle-left"></i> 返回文章列表</a>
					<h1><?php echo suisui_article_h($row['title']); ?></h1>
					<div class="suisui-article-meta">
						<span><i class="fa fa-clock-o"></i> <?php echo suisui_article_h($row['addtime']); ?></span>
						<span><i class="fa fa-eye"></i> <?php echo intval($row['count']) + 1; ?></span>
					</div>
				</div>
				<div class="suisui-article-content">
					<?php echo $row['content']; ?>
				</div>
				<div class="suisui-article-nav">
					<div>
						<span>上一篇</span>
						<?php echo empty($upResult) ? '<em>没有了</em>' : '<a href="' . article_url($upResult['id']) . '">' . suisui_article_h($upResult['title']) . '</a>'; ?>
					</div>
					<div>
						<span>下一篇</span>
						<?php echo empty($downResult) ? '<em>没有了</em>' : '<a href="' . article_url($downResult['id']) . '">' . suisui_article_h($downResult['title']) . '</a>'; ?>
					</div>
				</div>
			</article>
		</main>
	</div>
</body>
</html>
