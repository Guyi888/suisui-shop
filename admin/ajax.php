<?php
include("../includes/common.php");
$act=isset($_GET['act'])?daddslashes($_GET['act']):null;

$noAdminActions = ['create_chat_session'];
if(!in_array($act, $noAdminActions) && $islogin==1){}else if(in_array($act, $noAdminActions)){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");

@header('Content-Type: application/json; charset=UTF-8');

if(!checkRefererHost())exit('{"code":403}');

function q8_admin_front_visit_where(){
	return "url NOT LIKE '/admin/%' AND url NOT LIKE '/api/%' AND url NOT LIKE '/includes/%' AND url NOT LIKE '/assets/%' AND url NOT LIKE '/template/%' AND url NOT LIKE '%ajax.php%' AND url NOT LIKE '%api.php%' AND url NOT LIKE '%cron.php%' AND url NOT REGEXP '\\\\.(css|js|png|jpg|jpeg|gif|ico|svg|webp|woff|woff2|ttf|map)(\\\\?|$)'";
}

function q8_admin_dashboard_trade_chart(){
	global $DB;
	$chart = array('date' => array(), 'date_full' => array(), 'orders' => array(), 'money' => array(), 'range_start' => '', 'range_end' => '');
	for($i = 6; $i >= 0; $i--) {
		$date = date('Y-m-d', strtotime("-$i days"));
		$start = $date . ' 00:00:00';
		$end = $date . ' 23:59:59';
		$point = 6 - $i;
		if($i == 6) $chart['range_start'] = $date;
		if($i == 0) $chart['range_end'] = $date;
		$orderCount = $DB->getColumn("SELECT count(*) FROM pre_orders WHERE addtime>='{$start}' AND addtime<='{$end}'");
		$payMoney = $DB->getColumn("SELECT COALESCE(sum(money),0) FROM pre_pay WHERE addtime>='{$start}' AND addtime<='{$end}' AND status=1");
		$chart['date'][] = array($point, date('m-d', strtotime($date)));
		$chart['date_full'][] = $date;
		$chart['orders'][] = array($point, intval($orderCount));
		$chart['money'][] = array($point, round($payMoney, 2));
	}
	return $chart;
}

function q8_admin_message_scope_labels(){
	if(function_exists('q8_get_message_scope_labels')){
		return q8_get_message_scope_labels();
	}
	return array(
		0 => html_entity_decode('&#20840;&#37096;&#29992;&#25143;', ENT_QUOTES, 'UTF-8'),
		1 => html_entity_decode('&#20840;&#37096;&#26222;&#36890;&#29992;&#25143;', ENT_QUOTES, 'UTF-8'),
		2 => html_entity_decode('&#20840;&#37096;&#20998;&#31449;&#31449;&#38271;', ENT_QUOTES, 'UTF-8'),
		3 => html_entity_decode('&#26222;&#21450;&#29256;&#20998;&#31449;&#31449;&#38271;', ENT_QUOTES, 'UTF-8'),
		4 => html_entity_decode('&#19987;&#19994;&#29256;&#20998;&#31449;&#31449;&#38271;', ENT_QUOTES, 'UTF-8'),
		5 => html_entity_decode('&#20027;&#31449;&#26222;&#36890;&#29992;&#25143;', ENT_QUOTES, 'UTF-8'),
		6 => html_entity_decode('&#20998;&#31449;&#19979;&#32423;&#26222;&#36890;&#29992;&#25143;', ENT_QUOTES, 'UTF-8')
	);
}

function q8_admin_message_scope_text($scope){
	$labels = q8_admin_message_scope_labels();
	$scope = intval($scope);
	return isset($labels[$scope]) ? $labels[$scope] : $labels[0];
}

function q8_admin_message_status_text($status){
	$status = intval($status);
	if($status === 1) return html_entity_decode('&#21457;&#36865;&#20013;', ENT_QUOTES, 'UTF-8');
	if($status === 2) return html_entity_decode('&#24050;&#23436;&#25104;', ENT_QUOTES, 'UTF-8');
	return html_entity_decode('&#24453;&#21457;&#36865;', ENT_QUOTES, 'UTF-8');
}

function q8_admin_message_mail_ready(){
	global $conf;
	if(intval($conf['mail_cloud']) === 1 || intval($conf['mail_cloud']) === 2){
		return !empty($conf['mail_apiuser']) && !empty($conf['mail_apikey']);
	}
	return !empty($conf['mail_name']) && !empty($conf['mail_port']) && !empty($conf['mail_smtp']) && !empty($conf['mail_pwd']);
}

function q8_admin_message_fetch_task($taskId){
	global $DB;
	q8_mail_center_ensure_tables();
	return $DB->getRow("SELECT * FROM pre_mail_task WHERE id=:id LIMIT 1", array(':id' => intval($taskId)));
}

function q8_admin_message_task_payload($task){
	if(!$task) return null;
	$total = intval($task['total_count']);
	$success = intval($task['success_count']);
	$fail = intval($task['fail_count']);
	$pending = max(0, $total - $success - $fail);
	$targetMode = intval(isset($task['target_mode']) ? $task['target_mode'] : 0);
	$targetValue = trim((string)(isset($task['target_value']) ? $task['target_value'] : ''));
	$targetLabel = trim((string)(isset($task['target_label']) ? $task['target_label'] : ''));
	$scopeLabels = q8_admin_message_scope_labels();
	$scopeValue = intval($task['scope']);
	$scopeText = isset($scopeLabels[$scopeValue]) ? $scopeLabels[$scopeValue] : $scopeLabels[0];
	$statusValue = intval($task['status']);
	$statusText = q8_admin_message_status_text($statusValue);
	if($targetMode === 1){
		$specificScope = html_entity_decode('&#25351;&#23450;&#29992;&#25143;', ENT_QUOTES, 'UTF-8');
		$scopeText = $targetLabel !== '' ? ($specificScope . ' | ' . $targetLabel) : ($targetValue !== '' ? ($specificScope . ' | ' . $targetValue) : $specificScope);
	}
	return array(
		'id' => intval($task['id']),
		'scope' => intval($task['scope']),
		'scope_text' => $scopeText,
		'target_mode' => $targetMode,
		'target_value' => $targetValue,
		'target_label' => $targetLabel,
		'subject' => (string)$task['subject'],
		'sync_notice' => intval($task['sync_notice']),
		'notice_id' => intval($task['notice_id']),
		'total_count' => $total,
		'success_count' => $success,
		'fail_count' => $fail,
		'pending_count' => $pending,
		'status' => $statusValue,
		'status_text' => $statusText,
		'last_error' => (string)$task['last_error'],
		'creator' => (string)$task['creator'],
		'addtime' => (string)$task['addtime'],
		'starttime' => (string)$task['starttime'],
		'endtime' => (string)$task['endtime']
	);
}

function q8_admin_message_recount_task($taskId){
	global $DB;
	q8_mail_center_ensure_tables();
	$taskId = intval($taskId);
	$taskTable = 'pre_mail_task';
	$itemTable = 'pre_mail_task_item';
	$task = $DB->getRow("SELECT * FROM `{$taskTable}` WHERE id=:id LIMIT 1", array(':id' => $taskId));
	if(!$task) return null;
	$success = intval($DB->getColumn("SELECT COUNT(*) FROM `{$itemTable}` WHERE task_id=:task_id AND status=1", array(':task_id' => $taskId)));
	$fail = intval($DB->getColumn("SELECT COUNT(*) FROM `{$itemTable}` WHERE task_id=:task_id AND status=2", array(':task_id' => $taskId)));
	$total = intval($task['total_count']);
	$pending = max(0, $total - $success - $fail);
	$status = $pending > 0 ? max(1, intval($task['status'])) : 2;
	$lastError = $fail > 0 ? (string)$DB->getColumn("SELECT result FROM `{$itemTable}` WHERE task_id=:task_id AND status=2 ORDER BY id DESC LIMIT 1", array(':task_id' => $taskId)) : '';
	$sql = "UPDATE `{$taskTable}` SET success_count=:success_count, fail_count=:fail_count, status=:status, last_error=:last_error";
	if($pending === 0){
		$sql .= ", endtime=IFNULL(endtime, NOW())";
	}
	$sql .= " WHERE id=:id";
	$DB->exec($sql, array(
		':success_count' => $success,
		':fail_count' => $fail,
		':status' => $status,
		':last_error' => $lastError,
		':id' => $taskId
	));
	return $DB->getRow("SELECT * FROM `{$taskTable}` WHERE id=:id LIMIT 1", array(':id' => $taskId));
}

switch($act){
case 'getcount':
	@header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
	@header('Pragma: no-cache');
	$countCacheKey = 'getcount_admin10';
	$result = $CACHE->read($countCacheKey);
	$isUpdate = false;
	if (!empty($result)) {
		$result = unserialize($result);
		if ((time() - $result['time']) > 60)
			$isUpdate = true;
		else
			$result = $result['data'];
	} else {
		$isUpdate = true;
	}
	if($isUpdate){
		$thtime=date("Y-m-d").' 00:00:00';
		$yesterday_time = date("Y-m-d",strtotime("-1 day")).' 00:00:00';
		$count1=$DB->getColumn("SELECT count(*) FROM pre_orders");
		$count2=$DB->getColumn("SELECT count(*) FROM pre_orders WHERE status=1");
		$count3=$DB->getColumn("SELECT count(*) FROM pre_orders WHERE status=0");
		$count4=$DB->getColumn("SELECT count(*) FROM pre_orders WHERE addtime>='$thtime'");
		$count5=$DB->getColumn("SELECT sum(money) FROM pre_pay WHERE `type` IN ('qqpay','wxpay','alipay') AND addtime>='$thtime' AND status=1");

		$strtotime=strtotime($conf['build']);
		$now=time();
		$yxts=ceil(($now-$strtotime)/86400);

		$count6=$DB->getColumn("SELECT count(*) FROM pre_site");
		$count21=$DB->getColumn("SELECT sum(rmb) FROM pre_site");
		$count7=$DB->getColumn("SELECT count(*) FROM pre_site WHERE addtime>='$thtime'");
        $commissionAction = hex2bin('e68f90e68890');
        $count8=$DB->getColumn("SELECT sum(point) FROM pre_points WHERE action='$commissionAction' AND addtime>='$thtime'");
        $count9=0;
        $count10=0;

		$count11=$DB->getColumn("SELECT sum(realmoney) FROM `pre_tixian` WHERE `status` = 0");

		$count12=$DB->getColumn("SELECT sum(money) FROM `pre_pay` WHERE `type` = 'qqpay' AND `addtime` > '$thtime' AND `status` = 1");
		$count13=$DB->getColumn("SELECT sum(money) FROM `pre_pay` WHERE `type` = 'wxpay' AND `addtime` > '$thtime' AND `status` = 1");
		$count14=$DB->getColumn("SELECT sum(money) FROM `pre_pay` WHERE `type` = 'alipay' AND `addtime` > '$thtime' AND `status` = 1");

		$id1 = $DB->getColumn("SELECT id FROM pre_orders WHERE `addtime`<'$thtime' ORDER BY id DESC LIMIT 1");
		$id2 = $DB->getColumn("SELECT id FROM pre_orders WHERE `addtime`<'$yesterday_time' ORDER BY id DESC LIMIT 1");
		$sql="select money,cost from pre_orders where (status = 1 or status = 2) and id > '$id1'";
		$today_list = $DB->getAll($sql);
		$today_total_money = 0;
		foreach($today_list as $k=>$v){
			$today_total_money += ($v['money'] - $v['cost']);
		}

		$sql="select money,cost from pre_orders where (status = 1 or status = 2) and id <= '$id1' and id > '$id2'";
		$yesterday_list = $DB->getAll($sql);
		$yesterday_total_money = 0;
		foreach($yesterday_list as $k=>$v){
			$yesterday_total_money += ($v['money'] - $v['cost']);
		}

		$count17=$DB->getColumn("SELECT count(*) FROM pre_workorder where status=0 or status=1");
		$todayGoodsListed=$DB->getColumn("SELECT count(*) FROM pre_tools WHERE addtime>=:thtime AND active=1 AND close=0", array(':thtime' => $thtime));
		$todayGoodsDown=$DB->getColumn("SELECT count(*) FROM pre_tools WHERE close=1 AND uptime>=:uptime", array(':uptime' => strtotime(date('Y-m-d'))));
		$todaySignUsers=$DB->getColumn("SELECT count(DISTINCT zid) FROM pre_qiandao WHERE date=:date", array(':date' => date('Y-m-d')));

	$today = date('Y-m-d');
	$frontVisitWhere = q8_admin_front_visit_where();
	$visitTableExists = $DB->getColumn("SHOW TABLES LIKE 'shua_visit_ips'");
	$visit_today = 0;
	$ip_today = 0;
	if($visitTableExists) {
		$visit_today = $DB->getColumn("SELECT COALESCE(SUM(visits),0) FROM shua_visit_ips WHERE date = :date AND {$frontVisitWhere}", array(':date' => $today));
		$ip_today = $DB->getColumn("SELECT COUNT(DISTINCT ip) FROM shua_visit_ips WHERE date = :date AND {$frontVisitWhere}", array(':date' => $today));
	}

    // normalize visit counters
    if($visit_today === false) $visit_today = 0;
	if($ip_today === false) $ip_today = 0;

	$visit_chart = array('date' => array(), 'date_full' => array(), 'visits' => array(), 'ips' => array(), 'range_start' => '', 'range_end' => '');
	try {
		for($i=6; $i>=0; $i--) {
			$date = date('Y-m-d', strtotime("-$i days"));
			$short_date = date('m-d', strtotime("-$i days"));
			$point = 6 - $i;
			if($i == 6) $visit_chart['range_start'] = $date;
			if($i == 0) $visit_chart['range_end'] = $date;
			$visit_chart['date'][] = array($point, $short_date);
			$visit_chart['date_full'][] = $date;

			if($visitTableExists) {
				$stat = $DB->getRow("SELECT COALESCE(SUM(visits),0) AS visits, COUNT(DISTINCT ip) AS ip_count FROM shua_visit_ips WHERE date = :date AND {$frontVisitWhere}", array(':date' => $date));
				$visit_chart['visits'][] = array($point, $stat ? intval($stat['visits']) : 0);
				$visit_chart['ips'][] = array($point, $stat ? intval($stat['ip_count']) : 0);
			} else {
				$visit_chart['visits'][] = array($point, 0);
				$visit_chart['ips'][] = array($point, 0);
			}
		}
	} catch (Exception $e) {
		$visit_chart = null;
	}

	$result=array("code"=>0,"yxts"=>$yxts,"count1"=>$count1,"count2"=>$count2,"count3"=>$count3,"count4"=>$count4,"count5"=>round($count5,2),"count6"=>$count6,"count7"=>$count7,"count8"=>round($count8,2),"count9"=>round($count9,2),"count10"=>round($count10,2),"count11"=>round($count11,2),"count12"=>round($count12,2),"count13"=>round($count13,2),"count14"=>round($count14,2),"count15"=>round($today_total_money,2),"count16"=>round($yesterday_total_money,2),"count17"=>$count17,"count18"=>$todayGoodsListed,"count19"=>$todayGoodsDown,"count20"=>$todaySignUsers,"count21"=>round($count21,2),"chart"=>q8_admin_dashboard_trade_chart(), "visit_today"=>$visit_today, "ip_today"=>$ip_today, "visit_chart"=>$visit_chart);
		$CACHE->save($countCacheKey, serialize(['time' => time(), 'data' => $result]));
	}
	exit(json_encode($result));
break;
case 'notice':
	if(!isset($_SESSION['notice'])){
		$_SESSION['notice'] = getNotice();
	}
	if(!isset($_SESSION['Exten'])){
		$_SESSION['Exten'] = getExten();
	}
	$result = array("code" => 0, "notice" => $_SESSION['notice'], 'exten' => $_SESSION['Exten']);
	exit(json_encode($result));
	break;
case 'qdcount':
	$day=date("Y-m-d");
	$lastday = date("Y-m-d",strtotime("-1 day"));
	$count1=$DB->getColumn("SELECT count(*) FROM pre_qiandao WHERE date='$day'");
	$count2=$DB->getColumn("SELECT count(*) FROM pre_qiandao WHERE date='$lastday'");
	$count3=$DB->getColumn("SELECT count(*) FROM pre_qiandao");
	$count4=$DB->getColumn("SELECT sum(reward) FROM pre_qiandao WHERE date='$day'");
	$count5=$DB->getColumn("SELECT sum(reward) FROM pre_qiandao WHERE date='$lastday'");
	$count6=$DB->getColumn("SELECT sum(reward) FROM pre_qiandao");
	$result=array("count1"=>$count1,"count2"=>$count2,"count3"=>$count3,"count4"=>round($count4,2),"count5"=>round($count5,2),"count6"=>round($count6,2));
	exit(json_encode($result));
break;
case 'tool':
	adminpermission('shop', 2);
	$tid=intval($_POST['tid']);
	$rows=$DB->getRow("select * from pre_tools where tid=:tid limit 1", array(':tid' => $tid));
	if(!$rows)
			exit('{"code":-1,"msg":"notice missing"}');
	exit('{"code":0,"name":"'.$rows['name'].'"}');
break;
case 'uploadimg':
	adminpermission('shop', 2);
	if(!isset($_POST['do']) || empty($_POST['do'])){
		exit(json_encode(array('code'=>-1,'msg'=>'missing upload action')));
	}
	if($_POST['do']=='upload'){
		$type = isset($_POST['type']) ? $_POST['type'] : 'product';
		if(!isset($_FILES['file']) || $_FILES['file']['error'] != UPLOAD_ERR_OK){
			exit(json_encode(array('code'=>-1,'msg'=>'upload failed')));
		}
		$allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
		$file_type = $_FILES['file']['type'];
		$file_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
		$allowed_exts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
		$max_size = 5 * 1024 * 1024;
		if (!in_array($file_type, $allowed_types) || !in_array($file_ext, $allowed_exts)) {
			exit(json_encode(array('code'=>-1,'msg'=>'only jpg/jpeg/png/gif/webp allowed')));
		}
		if ($_FILES['file']['size'] > $max_size) {
			exit(json_encode(array('code'=>-1,'msg'=>'max 5MB')));
		}
		$image_info = getimagesize($_FILES['file']['tmp_name']);
		if (!$image_info) {
			exit(json_encode(array('code'=>-1,'msg'=>'invalid image file')));
		}
		$filename = $type.'_'.md5(uniqid(mt_rand(), true) . time() . file_get_contents($_FILES['file']['tmp_name'])).'.'.$file_ext;
		$fileurl = 'assets/img/Product/'.$filename;
		if (!is_dir(ROOT.'assets/img/Product/')) {
			mkdir(ROOT.'assets/img/Product/', 0755, true);
		}
		if(move_uploaded_file($_FILES['file']['tmp_name'], ROOT.'assets/img/Product/'.$filename)){
			exit(json_encode(array('code'=>0,'msg'=>'succ','url'=>$fileurl)));
		}else{
			exit(json_encode(array('code'=>-1,'msg'=>'save failed')));
		}
	}
	exit(json_encode(array('code'=>-1,'msg'=>'unsupported upload action')));
break;
case 'article_upload':
	adminpermission('article', 2);
	$file_name = $_FILES['imgFile']['name'];
	$tmp_name = $_FILES['imgFile']['tmp_name'];
    // parse upload file extension
    $temp_arr = explode(".", $file_name);
	$file_ext = array_pop($temp_arr);
	$file_ext = strtolower(trim($file_ext));
	if (in_array($file_ext, array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'webp')) === false) {
		exit('{"error":1,"message":"File extension is not allowed"}');
	}
	if(!is_dir(ROOT.'assets/img/Article/')){
		mkdir(ROOT.'assets/img/Article/', 0755, true);
	}
	$file_name = 'article_'.md5(uniqid(mt_rand(), true)).'.'.$file_ext;
	$file_path = ROOT.'assets/img/Article/'.$file_name;
	if(move_uploaded_file($tmp_name, $file_path)){
		exit(json_encode(array('error'=>0,'url'=>'/assets/img/Article/'.$file_name)));
	}
	exit('{"error":1,"message":"Upload failed"}');
break;

case 'upload_favicon':
	adminpermission('set', 2);
	if(!isset($_FILES['favicon']) || $_FILES['favicon']['error'] !== UPLOAD_ERR_OK){
		exit(json_encode(array('code' => -1, 'msg' => 'Please select a favicon file')));
	}
	$file = $_FILES['favicon'];
	$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
	$allowedExts = array('ico', 'png', 'jpg', 'jpeg', 'gif');
	if(!in_array($ext, $allowedExts, true)){
		exit(json_encode(array('code' => -1, 'msg' => 'Only ico, png, jpg, jpeg and gif are allowed')));
	}
	if($file['size'] > 2 * 1024 * 1024){
		exit(json_encode(array('code' => -1, 'msg' => 'File size must be less than 2MB')));
	}
	if($ext !== 'ico' && !getimagesize($file['tmp_name'])){
		exit(json_encode(array('code' => -1, 'msg' => 'Invalid image file')));
	}
	$faviconDir = ROOT.'assets/img/favicon/';
	if(!is_dir($faviconDir)){
		mkdir($faviconDir, 0755, true);
	}
	$targetName = 'favicon.'.$ext;
	$targetPath = $faviconDir.$targetName;
	foreach ((glob($faviconDir.'favicon.*') ?: array()) as $oldPath) {
		if (is_file($oldPath) && strcasecmp(basename($oldPath), $targetName) !== 0) {
			@unlink($oldPath);
		}
	}
	if(move_uploaded_file($file['tmp_name'], $targetPath) || copy($file['tmp_name'], $targetPath)){
		exit(json_encode(array('code' => 0, 'msg' => 'success', 'url' => '/assets/img/favicon/'.$targetName)));
	}
	exit(json_encode(array('code' => -1, 'msg' => 'Failed to save favicon')));
break;

case 'kms':
	adminpermission('faka', 2);
	$id=intval($_GET['id']);
	$rows=$DB->getRow("select * from pre_faka where kid=:id limit 1", array(':id' => $id));
	if(!$rows)
		exit('{"code":-1,"msg":"card missing"}');
	$data = '<li class="list-group-item" style="word-break:break-all;"><b>&#21345;&#23494;&#65306;</b>'.htmlspecialchars($rows['km'], ENT_QUOTES, 'UTF-8').'</li><li class="list-group-item" style="word-break:break-all;"><b>&#21345;&#23494;&#23494;&#30721;&#65306;</b>'.htmlspecialchars($rows['pw'], ENT_QUOTES, 'UTF-8').'</li>';
	$result=array("code"=>0,"msg"=>"succ","data"=>$data);
	exit(json_encode($result));
break;
case 'checkshequ':
	$url = $_POST['url'];
	if(gethostbyname($url)=='127.0.0.1'){
		exit('{"code":0}');
	}else{
		exit('{"code":1}');
	}
break;
case 'checkclone':
	$url = $_POST['url'];
	$url_arr = parse_url($url);
	if($url_arr['host']==$_SERVER['HTTP_HOST'])exit('{"code":2}');
	$data = get_curl($url.'api.php?act=clone');
	$arr = json_decode($data,true);
	if(is_array($arr) && array_key_exists('code',$arr) && array_key_exists('msg',$arr)){
		exit('{"code":1}');
	}elseif(substr(bin2hex($data),0,6)=='efbbbf'){
		exit('{"code":3}');
	}else{
		exit('{"code":0}');
	}
break;
case 'checkdwz':
	$url = $_POST['url'];
	$data = get_curl($url);
	if(json_decode($data,true)){
		exit('{"code":1}');
	}elseif($data){
		exit('{"code":2}');
	}else{
		exit('{"code":0}');
	}
break;

case 'gettool': // get goods detail
	$cid=intval($_GET['cid']);
	$rs=$DB->query("SELECT * FROM pre_tools WHERE cid=:cid AND active=1 order by sort asc", array(':cid' => $cid));
	$data = array();
	while($res = $rs->fetch()){
		$data[]=array('tid'=>$res['tid'],'name'=>$res['name']);
	}
	$result=array("code"=>0,"msg"=>"succ","data"=>$data);
	exit(json_encode($result));
break;
case 'getfakatool': // fetch available docking goods
	$cid=intval($_GET['cid']);
	$rs=$DB->query("SELECT * FROM pre_tools WHERE cid=:cid and is_curl IN (2,4) and active=1 order by sort asc", array(':cid' => $cid));
	$data = array();
	while($res = $rs->fetch()){
		$data[]=array('tid'=>$res['tid'],'name'=>$res['name']);
	}
	$result=array("code"=>0,"msg"=>"succ","data"=>$data);
	exit(json_encode($result));
break;

case 'setMessage': // toggle notice status
	$id=intval($_GET['id']);
	$active=intval($_GET['active']);
	$DB->exec("update pre_message set active=:active where id=:id", array(':active' => $active, ':id' => $id));
	exit('{"code":0,"msg":"succ"}');
break;
case 'getMessage': // get notice detail
	$id=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_message where id=:id limit 1", array(':id' => $id));
	if(!$row)
			exit('{"code":-1,"msg":"notice missing"}');
	$result=array("code"=>0,"msg"=>"succ","title"=>$row['title'],"type"=>$row['type'],"content"=>$row['content'],"date"=>$row['addtime']);
	exit(json_encode($result));
break;
case 'messageNoticeDetail':
	adminpermission('site', 1);
	$id=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_message where id=:id limit 1", array(':id' => $id));
	if(!$row) exit(json_encode(array('code' => -1, 'msg' => 'notice missing')));
	$scopeLabels = q8_admin_message_scope_labels();
	$scope = intval($row['type']);
	exit(json_encode(array('code' => 0, 'msg' => 'succ', 'notice' => array('id' => intval($row['id']), 'title' => $row['title'], 'type' => $scope, 'scope' => $scope, 'scope_text' => (isset($scopeLabels[$scope]) ? $scopeLabels[$scope] : 'all users'), 'content' => $row['content'], 'date' => $row['addtime'], 'active' => intval($row['active']), 'count' => intval($row['count'])))));
break;
case 'messageNoticeSave':
	adminpermission('site', 2);
	if($_SERVER['REQUEST_METHOD'] !== 'POST' || !q8_admin_check_csrf()) exit(json_encode(array('code' => 403, 'msg' => 'CSRF invalid')));
	$id=intval($_POST['id']);
	$title=trim(strip_tags(isset($_POST['title']) ? $_POST['title'] : ''));
	$content=trim(isset($_POST['content']) ? $_POST['content'] : '');
	$active=intval($_POST['active']) === 1 ? 1 : 0;
	if($id < 1) exit(json_encode(array('code' => -1, 'msg' => 'notice id invalid')));
	if($title === '') exit(json_encode(array('code' => -1, 'msg' => 'notice title required')));
	if($content === '') exit(json_encode(array('code' => -1, 'msg' => 'notice content required')));
	$row=$DB->getRow("select * from pre_message where id=:id limit 1", array(':id' => $id));
	if(!$row) exit(json_encode(array('code' => -1, 'msg' => 'notice missing')));
	$scope=isset($_POST['scope']) ? intval($_POST['scope']) : intval($row['type']);
	$scopeLabels = q8_admin_message_scope_labels();
	if(!array_key_exists($scope, $scopeLabels)) $scope = 0;
	$DB->exec("update pre_message set type=:type,title=:title,content=:content,active=:active where id=:id", array(':type' => $scope, ':title' => $title, ':content' => $content, ':active' => $active, ':id' => $id));
	$summary = preg_replace('/\s+/u', ' ', trim(strip_tags($content)));
	if(function_exists('mb_substr')){
		$summary = mb_substr($summary, 0, 120, 'UTF-8');
	}else{
		$summary = substr($summary, 0, 120);
	}
	exit(json_encode(array('code' => 0, 'msg' => 'saved', 'notice' => array('id' => $id, 'title' => $title, 'content' => $content, 'summary' => $summary, 'active' => $active, 'type' => $scope, 'scope' => $scope, 'scope_text' => (isset($scopeLabels[$scope]) ? $scopeLabels[$scope] : 'all users'), 'date' => $row['addtime'], 'count' => intval($row['count'])))));
break;
case 'messageNoticeToggle':
	adminpermission('site', 2);
	if($_SERVER['REQUEST_METHOD'] !== 'POST' || !q8_admin_check_csrf()) exit(json_encode(array('code' => 403, 'msg' => 'CSRF invalid')));
	$id=intval($_POST['id']);
	$active=intval($_POST['active']) === 1 ? 1 : 0;
	$exists=$DB->getColumn("select count(*) from pre_message where id=:id", array(':id' => $id));
	if(!$exists) exit(json_encode(array('code' => -1, 'msg' => 'notice missing')));
	$DB->exec("update pre_message set active=:active where id=:id", array(':active' => $active, ':id' => $id));
	exit(json_encode(array('code' => 0, 'msg' => 'succ')));
break;
case 'messageRecipientLookup':
	adminpermission('site', 1);
	$targetValue = trim((string)(isset($_GET['target_value']) ? $_GET['target_value'] : ''));
	$resolved = q8_mail_center_resolve_target($targetValue);
	if(!$resolved['ok']){
		exit(json_encode(array('code' => -1, 'msg' => $resolved['msg'])));
	}
	$row = $resolved['row'];
	exit(json_encode(array(
		'code' => 0,
		'msg' => 'succ',
		'recipient' => array(
			'zid' => intval($row['zid']),
			'username' => (string)$row['username'],
			'qq' => (string)$row['qq'],
			'email' => (string)$row['email'],
			'power' => intval($row['power']),
			'target_label' => (string)$row['target_label']
		)
	)));
break;
case 'setArticle':
	adminpermission('article', 2);
	if($_SERVER['REQUEST_METHOD'] !== 'POST' || !q8_admin_check_csrf()) exit('{"code":403,"msg":"CSRF invalid"}');
	$id = intval($_POST['id']);
	$active = intval($_POST['active']);
	$DB->exec("update pre_article set active=:active where id=:id", array(':active' => $active, ':id' => $id));
	exit('{"code":0,"msg":"succ"}');
break;

case 'messageTaskCreate':
	adminpermission('site', 2);
	if($_SERVER['REQUEST_METHOD'] !== 'POST' || !q8_admin_check_csrf()) exit(json_encode(array('code' => 403, 'msg' => 'CSRF invalid')));
	if(!q8_admin_message_mail_ready()) exit(json_encode(array('code' => -1, 'msg' => 'Mail service is not configured')));
	q8_mail_center_ensure_tables();
	$recipientMode = isset($_POST['recipient_mode']) && $_POST['recipient_mode'] === 'single' ? 1 : 0;
	$scope = isset($_POST['scope']) ? intval($_POST['scope']) : 0;
	if(!array_key_exists($scope, q8_admin_message_scope_labels())) $scope = 0;
	$targetValue = trim((string)(isset($_POST['target_value']) ? $_POST['target_value'] : ''));
	$targetLabel = '';
	$subject = trim(strip_tags(isset($_POST['subject']) ? $_POST['subject'] : ''));
	$content = trim(isset($_POST['content']) ? $_POST['content'] : '');
	$syncNotice = isset($_POST['sync_notice']) && intval($_POST['sync_notice']) === 1 ? 1 : 0;
	if($subject === '' || $content === ''){
		exit(json_encode(array('code' => -1, 'msg' => 'Subject and content are required')));
	}
	if($recipientMode === 1){
		$resolved = q8_mail_center_resolve_target($targetValue);
		if(!$resolved['ok']){
			exit(json_encode(array('code' => -1, 'msg' => $resolved['msg'])));
		}
		$recipients = array($resolved['row']);
		$targetLabel = $resolved['row']['target_label'];
		$syncNotice = 0;
	}else{
		$recipients = q8_mail_center_recipients_by_scope($scope);
	}
	$total = count($recipients);
	if($total < 1){
		exit(json_encode(array('code' => -1, 'msg' => $recipientMode === 1 ? 'Target user not found' : 'No recipients matched the selected scope')));
	}
	$noticeId = $syncNotice ? q8_mail_center_sync_notice($subject, $scope, $content) : 0;
	$creator = isset($conf['admin_user']) ? trim((string)$conf['admin_user']) : 'admin';
	$taskTable = 'pre_mail_task';
	$itemTable = 'pre_mail_task_item';
	$DB->exec("INSERT INTO `{$taskTable}` (`scope`,`target_mode`,`target_value`,`target_label`,`subject`,`content`,`sync_notice`,`notice_id`,`total_count`,`success_count`,`fail_count`,`status`,`last_error`,`creator`,`addtime`) VALUES (:scope,:target_mode,:target_value,:target_label,:subject,:content,:sync_notice,:notice_id,0,0,0,0,'',:creator,NOW())", array(
		':scope' => $scope,
		':target_mode' => $recipientMode,
		':target_value' => $recipientMode === 1 ? $targetValue : '',
		':target_label' => $targetLabel,
		':subject' => $subject,
		':content' => $content,
		':sync_notice' => $syncNotice,
		':notice_id' => $noticeId,
		':creator' => $creator === '' ? 'admin' : $creator
	));
	$taskId = intval($DB->lastInsertId());
	if($taskId < 1){
		exit(json_encode(array('code' => -1, 'msg' => 'Failed to create mail task')));
	}
	$inserted = 0;
	foreach($recipients as $recipient){
		$DB->exec("INSERT INTO `{$itemTable}` (`task_id`,`zid`,`username`,`qq`,`email`,`status`,`result`,`addtime`) VALUES (:task_id,:zid,:username,:qq,:email,0,'',NOW())", array(
			':task_id' => $taskId,
			':zid' => intval($recipient['zid']),
			':username' => (string)$recipient['username'],
			':qq' => (string)$recipient['qq'],
			':email' => (string)$recipient['email']
		));
		$inserted++;
	}
	$DB->exec("UPDATE `{$taskTable}` SET total_count=:total_count WHERE id=:id", array(':total_count' => $inserted, ':id' => $taskId));
	$task = q8_admin_message_fetch_task($taskId);
	exit(json_encode(array('code' => 0, 'msg' => 'Mail task created', 'task' => q8_admin_message_task_payload($task))));
break;
case 'messageTaskRun':
	adminpermission('site', 2);
	if($_SERVER['REQUEST_METHOD'] !== 'POST' || !q8_admin_check_csrf()) exit(json_encode(array('code' => 403, 'msg' => 'CSRF invalid')));
	if(!q8_admin_message_mail_ready()){
		$result = array('code' => -1, 'msg' => 'Mail service is not configured');
		exit(json_encode($result));
	}
	q8_mail_center_ensure_tables();
	$taskId = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
	$limit = isset($_POST['limit']) ? intval($_POST['limit']) : 20;
	if($limit < 1) $limit = 1;
	if($limit > 20) $limit = 20;
	$task = q8_admin_message_fetch_task($taskId);
	if(!$task){
		$result = array('code' => -1, 'msg' => 'Mail task not found');
		exit(json_encode($result));
	}
	$taskTable = 'pre_mail_task';
	$itemTable = 'pre_mail_task_item';
	if(intval($task['status']) !== 1){
		$DB->exec("UPDATE `{$taskTable}` SET status=1, starttime=IFNULL(starttime, NOW()), endtime=NULL WHERE id=:id", array(':id' => $taskId));
		$task = q8_admin_message_fetch_task($taskId);
	}
	$rs = $DB->query("SELECT * FROM `{$itemTable}` WHERE task_id=:task_id AND status=0 ORDER BY id ASC LIMIT {$limit}", array(':task_id' => $taskId));
	$items = array();
	while($row = $rs->fetch()){
		$items[] = $row;
	}
	if(empty($items)){
		$task = q8_admin_message_recount_task($taskId);
		$result = array(
			'code' => 0,
			'msg' => 'No pending recipients',
			'done' => true,
			'task' => q8_admin_message_task_payload($task),
			'batch_success' => 0,
			'batch_fail' => 0,
		);
		exit(json_encode($result));
	}
	$batchSuccess = 0;
	$batchFail = 0;
	foreach($items as $item){
		$email = trim((string)$item['email']);
		$resultText = '';
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			$resultText = 'invalid email';
		}else{
			$mailResult = send_mail($email, $task['subject'], q8_mail_center_wrap_html($task['subject'], $task['content']));
			if($mailResult === true){
				$DB->exec("UPDATE `{$itemTable}` SET status=1, result='success', sent_at=NOW() WHERE id=:id", array(':id' => $item['id']));
				$batchSuccess++;
				continue;
			}
			$resultText = trim((string)$mailResult);
		}
		if($resultText === '') $resultText = 'send failed';
		$DB->exec("UPDATE `{$itemTable}` SET status=2, result=:result, sent_at=NOW() WHERE id=:id", array(':result' => mb_substr($resultText, 0, 250, 'UTF-8'), ':id' => $item['id']));
		$batchFail++;
	}
	$task = q8_admin_message_recount_task($taskId);
	$result = array(
		'code' => 0,
		'msg' => intval($task['status']) === 2 ? 'Mail task completed' : 'Batch send finished',
		'done' => intval($task['status']) === 2,
		'batch_success' => $batchSuccess,
		'batch_fail' => $batchFail,
		'task' => q8_admin_message_task_payload($task),
	);
	exit(json_encode($result));
