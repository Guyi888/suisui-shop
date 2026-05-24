<?php
include "../includes/common.php";
if ($islogin != 1) {
	exit('{"code":-1,"msg":"no login"}');
}
adminpermission("site", 1);
include __DIR__ . "/includes/site_relation.php";

$zid = isset($_GET['zid']) ? intval($_GET['zid']) : 0;
header('Content-Type: text/html; charset=utf-8');
$index = q8_admin_relation_load_index();
?><!DOCTYPE html>
<html lang="zh-cn">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="../assets/vendor/twitter-bootstrap/3.4.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="../assets/vendor/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="./assets/css/admin-shell.css?v=<?php echo urlencode(defined('VERSION') ? VERSION : '1.0.0'); ?>">
	<style>body{margin:0;background:#f7fbff;}</style>
</head>
<body class="admin-shell-page">
<?php echo q8_admin_relation_detail_html($zid, $index); ?>
</body>
</html>
