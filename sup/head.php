<?php
if($conf['cdnpublic']==1){
	$cdnpublic = '//lib.baomitu.com/';
}elseif($conf['cdnpublic']==2){
	$cdnpublic = 'https://cdn.bootcdn.net/ajax/libs/';
}elseif($conf['cdnpublic']==4){
	$cdnpublic = '//s1.pstatp.com/cdn/expire-1-M/';
}else{
	$cdnpublic = '//lib.baomitu.com/';
}
if(!empty($conf['staticurl'])){
	$cdnserver = '//'.$conf['staticurl'].'/';
}else{
	$cdnserver = '../';
}

if(substr($suprow['user'],0,3)=='qq_' && !empty($suprow['nickname'])){
	$nickname = htmlspecialchars($suprow['nickname']);
}else{
	$nickname = $suprow['user'];
}
if(empty($suprow['qq']) && !empty($suprow['faceimg'])){
	$faceimg = htmlspecialchars($suprow['faceimg']);
}elseif(!empty($suprow['qq'])){
	$faceimg = '//q4.qlogo.cn/headimg_dl?dst_uin='.$suprow['qq'].'&spec=100';
}else{
	$faceimg = '../assets/img/user.png';
}

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
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8" />
  <title><?php echo $title ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <link href="<?php echo $cdnpublic?>twitter-bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="<?php echo $cdnpublic?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="<?php echo $cdnserver?>assets/user/css/animate.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo $cdnserver?>assets/user/css/app.css" type="text/css" />
  <style>
  /* 全局淡蓝色主题重置 */
  :root {
    --light-blue: #e3f2fd;
    --primary-blue: #1976d2;
    --primary-light: #42a5f5;
    --primary-lighter: #64b5f6;
    --primary-lightest: #bbdefb;
    --text-primary: #333333;
    --border-color: #bbdefb;
  }

  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    background-color: #f5f9ff;
    color: var(--text-primary);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }

  /* 苹果风格导航栏 */
  .apple-navbar {
      background-color: #ffffff;
      border: none;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      border-radius: 0;
      height: 44px;
      min-height: 44px;
  }

  .apple-navbar .navbar-brand {
      color: #007aff !important;
      font-weight: 600;
      font-size: 17px;
      line-height: 44px;
      padding: 0 15px;
      height: 44px;
  }

  .apple-navbar .navbar-brand i {
      margin-right: 8px;
      font-size: 18px;
  }

  /* 苹果风格按钮 */
  .apple-btn {
      border-radius: 10px;
      border: none;
      background: #007aff;
      color: white;
      font-weight: 500;
      font-size: 14px;
      padding: 8px 16px;
      transition: all 0.2s ease;
  }

  .apple-btn:hover {
      background: #0056b3;
      color: white;
  }

  /* 苹果风格侧边栏 */
  .apple-aside {
      background-color: #ffffff;
      border-right: 1px solid #e5e5ea;
      width: 220px;
      height: calc(100vh - 44px);
      overflow-y: auto;
  }

  .apple-aside .nav > li > a {
      color: #1c1c1e;
      border-radius: 10px;
      padding: 12px 16px;
      margin: 4px 8px;
      font-size: 14px;
      transition: all 0.2s ease;
      border: none;
  }

  .apple-aside .nav > li > a:hover,
  .apple-aside .nav > li.active > a {
      background-color: #f2f2f7;
      color: #007aff;
  }

  .apple-aside .nav > li > a i {
      margin-right: 12px;
      width: 16px;
      text-align: center;
      font-size: 16px;
  }

  .apple-aside .nav-sub {
      background-color: transparent;
      margin-left: 24px;
  }

  .apple-aside .nav-sub > li > a {
      padding: 8px 16px;
      color: #8e8e93;
      font-size: 13px;
      border-radius: 6px;
  }

  .apple-aside .nav-sub > li > a:hover {
      background-color: #f2f2f7;
      color: #1c1c1e;
  }

  /* 用户下拉菜单 */
  .user-dropdown .dropdown-toggle {
      color: #1c1c1e !important;
      border-radius: 10px;
      padding: 4px 12px;
      background-color: transparent;
      border: none;
      display: flex;
      align-items: center;
  }

  .user-dropdown .dropdown-toggle:hover {
      background-color: #f2f2f7;
  }

  .user-avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      margin-right: 8px;
      border: none;
  }

  .user-dropdown .dropdown-menu {
      border-radius: 13px;
      border: 1px solid #e5e5ea;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
      padding: 8px 0;
      min-width: 200px;
  }

  .user-dropdown .dropdown-menu li a {
      padding: 8px 16px;
      color: #1c1c1e;
      font-size: 14px;
  }

  .user-dropdown .dropdown-menu li a:hover {
      background-color: #f2f2f7;
      color: #007aff;
  }

  .user-dropdown .dropdown-menu .divider {
      margin: 8px 0;
      background-color: #e5e5ea;
  }

  /* 面包屑导航 */
  .breadcrumb-apple {
      background-color: transparent;
      color: #8e8e93;
      border-radius: 0;
      padding: 16px 20px;
      margin: 0;
      border: none;
      font-size: 14px;
  }

  .breadcrumb-apple a {
      color: #007aff;
  }

  .breadcrumb-apple a:hover {
      color: #0056b3;
      text-decoration: none;
  }

  .breadcrumb-apple > li + li:before {
      color: #8e8e93;
      content: ">";
      padding: 0 8px;
  }

  /* 分割线 */
  .section-divider {
      background-color: #e5e5ea;
      height: 1px;
      margin: 16px 0;
  }

  /* 菜单项标题 */
  .menu-title {
      color: #8e8e93;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      padding: 16px 16px 8px;
      letter-spacing: 0.5px;
  }

  /* 内容区域 */
  .app-content {
      background-color: #f2f2f7;
      margin-left: 220px;
      height: calc(100vh - 44px);
      overflow-y: auto;
  }

  .app-content-body {
      padding: 20px;
  }

  /* 响应式设计 */
  @media (max-width: 768px) {
      .apple-navbar .navbar-brand {
          font-size: 16px;
      }

      .apple-navbar .navbar-brand i {
          font-size: 16px;
      }

      /* 移动端侧边栏默认隐藏 */
      .apple-aside {
          position: fixed;
          left: -260px;
          top: 44px;
          width: 260px;
          height: calc(100vh - 44px);
          z-index: 1000000;
          transition: left 0.3s ease;
      }

      /* 移动端侧边栏显示状态 */
      .apple-aside.off-screen-open {
          left: 0;
      }

      /* 移动端内容区域占满整个宽度 */
      .app-content {
          margin-left: 0 !important;
          width: 100%;
      }

      /* 移动端内容区域内边距调整 */
      .app-content-body {
          padding: 10px;
      }

      /* 移动端面包屑导航样式调整 */
      .breadcrumb-apple {
          padding: 12px 10px;
          font-size: 13px;
      }

      /* 移动端按钮组适应宽度 */
      .btn-group {
          width: 100%;
          display: flex;
          margin-bottom: 10px;
      }

      .btn-group .btn {
          flex: 1;
      }

      /* 移动端表格响应式处理 */
      table {
          font-size: 13px;
      }

      /* 移动端输入框和选择框宽度调整 */
      input, select, textarea {
          width: 100%;
          max-width: 100%;
      }

      /* 添加遮罩层样式 */
      .aside-backdrop {
          position: fixed;
          top: 44px;
          left: 0;
          right: 0;
          bottom: 0;
          background-color: rgba(0, 0, 0, 0.5);
          z-index: 1030;
          display: none;
      }

      .aside-backdrop.show {
          display: block;
      }
  }

  /* 平板设备适配 */
  @media (min-width: 769px) and (max-width: 992px) {
      .apple-aside {
          width: 180px;
      }

      .app-content {
          margin-left: 180px;
      }

      .apple-aside .nav > li > a {
          padding: 10px 12px;
          font-size: 13px;
      }

      .apple-aside .nav > li > a i {
          margin-right: 8px;
      }
  }

  /* 折叠按钮 */
  .collapse-btn {
      color: #8e8e93;
      background-color: transparent;
      border: none;
      padding: 0 15px;
      line-height: 44px;
      font-size: 18px;
  }

  .collapse-btn:hover {
      color: #007aff;
      background-color: transparent;
  }

  /* 滚动条样式 */
  ::-webkit-scrollbar {
      width: 8px;
      height: 8px;
  }

  ::-webkit-scrollbar-track {
      background-color: var(--light-blue);
  }

  ::-webkit-scrollbar-thumb {
      background-color: var(--primary-lighter);
      border-radius: 4px;
  }

  ::-webkit-scrollbar-thumb:hover {
      background-color: var(--primary-light);
  }

  /* 淡蓝色主题元素样式 */

  /* div 面板标题样式 */
  .panel-heading {
      background-color: var(--primary-blue) !important;
      color: white !important;
      border-color: var(--primary-blue) !important;
  }

  /* 按钮样式 */
  .btn-primary {
      background-color: var(--primary-blue) !important;
      border-color: var(--primary-blue) !important;
  }

  .btn-primary:hover,
  .btn-primary:focus {
      background-color: var(--primary-light) !important;
      border-color: var(--primary-light) !important;
  }

  /* 链接样式 */
  a {
      color: var(--primary-blue) !important;
  }

  a:hover,
  a:focus {
      color: var(--primary-light) !important;
  }

  /* 输入框样式 */
  input[type="text"],
  input[type="password"],
  input[type="email"],
  input[type="number"],
  input[type="submit"],
  input[type="button"],
  textarea,
  select {
      background-color: var(--light-blue) !important;
      border-color: var(--border-color) !important;
      color: var(--text-primary) !important;
  }

  input[type="text"]:focus,
  input[type="password"]:focus,
  input[type="email"]:focus,
  input[type="number"]:focus,
  textarea:focus,
  select:focus {
      background-color: white !important;
      border-color: var(--primary-light) !important;
      box-shadow: 0 0 0 2px rgba(25, 118, 210, 0.2) !important;
  }

  /* 表单组样式 */
  .form-group {
      margin-bottom: 15px;
  }

  /* 面板样式 */
  .panel {
      border-color: var(--border-color) !important;
      box-shadow: 0 2px 4px rgba(25, 118, 210, 0.1) !important;
  }

  .panel-body {
      background-color: white !important;
  }

  /* 按钮通用样式 */
  button {
      background-color: var(--primary-blue) !important;
      color: white !important;
      border-color: var(--primary-blue) !important;
  }

  button:hover,
  button:focus {
      background-color: var(--primary-light) !important;
      border-color: var(--primary-light) !important;
  }

  /* 导航栏按钮 */
  .navbar-btn {
      background-color: var(--light-blue) !important;
      color: var(--primary-blue) !important;
      border-color: var(--border-color) !important;
  }

  .navbar-btn:hover {
      background-color: var(--primary-lightest) !important;
      color: var(--primary-light) !important;
  }
  </style>
    <script src="<?php echo $cdnpublic?>jquery/2.1.4/jquery.min.js"></script>
    <script src="<?php echo $cdnpublic?>twitter-bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="<?php echo $cdnpublic?>layer/3.1.1/layer.js"></script>
    <script src="<?php echo $cdnserver?>assets/user/js/app.js"></script>
    <script>
    // 移动端侧边栏控制脚本
    $(document).ready(function() {
        // 添加遮罩层
        var backdrop = $('<div class="aside-backdrop"></div>');
        $(document.body).append(backdrop);

        // 侧边栏开关按钮点击事件
        $('.pull-right.visible-xs').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var aside = $('.apple-aside');
            aside.toggleClass('off-screen-open');
            backdrop.toggleClass('show');
        });

        // 点击遮罩层关闭侧边栏
        backdrop.on('click', function() {
            $('.apple-aside').removeClass('off-screen-open');
            $(this).removeClass('show');
        });

        // 侧边栏内点击阻止冒泡
        $('.apple-aside').on('click', function(e) {
            e.stopPropagation();
        });

        // 折叠菜单项控制
        $('.nav > li > a.auto').on('click', function(e) {
            if ($(window).width() <= 768) {
                e.preventDefault();
                var parentLi = $(this).parent('li');
                var submenu = parentLi.find('ul.nav-sub');

                if (submenu.is(':visible')) {
                    submenu.slideUp(200);
                    parentLi.removeClass('active');
                    $(this).find('.fa-angle-down').removeClass('fa-angle-down').addClass('fa-angle-right');
                } else {
                    // 关闭其他打开的子菜单
                    $('.nav > li > ul.nav-sub').slideUp(200);
                    $('.nav > li > a.auto .fa-angle-down').removeClass('fa-angle-down').addClass('fa-angle-right');

                    // 打开当前子菜单
                    submenu.slideDown(200);
                    parentLi.addClass('active');
                    $(this).find('.fa-angle-right').removeClass('fa-angle-right').addClass('fa-angle-down');
                }
            }
        });

        // 窗口大小变化时调整布局
        $(window).on('resize', function() {
            var width = $(window).width();

            if (width > 768) {
                // 桌面模式
                $('.apple-aside').removeClass('off-screen-open');
                backdrop.removeClass('show');
                $('.nav > li > ul.nav-sub').css('display', '');
            }
        });
    });
    </script>
  <!--[if lt IE 9]>
    <script src="<?php echo $cdnpublic?>html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="<?php echo $cdnpublic?>respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body>