break;
case 'messageTaskItems':
	adminpermission('site', 1);
	q8_mail_center_ensure_tables();
	$taskId = isset($_GET['id']) ? intval($_GET['id']) : 0;
	$task = q8_admin_message_fetch_task($taskId);
	if(!$task){
		exit(json_encode(array('code' => -1, 'msg' => 'Mail task not found')));
	}
	$itemTable = 'pre_mail_task_item';
	$rs = $DB->query("SELECT * FROM `{$itemTable}` WHERE task_id=:task_id ORDER BY status DESC, id DESC LIMIT 200", array(':task_id' => $taskId));
	$items = array();
	while($row = $rs->fetch()){
		$status = intval($row['status']);
		$statusText = $status === 1 ? 'sent' : ($status === 2 ? 'failed' : 'pending');
		$items[] = array(
			'id' => intval($row['id']),
			'zid' => intval($row['zid']),
			'username' => (string)$row['username'],
			'qq' => (string)$row['qq'],
			'email' => (string)$row['email'],
			'status' => $status,
			'status_text' => $statusText,
			'result' => (string)$row['result'],
			'sent_at' => (string)$row['sent_at']
		);
	}
	exit(json_encode(array('code' => 0, 'msg' => 'succ', 'task' => q8_admin_message_task_payload($task), 'items' => $items)));
