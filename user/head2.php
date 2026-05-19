<?php

$cdnpublic = '../assets/vendor/';
if(!empty($conf['staticurl'])){
	$cdnserver = '//'.$conf['staticurl'].'/';
}else{
	$cdnserver = '../';
}
list($background_image, $background_css) = \lib\Template::getBackground('../');

$template_route = \lib\Template::loadRoute();
if($template_route){
	if($template_route['userlogin'] && checkIfActive('login')){
		include($template_route['userlogin']);exit;
	}elseif($template_route['userreg'] && checkIfActive('reg')){
		include($template_route['userreg']);exit;
	}elseif($template_route['userfindpwd'] && checkIfActive('findpwd')){
		include($template_route['userfindpwd']);exit;
	}elseif($template_route['userregsite'] && checkIfActive('regsite')){
		include($template_route['userregsite']);exit;
	}elseif($template_route['userregok'] && checkIfActive('regok')){
		include($template_route['userregok']);exit;
	}
}

@header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?php echo $title ?></title>
  <link href="../assets/vendor/twitter-bootstrap/3.3.7/css/bootstrap.min.css?v=q8vendor1" rel="stylesheet"/>
  <link href="../assets/vendor/font-awesome/4.7.0/css/font-awesome.min.css?v=q8vendor1" rel="stylesheet"/>
  <link rel="stylesheet" href="<?php echo $cdnserver?>assets/simple/css/plugins.css">
  <link rel="stylesheet" href="<?php echo $cdnserver?>assets/simple/css/main.css">
  <link rel="stylesheet" href="<?php echo $cdnserver?>assets/css/common.css">
  <script src="../assets/vendor/modernizr/2.8.3/modernizr.min.js?v=q8vendor1"></script>
  <!--[if lt IE 9]>
    <script src="../assets/vendor/html5shiv/3.7.3/html5shiv.min.js?v=q8vendor1"></script>
    <script src="../assets/vendor/respond.js/1.4.2/respond.min.js?v=q8vendor1"></script>
  <![endif]-->
<?php echo $background_css?>
<?php echo q8_render_custom_css('user'); ?>
</head>
<body>
