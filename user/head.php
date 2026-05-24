<?php
$cdnpublic = '../assets/vendor/';
if(!empty($conf['staticurl'])){
	$cdnserver = '//'.$conf['staticurl'].'/';
}else{
	$cdnserver = '../';
}
if($conf['ui_user']==1){
	$ui_user = array('bg-dark','bg-white-only','bg-dark');
}else{
	$ui_user = array('bg-primary','bg-primary','bg-light dker');
}

if(substr($userrow['user'],0,3)=='qq_' && !empty($userrow['nickname'])){
	$nickname = htmlspecialchars($userrow['nickname']);
}else{
	$nickname = $userrow['user'];
}
if(empty($userrow['qq']) && !empty($userrow['faceimg'])){
	$faceimg = htmlspecialchars($userrow['faceimg']);
}elseif(!empty($userrow['qq'])){
	$faceimg = '../assets/simple/img/head3.jpg';
}else{
	$faceimg = '../assets/img/user.png';
}

$q8_nav_msg_unread = 0;
$q8_nav_work_reply = 0;
if(isset($islogin2) && $islogin2==1 && isset($DB) && isset($userrow['zid'])){
	$q8_nav_msg_unread = q8_count_unread_messages($userrow);
	$q8_nav_work_reply = intval($DB->getColumn("SELECT count(*) FROM pre_workorder WHERE zid='{$userrow['zid']}' AND status=1"));
}
$q8_can_manage_child_sites = function_exists('q8_site_can_create_child_site') ? q8_site_can_create_child_site($userrow) : (isset($userrow['power']) && $userrow['power']==2);

$newuserhead=null;
$newuserfoot=null;
$template_route = \lib\Template::loadRoute();
if($template_route){
	$newuserhead = $template_route['userhead'];
	$newuserfoot = $template_route['userfoot'];
	if($template_route['userindex'] && checkIfActive(',index')){
		include($template_route['userindex']);exit;
	}
}
if($newuserhead){
	include($newuserhead);
	return;
}