break;

case 'messageTaskRetry':
	adminpermission('site', 2);
	if($_SERVER['REQUEST_METHOD'] !== 'POST' || !q8_admin_check_csrf()) exit(json_encode(array('code' => 403, 'msg' => 'CSRF invalid')));
	q8_mail_center_ensure_tables();
	$taskId = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
	$task = q8_admin_message_fetch_task($taskId);
	if(!$task){
		exit(json_encode(array('code' => -1, 'msg' => 'Mail task not found')));
	}
	$taskTable = 'pre_mail_task';
	$itemTable = 'pre_mail_task_item';
	$reset = $DB->exec("UPDATE `{$itemTable}` SET status=0, result='', sent_at=NULL WHERE task_id=:task_id AND status=2", array(':task_id' => $taskId));
	if(intval($reset) < 1){
		exit(json_encode(array('code' => -1, 'msg' => 'No failed recipients to retry')));
	}
	$successCount = intval($DB->getColumn("SELECT COUNT(*) FROM `{$itemTable}` WHERE task_id=:task_id AND status=1", array(':task_id' => $taskId)));
	$failCount = intval($DB->getColumn("SELECT COUNT(*) FROM `{$itemTable}` WHERE task_id=:task_id AND status=2", array(':task_id' => $taskId)));
	$DB->exec("UPDATE `{$taskTable}` SET success_count=:success_count, fail_count=:fail_count, status=0, last_error='', endtime=NULL WHERE id=:id", array(
		':success_count' => $successCount,
		':fail_count' => $failCount,
		':id' => $taskId
	));
	$task = q8_admin_message_fetch_task($taskId);
	exit(json_encode(array('code' => 0, 'msg' => 'Retry queue prepared', 'task' => q8_admin_message_task_payload($task))));
