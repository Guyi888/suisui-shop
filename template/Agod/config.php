<?php
$template_info = [
	'name' => 'Agod',
	'version' => 1.0,
];

$bgimgurl = intval($backgroundimg)?intval($backgroundimg):123;

$template_settings = [
	'backgroundimg' => ['name'=>'自定义背景图片', 'type'=>'input', 'note'=>'默认不填写为白色'],
	'defaultcid' => ['name'=>'点亮分类ID', 'type'=>'input', 'note'=>'点亮分类ID，默认为1'],
	'userStyle' => ['name'=>'全局自定义CSS', 'type'=>'textarea', 'note'=>'如果不生效请添加!important强制'],
];
