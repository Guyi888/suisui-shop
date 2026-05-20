<?php
if (!defined('IN_CRONLITE')) exit();

function suisui_article_list_h($value) {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$suisuiVariant = isset($suisuiTemplateVariant) && $suisuiTemplateVariant === 'xhy01' ? 'xhy01' : 'suisui';
$suisuiTheme = $suisuiVariant === 'xhy01' ? 'coral' : 'fresh';
$suisuiFavicon = function_exists('q8_brand_favicon_href') ? q8_brand_favicon_href() : (!empty($conf['favicon']) ? $conf['favicon'] : '/assets/img/favicon/favicon.ico');
$suisuiLogo = isset($logo) && $logo ? $logo : 'assets/img/logo.png';
$kw = isset($_GET['kw']) ? trim(strip_tags((string)$_GET['kw'])) : '';
$where = "active=1";
if ($kw !== '') {
	$where .= " AND title LIKE '%" . daddslashes($kw) . "%'";
}
$msgcount = intval($DB->getColumn("SELECT count(*) FROM " . DBQZ . "article WHERE $where"));
$pagesize = 10;
$pages = max(1, (int)ceil($msgcount / $pagesize));
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
if ($page > $pages) $page = $pages;
$offset = $pagesize * ($page - 1);
$rs = $DB->query("SELECT id,title,content,addtime FROM " . DBQZ . "article WHERE $where ORDER BY top DESC,id DESC LIMIT $offset,$pagesize");
$msgrow = array();
while ($res = $rs->fetch(PDO::FETCH_ASSOC)) {
	$msgrow[] = $res;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<title><?php echo suisui_article_list_h($conf['sitename']); ?> - 文章列表</title>
	<link rel="icon" href="<?php echo suisui_article_list_h($suisuiFavicon); ?>" type="image/x-icon">
	<link rel="stylesheet" href="/assets/vendor/twitter-bootstrap/3.4.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="/assets/vendor/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="/template/suisui/assets/css/suisui.css?ver=<?php echo VERSION; ?>.logic2">
	<?php if ($suisuiVariant === 'xhy01') { ?><link rel="stylesheet" href="/template/XHY-01/assets/css/xhy01.css?ver=<?php echo VERSION; ?>.logic2"><?php } ?>
</head>
<body class="suisui-page suisui-theme-<?php echo suisui_article_list_h($suisuiTheme); ?> suisui-variant-<?php echo suisui_article_list_h($suisuiVariant); ?>">
	<div class="suisui-shell">
		<header class="suisui-topbar">
			<a class="suisui-brand" href="./" aria-label="<?php echo suisui_article_list_h($conf['sitename']); ?>">
				<img class="suisui-logo" src="/<?php echo ltrim(suisui_article_list_h($suisuiLogo), '/'); ?>" alt="<?php echo suisui_article_list_h($conf['sitename']); ?>">
				<span class="suisui-brand-text">
					<span class="suisui-brand-title"><?php echo suisui_article_list_h($conf['sitename']); ?></span>
					<span class="suisui-brand-sub">岁岁云商城 / 岁岁 @qqfaka</span>
				</span>
			</a>
			<nav class="suisui-actions">
				<a class="suisui-btn" href="./"><i class="fa fa-home"></i> 首页</a>
				<a class="suisui-btn suisui-btn-primary" href="./user/login.php"><i class="fa fa-user-circle"></i> 用户中心</a>
			</nav>
		</header>

		<main class="suisui-article-layout">
			<section class="suisui-panel suisui-article-list-panel">
				<div class="suisui-article-head">
					<div class="suisui-kicker"><i class="fa fa-newspaper-o"></i> 平台公告与帮助</div>
					<h1>文章列表</h1>
					<p>查看平台公告、商品说明和使用帮助。</p>
				</div>
				<form class="suisui-article-search" action="./" method="get">
					<input type="hidden" name="mod" value="articlelist">
					<input type="text" name="kw" class="form-control" value="<?php echo suisui_article_list_h($kw); ?>" placeholder="输入关键词搜索文章">
					<button type="submit" class="suisui-btn suisui-btn-primary"><i class="fa fa-search"></i> 搜索</button>
				</form>
				<div class="suisui-article-list">
					<?php foreach ($msgrow as $row) {
						$content = trim(strip_tags($row['content']));
						if (mb_strlen($content, 'UTF-8') > 100) $content = mb_substr($content, 0, 100, 'UTF-8') . '...';
					?>
					<a class="suisui-article-item" href="<?php echo article_url($row['id']); ?>">
						<strong><?php echo suisui_article_list_h($row['title']); ?></strong>
						<span><?php echo suisui_article_list_h($row['addtime']); ?></span>
						<p><?php echo suisui_article_list_h($content); ?></p>
					</a>
					<?php } ?>
					<?php if (!$msgrow) { ?>
					<div class="suisui-empty"><i class="fa fa-info-circle"></i> 暂无文章</div>
					<?php } ?>
				</div>
				<?php if ($msgcount > $pagesize) {
					$kwLink = $kw !== '' ? '&kw=' . urlencode($kw) : '';
				?>
				<div class="suisui-pager">
					<?php if ($page > 1) { ?><a class="suisui-btn" href="<?php echo article_url(0, 'page=' . ($page - 1) . $kwLink); ?>"><i class="fa fa-angle-left"></i> 上一页</a><?php } ?>
					<span><?php echo $page; ?> / <?php echo $pages; ?></span>
					<?php if ($page < $pages) { ?><a class="suisui-btn" href="<?php echo article_url(0, 'page=' . ($page + 1) . $kwLink); ?>">下一页 <i class="fa fa-angle-right"></i></a><?php } ?>
				</div>
				<?php } ?>
			</section>
		</main>
	</div>
</body>
</html>
