<?php
require 'inc.php';

$urlarr=explode('/',$_SERVER['PATH_INFO']);
$act = $urlarr[1];

@header('Content-Type: text/html; charset=UTF-8');

if($act=='shequ')
	{
		$shequid = intval($urlarr[2]);
		if(!$shequid)exit('No Shequ ID');
		$tradeno = daddslashes($urlarr[3]);
		if(!$tradeno)exit('No tradeno');
		// 使用参数化查询修复SQL注入漏洞
		$shequ = $DB->getRow("SELECT * FROM pre_shequ WHERE id=:id LIMIT 1", [':id' => $shequid]);
		if(!$shequ)exit('Shequ not exists');
		// 使用参数化查询修复SQL注入漏洞
		$order = $DB->getRow("SELECT * FROM pre_orders WHERE tradeno=:tradeno LIMIT 1", [':tradeno' => $tradeno]);
		if(!$order)exit('Order not exists');
	$list = third_call($shequ['type'], $shequ, 'notify', [$order]);
	if(!$list)echo 'No support';
}
else
{
	echo 'No Act';
}