@header('Content-Type: text/html; charset=UTF-8');
$q8FaviconHref = function_exists('q8_brand_favicon_href') ? q8_brand_favicon_href() : '/assets/img/favicon/favicon.ico';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8" />
  <title><?php echo $title ?></title>
  <link rel="icon" href="<?php echo htmlspecialchars($q8FaviconHref, ENT_QUOTES, 'UTF-8'); ?>" type="image/x-icon" />
  <link rel="shortcut icon" href="<?php echo htmlspecialchars($q8FaviconHref, ENT_QUOTES, 'UTF-8'); ?>" type="image/x-icon" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <link href="../assets/vendor/bootstrap341/css/bootstrap.min.css?v=q8vendor2" rel="stylesheet"/>
  <link href="../assets/vendor/font-awesome/4.7.0/css/font-awesome.min.css?v=q8vendor1" rel="stylesheet"/>
  <link href="../assets/Agod/theme/default/layer.css?v=q8userclean3" rel="stylesheet"/>
  <link rel="stylesheet" href="<?php echo $cdnserver?>assets/user/css/animate.css?v=<?php echo VERSION?>" type="text/css" />
  <link rel="stylesheet" href="<?php echo $cdnserver?>assets/user/css/app.css?v=<?php echo VERSION?>" type="text/css" />
    <script src="../assets/vendor/jquery/1.12.4/jquery.min.js?v=q8vendor1"></script>
    <script src="../assets/vendor/bootstrap341/js/bootstrap.min.js?v=q8vendor2"></script>
    <script src="../assets/Agod/layer.js?v=q8vendor1"></script>
    <script src="<?php echo $cdnserver?>assets/user/js/app.js?v=<?php echo VERSION?>"></script>
  <style id="user-nav-fouc-fixed">
    .app-header .navbar-header.bg-primary,
    .app-header .navbar-header.bg-dark,
    .app-header .navbar-collapse.bg-primary,
    .app-header .navbar-collapse.bg-white-only,
    .app-aside.bg-light,
    .app-aside.bg-dark {
      background: #ffffff !important;
      color: #1f2937 !important;
      box-shadow: 0 1px 10px rgba(15, 98, 217, .08);
    }
    .app-header .navbar-brand,
    .app-header .navbar-brand i,
    .app-header .navbar-nav > li > a,
    .app-header .navbar-btn,
    .app-header .navbar-btn i {
      color: #1f2937 !important;
    }
    .app {
      background: #f0f7ff;
    }
  </style>
  <style id="q8-user-ui-clean">
    .app .btn:not(.navbar-btn):not(.dropdown-toggle):not(.no-shadow),
    .app button.btn,
    .app a.btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
      border-radius: 8px;
      line-height: 1.15;
      white-space: nowrap;
      vertical-align: middle;
    }
    .app .btn-block,
    .app input.btn-block {
      width: 100%;
    }
    .app .btn-xs {
      min-height: 26px;
      padding: 3px 10px;
      font-size: 12px;
    }
    .app .btn-sm {
      min-height: 32px;
      padding: 6px 12px;
      font-size: 13px;
    }
    .app input.btn,
    .app input[type="submit"].btn,
    .app input[type="button"].btn {
      display: inline-block;
      text-align: center;
    }
    .app .input-group-btn .btn {
      border-radius: 0 8px 8px 0;
    }
    .app .modal-content {
      overflow: hidden;
      border: 0;
      border-radius: 14px;
      box-shadow: 0 24px 60px rgba(15,23,42,.22);
    }
    .app .modal-header {
      position: relative;
      border: 0;
      background: linear-gradient(135deg,#1677ff,#22c4c8);
      color: #fff;
    }
    .app .modal-title {
      color: #fff;
      font-weight: 900;
    }
    .app .modal-header .close {
      position: absolute;
      right: 14px;
      top: 10px;
      width: 28px;
      height: 28px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
      border-radius: 50%;
      background: rgba(255,255,255,.18);
      opacity: 1;
      color: transparent;
      text-shadow: none;
      font-size: 0;
      line-height: 1;
      outline: none;
    }
    .app .modal-header .close:before,
    .app .modal-header .close:after {
      content: "";
      position: absolute;
      width: 13px;
      height: 2px;
      border-radius: 2px;
      background: #fff;
    }
    .app .modal-header .close:before {
      transform: rotate(45deg);
    }
    .app .modal-header .close:after {
      transform: rotate(-45deg);
    }
    .app .modal-header .close:hover {
      background: rgba(239,68,68,.95);
    }
    .app .modal-footer {
      border-top: 1px solid #eaf1fb;
      background: #f7fbff;
      text-align: center;
    }
    .app .modal-footer .btn {
      min-width: 118px;
      min-height: 34px;
      border: 0;
      border-radius: 999px;
      background: linear-gradient(135deg,#1677ff,#22c4c8);
      color: #fff;
      font-weight: 900;
      box-shadow: 0 10px 22px rgba(22,119,255,.18);
    }
    .app .modal-footer .btn:hover,
    .app .modal-footer .btn:focus {
      color: #fff;
      filter: brightness(1.03);
    }
    .q8-site-notice-card {
      margin-top: 14px;
      padding: 14px 15px;
      border-radius: 10px;
      border: 1px solid #dbeafe;
      background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
      box-shadow: 0 5px 16px rgba(22,119,255,.07);
    }
    .q8-site-notice-title {
      display: flex;
      align-items: center;
      margin-bottom: 9px;
      color: #175cd3;
      font-weight: 800;
    }
    .q8-site-notice-title i {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 30px;
      height: 30px;
      margin-right: 8px;
      border-radius: 9px;
      color: #fff;
      background: linear-gradient(135deg, #1677ff, #38a3ff);
      box-shadow: 0 4px 10px rgba(22,119,255,.18);
    }
    .q8-site-notice-body {
      color: #334155;
      line-height: 1.75;
      font-size: 13px;
      word-break: break-word;
    }
    .q8-site-notice-body ul {
      padding-left: 0 !important;
      margin: 0;
      list-style: none;
    }
    .q8-site-notice-body li,
    .q8-site-notice-body p,
    .q8-site-notice-body div:not(.btn-group):not(.btn-group-justified) {
      margin-bottom: 7px;
    }
    .q8-site-notice-body .btn-group,
    .q8-site-notice-body .btn-group-justified {
      margin-top: 10px !important;
    }
    .q8-site-notice-body .btn {
      border-radius: 8px;
    }
    .q8-site-notice-stack {
      display: grid;
      gap: 12px;
    }
    .q8-site-notice-segment {
      padding: 12px 13px;
      border-radius: 10px;
      border: 1px solid #e2ebfb;
      background: rgba(255,255,255,.92);
    }
    .q8-site-notice-segment__title {
      margin-bottom: 8px;
      color: #175cd3;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: .06em;
    }
    .q8-site-notice-segment__body {
      color: #334155;
      line-height: 1.75;
      font-size: 13px;
      word-break: break-word;
    }
    .q8-site-notice-segment__body > :last-child {
      margin-bottom: 0;
    }
  </style>

  <!--[if lt IE 9]>
    <script src="../assets/vendor/html5shiv/3.7.3/html5shiv.min.js?v=q8vendor1"></script>
    <script src="../assets/vendor/respond.js/1.4.2/respond.min.js?v=q8vendor1"></script>
  <![endif]-->

  <style>
    .navbar-header button[ui-toggle="off-screen"] .fa-bars{
      font-size:18px;
      line-height:1;
    }

    .app-header .navbar{
      min-height:56px;
      height:56px;
    }
    .app-header .navbar-header{
      min-height:56px;
      height:56px;
      display:flex;
      align-items:center;
    }
    .app-header .navbar-collapse,
    .app-header .navbar-collapse .navbar-nav,
    .app-header .navbar-collapse .navbar-nav > li,
    .app-header .navbar-right{
      min-height:56px;
    }
    .app-header .navbar-nav > li > a,
    .app-header .navbar-nav > li > .btn,
    .app-header .navbar-right .dropdown-toggle.clear{
      min-height:56px;
      height:56px;
      display:flex;
      align-items:center;
    }
    .app-header .navbar-brand{
      height:56px;
      line-height:56px;
      padding-top:0;
      padding-bottom:0;
      display:flex;
      align-items:center;
    }
.app-header .q8-menu-toggle{
      height:34px;
      min-width:78px;
      padding:0 12px !important;
      display:inline-flex !important;
      align-items:center;
      justify-content:center;
      border-radius:8px;
      line-height:1;
    }
    .app-header .q8-menu-toggle .q8-menu-state{
      align-items:center;
      justify-content:center;
      gap:5px;
      font-weight:700;
      line-height:1;
      white-space:nowrap;
    }
    .app-header .q8-menu-toggle .q8-menu-state.text{display:inline-flex;}
    .app-header .q8-menu-toggle .q8-menu-state.text-active{display:none;}
    .app.app-aside-folded .app-header .q8-menu-toggle .q8-menu-state.text{display:none;}
    .app.app-aside-folded .app-header .q8-menu-toggle .q8-menu-state.text-active{display:inline-flex;}
    .app-header .q8-menu-toggle .fa-fw{
      width:auto;
      min-width:16px;
    }
    .app-header .navbar-right .dropdown-toggle.clear{
      min-height:50px;
      display:flex;
      align-items:center;
      gap:8px;
    }
    .app-header .thumb-sm.avatar{
      width:34px !important;
      height:34px !important;
      display:inline-block;
      overflow:visible;
      flex:0 0 34px;
      margin-top:0 !important;
      margin-bottom:0 !important;
    }
    .app-header .thumb-sm.avatar img{
      width:34px !important;
      height:34px !important;
      display:block;
      object-fit:cover;
      border-radius:50%;
    }
    .app-header .thumb-sm.avatar .on{
      right:0;
      bottom:0;
    }
    .navi > ul > li > a { position: relative; }
    .q8-nav-badge {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      min-width: 18px;
      height: 18px;
      padding: 0 5px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 999px;
      background: #ef4444;
      color: #fff;
      font-size: 11px;
      line-height: 18px;
      font-weight: 900;
      box-shadow: 0 6px 14px rgba(239,68,68,.22);
    }
    .app-aside-folded .q8-nav-badge {
      right: 8px;
      top: 8px;
      transform: none;
    }
  </style>

  <?php echo q8_render_custom_css('user'); ?>
</head>
<body>
<?php if($islogin2==1){
if($userrow['status']==0){
	sysmsg('&#24403;&#21069;&#36134;&#21495;&#24050;&#34987;&#31105;&#29992;', true);exit;
}elseif($userrow['power']>0 && $conf['fenzhan_expiry']>0 && $userrow['endtime']<$date){
	sysmsg('&#24403;&#21069;&#36134;&#21495;&#24050;&#21040;&#26399;&#65292;&#35831;&#32852;&#31995;&#31649;&#29702;&#21592;&#32493;&#36153;', true);exit;
}
?>
<div class="app app-header-fixed  ">
  <header id="header" class="app-header navbar ng-scope" role="menu">
      <div class="navbar-header <?php echo $ui_user[0]?>">
        <button class="pull-right visible-xs" ui-toggle="off-screen" target=".app-aside" ui-scroll="app">
          <i class="fa fa-bars"></i>
        </button>
        <a href="./" class="navbar-brand text-lt">
          <i class="fa fa-desktop hidden-xs"></i>
          <span class="hidden-folded m-l-xs">&#29992;&#25143;&#20013;&#24515;</span>
        </a>
      </div>

      <div class="collapse pos-rlt navbar-collapse box-shadow <?php echo $ui_user[1]?>">
        <!-- buttons -->
        <div class="nav navbar-nav hidden-xs">
          <a href="#" class="btn no-shadow navbar-btn q8-menu-toggle" ui-toggle="app-aside-folded" target=".app">
            <span class="text q8-menu-state"><i class="fa fa-dedent fa-fw"></i><span>&#33756;&#21333;</span></span>
            <span class="text-active q8-menu-state"><i class="fa fa-indent fa-fw"></i><span>&#33756;&#21333;</span></span>
          </a>
        </div>
        <!-- / buttons -->

        <!-- nabar right -->
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">
            <a href="#" data-toggle="dropdown" class="dropdown-toggle clear" data-toggle="dropdown">
              <span class="thumb-sm avatar pull-right m-t-n-sm m-b-n-sm m-l-sm">
                <img src="<?php echo $faceimg ?>">
                <i class="on md b-white bottom"></i>
              </span>
              <span class="hidden-sm hidden-md"><?php echo $nickname ?></span> <b class="caret"></b>
            </a>
            <!-- dropdown -->
            <ul class="dropdown-menu animated fadeInRight w">
              <li>
                <a href="./">
                  <span>&#29992;&#25143;&#39318;&#39029;</span>
                </a>
              </li>
              <li>
                <a href="./usetmoban.php?mod=user">
                  <span>&#36164;&#26009;&#35774;&#32622;</span>
                </a>
              </li>
			  <li>
                <a href="../">
                  <span>&#36820;&#22238;&#39318;&#39029;</span>
                </a>
              </li>
              <li class="divider"></li>
              <li>
                <a ui-sref="access.signin" href="login.php?logout">&#36864;&#20986;&#30331;&#24405;</a>
              </li>
            </ul>
            <!-- / dropdown -->
          </li>
        </ul>
        <!-- / navbar right -->
      </div>
      <!-- / navbar collapse -->
  </header>
  <!-- / header -->
  <!-- aside -->
  <aside id="aside" class="app-aside hidden-xs <?php echo $ui_user[2]?>">
      <div class="aside-wrap">
        <div class="navi-wrap">

          <!-- nav -->
          <nav ui-nav class="navi">
            <ul class="nav">
              <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                <span>&#24555;&#25463;&#20837;&#21475;</span>
              </li>
              <li class="<?php echo checkIfActive(',index')?>">
                <a href="./">
                  <i class="fa fa-user-circle-o"></i>
                  <span>&#29992;&#25143;&#39318;&#39029;</span>
                </a>
              </li>
	<li class="">
                <a href="../">
                  <i class="fa fa-home"></i>
                  <span>&#36820;&#22238;&#39318;&#39029;</span>
                </a>
              </li>
	<li class="<?php echo checkIfActive('sitetask,qiandao,agenttool,pricecontrol')?>">
                <a href class="auto">
                  <span class="pull-right text-muted">
                    <i class="fa fa-fw fa-angle-right text"></i>
                    <i class="fa fa-fw fa-angle-down text-active"></i>
                  </span>
                  <i class="fa fa-gift"></i>
                  <span>&#32593;&#31449;&#31119;&#21033;</span>
                </a>
                <ul class="nav nav-sub dk">
                  <?php if($conf['qiandao_reward']){?>
                  <li class="<?php echo checkIfActive('qiandao')?>">
                    <a href="./qiandao.php">
                      <i class="fa fa-calendar-check-o w-4 text-center"></i>
                      <span>&#31614;&#21040;&#39046;&#38065;</span>
                    </a>
                  </li>
                  <?php }?>
                  <li class="<?php echo checkIfActive('sitetask')?>">
                    <a href="./sitetask.php">
                      <i class="fa fa-tasks w-4 text-center"></i>
                      <span>&#20195;&#29702;&#31119;&#21033;</span>
                    </a>
                  </li>
                  <li class="<?php echo checkIfActive('agenttool')?>">
                    <a href="./agenttool.php">
                      <i class="fa fa-wrench w-4 text-center"></i>
                      <span>&#20195;&#29702;&#24037;&#20855;</span>
                    </a>
                  </li>
                  <li class="<?php echo checkIfActive('pricecontrol')?>">
                    <a href="./pricecontrol.php">
                      <i class="fa fa-shield w-4 text-center"></i>
                      <span>控价公示</span>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="<?php echo checkIfActive('shop')?>">
                <a href="/user/shop.php">
                  <i class="fa fa-shopping-cart"></i>
                  <span>&#22312;&#32447;&#19979;&#21333;</span>
                </a>
              </li>
	<?php if($conf['openbatchorder']==1){?><li class="<?php echo checkIfActive('shops')?>">
                <a href="./shops.php">
                  <i class="fa fa-copy"></i>
                  <span>&#25209;&#37327;&#19979;&#21333;</span>
                </a>
              </li><?php }?>
	<?php if($conf['workorder_open']==1){?>
	<li class="<?php echo checkIfActive('workorder')?>">
                <a href="./workorder.php">
                  <i class="fa fa-ticket"></i>
                  <span>&#25105;&#30340;&#24037;&#21333;</span>
                  <?php if($q8_nav_work_reply>0){?><em class="q8-nav-badge"><?php echo $q8_nav_work_reply?></em><?php }?>
                </a>
              </li>
	<?php }?>

	<li class="<?php echo checkIfActive('workorder2')?>">
                <a href="./workorder2.php">
                  <i class="fa fa-comment-o"></i>
                  <span>&#32593;&#30424;&#25237;&#35785;</span>
                </a>
              </li>


	<?php if($userrow['power']>0){?>
	<li class="<?php echo checkIfActive('classlist,shoplist,sitelist,userlist,visitlogs,price')?>">
                <a href class="auto">
                  <span class="pull-right text-muted">
                    <i class="fa fa-fw fa-angle-right text"></i>
                    <i class="fa fa-fw fa-angle-down text-active"></i>
                  </span>
                  <i class="fa fa-cogs"></i>
                  <span>&#31449;&#28857;&#31649;&#29702;</span>
                </a>
                <ul class="nav nav-sub dk">
		<li class="<?php echo checkIfActive('classlist')?>">
                    <a href="./classlist.php">
                      <i class="fa fa-th-large w-4 text-center"></i>
                      <span>&#20998;&#31867;&#21015;&#34920;</span>
                    </a>
                  </li>
                  <li class="<?php echo checkIfActive('shoplist')?>">
                    <a href="./shoplist.php">
                      <i class="fa fa-shopping-bag w-4 text-center"></i>
                      <span>&#21830;&#21697;&#21015;&#34920;</span>
                    </a>
                  </li>
                  <li class="<?php echo checkIfActive('price')?>">
                    <a href="./price.php">
                      <i class="fa fa-sliders w-4 text-center"></i>
                      <span>&#21152;&#20215;&#27169;&#26495;</span>
                    </a>
                  </li>
		<?php if($q8_can_manage_child_sites){?>
                  <li class="<?php echo checkIfActive('sitelist')?>">
                    <a href="./sitelist.php">
                      <i class="fa fa-building-o w-4 text-center"></i>
                      <span>&#20998;&#31449;&#21015;&#34920;</span>
                    </a>
                  </li><?php }?>
                  <li class="<?php echo checkIfActive('userlist')?>">
                    <a href="./userlist.php">
                      <i class="fa fa-users w-4 text-center"></i>
                      <span>&#29992;&#25143;&#21015;&#34920;</span>
                    </a>
                  </li>
                  <li class="<?php echo checkIfActive('visitlogs')?>">
                    <a href="./visitlogs.php">
                      <i class="fa fa-eye w-4 text-center"></i>
                      <span>&#35775;&#38382;&#26126;&#32454;</span>
                    </a>
                  </li>
                </ul>
              </li>
	<?php }?>
	<li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                <span>&#35746;&#21333;&#19982;&#36130;&#21153;</span>
              </li>
              <li class="<?php echo checkIfActive('list')?>">
                <a href="<?php echo $userrow['power']>0?'./list.php':'../?chadan=1'?>">
                  <i class="fa fa-search"></i>
                  <span>&#35746;&#21333;&#26597;&#35810;</span>
                </a>
              </li>
              <li class="<?php echo checkIfActive('record')?>">
                <a href="./record.php">
                  <i class="fa fa-money"></i>
                  <span>&#25910;&#25903;&#26126;&#32454;</span>
                </a>
              </li>
	<?php if($userrow['power']>0 && $conf['fenzhan_tixian']==1){?>
              <li class="<?php echo checkIfActive('tixian')?>">
                <a href="./tixian.php">
                  <i class="fa fa-credit-card"></i>
                  <span>&#20313;&#39069;&#25552;&#29616;</span>
                </a>
              </li>
	<?php }?>
	<?php if($userrow['power']>0 && $conf['fenzhan_rank']==1){?>
              <li class="<?php echo checkIfActive('rank')?>">
                <a href="./rank.php">
                  <i class="fa fa-line-chart"></i>
                  <span>&#31449;&#28857;&#25490;&#34892;</span>
                </a>
              </li>
	<?php }?>
              <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                <span>&#35774;&#32622;&#19982;&#26381;&#21153;</span>
              </li>
              <li class="<?php echo checkIfActive('uset,user,site,logo,skimg,upwxqrcode')?>">
                <a href class="auto">
                  <span class="pull-right text-muted">
                    <i class="fa fa-fw fa-angle-right text"></i>
                    <i class="fa fa-fw fa-angle-down text-active"></i>
                  </span>
                  <i class="fa fa-cog"></i>
                  <span>&#31995;&#32479;&#35774;&#32622;</span>
                </a>
                <ul class="nav nav-sub dk">
		<li class="<?php echo checkIfActive('user')?>">
                    <a href="./usetmoban.php?mod=user">
                      <i class="fa fa-user-circle w-4 text-center"></i>
                      <span>&#36164;&#26009;&#35774;&#32622;</span>
                    </a>
                  </li>
	<?php if($userrow['power']>0){?>
                  <li class="<?php echo checkIfActive('site')?>">
                    <a href="./usetmoban.php?mod=site">
                      <i class="fa fa-globe w-4 text-center"></i>
                      <span>&#31449;&#28857;&#20449;&#24687;&#35774;&#32622;</span>
                    </a>
                  </li>
		<?php if($conf['fenzhan_edithtml']==1){?>
                  <li class="<?php echo checkIfActive('logo')?>">
                    <a href="./usetmoban.php?mod=logo">
                      <i class="fa fa-picture-o w-4 text-center"></i>
                      <span>Logo &#35774;&#32622;</span>
                    </a>
                  </li>

		<?php }?>
                  <li class="<?php echo checkIfActive('skimg')?>">
                    <a href="./usetmoban.php?mod=skimg">
                      <i class="fa fa-picture-o w-4 text-center"></i>
                      <span>&#25910;&#27454;&#22270;&#35774;&#32622;</span>
                    </a>
                  </li>
	<?php }?>
                </ul>
              </li>
              <li class="<?php echo checkIfActive('message')?>">
                <a href="./message.php">
                  <i class="fa fa-bell"></i>
                  <span>&#28040;&#24687;&#36890;&#30693;</span>
                  <?php if($q8_nav_msg_unread>0){?><em class="q8-nav-badge"><?php echo $q8_nav_msg_unread?></em><?php }?>
                </a>
              </li>
                            <?php if($userrow['power']>0){ /* q8-template-power-only */ ?>
<li class="<?php echo checkIfActive('site2')?>">
                <a href="./usetmoban.php?mod=site2">
                  <i class="fa fa-desktop"></i>
                  <span>&#21069;&#21488;&#27169;&#26495;&#35774;&#32622;</span>
                </a>
              </li>
<?php } ?>
	<?php if($userrow['power']>0){?>
              <li class="<?php echo checkIfActive('faq')?>">
                <a href="./faq.php">
                  <i class="fa fa-question-circle"></i>
                  <span>&#24120;&#35265;&#38382;&#39064;</span>
                </a>
              </li>
	<?php }?>
              <li>
                <a ui-sref="access.signin" href="login.php?logout">
                  <i class="fa fa-sign-out"></i>
                  <span>&#36864;&#20986;&#30331;&#24405;</span>
                </a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
  </aside>
<div id="content" class="app-content" role="main">
    <div class="app-content-body ">
				<div class="bg-light lter b-b wrapper-sm ng-scope">
					<ul class="breadcrumb" style="padding: 0;margin: 0;">
						<li><i class="fa fa-home"></i><a href="./">&#29992;&#25143;&#20013;&#24515;</a></li>
						<li><?php echo $title ?></li>
					</ul>
				</div>
  <!-- / aside -->
<?php }?>