break;
case 'workorder_change':
	adminpermission('workorder', 2);
	$aid=$_POST['aid'];
	$checkbox=$_POST['checkbox'];
	$i=0;
	foreach($checkbox as $id){
		if($aid==1){
			$DB->exec("update pre_workorder set status=0 where id=:id limit 1", array(':id' => $id));
			$i++;
		}elseif($aid==2){
			$DB->exec("update pre_workorder set status=2 where id=:id limit 1", array(':id' => $id));
			$i++;
		}elseif($aid==3){
			$rows=$DB->getRow("select * from pre_workorder where id=:id limit 1", array(':id' => $id));
			$content=str_replace(array('*','^','|'),'',trim(strip_tags(daddslashes($_POST['content']))));
			if($rows && $rows['status']<2 && !empty($content)){
				$content = addslashes($rows['content']).'*1^'.$date.'^'.$content;
				$DB->exec("update pre_workorder set content=:content,status=1 where id=:id limit 1", array(':content' => $content, ':id' => $id));
				$i++;
			}
		}elseif($aid==4){
			$DB->exec("DELETE FROM pre_workorder WHERE id=:id limit 1", array(':id' => $id));
			$i++;
		}
	}
	exit(json_encode(array('code' => 0, 'msg' => 'Processed '.$i.' workorders')));
break;
case 'delworkorder':
	adminpermission('workorder', 2);
	$id=intval($_GET['id']);
	$sql="DELETE FROM pre_workorder WHERE id='$id' limit 1";
	if($DB->exec($sql)!==false){
		exit(json_encode(array('code' => 0, 'msg' => 'Deleted workorder')));
	}else{
		exit(json_encode(array('code' => -1, 'msg' => 'Delete failed: '.$DB->error())));
	}
break;
case 'add_speedy_text':
	$content = trim(strip_tags(daddslashes($_POST['content'])));
	if($conf['speedy_list']){
		$speedy_list = explode("^", $conf['speedy_list']);
	}else{
		$speedy_list = array();
	}
	$speedy_list[] = $content;
	saveSetting('speedy_list', implode("^",$speedy_list));
	$CACHE->clear();
	exit(json_encode(array('code' => 0, 'msg' => 'Saved quick reply', 'id' => count($speedy_list)-1, 'content' => $content)));
break;
case 'del_speedy_text':
	$ids = explode(',',$_POST['ids']);
	if (!isset($_POST['ids']) || count($ids)<=0) {
		exit(json_encode(array('code' => -1, 'msg' => 'Please select at least one quick reply')));
	}
	$speedy_list = explode("^", $conf['speedy_list']);
	foreach($ids as $id){
		array_splice($speedy_list, $id, 1);
	}
	saveSetting('speedy_list', implode("^",$speedy_list));
	$CACHE->clear();
	exit(json_encode(array('code' => 0, 'msg' => 'Deleted quick reply entries')));
break;


case 'add_member':
	adminpermission('set', 2);
	$name=$_POST['name'];
	$tid=$_POST['tid'];
	$rate=str_replace('%','',$_POST['rate']);
	if(!$name||!$tid||!$rate){
		exit(json_encode(array('code' => -1, 'msg' => 'Missing required parameters')));
	}
	$sql=$DB->exec("INSERT INTO `pre_gift`(`name`,`tid`,`rate`,`ok`) VALUES (:name,:tid,:rate,0)", array(':name' => $name, ':tid' => $tid, ':rate' => $rate));
	if($sql){
		exit(json_encode(array('code' => 0, 'msg' => 'Added pricing template')));
	}else{
		exit(json_encode(array('code' => 1, 'msg' => 'Add failed: '.$DB->error())));
	}
break;
case 'edit_cj':
	adminpermission('set', 2);
	$id=$_POST['id'];
	if(!$id){
		exit(json_encode(array('code' => -1, 'msg' => 'Missing template ID')));
	}
	$sql=$DB->getRow("SELECT * FROM pre_gift where id=:id", array(':id' => $id));
	if($sql){
		$cid = $DB->getColumn("select cid from pre_tools where tid=:tid limit 1", array(':tid' => $sql['tid']));
		exit(json_encode(array('code' => 0, 'msg' => 'succ', 'id' => $id, 'name' => $sql['name'], 'cid' => $cid, 'tid' => $sql['tid'], 'rate' => $sql['rate'])));
	}else{
		exit(json_encode(array('code' => 1, 'msg' => 'Template not found: '.$DB->error())));
	}
break;
case 'edit_cj_ok':
	adminpermission('set', 2);
	$id=$_POST['id'];
	$name=$_POST['name'];
	$tid=$_POST['tid'];
	$rate=$_POST['rate'];
	if(!$id){
		exit(json_encode(array('code' => -1, 'msg' => 'Missing template ID')));
	}
	$sql=$DB->exec("UPDATE pre_gift set name=:name,tid=:tid,rate=:rate where id=:id", array(':name' => $name, ':tid' => $tid, ':rate' => $rate, ':id' => $id));
	if($sql!==false){
		exit(json_encode(array('code' => 0, 'msg' => 'Updated pricing template')));
	}else{
		exit(json_encode(array('code' => 1, 'msg' => 'Update failed: '.$DB->error())));
	}
