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

if(substr($userrow['user'],0,3)=='qq_' && !empty($userrow['nickname'])){
	$nickname = htmlspecialchars($userrow['nickname']);
}else{
	$nickname = $userrow['user'];
}
if(empty($userrow['qq']) && !empty($userrow['faceimg'])){
	$faceimg = htmlspecialchars($userrow['faceimg']);
}elseif(!empty($userrow['qq'])){
	$faceimg = '//q4.qlogo.cn/headimg_dl?dst_uin='.$userrow['qq'].'&spec=100';
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
  <?php if(!empty($conf['favicon'])){echo '<link rel="icon" href="'.htmlspecialchars($conf['favicon']).'" type="image/x-icon" />';}?>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <link href="<?php echo $cdnpublic?>twitter-bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="<?php echo $cdnpublic?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="<?php echo $cdnserver?>assets/user/css/animate.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo $cdnserver?>assets/user/css/app.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo $cdnserver?>assets/user/css/modern.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo $cdnserver?>assets/user/css/modern-ultimate.css" type="text/css" />
    <script src="<?php echo $cdnpublic?>jquery/1.12.4/jquery.min.js"></script>
    <script src="<?php echo $cdnpublic?>twitter-bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="<?php echo $cdnpublic?>layer/3.1.1/layer.js"></script>
    <script src="<?php echo $cdnserver?>assets/user/js/app.js"></script>
    <script src="<?php echo $cdnserver?>assets/user/js/modern.js"></script>
  <!--[if lt IE 9]>
    <script src="<?php echo $cdnpublic?>html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="<?php echo $cdnpublic?>respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body>
<?php if($islogin2==1){
if($userrow['status']==0){
	sysmsg('你的账号已被封禁！',true);exit;
}elseif($userrow['power']>0 && $conf['fenzhan_expiry']>0 && $userrow['endtime']<$date){
	sysmsg('你的账号已到期，请联系管理员续费！',true);exit;
}
?>
<div class="app app-header-fixed">
  <!-- 头部导航 -->
  <header id="header" class="app-header navbar" role="menu">
      <div class="navbar-header">
        <a href="./" class="navbar-brand">
          <i class="fa fa-desktop hidden-xs"></i>
          <span class="hidden-folded m-l-xs">系统管理中心</span>
        </a>
        <!-- 电脑端侧边栏按钮 -->
        <a href="#" class="btn navbar-btn hidden-xs pull-right" id="desktop-menu-btn">
          <i class="fa fa-dedent fa-fw text"> 菜单</i>
          <i class="fa fa-indent fa-fw text-active">菜单</i>
        </a>
        <!-- 移动端侧边栏按钮 -->
        <button class="pull-right visible-xs" onclick="return toggleMobileSidebar(event);">
          <i class="fa fa-bars"></i>
        </button>
      </div>

      <div class="collapse pos-rlt navbar-collapse">

        <!-- 用户菜单右侧 -->
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">
            <a href="#" data-toggle="dropdown" class="dropdown-toggle clear">
              <span class="thumb-sm avatar pull-right">
                <img src="<?php echo $faceimg ?>" alt="头像">
              </span>
              <span class="hidden-sm hidden-md"><?php echo $nickname ?></span> <b class="caret"></b>
            </a>
            <ul class="dropdown-menu">
              <li><a href="./"><span>用户中心</span></a></li>
              <li><a href="./uset.php?mod=user"><span>修改资料</span></a></li>
              <li><a href="../"><span>返回首页</span></a></li>
              <li class="divider"></li>
              <li><a href="login.php?logout">退出登录</a></li>
            </ul>
          </li>
        </ul>
      </div>
  </header>

  <!-- 侧边栏导航 -->
  <aside id="aside" class="app-aside hidden-xs">
      <div class="aside-wrap">
        <div class="navi-wrap">
          <nav ui-nav class="navi">
            <ul class="nav">
              <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                <span>导航</span>
              </li>
              <li class="<?php echo checkIfActive(',index')?>">
                <a href="./">
                  <i class="fa fa-user-circle-o"></i>
                  <span>用户中心</span>
                </a>
              </li>
              <li>
                <a href="../">
                  <i class="fa fa-home"></i>
                  <span>返回首页</span>
                </a>
              </li>
              <li class="<?php echo checkIfActive('shop')?>">
                <a href="/user/shop.php">
                  <i class="fa fa-shopping-cart"></i>
                  <span>自助下单</span>
                </a>
              </li>
              <?php if($conf['openbatchorder']==1){?><li class="<?php echo checkIfActive('shops')?>">
                <a href="./shops.php">
                  <i class="fa fa-copy"></i>
                  <span>批量下单</span>
                </a>
              </li><?php }?>
              <?php if($conf['workorder_open']==1){?>
              <li class="<?php echo checkIfActive('workorder')?>">
                <a href="./workorder.php">
                  <i class="fa fa-ticket"></i>
                  <span>我的工单</span>
                </a>
              </li>
              <?php }?>
              <li class="<?php echo checkIfActive('workorder2')?>">
                <a href="./workorder2.php">
                  <i class="fa fa-comment-o"></i>
                  <span>网盘投诉</span>
                </a>
              </li>
              <?php if($userrow['power']>0){?>
              <li class="<?php echo checkIfActive('classlist,shoplist,sitelist,userlist,sitetask')?>">
                <a href="javascript:void(0)" class="auto" onclick="toggleSubmenu(this)">
                    <span class="pull-right text-muted">
                        <i class="fa fa-fw fa-angle-right"></i>
                    </span>
                    <i class="fa fa-cogs"></i>
                    <span>网站管理</span>
                </a>
                <ul class="nav nav-sub">
                  <li class="<?php echo checkIfActive('classlist')?>">
                    <a href="./classlist.php">
                      <i class="fa fa-th-large w-4 text-center"></i>
                      <span>分类管理</span>
                    </a>
                  </li>
                  <li class="<?php echo checkIfActive('shoplist')?>">
                    <a href="./shoplist.php">
                      <i class="fa fa-shopping-bag w-4 text-center"></i>
                      <span>商品管理</span>
                    </a>
                  </li>
                  <?php if($userrow['power']==2){?>
                  <li class="<?php echo checkIfActive('sitelist')?>">
                    <a href="./sitelist.php">
                      <i class="fa fa-building-o w-4 text-center"></i>
                      <span>分站列表</span>
                    </a>
                  </li><?php }?>
                  <li class="<?php echo checkIfActive('userlist')?>">
                    <a href="./userlist.php">
                      <i class="fa fa-users w-4 text-center"></i>
                      <span>用户列表</span>
                    </a>
                  </li>
                  <li class="<?php echo checkIfActive('sitetask')?>">
                    <a href="./sitetask.php">
                      <i class="fa fa-tasks w-4 text-center"></i>
                      <span>站点任务</span>
                    </a>
                  </li>
                </ul>
              </li>
              <?php }?>
              <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                <span>查询</span>
              </li>
              <li class="<?php echo checkIfActive('list')?>">
                <a href="<?php echo $userrow['power']>0?'./list.php':'../?chadan=1'?>">
                  <i class="fa fa-search"></i>
                  <span>订单查询</span>
                </a>
              </li>
              <li class="<?php echo checkIfActive('record')?>">
                <a href="./record.php">
                  <i class="fa fa-money"></i>
                  <span>收支明细</span>
                </a>
              </li>
              <?php if($userrow['power']>0 && $conf['fenzhan_rank']==1){?>
              <li class="<?php echo checkIfActive('rank')?>">
                <a href="./rank.php">
                  <i class="fa fa-line-chart"></i>
                  <span>分站排行</span>
                </a>
              </li>
              <?php }?>
              <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                <span>其他</span>
              </li>
              <li class="<?php echo checkIfActive('uset')?>">
                <a href="javascript:void(0)" class="auto" onclick="toggleSubmenu(this)">
                  <span class="pull-right text-muted">
                    <i class="fa fa-fw fa-angle-right"></i>
                  </span>
                  <i class="fa fa-cog"></i>
                  <span>系统设置</span>
                </a>
                <ul class="nav nav-sub">
                  <li class="<?php echo checkIfActive('user')?>">
                    <a href="./uset.php?mod=user">
                      <i class="fa fa-user-circle w-4 text-center"></i>
                      <span>用户资料设置</span>
                    </a>
                  </li>
                  <?php if($userrow['power']>0){?>
                  <li class="<?php echo checkIfActive('site')?>">
                    <a href="./uset.php?mod=site">
                      <i class="fa fa-globe w-4 text-center"></i>
                      <span>网站信息设置</span>
                    </a>
                  </li>
                  <li class="<?php echo checkIfActive('skimg')?>">
                    <a href="./uset.php?mod=skimg">
                      <i class="fa fa-picture-o w-4 text-center"></i>
                      <span>收款图设置</span>
                    </a>
                  </li>
                  <?php }?>
                </ul>
              </li>
              <li class="<?php echo checkIfActive('message')?>">
                <a href="./message.php">
                  <i class="fa fa-bell"></i>
                  <span>消息通知</span>
                </a>
              </li>
              <li>
                <a href="./usetmoban.php?mod=site2">
                  <i class="fa fa-desktop"></i>
                  <span>前台模板设置</span>
                </a>
              </li>
              <?php if($userrow['power']>0){?>
              <li class="<?php echo checkIfActive('faq')?>">
                <a href="./faq.php">
                  <i class="fa fa-question-circle"></i>
                  <span>常见问题</span>
                </a>
              </li>
              <?php }?>
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

  <!-- 主内容区 -->
  <div id="content" class="app-content" role="main">
    <div class="app-content-body">
      <div class="bg-light lter b-b wrapper-sm">
        <ul class="breadcrumb">
          <li><i class="fa fa-home"></i><a href="./">管理中心</a></li>
          <li><?php echo $title ?></li>
        </ul>
      </div>
<?php }?>