<?php if($islogin3==1){
if($suprow['status']==0){
	sysmsg('你的账号已被封禁！',true);exit;
}
?>
<div class="app app-header-fixed">
  <header id="header" class="app-header navbar ng-scope apple-navbar" role="menu">
      <div class="navbar-header">
        <button class="pull-right visible-xs" ui-toggle="off-screen" target=".app-aside" ui-scroll="app">
          <i class="fa fa-bars"></i>
        </button>
        <a href="./" class="navbar-brand">
          <i class="fa fa-shopping-bag"></i>
          <span class="hidden-folded m-l-xs">供货商管理中心</span>
        </a>
      </div>

      <div class="collapse pos-rlt navbar-collapse">
        <!-- buttons -->
        <div class="nav navbar-nav hidden-xs">
          <button class="collapse-btn" ui-toggle="app-aside-folded" target=".app">
            <i class="fa fa-bars"></i>
          </button>
        </div>
        <!-- / buttons -->

        <!-- navbar right -->
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown user-dropdown">
            <a href="#" data-toggle="dropdown" class="dropdown-toggle clear">
              <img src="<?php echo $faceimg ?>" class="user-avatar">
              <span class="hidden-sm hidden-md"><?php echo $nickname ?></span>
              <b class="caret"></b>
            </a>
            <!-- dropdown -->
            <ul class="dropdown-menu animated fadeInRight">
              <li>
                <a href="./">
                  <i class="fa fa-home"></i> <span>控制台</span>
                </a>
              </li>
              <li>
                <a href="./uset.php?mod=user">
                  <i class="fa fa-user"></i> <span>修改资料</span>
                </a>
              </li>
			  <li>
                <a href="../">
                  <i class="fa fa-external-link"></i> <span>返回首页</span>
                </a>
              </li>
              <li class="divider"></li>
              <li>
                <a href="login.php?logout">
                  <i class="fa fa-sign-out"></i> <span>退出登录</span>
                </a>
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
  <aside id="aside" class="app-aside apple-aside">
      <div class="aside-wrap">
        <div class="navi-wrap">
          <!-- nav -->
          <nav class="navi">
            <ul class="nav">
              <li class="menu-title">
                主导航
              </li>
              <li class="<?php echo checkIfActive(',index')?>">
                <a href="./">
                  <i class="fa fa-dashboard"></i>
                  <span>控制台</span>
                </a>
              </li>
			  <li class="">
                <a href="../">
                  <i class="fa fa-home"></i>
                  <span>返回首页</span>
                </a>
              </li>

              <div class="section-divider"></div>

              <li class="menu-title">
                保证金管理
              </li>
                <li class="<?php echo checkIfActive('bond')?>">
                <a href="./bond.php" class="auto">
              <span class="pull-right text-muted">
                <i class="fa fa-fw fa-angle-right text"></i>
                <i class="fa fa-fw fa-angle-down text-active"></i>
              </span>
                    <i class="fa fa-lock"></i>
                    <span>保证金管理</span>
                </a>
                    <ul class="nav nav-sub">
                        <li class="<?php echo checkIfActive('bond')?>">
                            <a href="./bond.php">
                                <span>缴纳保证金</span>
                            </a>
                        </li>
                        <li class="<?php echo checkIfActive('bond')?>">
                            <a href="./bond.php?act=thaw">
                                <span>解冻保证金</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <div class="section-divider"></div>

                <li class="menu-title">
                    供货管理
                </li>
                <li class="<?php echo checkIfActive('shoplist,shopedit')?>">
                    <a href="./shoplist.php" class="auto">
                  <span class="pull-right text-muted">
                    <i class="fa fa-fw fa-angle-right text"></i>
                    <i class="fa fa-fw fa-angle-down text-active"></i>
                  </span>
                        <i class="fa fa-cubes"></i>
                        <span>供货管理</span>
                    </a>
                    <ul class="nav nav-sub">
                        <li class="<?php echo checkIfActive('shoplist')?>">
                            <a href="./shoplist.php">
                                <span>商品管理</span>
                            </a>
                        </li>
                        <li class="<?php echo checkIfActive('fakalist')?>">
                            <a href="./fakalist.php">
                                <span>卡密管理</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <div class="section-divider"></div>

			  <li class="menu-title">
                查询统计
              </li>
              <li class="<?php echo checkIfActive('record')?>">
                <a href="./record.php">
                  <i class="fa fa-chart-bar"></i>
                  <span>收支明细</span>
                </a>
              </li>

              <div class="section-divider"></div>

              <li class="menu-title">
                系统设置
              </li>
              <li class="<?php echo checkIfActive('uset')?>">
                <a href="./uset.php?mod=user" class="auto">
                  <span class="pull-right text-muted">
                    <i class="fa fa-fw fa-angle-right text"></i>
                    <i class="fa fa-fw fa-angle-down text-active"></i>
                  </span>
                  <i class="fa fa-cog"></i>
                  <span>系统设置</span>
                </a>
                <ul class="nav nav-sub">
			  <li class="<?php echo checkIfActive('user')?>">
                    <a href="./uset.php?mod=user">
                      <span>用户资料设置</span>
                    </a>
                  </li>
                  <li class="<?php echo checkIfActive('skimg')?>">
                    <a href="./uset.php?mod=skimg">
                      <span>收款图设置</span>
                    </a>
                  </li>
                </ul>
              </li>

              <div class="section-divider"></div>

              <li>
                <a href="login.php?logout">
                  <i class="fa fa-sign-out"></i>
                  <span>退出登录</span>
                </a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
  </aside>
<div id="content" class="app-content" role="main">
    <div class="app-content-body">
			<div class="breadcrumb-apple">
				<ul class="breadcrumb">
					<li><a href="./">管理中心</a></li>
					<li><?php echo $title ?></li>
				</ul>
			</div>
  <!-- / aside -->
<?php }?>

