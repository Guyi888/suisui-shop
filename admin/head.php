<?php
if(!defined('IN_CRONLITE'))exit();
@header('Content-Type: text/html; charset=UTF-8');
@header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
@header('Pragma: no-cache');
@header('Expires: 0');

$scriptpath=str_replace('\\','/',$_SERVER['SCRIPT_NAME']);
$scriptpath = substr($scriptpath, 0, strrpos($scriptpath, '/'));
$scriptpath = substr($scriptpath, 0, strrpos($scriptpath, '/'));
$siteurl = (is_https() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$scriptpath.'/';

$admin_cdnpublic = 1;
if($admin_cdnpublic==1){
	$cdnpublic = '//lib.baomitu.com/';
}elseif($admin_cdnpublic==2){
	$cdnpublic = 'https://cdn.bootcdn.net/ajax/libs/';
}elseif($admin_cdnpublic==4){
	$cdnpublic = '//s1.pstatp.com/cdn/expire-1-M/';
}else{
	$cdnpublic = '//lib.baomitu.com/';
}

$isAdminLoginPage = basename($_SERVER['SCRIPT_NAME']) === 'login.php';
$isAdminIndexPage = basename($_SERVER['SCRIPT_NAME']) === 'index.php';
$isAdminCustomCssPage = basename($_SERVER['SCRIPT_NAME']) === 'customcss.php';
$adminScriptBase = basename($_SERVER['SCRIPT_NAME'], '.php');
$adminAssetVersion = (defined('VERSION') ? VERSION : '1.0.0') . '.20260521suisuiops19';
$adminCsrfToken = q8_admin_csrf_token();
$adminFaviconHref = function_exists('q8_brand_favicon_href') ? q8_brand_favicon_href() : '/assets/img/favicon/favicon.ico';

if (!function_exists('q8_admin_page_meta')) {
	function q8_admin_page_meta($script, $title, $mod = '')
	{
		$setGroups = array(
			'用户与分站' => array('fenzhan','proxy'),
			'财务管理' => array('pay','epay','epay_settle','epay_order','codepay','rebaterecharge'),
			'供货管理' => array('sup'),
			'营销工具' => array('qiandao','invite','invitegift'),
			'对接与同步' => array('cloneset'),
			'内容与外观' => array('site','template','template2','gonggao','copygg','upimg','upbgimg','rewrite','beautify','beautify_admin','beautify_sidebar','beautify_font','beautify_background'),
			'系统与安全' => array('mail','mailcon','captcha','defend','oauth','cron','cleanbom','dwz','map','chat'),
			'员工管理' => array('account')
		);
		$groups = array(
			'商品与发卡' => array('shoprank','region_price','shopnoo','seckill','fakalist','fakakms','cardlist','stock_notice','pricecontrol','classlist','shoplist','shopedit','price'),
			'用户与分站' => array('sitelist','userlist','siteprice','rank','sitetask','sitetaskedit','mj'),
			'财务管理' => array('record','profit','settlement','kmlist','payorder','tixian','pay','epay','rebaterecharge','list','export','orderjk','set_autoreorder'),
			'供货管理' => array('suplist','supshoplist','supshoplist2','suprecord','suptixian'),
			'营销工具' => array('agenttool','qiandao','invite','invitelog','choujiang','choujiang_list','coupons','coupon_rules','user_coupons'),
			'对接与同步' => array('shequlist','pricejk','sitelogs','toollogs','cx-synchronization','cx-api-synchronization','batchgoods','batch_tool','batch_docking','ajax_batch_docking','clone','cloneset'),
			'内容与外观' => array('article','recommend','faq'),
			'工单处理' => array('workorder','workorder2','message'),
			'系统与安全' => array('cc_protect','cf_ip_whitelist','cf_ip_blocklist','cron','clean','datamove','update','set_domain_landing','set_wall_guide'),
			'员工管理' => array('account'),
			'项目信息' => array('changelog','support')
		);
		$icons = array(
			'商品与发卡' => 'fa-cubes',
			'用户与分站' => 'fa-users',
			'财务管理' => 'fa-credit-card',
			'供货管理' => 'fa-truck',
			'营销工具' => 'fa-gift',
			'对接与同步' => 'fa-plug',
			'内容与外观' => 'fa-paint-brush',
			'工单处理' => 'fa-ticket',
			'系统与安全' => 'fa-shield',
			'员工管理' => 'fa-user-circle-o',
			'项目信息' => 'fa-info-circle'
		);
		$subtitles = array(
			'商品与发卡' => '商品、卡密、库存、控价、排行与活动入口统一整理。',
			'用户与分站' => '分站、用户、密价、排行与任务数据集中运营。',
			'财务管理' => '资金流水、利润统计、结算、接口与返利配置集中查看。',
			'供货管理' => '供货商、供货商品、结算与记录保持同一操作风格。',
			'营销工具' => '代理工具、签到、邀请、抽奖、优惠券等运营功能集中处理。',
			'对接与同步' => '对接站点、价格监控、同步日志、初始化与克隆工具统一管理。',
			'内容与外观' => '公告、文章、推荐、模板、背景与外观配置统一维护。',
			'工单处理' => '用户咨询、站内信与工单处理保持统一工作台体验。',
			'系统与安全' => '计划任务、清理、安全策略、版本与运行配置集中维护。',
			'员工管理' => '后台员工账号、权限与操作范围统一管理。',
			'项目信息' => '更新日志、赞助联系与项目说明。'
		);

		if ($script === 'set') {
			foreach ($setGroups as $group => $mods) {
				if (in_array($mod, $mods, true)) {
					return array(
						'group' => $group,
						'icon' => isset($icons[$group]) ? $icons[$group] : 'fa-th-large',
						'subtitle' => isset($subtitles[$group]) ? $subtitles[$group] : '',
					);
				}
			}
		}

		foreach ($groups as $group => $pages) {
			if (in_array($script, $pages, true)) {
				return array(
					'group' => $group,
					'icon' => isset($icons[$group]) ? $icons[$group] : 'fa-th-large',
					'subtitle' => isset($subtitles[$group]) ? $subtitles[$group] : '',
				);
			}
		}

		return array(
			'group' => '后台管理',
			'icon' => 'fa-th-large',
			'subtitle' => $title ? $title . ' 的常用操作与数据管理入口。' : '后台常用操作与数据管理入口。',
		);
	}
}

$adminPageMeta = q8_admin_page_meta($adminScriptBase, $title, isset($_GET['mod']) ? trim((string)$_GET['mod']) : '');
$bodyClass = $isAdminLoginPage ? 'login-page' : 'admin-shell-page';
$bodyClass .= ' admin-page-' . preg_replace('/[^a-z0-9_-]+/i', '-', $adminScriptBase);
if ($adminScriptBase === 'set' && isset($_GET['mod'])) {
	$bodyClass .= ' admin-mod-' . preg_replace('/[^a-z0-9_-]+/i', '-', trim((string)$_GET['mod']));
}
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8"/>
  <meta name="renderer" content="webkit"/>
  <meta name="force-rendering" content="webkit"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?php echo $title ?></title>
  <link rel="icon" href="<?php echo htmlspecialchars($adminFaviconHref, ENT_QUOTES, 'UTF-8'); ?>" type="image/x-icon" />
  <link rel="shortcut icon" href="<?php echo htmlspecialchars($adminFaviconHref, ENT_QUOTES, 'UTF-8'); ?>" type="image/x-icon" />
  <link href="<?php echo $cdnpublic?>twitter-bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="<?php echo $cdnpublic?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="../assets/appui/css/main.css">
  <link rel="stylesheet" href="../assets/appui/css/themes.css">
  <link id="theme-link" rel="stylesheet" href="<?php echo $_COOKIE['optionThemeColor']?$_COOKIE['optionThemeColor']:'../assets/appui/css/themes/flat-2.4.css';?>">
  <link rel="stylesheet" href="./assets/css/admin-shell.css?v=<?php echo urlencode($adminAssetVersion); ?>">
  <?php if($isAdminIndexPage){ ?><link rel="stylesheet" href="./assets/css/admin-dashboard.css?v=<?php echo urlencode($adminAssetVersion); ?>"><?php } ?>
  <?php if($adminScriptBase === 'update'){ ?><link rel="stylesheet" href="./assets/css/admin-update.css?v=<?php echo urlencode($adminAssetVersion); ?>"><?php } ?>
  <?php if($isAdminCustomCssPage){ ?><link rel="stylesheet" href="./assets/css/admin-custom-css.css?v=<?php echo urlencode($adminAssetVersion); ?>"><?php } ?>
  <script src="<?php echo $cdnpublic?>jquery/2.1.4/jquery.min.js"></script>
  <script src="<?php echo $cdnpublic?>twitter-bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <script src="<?php echo $cdnpublic?>layer/3.1.1/layer.js"></script>
  <script src="../assets/appui/js/plugins.js"></script>
  <script src="../assets/appui/js/app2.js"></script>
  <script>
    window.ADMIN_ASSET_VERSION = <?php echo json_encode($adminAssetVersion); ?>;
    window.ADMIN_CSRF_TOKEN = <?php echo json_encode($adminCsrfToken); ?>;
  </script>
  <script src="./assets/js/admin-shell.js?v=<?php echo urlencode($adminAssetVersion); ?>"></script>
  <!--[if lt IE 9]>
    <script src="<?php echo $cdnpublic?>html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="<?php echo $cdnpublic?>respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
  <!-- Google Fonts - 动态加载，仅在需要时加载 -->
  <?php if($conf['font_beautify'] == 1){
      $font_family = $conf['font_family'] ?? 'default';

      // 定义需要Google Fonts的字体映射
      $google_fonts = [
          'playfair' => 'Playfair+Display:wght@400;700',
          'montserrat' => 'Montserrat:wght@300;400;500;700',
          'pacifico' => 'Pacifico',
          'dancing' => 'Dancing+Script:wght@400;700',
          'lobster' => 'Lobster',
          'raleway' => 'Raleway:wght@300;400;500;700',
          'oswald' => 'Oswald:wght@300;400;500;700'
      ];

      // 如果用户选择了需要Google Fonts的字体，则加载相应字体
      if(isset($google_fonts[$font_family])){
          $font_param = $google_fonts[$font_family];
          // 添加异步加载、字体子集、CORS支持等优化
          $font_url = 'https://fonts.googleapis.com/css2?family=' . $font_param . '&display=swap&subset=latin,latin-ext';
          echo '<link rel="preload" href="' . $font_url . '" as="style" onload="this.onload=null;this.rel=stylesheet" crossorigin="anonymous">';
          echo '<noscript><link rel="stylesheet" href="' . $font_url . '" crossorigin="anonymous"></noscript>';
      }
  } ?>
  <!-- 鼠标美化效果 -->
  <?php if($conf['mouse_beautify'] == 1){
      $mouse_style = $conf['mouse_style'] ?? 'default';
  ?>
  <style>
    /* 自定义鼠标样式 */
    <?php if($mouse_style == 'cute'){
    ?>
    body {
      cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%23ff69b4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>'), auto;
    }
    a, button, input, select, textarea {
      cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%23ff69b4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>'), pointer;
    }
    <?php } elseif($mouse_style == 'modern'){
    ?>
    body {
      cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="%2300bfff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>'), auto;
    }
    a, button, input, select, textarea {
      cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="%2300bfff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>'), pointer;
    }
    <?php } elseif($mouse_style == 'game'){
    ?>
    body {
      cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%23ff4500" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>'), auto;
    }
    a, button, input, select, textarea {
      cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%23ff4500" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>'), pointer;
    }
    <?php } elseif($mouse_style == 'star'){
    ?>
    body {
      cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%23ffd700" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>'), auto;
    }
    a, button, input, select, textarea {
      cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%23ffd700" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>'), pointer;
    }
    <?php } elseif($mouse_style == 'heart'){
    ?>
    body {
      cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%23ff6b9d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>'), auto;
    }
    a, button, input, select, textarea {
      cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%23ff6b9d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>'), pointer;
    }
    <?php } elseif($mouse_style == 'arrow'){
    ?>
    body {
      cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%234ecdc4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'), auto;
    }
    a, button, input, select, textarea {
      cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%234ecdc4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>'), pointer;
    }
    <?php } elseif($mouse_style == 'cross'){
    ?>
    body {
      cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%2395e1d3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>'), auto;
    }
    a, button, input, select, textarea {
      cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%2395e1d3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>'), pointer;
    }
    <?php } elseif($mouse_style == 'drop'){
    ?>
    body {
      cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%23a8e6cf" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>'), auto;
    }
    a, button, input, select, textarea {
      cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%23a8e6cf" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>'), pointer;
    }
    <?php } elseif($mouse_style == 'triangle'){
    ?>
    body {
      cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%23ffaaa5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 22h20L12 2z"></path></svg>'), auto;
    }
    a, button, input, select, textarea {
      cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%23ffaaa5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>'), pointer;
    }
    <?php
    }
    ?>
  </style>
  <?php
  }
  ?>

  <!-- 字体美化效果 -->
  <?php if($conf['font_beautify'] == 1){
      $font_family = $conf['font_family'] ?? 'default';
      $font_size = $conf['font_size'] ?? '14px';
      $font_color = $conf['font_color'] ?? '#333333';
  ?>
  <style>
    /* 自定义字体样式 */
    <?php if($font_family != 'default'){
        $font_map = [
            'sans-serif' => '"Microsoft YaHei", Arial, sans-serif',
            'serif' => '"SimSun", serif',
            'monospace' => '"Consolas", "Courier New", monospace',
            'cursive' => '"KaiTi", cursive',
            'fantasy' => '"Arial Black", fantasy',
            // 特色中文字体
            'lisu' => '"LiSu", "KaiTi", cursive',
            'kaiti' => '"KaiTi", "Noto Serif SC", serif',
            'fangsong' => '"FangSong", "SimSun", serif',
            // 特色英文字体
            'playfair' => '"Playfair Display", Georgia, serif',
            'montserrat' => '"Montserrat", "Microsoft YaHei", sans-serif',
            'pacifico' => '"Pacifico", cursive',
            'dancing' => '"Dancing Script", cursive',
            'lobster' => '"Lobster", cursive',
            'raleway' => '"Raleway", "Microsoft YaHei", sans-serif',
            'oswald' => '"Oswald", "Microsoft YaHei", sans-serif'
        ];
        $selected_font = $font_map[$font_family] ?? '"Microsoft YaHei", Arial, sans-serif';
    ?>
    body {
      font-family: <?php echo $selected_font; ?>;
      font-size: <?php echo $font_size; ?>;
      color: <?php echo $font_color; ?>;
    }

    /* 确保所有文本元素都继承字体样式 */
    h1, h2, h3, h4, h5, h6, p, a, span, div, input, button, select, textarea, li {
      font-family: inherit;
      font-size: inherit;
      color: inherit;
    }
    <?php }
    ?>
  </style>
  <?php
  }
  ?>

  <!-- 背景美化效果 -->
  <?php if($conf['background_enable'] == 1){
      $background_type = $conf['background_type'] ?? 'particles';
      $background_speed = $conf['background_speed'] ?? 5;
      $background_color = $conf['background_color'] ?? '#3498db';
      $background_image_enable = $conf['background_image_enable'] ?? 1;
      $ui_colorto = $conf['ui_colorto'] ?? 0;
      $ui_color1 = $conf['ui_color1'] ?? '#3498db';
      $ui_color2 = $conf['ui_color2'] ?? '#2980b9';
      $gradientDirection = $ui_colorto == 0 ? '180deg' : '90deg';
  ?>
  <style>
    /* 背景美化样式 */
    #background-canvas {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
    }

    /* 渐变背景样式 */
    .gradient-background {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
      background: linear-gradient(<?php echo $gradientDirection; ?>, <?php echo $ui_color1; ?> 0%, <?php echo $ui_color2; ?> 100%);
      animation: gradient-animation <?php echo 20 - ($background_speed * 1.5); ?>s ease infinite;
    }

    @keyframes gradient-animation {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
  </style>
  <script>
    // 背景美化效果
    document.addEventListener('DOMContentLoaded', function() {
      const backgroundType = '<?php echo $background_type; ?>';
      const backgroundSpeed = <?php echo $background_speed; ?>;
      const backgroundColor = '<?php echo $background_color; ?>';
      const backgroundImageEnable = <?php echo $background_image_enable; ?>;
      const uiColor1 = '<?php echo $ui_color1; ?>';
      const uiColor2 = '<?php echo $ui_color2; ?>';
      const uiColorto = <?php echo $ui_colorto; ?>;

      // 创建背景容器
      function createBackground() {
        if (backgroundType === 'gradient') {
          // 渐变背景
          const gradientDiv = document.createElement('div');
          gradientDiv.className = 'gradient-background';
          const gradientDirection = uiColorto === 0 ? '180deg' : '90deg';
          gradientDiv.style.background = `linear-gradient(${gradientDirection}, ${uiColor1} 0%, ${uiColor2} 100%)`;
          document.body.appendChild(gradientDiv);
        } else if (backgroundType === 'grid') {
          // 网格背景
          const gridDiv = document.createElement('div');
          gridDiv.className = 'grid-background';
          gridDiv.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-color: #F3F3F3;
            background-image: linear-gradient(0deg, transparent 24%, #E1E1E1 25%, #E1E1E1 26%, transparent 27%,transparent 74%, #E1E1E1 75%, #E1E1E1 76%, transparent 77%,transparent),
                linear-gradient(90deg, transparent 24%, #E1E1E1 25%, #E1E1E1 26%, transparent 27%,transparent 74%, #E1E1E1 75%, #E1E1E1 76%, transparent 77%,transparent);
            background-size: 55px 55px;
          `;
          document.body.appendChild(gridDiv);
        } else if (backgroundType === 'image' && backgroundImageEnable === 1) {
          // 自定义背景图片
          const imageDiv = document.createElement('div');
          imageDiv.className = 'image-background';
          imageDiv.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: url('assets/img/bj.png') center center no-repeat;
            background-size: cover;
          `;
          document.body.appendChild(imageDiv);
        } else if (backgroundType === 'image' && backgroundImageEnable === 0) {
          // 背景图片已禁用，不显示任何内容
        } else {
          // 其他背景类型使用canvas
          const canvas = document.createElement('canvas');
          canvas.id = 'background-canvas';
          document.body.appendChild(canvas);

          const ctx = canvas.getContext('2d');
          canvas.width = window.innerWidth;
          canvas.height = window.innerHeight;

          // 根据背景类型绘制不同效果
          if (backgroundType === 'particles') {
            drawParticles(ctx, canvas, backgroundSpeed, backgroundColor);
          } else if (backgroundType === 'matrix') {
            // 黑客效果
            drawMatrix(ctx, canvas, backgroundSpeed);
          } else if (backgroundType === 'bubbles') {
            drawBubbles(ctx, canvas, backgroundSpeed, backgroundColor);
          }
        }
      }

      // 粒子效果
      function drawParticles(ctx, canvas, speed, color) {
        const particles = [];
        const particleCount = 100;

        // 初始化粒子
        for (let i = 0; i < particleCount; i++) {
          particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            radius: Math.random() * 3 + 1,
            color: color,
            speedX: (Math.random() - 0.5) * (speed / 2),
            speedY: (Math.random() - 0.5) * (speed / 2)
          });
        }

        // 动画循环
        function animate() {
          ctx.clearRect(0, 0, canvas.width, canvas.height);

          particles.forEach(particle => {
            // 更新位置
            particle.x += particle.speedX;
            particle.y += particle.speedY;

            // 边界检测
            if (particle.x < 0 || particle.x > canvas.width) {
              particle.speedX *= -1;
            }
            if (particle.y < 0 || particle.y > canvas.height) {
              particle.speedY *= -1;
            }

            // 绘制粒子
            ctx.beginPath();
            ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
            ctx.fillStyle = particle.color;
            ctx.fill();
          });

          // 绘制连接线
          for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
              const dx = particles[i].x - particles[j].x;
              const dy = particles[i].y - particles[j].y;
              const distance = Math.sqrt(dx * dx + dy * dy);

              if (distance < 100) {
                ctx.beginPath();
                ctx.strokeStyle = `${color}${Math.floor((1 - distance / 100) * 50).toString(16).padStart(2, '0')}`;
                ctx.lineWidth = 0.5;
                ctx.moveTo(particles[i].x, particles[i].y);
                ctx.lineTo(particles[j].x, particles[j].y);
                ctx.stroke();
              }
            }
          }

          requestAnimationFrame(animate);
        }

        animate();
      }

      // 黑客效果
      function drawMatrix(ctx, canvas, speed) {
        const chars = '01アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン';
        const fontSize = 14;
        const columns = Math.floor(canvas.width / fontSize);
        const drops = [];

        // 初始化雨滴位置
        for (let i = 0; i < columns; i++) {
          drops[i] = 1;
        }

        // 动画循环
        function animate() {
          // 半透明黑色背景
          ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
          ctx.fillRect(0, 0, canvas.width, canvas.height);

          // 绿色文字
          ctx.fillStyle = '#0f0';
          ctx.font = `${fontSize}px monospace`;

          // 绘制字符
          for (let i = 0; i < drops.length; i++) {
            const text = chars[Math.floor(Math.random() * chars.length)];
            ctx.fillText(text, i * fontSize, drops[i] * fontSize);

            // 随机重置雨滴位置
            if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
              drops[i] = 0;
            }

            // 移动雨滴
            drops[i] += speed / 10;
          }

          requestAnimationFrame(animate);
        }

        animate();
      }

      // 气泡效果
      function drawBubbles(ctx, canvas, speed, color) {
        const bubbles = [];
        const bubbleCount = 50;

        // 初始化气泡
        for (let i = 0; i < bubbleCount; i++) {
          bubbles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            radius: Math.random() * 30 + 10,
            color: color,
            speedY: -Math.random() * (speed / 5) - 0.5,
            speedX: (Math.random() - 0.5) * (speed / 5),
            opacity: Math.random() * 0.5 + 0.1
          });
        }

        // 动画循环
        function animate() {
          ctx.clearRect(0, 0, canvas.width, canvas.height);

          bubbles.forEach(bubble => {
            // 更新位置
            bubble.y += bubble.speedY;
            bubble.x += bubble.speedX;

            // 边界检测
            if (bubble.y < -bubble.radius) {
              bubble.y = canvas.height + bubble.radius;
              bubble.x = Math.random() * canvas.width;
            }
            if (bubble.x < -bubble.radius || bubble.x > canvas.width + bubble.radius) {
              bubble.x = Math.random() * canvas.width;
            }

            // 绘制气泡
            ctx.beginPath();
            ctx.arc(bubble.x, bubble.y, bubble.radius, 0, Math.PI * 2);
            ctx.fillStyle = `${bubble.color}${Math.floor(bubble.opacity * 255).toString(16).padStart(2, '0')}`;
            ctx.fill();

            // 绘制气泡高光
            ctx.beginPath();
            ctx.arc(bubble.x - bubble.radius * 0.3, bubble.y - bubble.radius * 0.3, bubble.radius * 0.2, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(255, 255, 255, ${bubble.opacity * 0.8})`;
            ctx.fill();
          });

          requestAnimationFrame(animate);
        }

        animate();
      }

      // 窗口大小改变时重新调整canvas大小
      window.addEventListener('resize', function() {
        const canvas = document.getElementById('background-canvas');
        if (canvas) {
          canvas.width = window.innerWidth;
          canvas.height = window.innerHeight;
        }
      });

      // 创建背景
      createBackground();
    });
  </script>
  <?php
  }
  ?>
  <?php echo function_exists('q8_render_custom_css') ? q8_render_custom_css('admin') : ''; ?>
</head>
<body class="<?php echo $bodyClass; ?>">
<!-- 页面加载指示器 -->
<div class="page-loader is-hidden" id="pageLoader" aria-hidden="true">
    <div class="page-loader__spinner">
        <span></span>
        <span></span>
        <span></span>
    </div>
</div>
<div class="admin-bg-img"></div>
<!-- BTPanel风格全局应用，保留原有PHP逻辑和内容输出区域 -->
<?php if($islogin==1){?>
    <div class="modal fade admin-search-modal" id="searchResultsModal" tabindex="-1" role="dialog" aria-labelledby="searchResultsModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="searchResultsModalLabel"><i class="fa fa-search"></i> 菜单搜索</h4>
                </div>
                <div class="modal-body">
                    <div class="admin-search-modal__summary" id="searchResultsSummary"></div>
                    <div class="admin-search-modal__empty" id="searchResultsEmpty" hidden>未找到匹配的菜单项</div>
                    <ul class="list-group admin-search-modal__list" id="searchResultsList"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                </div>
            </div>
        </div>
    </div>
<div class="bg"></div>
<div id="root">
<!-- 后台内容区开始 -->
    <div id="page-wrapper">
        <div id="page-container" class="header-fixed-top sidebar-visible-lg-full enable-cookies">
<div id="sidebar-alt" tabindex="-1" aria-hidden="true">
<a href="javascript:void(0)" id="sidebar-alt-close" onclick="App.sidebar('toggle-sidebar-alt');"><i class="fa fa-times"></i></a>
<div class="slimScrollDiv" style="position: relative; overflow: hidden; width: auto; height: 888px;"><div id="sidebar-scroll-alt" style="overflow: hidden; width: auto; height: 888px;">
<div class="sidebar-content">
<div class="sidebar-section admin-theme-panel">
<h4 class="text-light">框架变色</h4>
<br>
<ul class="sidebar-themes clearfix">
<li class="">
<a href="javascript:void(0)" class="themed-background-default" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/themes-2.2.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="" data-original-title="">
<span class="section-side themed-background-dark-default"></span>
<span class="section-content"></span>
</a>
</li>
<li class="">
<a href="javascript:void(0)" class="themed-background-classy" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/classy-2.4.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="" data-original-title="">
<span class="section-side themed-background-dark-classy"></span>
<span class="section-content"></span>
</a>
</li>
<li class="">
<a href="javascript:void(0)" class="themed-background-social" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/social-2.4.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="" data-original-title="">
<span class="section-side themed-background-dark-social"></span>
<span class="section-content"></span>
</a>
</li>
<li class="">
<a href="javascript:void(0)" class="themed-background-flat" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/flat-2.4.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="" data-original-title="">
<span class="section-side themed-background-dark-flat"></span>
<span class="section-content"></span>
</a>
</li>
<li class="">
<a href="javascript:void(0)" class="themed-background-amethyst" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/amethyst-2.4.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="" data-original-title="">
<span class="section-side themed-background-dark-amethyst"></span>
<span class="section-content"></span>
</a>
</li>
<li class="">
<a href="javascript:void(0)" class="themed-background-creme" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/creme-2.4.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="" data-original-title="">
<span class="section-side themed-background-dark-creme"></span>
<span class="section-content"></span>
</a>
</li>
<li class="">
<a href="javascript:void(0)" class="themed-background-passion" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/passion-2.4.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="" data-original-title="">
<span class="section-side themed-background-dark-passion"></span>
<span class="section-content"></span>
</a>
</li>
<li class="">
<a href="javascript:void(0)" class="themed-background-neumorphic" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/neumorphic-2.4.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="" data-original-title="">
<span class="section-side themed-background-dark-neumorphic"></span>
<span class="section-content"></span>
</a>
<br>
</li>
<li>
<a href="javascript:void(0)" class="themed-background-classy" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/classy-2.4.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="sidebar-light" data-original-title="">
<span class="section-side"></span>
<span class="section-content"></span>
</a>
</li>
<li>
<a href="javascript:void(0)" class="themed-background-social" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/social-2.4.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="sidebar-light" data-original-title="">
<span class="section-side"></span>
<span class="section-content"></span>
</a>
</li>
<li>
<a href="javascript:void(0)" class="themed-background-flat" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/flat-2.4.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="sidebar-light" data-original-title="">
<span class="section-side"></span>
<span class="section-content"></span>
</a>
</li>
<li>
<a href="javascript:void(0)" class="themed-background-amethyst" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/amethyst-2.4.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="sidebar-light" data-original-title="">
<span class="section-side"></span>
<span class="section-content"></span>
</a>
</li>
<li>
<a href="javascript:void(0)" class="themed-background-creme" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/creme-2.4.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="sidebar-light" data-original-title="">
<span class="section-side"></span>
<span class="section-content"></span>
</a>
</li>
<li>
<a href="javascript:void(0)" class="themed-background-passion" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/passion-2.4.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="sidebar-light" data-original-title="">
<span class="section-side"></span>
<span class="section-content"></span>
</a>
</li>
<li>
<a href="javascript:void(0)" class="themed-background-neumorphic" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/neumorphic-2.4.css" data-theme-navbar="navbar-inverse" data-theme-sidebar="sidebar-light" data-original-title="">
<span class="section-side"></span>
<span class="section-content"></span>
</a>
</li>

<li class="">
<a href="javascript:void(0)" class="themed-background-classy" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/classy-2.4.css" data-theme-navbar="navbar-default" data-theme-sidebar="" data-original-title="">
<span class="section-header"></span>
<span class="section-side themed-background-dark-classy"></span>
<span class="section-content"></span>
</a>
<br>
</li>
<li class="">
<a href="javascript:void(0)" class="themed-background-social" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/social-2.4.css" data-theme-navbar="navbar-default" data-theme-sidebar="" data-original-title="">
<span class="section-header"></span>
<span class="section-side themed-background-dark-social"></span>
<span class="section-content"></span>
</a>
</li>
<li>
<a href="javascript:void(0)" class="themed-background-flat" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/flat-2.4.css" data-theme-navbar="navbar-default" data-theme-sidebar="" data-original-title="">
<span class="section-header"></span>
<span class="section-side themed-background-dark-flat"></span>
<span class="section-content"></span>
</a>
</li>
<li class="">
<a href="javascript:void(0)" class="themed-background-amethyst" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/amethyst-2.4.css" data-theme-navbar="navbar-default" data-theme-sidebar="" data-original-title="">
<span class="section-header"></span>
<span class="section-side themed-background-dark-amethyst"></span>
<span class="section-content"></span>
</a>
</li>
<li class="">
<a href="javascript:void(0)" class="themed-background-creme" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/creme-2.4.css" data-theme-navbar="navbar-default" data-theme-sidebar="" data-original-title="">
<span class="section-header"></span>
<span class="section-side themed-background-dark-creme"></span>
<span class="section-content"></span>
</a>
</li>
<li class="">
<a href="javascript:void(0)" class="themed-background-passion" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/passion-2.4.css" data-theme-navbar="navbar-default" data-theme-sidebar="" data-original-title="">
<span class="section-header"></span>
<span class="section-side themed-background-dark-passion"></span>
<span class="section-content"></span>
</a>
</li>
<li class="">
<a href="javascript:void(0)" class="themed-background-neumorphic" data-toggle="tooltip" title="" data-theme="../assets/appui/css/themes/neumorphic-2.4.css" data-theme-navbar="navbar-default" data-theme-sidebar="" data-original-title="">
<span class="section-header"></span>
<span class="section-side themed-background-dark-neumorphic"></span>
<span class="section-content"></span>
</a>
</li>
</ul>
</div>
</div>
</div><div class="slimScrollBar" style="background: rgb(187, 187, 187); width: 3px; position: absolute; top: 0px; opacity: 0.4; display: none; border-radius: 7px; z-index: 99; right: 1px; height: 888px;"></div><div class="slimScrollRail" style="width: 3px; height: 100%; position: absolute; top: 0px; display: none; border-radius: 7px; background: rgb(51, 51, 51); opacity: 1; z-index: 90; right: 1px;"></div></div>
</div>
            <div id="sidebar">
                <div id="sidebar-brand">
				<a href="./" class="sidebar-title">
                    <i class="fa fa-cube"></i> <span class="sidebar-nav-mini-hide">管理后台</span>
                </a>
				</div>
                <div id="sidebar-scroll">
                    <div class="sidebar-content">
                        <?php echo q8_render_action('admin_sidebar_before_menu', array('script' => basename($_SERVER['SCRIPT_NAME']), 'title' => $title)); ?>
                        <ul class="sidebar-nav">

<?php
$count2 = $DB->getColumn("SELECT count(*) from pre_workorder WHERE status=0 or status=1");
$count_cloud = $DB->getColumn("SELECT count(*) from pre_workorder WHERE ts=1 and status=0");
$count = $DB->getColumn("SELECT count(*) FROM `pre_tools` a
	WHERE a.`active` = 1
	AND a.`close` = 0
	AND (
		(a.`is_curl` = 4 AND NOT EXISTS (SELECT 1 FROM `pre_faka` b WHERE b.`orderid` = 0 AND b.`tid` = a.`tid`))
		OR (a.`is_curl` <> 4 AND a.`stock` IS NOT NULL AND (a.`stock` + (SELECT COUNT(*) FROM `pre_faka` b WHERE b.`orderid` = 0 AND b.`tid` = a.`tid`)) <= 0)
	)");
$count3 = $DB->getColumn("SELECT count(*) from pre_tools where goods_sid != 0 and audit_status=0");
$renderMenuBadge = function($value) {
	$value = intval($value);
	if ($value <= 0) {
		return '';
	}
	return ' <span class="label label-danger admin-menu-badge">' . $value . '</span>';
};
?>
<li>
	<a class="<?php echo checkIfActive('index,')?>" href="./">
		<i class="fa fa-home sidebar-nav-icon"></i><span class="sidebar-nav-mini-hide">后台首页</span>
	</a>
</li>

<li class="<?php echo checkIfActive('list,export,payorder,orderjk,set_autoreorder')?>">
	<a href="javascript:void(0)" class="sidebar-nav-menu"><i class="fa fa-chevron-left sidebar-nav-indicator sidebar-nav-mini-hide"></i><i class="fa fa-list sidebar-nav-icon"></i><span class="sidebar-nav-mini-hide">订单与支付</span></a>
	<ul>
	<li>
		<a class="<?php echo checkIfActive('list,export')?>" href="./list.php">
			订单列表
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive('payorder')?>" href="./payorder.php">
			支付订单
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("orderjk") ?>" href="./orderjk.php">
			订单状态监控
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("set_autoreorder") ?>" href="./set_autoreorder.php">
			自动补单设置
		</a>
	</li>
	</ul>
</li>

<li class="<?php echo checkIfActive('classlist,shoplist,shopedit,price,shoprank,recommend,cardlist,shopnoo,region_price,seckill,fakalist,fakakms,mailcon,toollogs,stock_notice,pricecontrol')?>">
	<a href="javascript:void(0)" class="sidebar-nav-menu"><i class="fa fa-chevron-left sidebar-nav-indicator sidebar-nav-mini-hide"></i><i class="fa fa-shopping-cart sidebar-nav-icon"></i><span class="sidebar-nav-mini-hide">商品与发卡</span></a>
	<ul>
	<li>
		<a class="<?php echo checkIfActive("classlist") ?>" href="./classlist.php">
			分类列表
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("shoplist,shopedit") ?>" href="./shoplist.php">
			商品列表
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("shoprank") ?>" href="./shoprank.php">
			商品销量排行
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("recommend") ?>" href="./recommend.php">
			推荐商品
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("price") ?>" href="./price.php">
			加价模板
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("region_price") ?>" href="./region_price.php">
			指定地区加价
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("toollogs") ?>" href="./toollogs.php">
			商品动态
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("shopnoo") ?>" href="./shopnoo.php">
			批量替换介绍
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("seckill") ?>" href="./seckill.php">
			秒杀商品管理
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("fakalist") ?>" href="./fakalist.php">
			发卡库存
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("fakakms") ?>" href="./fakakms.php?my=add">
			添加卡密
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("cardlist") ?>" href="./cardlist.php">
			兑换卡密
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("mailcon") ?>" href="./set.php?mod=mailcon">
			发信模板
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive('stock_notice')?>" href="./stock_notice.php">
			库存告急<?php echo $renderMenuBadge($count); ?>
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive('pricecontrol') ?>" href="./pricecontrol.php">
			控价管理
		</a>
	</li>
	</ul>
</li>

<li class="<?php echo checkIfActive('sitelist,mj,rank,userlist,message,siteprice,sitetask,sitetaskedit,fenzhan')?>">
	<a href="javascript:void(0)" class="sidebar-nav-menu"><i class="fa fa-chevron-left sidebar-nav-indicator sidebar-nav-mini-hide"></i><i class="fa fa-users sidebar-nav-icon"></i><span class="sidebar-nav-mini-hide">用户与分站</span></a>
	<ul>
	<li>
		<a class="<?php echo checkIfActive("fenzhan") ?>" href="./set.php?mod=fenzhan">
			分站相关配置
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("1,siteprice") ?>" href="./sitelist.php?mod=1">
			分站列表
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("userlist") ?>" href="./userlist.php">
			用户列表
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("mj") ?>" href="./sitelist.php?mod=mj">
			密价用户
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("rank") ?>" href="./rank.php">
			分站排行
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("sitetask,sitetaskedit") ?>" href="./sitetask.php">
			站点任务管理
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("message") ?>" href="./message.php">
			消息群发
		</a>
	</li>
	</ul>
</li>

<li class="<?php echo checkIfActive('record,tixian,profit,kmlist,pay,epay,rebaterecharge,settlement')?>">
	<a href="javascript:void(0)" class="sidebar-nav-menu"><i class="fa fa-chevron-left sidebar-nav-indicator sidebar-nav-mini-hide"></i><i class="fa fa-credit-card sidebar-nav-icon"></i><span class="sidebar-nav-mini-hide">财务管理</span></a>
	<ul>
	<li>
		<a class="<?php echo checkIfActive("record") ?>" href="./record.php">
			收支明细
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive('profit') ?>" href="./profit.php">
			利润统计
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive('settlement') ?>" href="./settlement.php">
			&#36890;&#36947;&#32467;&#31639;
		</a>
	</li>
	<?php if($conf['fenzhan_tixian']==1){?>
	<li>
		<a class="<?php echo checkIfActive("tixian") ?>" href="./tixian.php">
			余额提现
		</a>
	</li>
	<?php }?>
	<li>
		<a class="<?php echo checkIfActive("kmlist") ?>" href="./kmlist.php">
			加款卡密
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("pay,epay") ?>" href="./set.php?mod=pay">
			支付接口配置
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("rebaterecharge") ?>" href="./set.php?mod=rebaterecharge">
			充值返利设置
		</a>
	</li>
	</ul>
</li>

<li class="<?php echo checkIfActive('suplist,suptixian,suprecord,supshoplist,sup,supshoplist2')?>">
	<a href="javascript:void(0)" class="sidebar-nav-menu"><i class="fa fa-chevron-left sidebar-nav-indicator sidebar-nav-mini-hide"></i><i class="fa fa-sitemap sidebar-nav-icon"></i><span class="sidebar-nav-mini-hide">供货管理</span><?php echo $renderMenuBadge($count3); ?></a>
	<ul>
	<li>
		<a class="<?php echo checkIfActive("sup") ?>" href="./set.php?mod=sup">
			供货商相关配置
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("suplist,siteprice") ?>" href="./suplist.php">
			供货商列表
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("supshoplist2") ?>" href="./supshoplist2.php">
			商品列表
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("supshoplist") ?>" href="./supshoplist.php">
			审核商品<?php echo $renderMenuBadge($count3); ?>
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("suprecord") ?>" href="./suprecord.php">
			收支明细
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("suptixian") ?>" href="./suptixian.php">
			余额提现
		</a>
	</li>
	</ul>
</li>

<li class="<?php echo checkIfActive('qiandao,invite,invitelog,choujiang,choujiang_list,coupons,coupon_rules,user_coupons,agenttool')?>">
	<a href="javascript:void(0)" class="sidebar-nav-menu"><i class="fa fa-chevron-left sidebar-nav-indicator sidebar-nav-mini-hide"></i><i class="fa fa-gift sidebar-nav-icon"></i><span class="sidebar-nav-mini-hide">营销工具</span></a>
	<ul>
	<li>
		<a class="<?php echo checkIfActive("qiandao") ?>" href="./set.php?mod=qiandao">
			每日签到设置
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("invite,invitelog") ?>" href="./set.php?mod=invite">
			推广链接设置
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("coupons") ?>" href="./coupons.php">
			优惠券管理
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("coupon_rules") ?>" href="./coupon_rules.php">
			优惠券发放规则
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("user_coupons") ?>" href="./user_coupons.php">
			用户优惠券管理
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("choujiang") ?>" href="./choujiang.php">
			抽奖商品设置
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("choujiang_list") ?>" href="./choujiang_list.php">
			抽奖商品列表
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive('agenttool') ?>" href="./agenttool.php">
			代理工具管理
		</a>
	</li>
	</ul>
</li>

<li class="<?php echo checkIfActive('shequlist,pricejk,log,clone,cloneset,shequ,batchgoods,batch_docking,batch_tool,cx-synchronization,cx-api-synchronization')?>">
	<a href="javascript:void(0)" class="sidebar-nav-menu"><i class="fa fa-chevron-left sidebar-nav-indicator sidebar-nav-mini-hide"></i><i class="fa fa-plug sidebar-nav-icon"></i><span class="sidebar-nav-mini-hide">对接与同步</span></a>
	<ul>
	<li>
		<a class="<?php echo checkIfActive("shequlist") ?>" href="./shequlist.php">
			对接站点管理
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("pricejk") ?>" href="./pricejk.php">
			价格监控
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("log") ?>" href="./log.php">
			对接日志
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("batchgoods") ?>" href="./batchgoods.php">
			通用批量对接
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("batch_tool") ?>" href="./batch_tool.php">
			商品初始化工具
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("cx-synchronization") ?>" href="./cx-synchronization.php">
			自动同步设置
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("clone,cloneset") ?>" href="./clone.php">
			克隆站点
		</a>
	</li>
	</ul>
</li>

<li class="<?php echo checkIfActive('article,rewrite,faq,gonggao,copygg,template,template2,upimg,upbgimg,chat,beautify,beautify_admin,beautify_sidebar,beautify_font,beautify_background,customcss')?>">
	<a href="javascript:void(0)" class="sidebar-nav-menu"><i class="fa fa-chevron-left sidebar-nav-indicator sidebar-nav-mini-hide"></i><i class="fa fa-paint-brush sidebar-nav-icon"></i><span class="sidebar-nav-mini-hide">内容与外观</span></a>
	<ul>
	<li>
		<a class="<?php echo checkIfActive('article,rewrite')?>" href="./article.php">
			文章列表
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive('faq')?>" href="./faq.php">
			常见问题
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("gonggao,copygg") ?>" href="./set.php?mod=gonggao">
			网站公告配置
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("template,template2") ?>" href="./set.php?mod=template">
			首页模板设置
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("upimg,upbgimg") ?>" href="./set.php?mod=upimg">
			Logo与背景设置
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive('chat') ?>" href="./set.php?mod=chat">
			浮动客服配置
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive('beautify') ?>" href="./set.php?mod=beautify">
			前台美化
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive('beautify_admin') ?>" href="./set.php?mod=beautify_admin">
			鼠标美化
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive('beautify_sidebar') ?>" href="./set.php?mod=beautify_sidebar">
			后台侧边栏框架美化
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive('beautify_font') ?>" href="./set.php?mod=beautify_font">
			字体美化
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive('beautify_background') ?>" href="./set.php?mod=beautify_background">
			背景美化
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive('customcss') ?>" href="./customcss.php">
			自定义 CSS
		</a>
	</li>
	</ul>
</li>

<li class="<?php echo checkIfActive('workorder,workorder2')?>">
	<a href="javascript:void(0)" class="sidebar-nav-menu"><i class="fa fa-chevron-left sidebar-nav-indicator sidebar-nav-mini-hide"></i><i class="fa fa-ticket sidebar-nav-icon"></i><span class="sidebar-nav-mini-hide">工单处理</span><?php echo $renderMenuBadge($count2); ?></a>
	<ul>
	<li>
		<a class="<?php echo checkIfActive("workorder") ?>" href="./workorder.php">
			工单列表<?php echo $renderMenuBadge($count2); ?>
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("workorder2") ?>" href="./workorder2.php">
			网盘失效<?php echo $renderMenuBadge($count_cloud); ?>
		</a>
	</li>
	</ul>
</li>

<li class="<?php echo checkIfActive('site,mail,captcha,defend,oauth,cron,clean,cleanbom,update,datamove,dwz,set_domain_landing,set_wall_guide,cf_ip_whitelist,cf_ip_blocklist,sitelogs')?>">
	<a href="javascript:void(0)" class="sidebar-nav-menu"><i class="fa fa-chevron-left sidebar-nav-indicator sidebar-nav-mini-hide"></i><i class="fa fa-shield sidebar-nav-icon"></i><span class="sidebar-nav-mini-hide">系统与安全</span></a>
	<ul>
	<li>
		<a class="<?php echo checkIfActive("site") ?>" href="./set.php?mod=site">
			网站信息配置
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("mail") ?>" href="./set.php?mod=mail">
			邮箱与提醒配置
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("oauth") ?>" href="./set.php?mod=oauth">
			快捷登录配置
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("captcha") ?>" href="./set.php?mod=captcha">
			验证与IP配置
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("defend") ?>" href="./set.php?mod=defend">
			防CC攻击设置
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("dwz") ?>" href="./set.php?mod=dwz">
			防红接口设置
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive('set_domain_landing') ?>" href="./set_domain_landing.php">
			域名落地页
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive('set_wall_guide') ?>" href="./set_wall_guide.php">
			防墙引导页
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive('cf_ip_whitelist') ?>" href="./cf_ip_whitelist.php">
			CF IP白名单
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive('cf_ip_blocklist') ?>" href="./cf_ip_blocklist.php">
			CF IP黑名单
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("cron") ?>" href="./set.php?mod=cron">
			计划任务设置
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("clean") ?>" href="./clean.php">
			系统数据清理
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("datamove") ?>" href="./datamove.php">
			数据迁移
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("update") ?>" href="./update.php">
			检查版本更新
		</a>
	</li>
	<li>
		<a class="<?php echo checkIfActive("sitelogs") ?>" href="./sitelogs.php">
			站点日志
		</a>
	</li>
	</ul>
</li>

<li>
	<a class="<?php echo checkIfActive('account')?>" href="./account.php">
		<i class="fa fa-user-circle-o sidebar-nav-icon"></i><span class="sidebar-nav-mini-hide">员工权限</span>
	</a>
</li>


<li>
	<a class="<?php echo checkIfActive('changelog')?>" href="./changelog.php">
		<i class="fa fa-list-alt sidebar-nav-icon"></i><span class="sidebar-nav-mini-hide">&#26356;&#26032;&#26085;&#24535;</span>
	</a>
</li>
<li>
	<a class="<?php echo checkIfActive('support')?>" href="./support.php">
		<i class="fa fa-paper-plane sidebar-nav-icon"></i><span class="sidebar-nav-mini-hide">&#32852;&#31995;&#36190;&#21161;</span>
	</a>
</li>

<?php echo q8_render_action('admin_sidebar_after_menu', array('script' => basename($_SERVER['SCRIPT_NAME']), 'title' => $title)); ?>
                        </ul>
                    </div>
                </div>
                <div id="sidebar-extra-info" class="sidebar-content sidebar-nav-mini-hide">
<div class="progress progress-mini push-bit">
<div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
</div>
<div class="text-center">
<small><span id="year-copy">2018</span> © <a href="#"><?php echo $conf['sitename']?></a></small>
</div>
</div>
            </div>
            <div id="main-container">
                <header class="navbar navbar-inverse navbar-fixed-top">

<ul class="nav navbar-nav-custom">

<li>
<a href="javascript:void(0)" onclick="App.sidebar('toggle-sidebar');this.blur();">
<i class="fa fa-ellipsis-v fa-fw animation-fadeInRight" id="sidebar-toggle-mini"></i>
<i class="fa fa-bars fa-fw animation-fadeInRight" id="sidebar-toggle-full"></i>菜单
</a>
</li>
<li>
<a href="javascript:void(0)" onclick="javascript:history.go(-1);">
<i class="fa fa-reply fa-fw animation-fadeInRight"></i> 返回
</a>
</li>
<li class="hidden-xs admin-header-search">
<div class="navbar-form navbar-form-sm">
<div class="form-group">
<div class="input-group">
<input type="text" id="global-search" class="form-control input-sm" placeholder="全局搜索菜单..." autocomplete="off">
<span class="input-group-btn">
<button class="btn btn-sm btn-primary" type="button" onclick="performGlobalSearch()" aria-label="执行菜单搜索">
<i class="fa fa-search"></i>
</button>
</span>
</div>
</div>
</div>
</li>

</ul>


<ul class="nav navbar-nav-custom pull-right">
<li>
<a href="javascript:void(0)" onclick="App.sidebar('toggle-sidebar-alt');this.blur();">
<i class="fa fa-wrench sidebar-nav-icon"></i>
</a>
</li>
<li class="dropdown">
<a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
<img src="<?php echo site_contact_avatar($conf['kfqq'], '../assets/img/user.png'); ?>" alt="avatar">
</a>
<ul class="dropdown-menu dropdown-menu-right">
<li class="dropdown-header text-center">
<strong>管理员用户</strong>
</li>
<li>
<a href="set.php?mod=bind">
<i class="fa fa-qrcode fa-fw pull-right"></i>
扫码登录
</a>
</li>
<li>
<a href="set.php?mod=account">
<i class="fa fa-pencil-square fa-fw pull-right"></i>
密码修改
</a>
</li>
<li>
<a href="../">
<i class="fa fa-home fa-fw pull-right"></i>
网站首页
</a>
</li>
<li class="divider">
</li>
<li>
<a href="login.php?logout">
<i class="fa fa-power-off fa-fw pull-right"></i>
退出登录
</a>
</li>
</ul>
</li>
</ul>
</header>
<div id="page-content">
<div class="main-bg-img"></div>
			<div class="main pjaxmain">
				<div class="content-header admin-modern-header">
					<div class="admin-modern-header__main">
						<span class="admin-modern-header__icon"><i class="fa <?php echo htmlspecialchars($adminPageMeta['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i></span>
						<div>
							<div class="admin-modern-header__eyebrow"><?php echo htmlspecialchars($adminPageMeta['group'], ENT_QUOTES, 'UTF-8'); ?></div>
							<h1><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
							<?php if (!empty($adminPageMeta['subtitle'])) { ?>
							<p><?php echo htmlspecialchars($adminPageMeta['subtitle'], ENT_QUOTES, 'UTF-8'); ?></p>
							<?php } ?>
						</div>
					</div>
					<div class="admin-modern-header__actions">
						<a href="javascript:window.location.reload();" class="btn btn-default"><i class="fa fa-refresh"></i> 刷新</a>
						<a href="../" target="_blank" rel="noopener" class="btn btn-primary"><i class="fa fa-external-link"></i> 前台</a>
					</div>
				</div>
<div class="row">
<?php }?>