break;
case 'del_member':
	adminpermission('set', 2);
	$id=$_POST['id'];
	if(!$id){
		exit(json_encode(array('code' => -1, 'msg' => 'Missing template ID')));
	}
	$sql=$DB->exec("DELETE FROM pre_gift WHERE id=:id", array(':id' => $id));
	if($sql!==false){
		exit(json_encode(array('code' => 0, 'msg' => 'Deleted pricing template')));
	}else{
		exit(json_encode(array('code' => 1, 'msg' => 'Delete failed: '.$DB->error())));
	}
break;
case 'cishu':
	adminpermission('set', 2);
	$cishu=$_GET['cishu'];
	$gift_open=$_GET['gift_open'];
	$cjmsg=$_GET['cjmsg'];
	$cjmoney=$_GET['cjmoney'];
	$gift_log=$_GET['gift_log'];
	saveSetting('cishu',$cishu);
	saveSetting('gift_open',$gift_open);
	saveSetting('cjmsg',$cjmsg);
	saveSetting('cjmoney',$cjmoney);
	saveSetting('gift_log',$gift_log);
	$ad=$CACHE->clear();
	if($ad){
		exit(json_encode(array('code' => 0, 'msg' => 'Saved draw settings')));
	}else{
		exit(json_encode(array('code' => 1, 'msg' => 'Save failed: '.$DB->error())));
	}
break;
case 'setCut':
	adminpermission('shop', 2);
	$id=intval($_GET['id']);
	$active=intval($_GET['active']);
	$DB->exec("update pre_cutshop set active=:active where id=:id", array(':active' => $active, ':id' => $id));
	exit('{"code":0,"msg":"succ"}');
break;
case 'delCutLog':
	adminpermission('shop', 2);
	$id=intval($_GET['id']);
	$sql="DELETE FROM pre_cut WHERE id='$id' limit 1";
	if($DB->exec($sql)!==false){
		exit(json_encode(array('code' => 0, 'msg' => 'Deleted flash-sale log')));
	}else{
		exit(json_encode(array('code' => -1, 'msg' => 'Delete failed: '.$DB->error())));
	}
break;
case 'setGroup':
	adminpermission('shop', 2);
	$id=intval($_GET['id']);
	$active=intval($_GET['active']);
	$DB->exec("update pre_groupshop set active=:active where id=:id", array(':active' => $active, ':id' => $id));
	exit('{"code":0,"msg":"succ"}');
break;
case 'delGroup':
	adminpermission('shop', 2);
	$id=intval($_GET['id']);
	$sql="DELETE FROM pre_groupshop WHERE id='$id' limit 1";
	if($DB->exec($sql)!==false){
		exit(json_encode(array('code' => 0, 'msg' => 'Deleted grouped sale log')));
	}else{
		exit(json_encode(array('code' => -1, 'msg' => 'Delete failed: '.$DB->error())));
	}
case 'delInvite':
	adminpermission('shop', 2);
	q8_admin_require_post_csrf();
	$id=intval($_POST['id']);
	$sql='DELETE FROM pre_inviteshop WHERE id=:id limit 1';
	if($DB->exec($sql, array(':id' => $id))!==false){
		exit(json_encode(array('code' => 0, 'msg' => 'Deleted invite item')));
	}else
		exit(json_encode(array('code' => -1, 'msg' => 'Delete failed: '.$DB->error())));
break;
case 'setInvite': // invite item status
	adminpermission('shop', 2);
	q8_admin_require_post_csrf();
	$id=intval($_POST['id']);
	$active=intval($_POST['active']);
	$DB->exec('update pre_inviteshop set active=:active where id=:id', array(':active' => $active, ':id' => $id));
	exit(json_encode(array('code' => 0, 'msg' => 'Saved invite status')));
break;
case 'delInviteLog':
	adminpermission('shop', 2);
	q8_admin_require_post_csrf();
	$id=intval($_POST['id']);
	$sql='DELETE FROM pre_invite WHERE id=:id limit 1';
	if($DB->exec($sql, array(':id' => $id))!==false){
		exit(json_encode(array('code' => 0, 'msg' => 'Deleted invite log')));
	}else
		exit(json_encode(array('code' => -1, 'msg' => 'Delete failed: '.$DB->error())));
break;
case 'create_url':
	$force = trim(daddslashes($_GET['force']));
	$url = trim(daddslashes($_GET['longurl']));
	if($force==1){
		$turl = fanghongdwz($url,true);
	}else{
		$turl = fanghongdwz($url);
	}
	if($turl == $url){
		$result = array('code'=>-1, 'msg'=>'Short URL service returned original URL');
	}elseif(strpos($turl,'/')!==false){
		$result = array('code'=>0, 'msg'=>'succ', 'url'=> $turl);
	}else{
		$result = array('code'=>-1, 'msg'=>'Generate failed: '.$turl);
	}
	exit(json_encode($result));
break;
case 'rewrite':
	adminpermission('set', 2);
	$article_rewrite = intval($_POST['article_rewrite']);
	$server_software = strtolower($_SERVER['SERVER_SOFTWARE']);
	if($article_rewrite==1 && (strpos($server_software,'apache')!==false || strpos($server_software,'kangle')!==false)){
		$filecontent = '<IfModule mod_rewrite.c>
  Options +FollowSymlinks
  RewriteEngine On

  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^article-(.[0-9]*).html$ index.php?mod=article&id=$1 [QSA,PT,L]
  RewriteRule ^(.[a-zA-Z0-9\\-\\_]+).html$ index.php?mod=$1 [QSA,PT,L]
</IfModule>';
		if(!file_put_contents(ROOT.'.htaccess', $filecontent)){
			exit(json_encode(array('code' => -1, 'msg' => 'Failed to write .htaccess')));
		}
	}
	saveSetting('article_rewrite', $article_rewrite);
	$ad=$CACHE->clear();
	if($ad)exit(json_encode(array('code' => 0, 'msg' => 'succ')));
	else exit(json_encode(array('code' => -1, 'msg' => 'Cache clear failed')));
break;
case 'set':
	adminpermission('set', 2);
	foreach($_POST as $k=>$v){
		saveSetting($k, $v);
	}
	$ad=$CACHE->clear();
	if($ad)exit(json_encode(array('code' => 0, 'msg' => 'succ')));
	else exit(json_encode(array('code' => -1, 'msg' => 'Cache clear failed')));
break;
case 'map':
	adminpermission('set', 2);
	$map_type = intval(trim(daddslashes(strip_tags($_POST['map_type']))));
	$map_urlpattern = trim(daddslashes(strip_tags($_POST['map_urlpattern'])));
	$map_priority = floatval(daddslashes(strip_tags($_POST['map_priority'])));
	if ($map_priority <= 0) $map_priority = 0.5;
	if ($map_priority > 1) $map_priority = 1;
	if (empty($map_urlpattern)) {
		exit(json_encode(array('code' => -1, 'msg' => 'URL pattern is required')));
	}
	if (!is_dir(ROOT . 'spider')) {
		mkdir(ROOT . 'spider');
	}
	$data = array();
	$count = 0;
	$fileName = '';
	$filePath = '';
	$xml = <<<'xml'
<?xml version="1.0" encoding="utf-8"?>
	<urlset>
xml;
	if ($map_type == 1) {
		$count = intval($DB->getColumn("SELECT count(*) FROM pre_class WHERE active='1'"));
		if ($count > 0) {
			$data = $DB->getAll("SELECT * FROM pre_class WHERE active='1' order by sort asc");
		}
		foreach ($data as $row) {
			$url = str_replace('[siteurl]', $_SERVER['HTTP_HOST'], $map_urlpattern);
			$url = str_replace('[cid]', $row['cid'], $url);
			$xml .= "\n\t\t<url>\n";
			$xml .= "\t\t\t<loc>" . $url . "</loc>\n";
			$xml .= "\t\t\t<lastmod>" . date('Y-m-d') . "</lastmod>\n";
			$xml .= "\t\t\t<changefreq>daily</changefreq>\n";
			$xml .= "\t\t\t<priority>" . $map_priority . "</priority>\n";
			$xml .= "\t\t</url>";
		}
		$fileName = 'map_class.xml';
		$filePath = ROOT . 'spider/' . $fileName;
	} elseif ($map_type == 2) {
		$count = intval($DB->getColumn("SELECT count(*) FROM pre_tools WHERE active='1' and close='0'"));
		if ($count > 0) {
			$data = $DB->getAll("SELECT * FROM pre_tools WHERE active='1' and close='0' order by sort asc");
		}
		foreach ($data as $row) {
			$url = str_replace('[siteurl]', $_SERVER['HTTP_HOST'], $map_urlpattern);
			$url = str_replace('[cid]', $row['cid'], $url);
			$url = str_replace('[tid]', $row['tid'], $url);
			$xml .= "\n\t\t<url>\n";
			$xml .= "\t\t\t<loc>" . $url . "</loc>\n";
			$xml .= "\t\t\t<lastmod>" . date('Y-m-d') . "</lastmod>\n";
			$xml .= "\t\t\t<changefreq>daily</changefreq>\n";
			$xml .= "\t\t\t<priority>" . $map_priority . "</priority>\n";
			$xml .= "\t\t</url>";
		}
		$fileName = 'map_goods.xml';
		$filePath = ROOT . 'spider/' . $fileName;
	} elseif ($map_type == 3) {
		$count = intval($DB->getColumn("SELECT count(*) FROM pre_article WHERE active='1'"));
		if ($count > 0) {
			$data = $DB->getAll("SELECT id FROM pre_article WHERE active='1' order by id asc");
		}
		$url = str_replace('[siteurl]', $_SERVER['HTTP_HOST'], $map_urlpattern);
		$url = str_replace('[aid]', 'index', $url);
		$url = str_replace('[cid]', 'index', $url);
		$xml .= "\n\t\t<url>\n";
		$xml .= "\t\t\t<loc>" . $url . "</loc>\n";
		$xml .= "\t\t\t<lastmod>" . date('Y-m-d') . "</lastmod>\n";
		$xml .= "\t\t\t<changefreq>daily</changefreq>\n";
		$xml .= "\t\t\t<priority>" . $map_priority . "</priority>\n";
		$xml .= "\t\t</url>";
		foreach ($data as $row) {
			$url = str_replace('[siteurl]', $_SERVER['HTTP_HOST'], $map_urlpattern);
			$url = str_replace('[aid]', $row['id'], $url);
			$url = str_replace('[cid]', $row['id'], $url);
			$xml .= "\n\t\t<url>\n";
			$xml .= "\t\t\t<loc>" . $url . "</loc>\n";
			$xml .= "\t\t\t<lastmod>" . date('Y-m-d') . "</lastmod>\n";
			$xml .= "\t\t\t<changefreq>daily</changefreq>\n";
			$xml .= "\t\t\t<priority>" . $map_priority . "</priority>\n";
			$xml .= "\t\t</url>";
		}
		$fileName = 'map_message.xml';
		$filePath = ROOT . 'spider/' . $fileName;
	} else {
		exit(json_encode(array('code' => -1, 'msg' => 'Unsupported map type')));
	}
	$xml .= "\n\t</urlset>";
	if (file_put_contents($filePath, $xml) !== false) {
		$result = array('code' => 0, 'msg' => 'Generated ' . $fileName . ' with ' . $count . ' items');
	} else {
		$result = array('code' => -1, 'msg' => 'Failed to write sitemap file');
	}
	exit(json_encode($result));
break;
case 'thirdloginunbind':
	adminpermission('set', 2);
	$type = isset($_POST['type'])?$_POST['type']:exit;
	$key = $type=='wx'?'thirdlogin_wx':'thirdlogin_qq';
	saveSetting($key, '');
	$CACHE->clear();
	exit('{"code":0,"msg":"succ"}');
