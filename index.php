<?php
/*
首页
岁岁 @qqfaka
*/
$is_defend=true;
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('require PHP >= 7.4');
}
if (isset($_SERVER) && $_SERVER['REQUEST_URI'] == '/favicon.ico')exit;

include("./includes/common.php");

@header('Content-Type: text/html; charset=UTF-8');
if($conf['parapet']==1 && !isset($_SESSION['time'])){
	$_SESSION['time'] = time();
	exit("<script language='javascript'>window.location.href='?key=".$_SESSION['time']."';</script>");
}
if($conf['fenzhan_page']==1 && !empty($conf['fenzhan_remain']) && !in_array($domain,explode(',',$conf['fenzhan_remain'])) && $is_fenzhan==false){
	include ROOT.'template/default/404.html';
	exit;
}

$qq=isset($_GET['qq'])?htmlspecialchars(strip_tags(trim($_GET['qq']))):null;

$addsalt=md5(mt_rand(0,999).time());
$_SESSION['addsalt']=$addsalt;
$x = new \lib\hieroglyphy();
$addsalt_js = $x->hieroglyphyString($addsalt);

if($is_fenzhan==true && file_exists(ROOT.'assets/img/logo_'.$conf['zid'].'.png')){
	$logo = 'assets/img/logo_'.$conf['zid'].'.png';
}else{
	$logo = 'assets/img/logo.png';
}
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
	$cdnserver = null;
}

if(!empty($conf['gg_announce']))$conf['anounce']=$conf['gg_announce'].$conf['anounce'];

if($is_fenzhan == true && function_exists('q8_site_can_create_child_site') && !q8_site_can_create_child_site(isset($siterow) ? $siterow : array())){
	$conf['fenzhan_buy'] = 0;
}
if($is_fenzhan == true && $siterow['power']==2){
	$q8FenzhanPricing = q8_get_fenzhan_price_context($conf, $is_fenzhan, isset($siterow) ? $siterow : array());
	$conf['fenzhan_price'] = $q8FenzhanPricing['normal_price'];
	$conf['fenzhan_price2'] = $q8FenzhanPricing['professional_price'];
	$conf['fenzhan_cost2'] = $q8FenzhanPricing['professional_cost'];
}

if($conf['sitename_hide']==1 && !empty($conf['title'])){
	$hometitle = $conf['title'];
}else{
	$hometitle = $conf['sitename'].(!empty($conf['title'])?' - '.$conf['title']:null);
}

list($background_image, $background_css) = \lib\Template::getBackground();

if($conf['invite_tid'] && isset($_GET['i']) && $_GET['i']!=$_COOKIE['invite']){
	$invite_result = processInvite($_GET['i']);
	if($invite_result=='captcha'){
		@header('Content-Type: text/html; charset=UTF-8');
		include TEMPLATE_ROOT.'default/captcha.php';
		exit;
	}
}

if($conf['forceloginhome']==1 && !$islogin2){
	exit("<script language='javascript'>window.location.href='./user/login.php?back=index';</script>");
}

$mod = isset($_GET['mod'])?$_GET['mod']:'index';
$loadfile = \lib\Template::load($mod);
ob_start();
include $loadfile;
$q8_template_html = ob_get_clean();
if (function_exists('q8_inject_custom_css_into_html')) {
	echo q8_inject_custom_css_into_html($q8_template_html, 'site');
} else {
	echo $q8_template_html;
}
include ROOT."template/default/chat/widget.php";
