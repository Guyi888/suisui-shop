<?php
if(!defined('IN_CRONLITE'))exit();

// 检查是否需要执行
$cron_lastdo = $DB->getColumn("SELECT v FROM pre_config WHERE k='cron_auto_sync_lastdo'");
if($cron_lastdo && time() - strtotime($cron_lastdo) < 300) exit('Too frequent');

// 更新最后执行时间
$DB->exec("REPLACE INTO pre_config SET k='cron_auto_sync_lastdo',v='".$date."'");

// 调用同步API
$url = $siteurl.'admin/cx-api-synchronization.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
curl_close($ch);

if($response == 'success'){
    // 记录成功日志
    $DB->exec("INSERT INTO `pre_log` (`type`,`date`,`ip`,`city`,`data`) VALUES ('cron','".$date."','127.0.0.1','系统','自动同步任务执行成功')");
}else{
    // 记录失败日志
    $DB->exec("INSERT INTO `pre_log` (`type`,`date`,`ip`,`city`,`data`) VALUES ('cron','".$date."','127.0.0.1','系统','自动同步任务执行失败')");
}
?> 