break;
case 'getServerIp':
	$ip = getServerIp();
	exit('{"code":0,"ip":"'.$ip.'"}');
break;
case 'epayurl':
	$id = intval($_GET['id']);
	$conf['payapi']=$id;
	if($id>0 && $url = pay_api()){
		exit('{"code":0,"url":"'.$url.'"}');
	}else{
		exit('{"code":-1}');
	}
break;
case 'micropayurl':
	$id = intval($_GET['id']);
	$conf['micropayapi']=$id;
	if($url = micropay_api()){
		exit('{"code":0,"url":"'.$url.'"}');
	}else{
		exit('{"code":-1}');
	}
break;
case 'iptype':
	$result = [
	['name'=>'0_X_FORWARDED_FOR', 'ip'=>real_ip(0), 'city'=>get_ip_city(real_ip(0))],
	['name'=>'1_X_REAL_IP', 'ip'=>real_ip(1), 'city'=>get_ip_city(real_ip(1))],
	['name'=>'2_REMOTE_ADDR', 'ip'=>real_ip(2), 'city'=>get_ip_city(real_ip(2))]
	];
	exit(json_encode($result));
break;
case 'transfer':
	adminpermission('super', 2);
	$id = intval($_POST['id']);
	if(empty($conf['fenzhan_daifu'])){
		exit(json_encode(array('code' => 0, 'msg' => html_entity_decode('&#26410;&#24320;&#21551;&#20998;&#31449;&#20195;&#20184;&#21151;&#33021;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
	}
	if(empty($conf['transfer_api_url']) || empty($conf['transfer_id']) || empty($conf['transfer_key']) || empty($conf['transfer_check']) || empty($_SESSION['transfer_pass'])){
		exit(json_encode(array('code' => 0, 'msg' => html_entity_decode('&#35831;&#20808;&#23436;&#25104;&#20195;&#20184;&#25509;&#21475;&#37197;&#32622;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
	}
	$transferApiUrl = trim($conf['transfer_api_url']);
	if(!preg_match('/^https?:\/\//i', $transferApiUrl)){
		exit(json_encode(array('code' => 0, 'msg' => html_entity_decode('&#20195;&#20184;&#25509;&#21475;&#22320;&#22336;&#26684;&#24335;&#38169;&#35823;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
	}
	$res = $DB->getRow("SELECT * FROM pre_tixian WHERE id=:id AND status=0", array(':id' => $id));
	if(!$res) {
		exit(json_encode(array('code' => 0, 'msg' => html_entity_decode('&#25552;&#29616;&#35760;&#24405;&#19981;&#23384;&#22312;&#25110;&#24050;&#22788;&#29702;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
	}
	if($res['pay_type'].'' == '1') {
		$type = '3';
	}elseif($res['pay_type'].'' == '0') {
		$type = '1';
	}else{
		$type = $res['pay_type'];
	}
	$param = array(
	    'api_id' => trim($conf['transfer_id']),
	    'money' => $res['realmoney'],
	    'payee_type' => $type,
	    'payee_account' => $res['pay_account'],
		'payee_name' => $res['pay_name'],
		'realname' => $conf['transfer_check'],
		'timestamp' => time(),
		'pay_pass' => $_SESSION['transfer_pass'],
	);
	$param['sign'] = yile_getSign($param, trim($conf['transfer_key']));
	$data = get_curl($transferApiUrl, $param);
	$json = json_decode($data, true);
	if(isset($json['code']) && $json['code']) {
		if($DB->exec("update pre_tixian set status=1,endtime=NOW() where id=:id", array(':id' => $id))===false) {
			exit(json_encode(array('code' => 0, 'msg' => html_entity_decode('&#20195;&#20184;&#25104;&#21151;&#65292;&#20294;&#25552;&#29616;&#29366;&#24577;&#26356;&#26032;&#22833;&#36133;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
		}
	    exit(json_encode(array('code' => 1, 'msg' => html_entity_decode('&#20195;&#20184;&#25104;&#21151;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
	}else{
	    exit(json_encode(array('code' => 0, 'msg' => isset($json['msg']) && $json['msg'] !== '' ? $json['msg'] : html_entity_decode('&#20195;&#20184;&#25509;&#21475;&#35843;&#29992;&#22833;&#36133;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
	}
break;
case 'transfer_config':
	adminpermission('super', 2);
	if(!$conf['fenzhan_daifu']) {
		exit(json_encode(array('code' => 0, 'msg' => html_entity_decode('&#26410;&#24320;&#21551;&#20998;&#31449;&#20195;&#20184;&#21151;&#33021;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
	}
	if(empty($_POST['api_url']) || empty($_POST['id']) || empty($_POST['key']) || empty($_POST['pass'])) {
		exit(json_encode(array('code' => 0, 'msg' => html_entity_decode('&#35831;&#23558;&#20195;&#20184;&#37197;&#32622;&#22635;&#20889;&#23436;&#25972;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
	}
	$transferApiUrl = trim($_POST['api_url']);
	if(!preg_match('/^https?:\/\//i', $transferApiUrl)) {
		exit(json_encode(array('code' => 0, 'msg' => html_entity_decode('&#20195;&#20184;&#25509;&#21475;&#22320;&#22336;&#26684;&#24335;&#38169;&#35823;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
	}
	if($_POST['check'] !== 'NO_CHECK' && $_POST['check'] !== 'FORCE_CHECK') {
		exit(json_encode(array('code' => 0, 'msg' => html_entity_decode('&#23454;&#21517;&#26657;&#39564;&#21442;&#25968;&#38169;&#35823;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
	}
	saveSetting('transfer_api_url', $transferApiUrl);
	saveSetting('transfer_id', $_POST['id']);
	saveSetting('transfer_key', $_POST['key']);
	saveSetting('transfer_check', $_POST['check']);
	$CACHE->clear();
	$_SESSION['transfer_pass'] = md5($_POST['pass']);
	$_SESSION['transfer'] = true;
	exit(json_encode(array('code' => 1, 'msg' => html_entity_decode('&#37197;&#32622;&#20445;&#23384;&#25104;&#21151;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
break;
case 'create_chat_session':
    $user_ip = isset($_POST['user_ip']) ? daddslashes($_POST['user_ip']) : '';
    if(empty($user_ip)) $user_ip = real_ip(1);

    $user_id = 0;
    $username = '';

    // read user info from cookie when available
    if(isset($_COOKIE['user_token'])){
        $token = authcode(daddslashes($_COOKIE['user_token']), 'DECODE', SYS_KEY);
        list($zid, $sid) = explode("\t", $token);
        if($userrow = $DB->getRow("SELECT * FROM pre_site WHERE zid=:zid LIMIT 1", [':zid' => intval($zid)])){
            $session = md5($userrow['user'].$userrow['pwd'].$password_hash);
            if($session === $sid && $userrow['status'] == 1){
                $user_id = $userrow['zid'];
                $username = $userrow['user'];
            }
        }
    }

    // find an active chat session for the same IP
    $existing_session = $DB->getRow("SELECT id FROM shua_chat_session WHERE user_ip=? AND status=1 LIMIT 1", [$user_ip]);

    if($existing_session){
        // update the active session owner
        if($user_id > 0){
            $DB->exec("UPDATE shua_chat_session SET user_id=?, username=? WHERE id=?", [$user_id, $username, $existing_session['id']]);
        }
        exit(json_encode(['code'=>1,'data'=>['session_id'=>$existing_session['id']]]));
    }

    // create a new chat session
    $DB->exec("INSERT INTO shua_chat_session (user_ip, user_id, username, status, create_time, last_msg_time) VALUES (?, ?, ?, 1, NOW(), NOW())", [$user_ip, $user_id, $username]);
    $session_id = $DB->lastInsertId();

    exit(json_encode(['code'=>1,'data'=>['session_id'=>$session_id]]));
    break;
case 'chat_session_list':
    adminpermission('chat', 2);
    $sessions = $DB->getAll("SELECT * FROM shua_chat_session ORDER BY last_msg_time DESC LIMIT 100");
    $data = [];
    foreach($sessions as $row){
        // hydrate session owner info when possible
        if($row['user_id'] == 0 && isset($_COOKIE['user_token'])){
            $token = authcode(daddslashes($_COOKIE['user_token']), 'DECODE', SYS_KEY);
            list($zid, $sid) = explode("\t", $token);
            if($userrow = $DB->getRow("SELECT * FROM pre_site WHERE zid=:zid LIMIT 1", [':zid' => intval($zid)])){
                $session = md5($userrow['user'].$userrow['pwd'].$password_hash);
                if($session === $sid && $userrow['status'] == 1){
                    $user_id = $userrow['zid'];
                    $username = $userrow['user'];
                    $DB->exec("UPDATE shua_chat_session SET user_id=?, username=? WHERE id=?", [$user_id, $username, $row['id']]);
                    $row['user_id'] = $user_id;
                    $row['username'] = $username;
                }
            }
        }

        $unread = $DB->getColumn("SELECT COUNT(*) FROM shua_chat_message WHERE session_id=? AND sender='user' AND id>(SELECT IFNULL(MAX(id),0) FROM shua_chat_message WHERE session_id=? AND sender='admin')", [$row['id'],$row['id']]);
        $data[] = [
            'id' => $row['id'],
            'user_ip' => $row['user_ip'],
            'user_id' => $row['user_id'],
            'username' => $row['username'],
            'status' => $row['status'],
            'last_msg_time' => $row['last_msg_time'],
            'create_time' => $row['create_time'],
            'unread' => $unread
        ];
    }
    exit(json_encode(['code'=>0,'data'=>$data]));
    break;
case 'chat_message_list':
    adminpermission('chat', 2);
    $session_id = intval($_GET['session_id']);
    $messages = $DB->getAll("SELECT * FROM shua_chat_message WHERE session_id=? ORDER BY id ASC LIMIT 100", [$session_id]);
    $data = [];
    foreach($messages as $msg){
        $data[] = [
            'id' => $msg['id'],
            'sender' => $msg['sender'],
            'content' => $msg['content'],
            'type' => $msg['type'],
            'create_time' => $msg['create_time']
        ];
    }
    exit(json_encode(['code'=>0,'data'=>$data]));
    break;
case 'chat_send_message':
    $session_id = intval($_POST['session_id']);
    $content = trim($_POST['content']);
    $type = isset($_POST['type']) ? intval($_POST['type']) : 0;
    $sender = isset($_POST['sender']) && in_array($_POST['sender'], array('user', 'admin'), true) ? $_POST['sender'] : 'admin';
    adminpermission('chat', 2);

    if($sender == 'user'){
        if(isset($_COOKIE['user_token'])){
            $token = authcode(daddslashes($_COOKIE['user_token']), 'DECODE', SYS_KEY);
            list($zid, $sid) = explode("\t", $token);
            if($userrow = $DB->getRow("SELECT * FROM pre_site WHERE zid=:zid LIMIT 1", array(':zid' => intval($zid)))){
                $session = md5($userrow['user'].$userrow['pwd'].$password_hash);
                if($session === $sid && $userrow['status'] == 1){
                    $user_id = $userrow['zid'];
                    $username = $userrow['user'];
                    $DB->exec("UPDATE shua_chat_session SET user_id=?, username=? WHERE id=?", array($user_id, $username, $session_id));
                }
            }
        }
    }

    if(isset($_FILES['image']) && $_FILES['image']['size'] > 0){
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        $allowed_exts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $file_type = strtolower((string)$_FILES['image']['type']);
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $max_size = 2 * 1024 * 1024;

        if(!in_array($file_type, $allowed_types, true) || !in_array($file_ext, $allowed_exts, true)){
            exit(json_encode(array('code'=>-1,'msg'=>html_entity_decode('&#20165;&#25903;&#25345; JPG/PNG/GIF/WEBP &#22270;&#29255;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
        }

        if($_FILES['image']['size'] > $max_size){
            exit(json_encode(array('code'=>-1,'msg'=>html_entity_decode('&#22270;&#29255;&#22823;&#23567;&#19981;&#33021;&#36229;&#36807; 2MB', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
        }

        $image_info = @getimagesize($_FILES['image']['tmp_name']);
        if($image_info === false){
            exit(json_encode(array('code'=>-1,'msg'=>html_entity_decode('&#22270;&#29255;&#25991;&#20214;&#39564;&#35777;&#22833;&#36133;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
        }

        $filename = 'chat_'.date('YmdHis').'_'.rand(1000,9999).'.'.$file_ext;
        $filepath = ROOT.'assets/img/chat/'.$filename;
        if(!is_dir(ROOT.'assets/img/chat/')) mkdir(ROOT.'assets/img/chat/', 0777, true);
        if(move_uploaded_file($_FILES['image']['tmp_name'], $filepath)){
            $content = '/assets/img/chat/'.$filename;
            $type = 1;
        }else{
            exit(json_encode(array('code'=>-1,'msg'=>html_entity_decode('&#22270;&#29255;&#19978;&#20256;&#22833;&#36133;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
        }
    }

    if(isset($_FILES['video']) && $_FILES['video']['size'] > 0){
        $chat_video_enable = isset($conf['chat_video_enable']) ? intval($conf['chat_video_enable']) : 1;
        if($chat_video_enable != 1){
            exit(json_encode(array('code'=>-10,'msg'=>html_entity_decode('&#24403;&#21069;&#26410;&#24320;&#21551;&#35270;&#39057;&#21457;&#36865;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
        }

        $allowed_types = array('video/mp4');
        $allowed_exts = array('mp4');
        $file_type = strtolower((string)$_FILES['video']['type']);
        $file_ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
        $max_mb = max(1, intval(isset($conf['chat_video_max_size']) ? $conf['chat_video_max_size'] : 10));
        $max_size = $max_mb * 1024 * 1024;

        if(!in_array($file_type, $allowed_types, true) || !in_array($file_ext, $allowed_exts, true)){
            exit(json_encode(array('code'=>-1,'msg'=>html_entity_decode('&#20165;&#25903;&#25345; MP4 &#35270;&#39057;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
        }

        if($_FILES['video']['size'] > $max_size){
            exit(json_encode(array('code'=>-1,'msg'=>html_entity_decode('&#35270;&#39057;&#22823;&#23567;&#19981;&#33021;&#36229;&#36807;', ENT_QUOTES, 'UTF-8').$max_mb.'MB'), JSON_UNESCAPED_UNICODE));
        }

        $filename = 'chat_video_'.date('YmdHis').'_'.rand(1000,9999).'.'.$file_ext;
        $filepath = ROOT.'assets/img/chat/'.$filename;
        if(!is_dir(ROOT.'assets/img/chat/')) mkdir(ROOT.'assets/img/chat/', 0777, true);
        if(move_uploaded_file($_FILES['video']['tmp_name'], $filepath)){
            $content = '/assets/img/chat/'.$filename;
            $type = 2;
        }else{
            exit(json_encode(array('code'=>-1,'msg'=>html_entity_decode('&#35270;&#39057;&#19978;&#20256;&#22833;&#36133;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
        }
    }

    $DB->exec("INSERT INTO shua_chat_message (session_id,sender,content,type,create_time) VALUES (?,?,?,?,NOW())", array($session_id, $sender, $content, $type));
    $DB->exec("UPDATE shua_chat_session SET last_msg_time=NOW() WHERE id=?", array($session_id));

    if($type == 1 && $sender == 'user'){
        exit(json_encode(array('code'=>0,'msg'=>html_entity_decode('&#21457;&#36865;&#25104;&#21151;', ENT_QUOTES, 'UTF-8'),'data'=>array('image_url'=>$content)), JSON_UNESCAPED_UNICODE));
    }

    exit(json_encode(array('code'=>0,'msg'=>html_entity_decode('&#21457;&#36865;&#25104;&#21151;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
    break;
case 'chat_close_session':
    adminpermission('chat', 2);
    $session_id = intval($_POST['session_id']);
    $DB->exec("UPDATE shua_chat_session SET status=0 WHERE id=?", array($session_id));
    exit(json_encode(array('code'=>0,'msg'=>html_entity_decode('&#20250;&#35805;&#24050;&#20851;&#38381;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
break;
case 'delToolLog':
	adminpermission('shop', 2);
	q8_admin_require_post_csrf();
	$id = intval($_POST['id']);
	$logType = isset($_POST['log_type']) && $_POST['log_type'] === 'offline' ? 'offline' : 'online';
	$table = $logType === 'offline' ? 'pre_toollogs_offline' : 'pre_toollogs';
	$label = $logType === 'offline'
		? html_entity_decode('&#19979;&#26550;&#26085;&#24535;', ENT_QUOTES, 'UTF-8')
		: html_entity_decode('&#19978;&#26550;&#26085;&#24535;', ENT_QUOTES, 'UTF-8');
	$sql = "DELETE FROM {$table} WHERE id=:id limit 1";
	if($DB->exec($sql, array(':id' => $id)) !== false){
		if(function_exists('q8_toollog_clear_cache')) q8_toollog_clear_cache();
		exit(json_encode(array('code'=>0,'msg'=>$label . html_entity_decode('&#21024;&#38500;&#25104;&#21151;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
	}else{
		exit(json_encode(array('code'=>-1,'msg'=>$label . html_entity_decode('&#21024;&#38500;&#22833;&#36133;&#65306;', ENT_QUOTES, 'UTF-8') . '['.$DB->error().']'), JSON_UNESCAPED_UNICODE));
	}
break;
case 'saveToolLog':
	adminpermission('shop', 2);
	q8_admin_require_post_csrf();
	if (function_exists('q8_toollog_ensure_tables')) {
		q8_toollog_ensure_tables();
	}
	$id = intval($_POST['id']);
	$logType = isset($_POST['log_type']) && $_POST['log_type'] === 'offline' ? 'offline' : 'online';
	$table = $logType === 'offline' ? 'pre_toollogs_offline' : 'pre_toollogs';
	$label = $logType === 'offline'
		? html_entity_decode('&#19979;&#26550;&#26085;&#24535;', ENT_QUOTES, 'UTF-8')
		: html_entity_decode('&#19978;&#26550;&#26085;&#24535;', ENT_QUOTES, 'UTF-8');
	$prefix = $logType === 'offline'
		? html_entity_decode('&#21830;&#21697;&#19979;&#26550;&#65306;', ENT_QUOTES, 'UTF-8')
		: html_entity_decode('&#21830;&#21697;&#19978;&#26550;&#65306;', ENT_QUOTES, 'UTF-8');
	$date = trim((string)$_POST['date']);
	$itemsJson = isset($_POST['items_json']) ? trim((string)$_POST['items_json']) : '';
	if ($id < 1) {
		exit(json_encode(array('code' => -1, 'msg' => html_entity_decode('&#35760;&#24405; ID &#38169;&#35823;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
	}
	$row = $DB->getRow("SELECT id,date FROM {$table} WHERE id=:id LIMIT 1", array(':id' => $id));
	if (!$row) {
		exit(json_encode(array('code' => -1, 'msg' => html_entity_decode('&#35760;&#24405;&#19981;&#23384;&#22312;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
	}
	$dateObj = DateTime::createFromFormat('Y-m-d', $date);
	if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
		exit(json_encode(array('code' => -1, 'msg' => html_entity_decode('&#26085;&#26399;&#26684;&#24335;&#19981;&#27491;&#30830;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
	}
	if ($itemsJson === '') {
		exit(json_encode(array('code' => -1, 'msg' => html_entity_decode('&#35831;&#33267;&#23569;&#20445;&#30041;&#19968;&#26465;&#21830;&#21697;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
	}
	$decodedEntries = json_decode($itemsJson, true);
	if (!is_array($decodedEntries)) {
		exit(json_encode(array('code' => -1, 'msg' => html_entity_decode('&#21830;&#21697;&#25968;&#25454;&#35299;&#26512;&#22833;&#36133;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
	}
	$entries = array();
	foreach ($decodedEntries as $entry) {
		$name = '';
		$tid = 0;
		if (is_array($entry)) {
			$name = isset($entry['name']) ? trim((string)$entry['name']) : '';
			$tid = isset($entry['tid']) ? intval($entry['tid']) : 0;
		} else {
			$name = trim((string)$entry);
		}
		$tid = max(0, $tid);
		if ($name === '' && $tid > 0) {
			$tool = $DB->getRow("SELECT name FROM pre_tools WHERE tid=:tid LIMIT 1", array(':tid' => $tid));
			if ($tool && isset($tool['name'])) {
				$name = trim((string)$tool['name']);
			}
		}
		if ($name === '') {
			continue;
		}
		if (function_exists('q8_toollog_append_unique_entry')) {
			q8_toollog_append_unique_entry($entries, $name, $tid);
		} else {
			$entries[] = array('name' => $name, 'tid' => $tid);
		}
	}
	if (empty($entries)) {
		exit(json_encode(array('code' => -1, 'msg' => html_entity_decode('&#35831;&#33267;&#23569;&#20445;&#30041;&#19968;&#26465;&#26377;&#25928;&#21830;&#21697;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
	}
	$content = function_exists('q8_toollog_build_content') ? q8_toollog_build_content($prefix, $entries) : '';
	if ($content === '') {
		exit(json_encode(array('code' => -1, 'msg' => html_entity_decode('&#26085;&#24535;&#20869;&#23481;&#29983;&#25104;&#22833;&#36133;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
	}
	$DB->exec("UPDATE {$table} SET content=:content,date=:date,addtime=NOW(),active=1 WHERE id=:id LIMIT 1", array(
		':content' => $content,
		':date' => $date,
		':id' => $id
	));
	$oldDate = isset($row['date']) ? trim((string)$row['date']) : '';
	if (function_exists('q8_toollog_merge_rows')) {
		q8_toollog_merge_rows($logType, $date);
		if ($oldDate !== '' && $oldDate !== $date) {
			q8_toollog_merge_rows($logType, $oldDate);
		}
	}
	if (function_exists('q8_toollog_clear_cache')) {
		q8_toollog_clear_cache();
	}
	exit(json_encode(array('code' => 0, 'msg' => $label . html_entity_decode('&#20445;&#23384;&#25104;&#21151;', ENT_QUOTES, 'UTF-8')), JSON_UNESCAPED_UNICODE));
break;
case 'batchToolLogOperation':
	adminpermission('shop', 2);
	q8_admin_require_post_csrf();
	if (function_exists('q8_toollog_ensure_tables')) {
		q8_toollog_ensure_tables();
	}
	$aid = intval($_POST['aid']);
	$logType = isset($_POST['log_type']) && $_POST['log_type'] === 'offline' ? 'offline' : 'online';
	$table = $logType === 'offline' ? 'pre_toollogs_offline' : 'pre_toollogs';
	$label = $logType === 'offline'
		? html_entity_decode('&#19979;&#26550;&#26085;&#24535;', ENT_QUOTES, 'UTF-8')
		: html_entity_decode('&#19978;&#26550;&#26085;&#24535;', ENT_QUOTES, 'UTF-8');
	$checkbox = isset($_POST['checkbox']) ? $_POST['checkbox'] : array();
	if (!is_array($checkbox)) {
		$checkbox = trim((string)$checkbox) === '' ? array() : explode(',', (string)$checkbox);
	}
	$ids = array();
	foreach ($checkbox as $item) {
		$itemId = intval($item);
		if ($itemId > 0) {
			$ids[$itemId] = $itemId;
		}
	}
	if (empty($ids)) {
		exit(json_encode(array(
			'code' => -1,
			'msg' => html_entity_decode('&#35831;&#33267;&#23569;&#36873;&#25321;&#19968;&#26465;&#35760;&#24405;', ENT_QUOTES, 'UTF-8')
		), JSON_UNESCAPED_UNICODE));
	}
	if ($aid !== 1) {
		exit(json_encode(array(
			'code' => -1,
			'msg' => html_entity_decode('&#19981;&#25903;&#25345;&#30340;&#25209;&#37327;&#25805;&#20316;', ENT_QUOTES, 'UTF-8')
		), JSON_UNESCAPED_UNICODE));
	}
	$deleted = 0;
	foreach ($ids as $itemId) {
		if ($DB->exec("DELETE FROM {$table} WHERE id=:id LIMIT 1", array(':id' => $itemId)) !== false) {
			$deleted++;
		}
	}
	if (function_exists('q8_toollog_clear_cache')) {
		q8_toollog_clear_cache();
	}
	exit(json_encode(array(
		'code' => 0,
		'msg' => html_entity_decode('&#24050;&#21024;&#38500;', ENT_QUOTES, 'UTF-8') . ' ' . $deleted . ' ' . $label
	), JSON_UNESCAPED_UNICODE));
break;
case 'setSeckill':
	adminpermission('shop', 2);
	q8_admin_require_post_csrf();
	$id = intval($_POST['id']);
	$active = intval($_POST['active']);
	$DB->exec("update pre_seckillshop set active=:active where id=:id", array(':active' => $active, ':id' => $id));
	exit(json_encode(array(
		'code' => 0,
		'msg' => html_entity_decode('&#25805;&#20316;&#25104;&#21151;', ENT_QUOTES, 'UTF-8')
	), JSON_UNESCAPED_UNICODE));
break;
case 'delSeckill':
	adminpermission('shop', 2);
	q8_admin_require_post_csrf();
	$id = intval($_POST['id']);
	$DB->exec("DELETE FROM pre_seckillshop WHERE id=:id limit 1", array(':id' => $id));
	exit(json_encode(array(
		'code' => 0,
		'msg' => html_entity_decode('&#21024;&#38500;&#25104;&#21151;', ENT_QUOTES, 'UTF-8')
	), JSON_UNESCAPED_UNICODE));
break;
case 'system_info':
	adminpermission('index', 1);
	$serverRoot = defined('ROOT') ? ROOT : dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
	$unknownText = html_entity_decode('&#26410;&#30693;', ENT_QUOTES, 'UTF-8');
	$formatBytes = function($bytes){
		$bytes = floatval($bytes);
		if($bytes <= 0) return '0 B';
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$power = (int)floor(log($bytes, 1024));
		$power = max(0, min($power, count($units) - 1));
		$value = $bytes / pow(1024, $power);
		return round($value, $power === 0 ? 0 : 2) . ' ' . $units[$power];
	};
	$getCpuCores = function(){
		if(stripos(PHP_OS, 'WIN') === 0){
			$winCores = intval(getenv('NUMBER_OF_PROCESSORS'));
			return $winCores > 0 ? $winCores : 1;
		}
		if(is_readable('/proc/cpuinfo')){
			$cpuInfo = file_get_contents('/proc/cpuinfo');
			if($cpuInfo !== false){
				preg_match_all('/^processor\s*:/m', $cpuInfo, $matches);
				if(!empty($matches[0])) return count($matches[0]);
			}
		}
		if(function_exists('shell_exec')){
			$coreOutput = trim((string)@shell_exec('nproc 2>/dev/null'));
			if($coreOutput !== '' && ctype_digit($coreOutput)) return intval($coreOutput);
		}
		return 1;
	};
	$getMemInfo = function(){
		$memory = array('total' => 0, 'available' => 0);
		if(!is_readable('/proc/meminfo')) return $memory;
		$lines = @file('/proc/meminfo', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if(!$lines) return $memory;
		$mem = array();
		foreach($lines as $line){
			if(strpos($line, ':') === false) continue;
			list($key, $value) = explode(':', $line, 2);
			$mem[trim($key)] = intval(filter_var($value, FILTER_SANITIZE_NUMBER_INT)) * 1024;
		}
		$memory['total'] = isset($mem['MemTotal']) ? $mem['MemTotal'] : 0;
		if(isset($mem['MemAvailable'])){
			$memory['available'] = $mem['MemAvailable'];
		}else{
			$memory['available'] = (isset($mem['MemFree']) ? $mem['MemFree'] : 0) + (isset($mem['Buffers']) ? $mem['Buffers'] : 0) + (isset($mem['Cached']) ? $mem['Cached'] : 0);
		}
		return $memory;
	};
	$cpuUsage = 0;
	if(function_exists('sys_getloadavg')){
		$loadAvg = @sys_getloadavg();
		if($loadAvg !== false && isset($loadAvg[0])){
			$cpuUsage = round((floatval($loadAvg[0]) / max(1, $getCpuCores())) * 100, 2);
		}
	}
	$cpuUsage = max(0, min(100, $cpuUsage));
	$memInfo = $getMemInfo();
	$memoryTotalBytes = max(0, intval($memInfo['total']));
	$memoryAvailableBytes = max(0, intval($memInfo['available']));
	$memoryUsedBytes = max(0, $memoryTotalBytes - $memoryAvailableBytes);
	$memoryUsage = $memoryTotalBytes > 0 ? round(($memoryUsedBytes / $memoryTotalBytes) * 100, 2) : 0;
	$diskTotalBytes = @disk_total_space($serverRoot);
	if($diskTotalBytes === false) $diskTotalBytes = @disk_total_space(dirname($serverRoot));
	$diskFreeBytes = @disk_free_space($serverRoot);
	if($diskFreeBytes === false) $diskFreeBytes = @disk_free_space(dirname($serverRoot));
	$diskTotalBytes = $diskTotalBytes !== false ? floatval($diskTotalBytes) : 0;
	$diskFreeBytes = $diskFreeBytes !== false ? floatval($diskFreeBytes) : 0;
	$diskUsedBytes = max(0, $diskTotalBytes - $diskFreeBytes);
	$diskPercent = $diskTotalBytes > 0 ? round(($diskUsedBytes / $diskTotalBytes) * 100, 2) : 0;
	$dbSizeMb = 0;
	global $dbconfig;
	if(!empty($dbconfig['dbname'])){
		$dbName = addslashes($dbconfig['dbname']);
		try{
			$dbSizeBytes = $DB->getColumn("SELECT COALESCE(SUM(data_length + index_length), 0) FROM information_schema.tables WHERE table_schema='{$dbName}'");
			$dbSizeMb = round(floatval($dbSizeBytes) / 1048576, 2);
		}catch(Exception $e){
			$dbSizeMb = 0;
		}
	}
	exit(json_encode(array(
		'code' => 0,
		'cpu_usage' => $cpuUsage,
		'memory_usage' => $memoryUsage,
		'memory_used' => $formatBytes($memoryUsedBytes),
		'memory_total' => $formatBytes($memoryTotalBytes),
		'disk_percent' => $diskPercent,
		'disk_used' => round($diskUsedBytes / 1073741824, 2),
		'disk_total' => round($diskTotalBytes / 1073741824, 2),
		'db_size' => $dbSizeMb,
		'server_time' => date('Y-m-d H:i:s'),
		'php_version' => PHP_VERSION,
		'unknown_text' => $unknownText
	), JSON_UNESCAPED_UNICODE));
break;

case 'get_visit_details':
	adminpermission('index', 1);
	$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
	$pageSize = isset($_GET['pageSize']) ? intval($_GET['pageSize']) : 20;
	$page = $page > 0 ? $page : 1;
	$pageSize = $pageSize > 0 ? min($pageSize, 100) : 20;
	$offset = ($page - 1) * $pageSize;
	$visitTable = DBQZ . '_visit_ips';
	$unknownText = html_entity_decode('&#26410;&#30693;', ENT_QUOTES, 'UTF-8');
	try {
		$tableExists = $DB->getColumn("SHOW TABLES LIKE '{$visitTable}'");
		if(!$tableExists){
			exit(json_encode(array(
				'code' => 1,
				'msg' => html_entity_decode('&#26242;&#26080;&#35775;&#38382;&#35760;&#24405;', ENT_QUOTES, 'UTF-8'),
				'total' => 0,
				'page' => $page,
				'pageSize' => $pageSize,
				'visits' => array()
			), JSON_UNESCAPED_UNICODE));
		}
		$frontVisitWhere = function_exists('q8_admin_front_visit_where') ? q8_admin_front_visit_where() : '1=1';
		$hasUsernameColumn = $DB->getColumn("SHOW COLUMNS FROM {$visitTable} LIKE 'username'");
		$usernameSelect = $hasUsernameColumn ? '`username`' : "'' AS username";
		$total = intval($DB->getColumn("SELECT COUNT(*) FROM {$visitTable} WHERE {$frontVisitWhere}"));
		if($total <= 0){
			exit(json_encode(array(
				'code' => 1,
				'msg' => html_entity_decode('&#26242;&#26080;&#35775;&#38382;&#35760;&#24405;', ENT_QUOTES, 'UTF-8'),
				'total' => 0,
				'page' => $page,
				'pageSize' => $pageSize,
				'visits' => array()
			), JSON_UNESCAPED_UNICODE));
		}
		$visits = $DB->getAll("SELECT `date`, `ip`, {$usernameSelect}, `url`, `user_agent`, `region`, `visits`, `updated_at` FROM {$visitTable} WHERE {$frontVisitWhere} ORDER BY `updated_at` DESC LIMIT {$offset}, {$pageSize}");
		$visitList = array();
		foreach((array)$visits as $visit){
			$visitList[] = array(
				'ip' => isset($visit['ip']) ? $visit['ip'] : '',
				'username' => isset($visit['username']) ? ($visit['username'] ?: '') : '',
				'url' => !empty($visit['url']) ? $visit['url'] : '-',
				'user_agent' => !empty($visit['user_agent']) ? $visit['user_agent'] : '-',
				'visit_time' => !empty($visit['updated_at']) ? $visit['updated_at'] : (isset($visit['date']) ? $visit['date'] : ''),
				'region' => !empty($visit['region']) ? $visit['region'] : $unknownText,
				'visits' => isset($visit['visits']) ? intval($visit['visits']) : 0
			);
		}
		exit(json_encode(array(
			'code' => 0,
			'total' => $total,
			'page' => $page,
			'pageSize' => $pageSize,
			'visits' => $visitList
		), JSON_UNESCAPED_UNICODE));
	} catch (Exception $e) {
		exit(json_encode(array(
			'code' => -1,
			'msg' => html_entity_decode('&#33719;&#21462;&#35775;&#38382;&#26126;&#32454;&#22833;&#36133;&#65306;', ENT_QUOTES, 'UTF-8') . $e->getMessage()
		), JSON_UNESCAPED_UNICODE));
	}
break;
default:
exit('{"code":-4,"msg":"No Act"}');
break;
}
