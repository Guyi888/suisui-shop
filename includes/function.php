<?php

function q8_is_https_request() {

	if ((isset($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off' && $_SERVER['HTTPS'] !== '')
		|| (isset($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443')
		|| (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
		|| (isset($_SERVER['REQUEST_SCHEME']) && strtolower((string)$_SERVER['REQUEST_SCHEME']) === 'https')) {
		return true;
	}

	return false;

}

function q8_get_cookie_domain() {

	$host = isset($_SERVER['HTTP_HOST']) ? strtolower(trim((string)$_SERVER['HTTP_HOST'])) : '';
	if ($host === '') {
		return '';
	}

	$host = preg_replace('/:\d+$/', '', $host);
	if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
		return '';
	}

	return $host;

}

function q8_set_admin_token_backup_cookie($token, $expires) {

	$domain = q8_get_cookie_domain();
	$secure = q8_is_https_request();
	setcookie('admin_token_backup', $token, $expires, '/', $domain, $secure, true);

}

function q8_clear_admin_token_backup_cookie() {

	q8_set_admin_token_backup_cookie('', time() - 604800);

}

function q8_admin_csrf_token() {

	return md5(session_id() . SYS_KEY);

}

function q8_admin_check_csrf($token = null) {

	if ($token === null) {
		if (isset($_POST['csrf_token'])) {
			$token = $_POST['csrf_token'];
		} elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
			$token = $_SERVER['HTTP_X_CSRF_TOKEN'];
		} else {
			$token = '';
		}
	}

	$expected = q8_admin_csrf_token();
	$token = (string)$token;

	if (function_exists('hash_equals')) {
		return hash_equals($expected, $token);
	}

	return $expected === $token;

}

function q8_admin_require_post_csrf($requireReferer = true) {

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		showmsg(html_entity_decode('&#35831;&#27714;&#26041;&#24335;&#38169;&#35823;&#65292;&#35831;&#21047;&#26032;&#39029;&#38754;&#21518;&#37325;&#35797;', ENT_QUOTES, 'UTF-8'), 3);
	}

	if ($requireReferer && !checkRefererHost()) {
		exit;
	}

	if (!q8_admin_check_csrf()) {
		showmsg(html_entity_decode('&#34920;&#21333;&#24050;&#22833;&#25928;&#65292;&#35831;&#21047;&#26032;&#39029;&#38754;&#21518;&#37325;&#35797;', ENT_QUOTES, 'UTF-8'), 3);
	}

}

function q8_get_site_table_columns() {

	global $DB;
	static $columns = null;

	if ($columns !== null) {
		return $columns;
	}

	$columns = array();
	$rows = $DB->getAll('SHOW COLUMNS FROM pre_site');
	if (is_array($rows)) {
		foreach ($rows as $row) {
			if (!empty($row['Field'])) {
				$columns[$row['Field']] = $row;
			}
		}
	}

	return $columns;

}

function q8_get_site_column_fallback($field, $meta, $conf = array(), $date = null) {

	$type = strtolower(isset($meta['Type']) ? (string)$meta['Type'] : '');
	if ($date === null || $date === '') {
		$date = date('Y-m-d H:i:s');
	}

	if ($field === 'status') return 1;
	if ($field === 'power' || $field === 'upzid' || $field === 'utype') return 0;
	if ($field === 'addtime') return $date;
	if ($field === 'endtime') return date('Y-m-d', strtotime('+1 year'));
	if ($field === 'sitename' && isset($conf['sitename'])) return (string)$conf['sitename'];
	if ($field === 'title' && isset($conf['title'])) return (string)$conf['title'];
	if ($field === 'keywords' && isset($conf['keywords'])) return (string)$conf['keywords'];
	if ($field === 'description' && isset($conf['description'])) return (string)$conf['description'];
	if ($field === 'anounce' && isset($conf['anounce'])) return (string)$conf['anounce'];
	if ($field === 'alert' && isset($conf['alert'])) return (string)$conf['alert'];

	if (strpos($type, 'int') !== false || strpos($type, 'decimal') !== false || strpos($type, 'float') !== false || strpos($type, 'double') !== false) {
		return 0;
	}
	if (strpos($type, 'datetime') !== false || strpos($type, 'timestamp') !== false) {
		return $date;
	}
	if (preg_match('/^date\b/', $type)) {
		return substr($date, 0, 10);
	}

	return '';

}

function q8_prepare_site_insert_payload($payload, $conf = array(), $date = null) {

	$columns = q8_get_site_table_columns();
	$values = array();
	if ($date === null || $date === '') {
		$date = date('Y-m-d H:i:s');
	}

	foreach ((array)$payload as $field => $value) {
		if ($field === 'zid' || !isset($columns[$field])) {
			continue;
		}
		$values[$field] = $value;
	}

	if (!isset($values['addtime']) && isset($columns['addtime'])) {
		$values['addtime'] = $date;
	}

	foreach ($columns as $field => $meta) {
		if ($field === 'zid' || array_key_exists($field, $values)) {
			continue;
		}
		$nullable = isset($meta['Null']) && strtoupper((string)$meta['Null']) === 'YES';
		$hasDefault = array_key_exists('Default', $meta) && $meta['Default'] !== null;
		$extra = strtolower(isset($meta['Extra']) ? (string)$meta['Extra'] : '');
		if (strpos($extra, 'auto_increment') !== false) {
			continue;
		}
		if (!$nullable && !$hasDefault) {
			$values[$field] = q8_get_site_column_fallback($field, $meta, $conf, $date);
		}
	}

	return $values;

}

function q8_insert_site_account($payload, $conf = array(), $date = null) {

	global $DB;

	$values = q8_prepare_site_insert_payload($payload, $conf, $date);
	if (empty($values)) {
		return false;
	}

	if (function_exists('q8_site_markup_template_ensure_fields')) {
		q8_site_markup_template_ensure_fields();
	}
	if (q8_site_has_column('site_prid') && !array_key_exists('site_prid', $values)) {
		$values['site_prid'] = 0;
	}
	if (intval(isset($values['power']) ? $values['power'] : 0) > 0 && intval(isset($values['upzid']) ? $values['upzid'] : 0) > 0 && function_exists('q8_site_can_create_child_site')) {
		$parentSite = $DB->getRow("SELECT zid,upzid,power FROM pre_site WHERE zid=:zid LIMIT 1", array(':zid' => intval($values['upzid'])));
		if (!q8_site_can_create_child_site($parentSite)) {
			return false;
		}
	}

	$fields = array();
	$placeholders = array();
	$params = array();
	foreach ($values as $field => $value) {
		$fields[] = '`' . $field . '`';
		$placeholders[] = ':' . $field;
		$params[':' . $field] = $value;
	}

	$sql = 'INSERT INTO `pre_site` (' . implode(',', $fields) . ') VALUES (' . implode(',', $placeholders) . ')';
	return $DB->exec($sql, $params);

}

if (!function_exists('q8_render_action')) {
	function q8_render_action($hook)
	{
		return '';
	}
}

function q8_template_name_is_valid($template)
{

	$template = trim((string)$template);
	if ($template === '' || $template === '0') {
		return false;
	}

	if (!preg_match('/^[a-zA-Z0-9\-]+$/', $template)) {
		return false;
	}

	return \lib\Template::exists($template);

}

function q8_template_name_for_save($template)
{

	$template = trim((string)$template);
	if ($template === '' || $template === '0') {
		return '';
	}

	if (!q8_template_name_is_valid($template)) {
		return false;
	}

	return $template;

}

function q8_template_name_resolve($template, $fallback = 'XHY-01')
{

	$template = trim((string)$template);
	$fallback = trim((string)$fallback);

	if (q8_template_name_is_valid($template)) {
		return $template;
	}

	if (q8_template_name_is_valid($fallback)) {
		return $fallback;
	}

	return 'default';

}

function q8_brand_asset_version()
{
	return defined('VERSION') ? VERSION : date('YmdHis');
}

function q8_brand_favicon_href()
{
	return '/assets/img/favicon/favicon.ico?v=' . rawurlencode(q8_brand_asset_version());
}

function q8_brand_logo_href()
{
	return '/assets/img/logo.png?r=74129';
}

function q8_get_fenzhan_price_context($conf, $isFenzhan = false, $siteRow = array())
{

	$normalPrice = isset($conf['fenzhan_price']) ? floatval($conf['fenzhan_price']) : 0;
	$professionalPrice = isset($conf['fenzhan_price2']) ? floatval($conf['fenzhan_price2']) : 0;
	$professionalCost = isset($conf['fenzhan_cost2']) ? floatval($conf['fenzhan_cost2']) : 0;

	if ($professionalCost <= 0) {
		$professionalCost = $professionalPrice;
	}

	if ($isFenzhan && is_array($siteRow) && intval(isset($siteRow['power']) ? $siteRow['power'] : 0) === 2) {
		$siteNormalPrice = isset($siteRow['ktfz_price']) ? floatval($siteRow['ktfz_price']) : 0;
		$siteProfessionalPrice = isset($siteRow['ktfz_price2']) ? floatval($siteRow['ktfz_price2']) : 0;

		if ($siteNormalPrice > 0) {
			$normalPrice = $siteNormalPrice;
		}
		if ($siteProfessionalPrice > 0 && $siteProfessionalPrice >= $professionalCost) {
			$professionalPrice = $siteProfessionalPrice;
		}
	}

	return array(
		'normal_price' => round($normalPrice, 2),
		'professional_price' => round($professionalPrice, 2),
		'professional_cost' => round($professionalCost, 2),
	);

}

function q8_site_can_create_child_site($siteRow)
{

	if (!is_array($siteRow)) {
		return false;
	}

	return intval(isset($siteRow['power']) ? $siteRow['power'] : 0) === 2
		&& intval(isset($siteRow['upzid']) ? $siteRow['upzid'] : 0) <= 1;

}

function q8_format_currency_amount($amount)
{

	$formatted = number_format((float)$amount, 2, '.', '');
	return rtrim(rtrim($formatted, '0'), '.');

}

function q8_get_message_scope_labels() {

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

function q8_get_message_types_by_power($power) {

	$power = intval($power);
	if ($power === 2) return array(0, 2, 4);
	if ($power === 1) return array(0, 2, 3);
	return array(0, 1);

}

function q8_get_message_types_for_userrow($userrow) {

	$power = intval(isset($userrow['power']) ? $userrow['power'] : 0);
	$upzid = intval(isset($userrow['upzid']) ? $userrow['upzid'] : 0);

	if ($power === 2) {
		return array(0, 2, 4);
	}
	if ($power === 1) {
		return array(0, 2, 3);
	}

	$types = array(0, 1);
	$types[] = $upzid > 0 ? 6 : 5;
	return $types;

}

function q8_parse_int_csv($csv) {

	$result = array();
	foreach (explode(',', (string)$csv) as $item) {
		$item = intval(trim($item));
		if ($item > 0) {
			$result[$item] = $item;
		}
	}
	return array_values($result);

}

function q8_build_int_csv($ids) {

	$ids = q8_parse_int_csv(implode(',', (array)$ids));
	if (empty($ids)) {
		return '';
	}
	return implode(',', $ids) . ',';

}

function q8_count_unread_messages($userrow) {

	global $DB;

	$types = q8_get_message_types_for_userrow($userrow);
	$typeSql = implode(',', array_map('intval', $types));
	$readIds = q8_parse_int_csv(isset($userrow['msgread']) ? $userrow['msgread'] : '');
	$sql = "SELECT count(*) FROM pre_message WHERE active=1 AND type IN ($typeSql)";
	if (!empty($readIds)) {
		$sql .= ' AND id NOT IN (' . implode(',', $readIds) . ')';
	}

	return intval($DB->getColumn($sql));

}

function q8_mark_message_read($zid, $msgread, $messageId) {

	global $DB;

	$zid = intval($zid);
	$messageId = intval($messageId);
	if ($zid <= 0 || $messageId <= 0) {
		return false;
	}

	$readIds = q8_parse_int_csv($msgread);
	if (!in_array($messageId, $readIds, true)) {
		$readIds[] = $messageId;
		sort($readIds, SORT_NUMERIC);
		$DB->exec('UPDATE pre_message SET count=count+1 WHERE id=:id', array(':id' => $messageId));
	}

	return $DB->exec('UPDATE pre_site SET msgread=:msgread WHERE zid=:zid', array(':msgread' => q8_build_int_csv($readIds), ':zid' => $zid)) !== false;

}

function q8_mark_all_messages_read($zid, $power = null) {

	global $DB;

	$userrow = null;
	if (is_array($zid)) {
		$userrow = $zid;
		$zid = intval(isset($userrow['zid']) ? $userrow['zid'] : 0);
		$power = isset($userrow['power']) ? $userrow['power'] : 0;
	}
	$zid = intval($zid);
	if ($zid <= 0) {
		return false;
	}

	if ($userrow === null) {
		$userrow = array('power' => intval($power));
	}
	$types = q8_get_message_types_for_userrow($userrow);
	$typeSql = implode(',', array_map('intval', $types));
	$rows = $DB->getAll("SELECT id FROM pre_message WHERE active=1 AND type IN ($typeSql)");
	$ids = array();
	if (is_array($rows)) {
		foreach ($rows as $row) {
			$id = intval(isset($row['id']) ? $row['id'] : 0);
			if ($id > 0) {
				$ids[] = $id;
			}
		}
	}

	return $DB->exec('UPDATE pre_site SET msgread=:msgread WHERE zid=:zid', array(':msgread' => q8_build_int_csv($ids), ':zid' => $zid)) !== false;

}

function curl_get($url)

{

	$ch = curl_init($url);

	$httpheader[] = "Accept: */*";

	$httpheader[] = "Accept-Encoding: gzip,deflate,sdch";

	$httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";

	$httpheader[] = "Connection: close";

	curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);

	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

	curl_setopt($ch, CURLOPT_ENCODING, "gzip");

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; U; Android 4.4.1; zh-cn; R815T Build/JOP40D) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.5 Mobile Safari/533.1");

	curl_setopt($ch, CURLOPT_TIMEOUT, 30);

	$ret = curl_exec($ch);
	$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);



	curl_close($ch);

	return $ret;

}



function get_curl($url,$post=0,$referer=0,$cookie=0,$header=0,$ua=0,$nobaody=0,$addheader=0){

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,$url);

	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

	$httpheader = array();
	$httpheader[] = "Accept: */*";

	$httpheader[] = "Accept-Encoding: gzip,deflate";

	$httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";

	$httpheader[] = "Connection: close";

	if($post){
		$httpheader[] = "Content-Type: application/x-www-form-urlencoded";
	}

	if($addheader){

		$httpheader = array_merge($httpheader, $addheader);

	}

	curl_setopt($ch, CURLOPT_TIMEOUT, 30);

	if($post){

		curl_setopt($ch, CURLOPT_POST, 1);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

	}

	curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);

	if($header){

		curl_setopt($ch, CURLOPT_HEADER, TRUE);

	}

	if($cookie){

		curl_setopt($ch, CURLOPT_COOKIE, $cookie);

	}

	if($referer){

		if($referer==1){

			curl_setopt($ch, CURLOPT_REFERER, 'http://m.qzone.com/infocenter?g_f=');

		}else{

			curl_setopt($ch, CURLOPT_REFERER, $referer);

		}

	}

	if($ua){

		curl_setopt($ch, CURLOPT_USERAGENT,$ua);

	}else{

		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');

	}

	if($nobaody){

		curl_setopt($ch, CURLOPT_NOBODY,1);

	}

	curl_setopt($ch, CURLOPT_ENCODING, "gzip");

	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

	$ret = curl_exec($ch);
	$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);



	curl_close($ch);

	return $ret;

}





function real_ip($type=0){

$ip = $_SERVER['REMOTE_ADDR'];

if($type<=0 && isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {

	foreach ($matches[0] AS $xip) {

		if (filter_var($xip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {

			$ip = $xip;

			break;

		}

	}

} elseif ($type<=0 && isset($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {

	$ip = $_SERVER['HTTP_CLIENT_IP'];

} elseif ($type<=1 && isset($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {

	$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];

} elseif ($type<=1 && isset($_SERVER['HTTP_X_REAL_IP']) && filter_var($_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {

	$ip = $_SERVER['HTTP_X_REAL_IP'];

}

return $ip;

}



function get_ip_city($ip)

{

    $url = 'http://whois.pconline.com.cn/ipJson.jsp?json=true&ip=';

    $city = curl_get($url . $ip);

	$city = mb_convert_encoding($city, "UTF-8", "GB2312");

    $city = json_decode($city, true);

    if ($city['city']) {

        $location = $city['pro'].$city['city'];

    } else {

        $location = $city['pro'];

    }

	if($location){

		return $location;

	}else{

		return false;

	}

}



function daddslashes($string) {

	if(is_array($string)) {

		foreach($string as $key => $val) {

			$string[$key] = daddslashes($val);

		}

	} else {

		$string = addslashes($string);

	}

	return $string;

}



function strexists($string, $find) {

	return !(strpos($string, $find) === FALSE);

}



function dstrpos($string, $arr) {

	if(empty($string)) return false;

	foreach((array)$arr as $v) {

		if(strpos($string, $v) !== false) {

			return true;

		}

	}

	return false;

}



function checkmobile() {

	$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);

	$ualist = array('android', 'midp', 'nokia', 'mobile', 'iphone', 'ipod', 'blackberry', 'windows phone');

	if((dstrpos($useragent, $ualist) || strexists($_SERVER['HTTP_ACCEPT'], "VND.WAP") || (isset($_SERVER['HTTP_VIA']) && strexists($_SERVER['HTTP_VIA'],'wap')))){

		return true;

	}else{

		return false;

	}

}



function checkEmail($value)

{

	if (preg_match("/^[\w\.\-]+@\w+([\.\-]\w+)*\.\w+$/", $value) && strlen($value) <= 60) {

		return true;

	} else {

		return false;

	}

}



function getSubstr($str, $leftStr, $rightStr)

{

	$left = strpos($str, $leftStr);

	$start = $left+strlen($leftStr);

	$right = strpos($str, $rightStr, $start);

	if($left < 0) return '';

	if($right>0){

		return substr($str, $start, $right-$start);

	}else{

		return substr($str, $start);

	}

}



function send_mail($to, $sub, $msg)

{

	global $conf;

	$senderName = q8_mail_sender_name();

	if($conf['mail_cloud']==1){

		$mail = new \lib\mail\Sendcloud($conf['mail_apiuser'], $conf['mail_apikey']);

		return $mail->send($to, $sub, $msg, $senderName, $conf['sitename']);

	}elseif($conf['mail_cloud']==2){

		$mail = new \lib\mail\Aliyun($conf['mail_apiuser'], $conf['mail_apikey']);

		return $mail->send($to, $sub, $msg, $senderName, $conf['sitename']);

	}else{

		if(!$conf['mail_name'] || !$conf['mail_port'] || !$conf['mail_smtp'] || !$conf['mail_pwd'])return false;

		$port = intval($conf['mail_port']);

		$mail = new \lib\mail\PHPMailer\PHPMailer(true);

		try{

			$mail->SMTPDebug = 0;

			$mail->CharSet = 'UTF-8';

			$mail->Timeout = 5;

			$mail->isSMTP();

			$mail->Host = $conf['mail_smtp'];

			$mail->SMTPAuth = true;

			$mail->Username = $conf['mail_name'];

			$mail->Password = $conf['mail_pwd'];

			if($port == 587) $mail->SMTPSecure = 'tls';

			else if($port >= 465) $mail->SMTPSecure = 'ssl';

			else $mail->SMTPAutoTLS = false;

			$mail->Port = intval($conf['mail_port']);

			$mail->setFrom($conf['mail_name'], $senderName);

			$mail->addAddress($to);

			$mail->addReplyTo($conf['mail_name'], $senderName);

			$mail->isHTML(true);

			$mail->Subject = $sub;

			$mail->Body = $msg;

			$mail->send();

			return true;

		} catch (Exception $e) {

			return $mail->ErrorInfo;

		}

	}

}

function q8_mail_sender_name()

{

	global $conf;

	$name = trim((string)$conf['mail_name2']);

	if ($name === '') {

		$name = trim((string)$conf['sitename']);

	}

	if ($name === '') {

		$name = trim((string)$conf['mail_name']);

	}

	return $name;

}

function q8_mail_center_valid_qq($value)

{

	$qq = trim((string)$value);

	if ($qq === '') {

		return '';

	}

	if (stripos($qq, '@qq.com') !== false) {

		$qq = substr($qq, 0, stripos($qq, '@qq.com'));

	}

	$qq = preg_replace('/\D+/', '', $qq);

	if (!preg_match('/^[1-9][0-9]{4,12}$/', $qq)) {

		return '';

	}

	return $qq;

}

function q8_mail_center_active_sites()

{

	global $DB;

	static $rows = null;

	if ($rows !== null) {

		return $rows;

	}

	$sql = "SELECT zid,user,qq,power,upzid,status FROM pre_site WHERE status=1 ORDER BY zid DESC";
	$rows = $DB->getAll($sql);
	if (!is_array($rows)) {
		$rows = array();
	}

	return $rows;

}

function q8_mail_center_scope_where($scope)

{

	$scope = intval($scope);

	switch ($scope) {

		case 1:
			return "status=1 AND power=0";

		case 2:
			return "status=1 AND power>0";

		case 3:
			return "status=1 AND power=1";

		case 4:
			return "status=1 AND power=2";

		case 5:
			return "status=1 AND power=0 AND upzid<=0";

		case 6:
			return "status=1 AND power=0 AND upzid>0";

		default:
			return "status=1";

	}

}

function q8_mail_center_scope_match($row, $scope)

{

	if (!is_array($row) || intval($row['status']) !== 1) {

		return false;

	}

	$scope = intval($scope);
	$power = intval($row['power']);
	$upzid = intval(isset($row['upzid']) ? $row['upzid'] : 0);

	switch ($scope) {

		case 1:
			return $power === 0;

		case 2:
			return $power > 0;

		case 3:
			return $power === 1;

		case 4:
			return $power === 2;

		case 5:
			return $power === 0 && $upzid <= 0;

		case 6:
			return $power === 0 && $upzid > 0;

		default:
			return true;

	}

}

function q8_mail_center_format_recipient($row)

{

	if (!is_array($row)) {

		return null;

	}

	$qq = q8_mail_center_valid_qq(isset($row['qq']) ? $row['qq'] : '');
	if ($qq === '') {
		return null;
	}

	$zid = intval(isset($row['zid']) ? $row['zid'] : 0);
	$username = trim((string)(isset($row['user']) ? $row['user'] : ''));
	$power = intval(isset($row['power']) ? $row['power'] : 0);

	return array(
		'zid' => $zid,
		'username' => $username,
		'qq' => $qq,
		'email' => $qq . '@qq.com',
		'power' => $power,
		'target_label' => 'UID ' . $zid . ' / ' . ($username === '' ? '--' : $username) . ' / ' . $qq . '@qq.com'
	);

}

function q8_mail_center_recipients_by_scope($scope)

{

	$list = array();
	foreach (q8_mail_center_active_sites() as $row) {
		if (!q8_mail_center_scope_match($row, $scope)) {
			continue;
		}
		$item = q8_mail_center_format_recipient($row);
		if (!$item) {
			continue;
		}
		$list[$item['zid']] = $item;
	}

	return array_values($list);

}

function q8_mail_center_scope_counts()

{

	$counts = array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0);
	foreach ($counts as $scope => $count) {
		$counts[$scope] = count(q8_mail_center_recipients_by_scope($scope));
	}

	return $counts;

}

function q8_mail_center_resolve_target($keyword)

{

	$keyword = trim((string)$keyword);
	if ($keyword === '') {
		return array('ok' => false, 'msg' => html_entity_decode('&#35831;&#36755;&#20837; UID / &#29992;&#25143;&#21517; / QQ', ENT_QUOTES, 'UTF-8'));
	}

	$rows = q8_mail_center_active_sites();
	$normalizedQq = q8_mail_center_valid_qq($keyword);
	$zid = ctype_digit($keyword) ? intval($keyword) : 0;

	if ($zid > 0) {
		foreach ($rows as $row) {
			if (intval($row['zid']) === $zid) {
				$item = q8_mail_center_format_recipient($row);
				if (!$item) {
					return array('ok' => false, 'msg' => html_entity_decode('&#35813; UID &#29992;&#25143;&#27809;&#26377;&#21487;&#29992;&#30340; QQ &#37038;&#31665;', ENT_QUOTES, 'UTF-8'));
				}
				return array('ok' => true, 'row' => $item, 'match' => 'zid');
			}
		}
	}

	$userMatches = array();
	foreach ($rows as $row) {
		if (trim((string)$row['user']) === $keyword) {
			$userMatches[] = $row;
		}
	}
	if (count($userMatches) > 1) {
		return array('ok' => false, 'msg' => html_entity_decode('&#29992;&#25143;&#21517;&#21629;&#20013;&#22810;&#26465;&#35760;&#24405;&#65292;&#35831;&#25913;&#29992; UID &#25110; QQ &#31934;&#30830;&#25351;&#23450;', ENT_QUOTES, 'UTF-8'));
	}
	if (count($userMatches) === 1) {
		$item = q8_mail_center_format_recipient($userMatches[0]);
		if (!$item) {
			return array('ok' => false, 'msg' => html_entity_decode('&#35813;&#29992;&#25143;&#27809;&#26377;&#21487;&#29992;&#30340; QQ &#37038;&#31665;', ENT_QUOTES, 'UTF-8'));
		}
		return array('ok' => true, 'row' => $item, 'match' => 'user');
	}

	if ($normalizedQq !== '') {
		$qqMatches = array();
		foreach ($rows as $row) {
			if (q8_mail_center_valid_qq($row['qq']) === $normalizedQq) {
				$qqMatches[] = $row;
			}
		}
		if (count($qqMatches) > 1) {
			return array('ok' => false, 'msg' => html_entity_decode('&#35813; QQ &#21629;&#20013;&#22810;&#26465;&#35760;&#24405;&#65292;&#35831;&#25913;&#29992; UID &#31934;&#30830;&#25351;&#23450;', ENT_QUOTES, 'UTF-8'));
		}
		if (count($qqMatches) === 1) {
			$item = q8_mail_center_format_recipient($qqMatches[0]);
			if (!$item) {
				return array('ok' => false, 'msg' => html_entity_decode('&#35813; QQ &#29992;&#25143;&#27809;&#26377;&#21487;&#29992;&#30340;&#37038;&#31665;', ENT_QUOTES, 'UTF-8'));
			}
			return array('ok' => true, 'row' => $item, 'match' => 'qq');
		}
	}

	return array('ok' => false, 'msg' => html_entity_decode('&#26410;&#25214;&#21040;&#21305;&#37197;&#30340;&#29992;&#25143;&#65292;&#35831;&#26816;&#26597; UID / &#29992;&#25143;&#21517; / QQ &#26159;&#21542;&#27491;&#30830;', ENT_QUOTES, 'UTF-8'));

}

function q8_mail_center_notice_type($scope)

{

	$scope = intval($scope);

	return in_array($scope, array(0, 1, 2, 3, 4, 5, 6), true) ? $scope : 0;

}

function q8_mail_center_wrap_html($subject, $content)

{

	$subjectText = htmlspecialchars((string)$subject, ENT_QUOTES, 'UTF-8');

	$body = trim((string)$content);

	if ($body !== '' && $body === strip_tags($body) && strpos($body, '<') === false) {

		$body = nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));

	}

	$sender = htmlspecialchars(q8_mail_sender_name(), ENT_QUOTES, 'UTF-8');

	$timeText = date('Y-m-d H:i:s');

	return '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>' . $subjectText . '</title></head><body style="margin:0;padding:24px;background:#f3f6fb;font-family:Arial,Microsoft YaHei,sans-serif;color:#1f2937;"><div style="max-width:720px;margin:0 auto;background:#ffffff;border-radius:18px;border:1px solid #dbe4f0;overflow:hidden;"><div style="padding:28px 32px;background:linear-gradient(135deg,#0f4c81,#16a1c5);color:#ffffff;"><div style="font-size:13px;letter-spacing:0.12em;opacity:0.82;">MESSAGE CENTER</div><h1 style="margin:12px 0 8px;font-size:28px;line-height:1.25;">' . $subjectText . '</h1><div style="font-size:13px;opacity:0.9;">' . htmlspecialchars($timeText, ENT_QUOTES, 'UTF-8') . ' - ' . $sender . '</div></div><div style="padding:32px;font-size:15px;line-height:1.8;">' . $body . '</div><div style="padding:0 32px 28px;font-size:12px;line-height:1.7;color:#6b7280;">' . $sender . ' message center. This email was sent automatically by the site notification system.</div></div></body></html>';

}

function q8_mail_center_ensure_tables()

{

	global $DB;

	static $ensured = false;

	if ($ensured) {

		return;

	}

	$taskTable = 'pre_mail_task';

	$itemTable = 'pre_mail_task_item';

	$DB->exec("CREATE TABLE IF NOT EXISTS `{$taskTable}` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`scope` tinyint(1) NOT NULL DEFAULT '0',
		`target_mode` tinyint(1) NOT NULL DEFAULT '0',
		`target_value` varchar(128) NOT NULL DEFAULT '',
		`target_label` varchar(255) NOT NULL DEFAULT '',
		`subject` varchar(255) NOT NULL DEFAULT '',
		`content` mediumtext,
		`sync_notice` tinyint(1) NOT NULL DEFAULT '0',
		`notice_id` int(11) unsigned NOT NULL DEFAULT '0',
		`total_count` int(11) unsigned NOT NULL DEFAULT '0',
		`success_count` int(11) unsigned NOT NULL DEFAULT '0',
		`fail_count` int(11) unsigned NOT NULL DEFAULT '0',
		`status` tinyint(1) NOT NULL DEFAULT '0',
		`last_error` varchar(255) NOT NULL DEFAULT '',
		`creator` varchar(64) NOT NULL DEFAULT '',
		`addtime` datetime DEFAULT NULL,
		`starttime` datetime DEFAULT NULL,
		`endtime` datetime DEFAULT NULL,
		PRIMARY KEY (`id`),
		KEY `status` (`status`),
		KEY `addtime` (`addtime`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

	$taskColumns = array(
		'target_mode' => "ALTER TABLE `{$taskTable}` ADD COLUMN `target_mode` tinyint(1) NOT NULL DEFAULT '0' AFTER `scope`",
		'target_value' => "ALTER TABLE `{$taskTable}` ADD COLUMN `target_value` varchar(128) NOT NULL DEFAULT '' AFTER `target_mode`",
		'target_label' => "ALTER TABLE `{$taskTable}` ADD COLUMN `target_label` varchar(255) NOT NULL DEFAULT '' AFTER `target_value`"
	);
	foreach ($taskColumns as $column => $sql) {
		$safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
		$exists = $DB->getColumn("SHOW COLUMNS FROM `{$taskTable}` LIKE '{$safeColumn}'");
		if (!$exists) {
			$DB->exec($sql);
		}
	}

	$DB->exec("CREATE TABLE IF NOT EXISTS `{$itemTable}` (
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`task_id` int(11) unsigned NOT NULL DEFAULT '0',
		`zid` int(11) unsigned NOT NULL DEFAULT '0',
		`username` varchar(64) NOT NULL DEFAULT '',
		`qq` varchar(32) NOT NULL DEFAULT '',
		`email` varchar(128) NOT NULL DEFAULT '',
		`status` tinyint(1) NOT NULL DEFAULT '0',
		`result` varchar(255) NOT NULL DEFAULT '',
		`sent_at` datetime DEFAULT NULL,
		`addtime` datetime DEFAULT NULL,
		PRIMARY KEY (`id`),
		KEY `task_status` (`task_id`,`status`),
		KEY `zid` (`zid`),
		KEY `email` (`email`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

	$ensured = true;

}

function q8_mail_center_sync_notice($title, $scope, $content)

{

	global $DB, $date;

	$title = trim((string)$title);

	$content = trim((string)$content);

	if ($title === '' || $content === '') {

		return 0;

	}

	$DB->exec("INSERT INTO pre_message (`type`,`title`,`content`,`addtime`,`active`) VALUES (:type,:title,:content,:addtime,1)", array(
		':type' => q8_mail_center_notice_type($scope),
		':title' => $title,
		':content' => $content,
		':addtime' => $date
	));

	return intval($DB->lastInsertId());

}

function q8_site_admin_notice_mode_labels()

{

	return array(
        0 => html_entity_decode('&#20165;&#26174;&#31034;&#20027;&#31449;&#20844;&#21578;', ENT_QUOTES, 'UTF-8'),
        1 => html_entity_decode('&#20027;&#31449;&#20844;&#21578; + &#20998;&#31449;&#33258;&#23450;&#20041;', ENT_QUOTES, 'UTF-8'),
        2 => html_entity_decode('&#20165;&#26174;&#31034;&#20998;&#31449;&#33258;&#23450;&#20041;', ENT_QUOTES, 'UTF-8')
	);

}

function q8_site_admin_notice_normalize_mode($mode)

{

	$mode = intval($mode);
	return array_key_exists($mode, q8_site_admin_notice_mode_labels()) ? $mode : 1;

}

function q8_site_table_columns($refresh = false)

{

	global $DB;

	static $columns = null;

	if ($refresh || !is_array($columns)) {
		$columns = array();
		$rows = $DB->getAll('SHOW COLUMNS FROM `pre_site`');
		if (is_array($rows)) {
			foreach ($rows as $row) {
				if (!empty($row['Field'])) {
					$columns[(string)$row['Field']] = $row;
				}
			}
		}
	}

	return $columns;

}

function q8_site_has_column($column)

{

	$column = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$column);
	if ($column === '') {
		return false;
	}

	$columns = q8_site_table_columns();
	return isset($columns[$column]);

}

function q8_site_markup_template_ensure_fields()

{

	global $DB;

	static $ensured = false;

	if ($ensured) {
		return;
	}

	$columns = array(
		'site_prid' => "ALTER TABLE `pre_site` ADD COLUMN `site_prid` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `template`"
	);

	foreach ($columns as $column => $sql) {
		$safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
		$exists = $DB->getColumn("SHOW COLUMNS FROM `pre_site` LIKE '{$safeColumn}'");
		if (!$exists) {
			$DB->exec($sql);
		}
	}

	q8_site_table_columns(true);

	$ensured = true;

}

function q8_tools_table_columns($refresh = false)

{

	global $DB;

	static $columns = null;

	if ($refresh || !is_array($columns)) {
		$columns = array();
		$rows = $DB->getAll('SHOW COLUMNS FROM `pre_tools`');
		if (is_array($rows)) {
			foreach ($rows as $row) {
				if (!empty($row['Field'])) {
					$columns[(string)$row['Field']] = $row;
				}
			}
		}
	}

	return $columns;

}

function q8_tools_has_column($column)

{

	$column = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$column);
	if ($column === '') {
		return false;
	}

	$columns = q8_tools_table_columns();
	return isset($columns[$column]);

}

function q8_tools_ensure_fields()

{

	global $DB;

	static $ensured = false;

	if ($ensured) {
		return;
	}

	$columns = array(
		'min_price' => "ALTER TABLE `pre_tools` ADD COLUMN `min_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `price`"
	);

	foreach ($columns as $column => $sql) {
		$safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
		$exists = $DB->getColumn("SHOW COLUMNS FROM `pre_tools` LIKE '{$safeColumn}'");
		if (!$exists) {
			$DB->exec($sql);
		}
	}

	q8_tools_table_columns(true);

	$ensured = true;

}

function q8_price_rule_table_columns($refresh = false)

{

	global $DB;

	static $columns = null;

	if ($refresh || !is_array($columns)) {
		$columns = array();
		$rows = $DB->getAll('SHOW COLUMNS FROM `pre_price`');
		if (is_array($rows)) {
			foreach ($rows as $row) {
				if (!empty($row['Field'])) {
					$columns[(string)$row['Field']] = $row;
				}
			}
		}
	}

	return $columns;

}

function q8_price_rule_has_column($column)

{

	$column = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$column);
	if ($column === '') {
		return false;
	}

	$columns = q8_price_rule_table_columns();
	return isset($columns[$column]);

}

function q8_price_rule_ensure_fields()

{

	global $DB;

	static $ensured = false;

	if ($ensured) {
		return;
	}

	$columns = array(
		'zid' => "ALTER TABLE `pre_price` ADD COLUMN `zid` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`"
	);

	foreach ($columns as $column => $sql) {
		$safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
		$exists = $DB->getColumn("SHOW COLUMNS FROM `pre_price` LIKE '{$safeColumn}'");
		if (!$exists) {
			$DB->exec($sql);
		}
	}

	$indexExists = $DB->getColumn("SHOW INDEX FROM `pre_price` WHERE Key_name='idx_zid'");
	if (!$indexExists) {
		$DB->exec("ALTER TABLE `pre_price` ADD INDEX `idx_zid` (`zid`)");
	}

	q8_price_rule_table_columns(true);

	$ensured = true;

}

function q8_price_rule_owner_id($zid)

{

	return max(0, intval($zid));

}

function q8_price_rule_fetch_rows($ownerZid = 0)

{

	global $DB;

	q8_price_rule_ensure_fields();

	return $DB->getAll(
		"SELECT id,zid,name,kind,p_0,p_1,p_2 FROM pre_price WHERE zid=:zid ORDER BY id DESC",
		array(':zid' => q8_price_rule_owner_id($ownerZid))
	);

}

function q8_price_rule_fetch_row($id, $ownerZid = null)

{

	global $DB;

	q8_price_rule_ensure_fields();

	$id = intval($id);
	if ($id <= 0) {
		return false;
	}

	if ($ownerZid === null) {
		return $DB->getRow(
			"SELECT id,zid,name,kind,p_0,p_1,p_2 FROM pre_price WHERE id=:id LIMIT 1",
			array(':id' => $id)
		);
	}

	return $DB->getRow(
		"SELECT id,zid,name,kind,p_0,p_1,p_2 FROM pre_price WHERE id=:id AND zid=:zid LIMIT 1",
		array(':id' => $id, ':zid' => q8_price_rule_owner_id($ownerZid))
	);

}

function q8_price_rule_exists_for_owner($id, $ownerZid = 0)

{

	global $DB;

	q8_price_rule_ensure_fields();

	return intval($DB->getColumn(
		"SELECT COUNT(*) FROM pre_price WHERE id=:id AND zid=:zid",
		array(':id' => intval($id), ':zid' => q8_price_rule_owner_id($ownerZid))
	)) > 0;

}

function q8_site_admin_notice_ensure_fields()

{

	global $DB;

	static $ensured = false;

	if ($ensured) {
		return;
	}

	$columns = array(
		'admin_notice_panel' => "ALTER TABLE `pre_site` ADD COLUMN `admin_notice_panel` TEXT NULL AFTER `alert`",
		'admin_notice_mode' => "ALTER TABLE `pre_site` ADD COLUMN `admin_notice_mode` TINYINT(1) NOT NULL DEFAULT '1' AFTER `admin_notice_panel`"
	);

	foreach ($columns as $column => $sql) {
		$safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
		$exists = $DB->getColumn("SHOW COLUMNS FROM `pre_site` LIKE '{$safeColumn}'");
		if (!$exists) {
			$DB->exec($sql);
		}
	}

	q8_site_table_columns(true);

	$ensured = true;

}

function q8_site_admin_notice_render_segment($title, $content)

{

	$title = trim((string)$title);
	$content = trim((string)$content);
	if ($content === '') {
		return '';
	}

	return '<section class="q8-site-notice-segment"><div class="q8-site-notice-segment__title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</div><div class="q8-site-notice-segment__body">' . $content . '</div></section>';

}

function q8_site_admin_notice_context($siteRow, $conf)

{

	q8_site_admin_notice_ensure_fields();

	$globalPanel = trim((string)(isset($conf['gg_panel']) ? $conf['gg_panel'] : ''));
	$customPanel = trim((string)(isset($siteRow['admin_notice_panel']) ? $siteRow['admin_notice_panel'] : ''));
	$mode = q8_site_admin_notice_normalize_mode(isset($siteRow['admin_notice_mode']) ? $siteRow['admin_notice_mode'] : 1);
	$html = '';
	$layered = false;

	if ($mode === 0) {
		$html = $globalPanel;
	} elseif ($mode === 2) {
		$html = $customPanel !== '' ? $customPanel : $globalPanel;
	} else {
		$segments = array();
		if ($globalPanel !== '') {
            $segments[] = q8_site_admin_notice_render_segment(html_entity_decode('&#20027;&#31449;&#20844;&#21578;', ENT_QUOTES, 'UTF-8'), $globalPanel);
		}
		if ($customPanel !== '') {
            $segments[] = q8_site_admin_notice_render_segment(html_entity_decode('&#20998;&#31449;&#20844;&#21578;', ENT_QUOTES, 'UTF-8'), $customPanel);
		}
		$layered = count($segments) > 1;
		$html = $layered ? '<div class="q8-site-notice-stack">' . implode('', $segments) . '</div>' : implode('', $segments);
	}

	return array(
		'mode' => $mode,
		'mode_label' => q8_site_admin_notice_mode_labels()[$mode],
		'global_html' => $globalPanel,
		'custom_html' => $customPanel,
		'html' => $html,
		'is_layered' => $layered
	);

}



function getSetting($k, $force = false)

{

	global $DB,$CACHE;

	if($force) return $DB->getColumn("SELECT v FROM pre_config WHERE k=:k LIMIT 1", [':k'=>$k]);

	$cache = $CACHE->get($k);

	return $cache[$k];

}



function saveSetting($k, $v)

{

	global $DB;

	return $DB->exec("REPLACE INTO pre_config SET v=:v,k=:k", [':v'=>$v, ':k'=>daddslashes($k)]);

}



function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

	$ckey_length = 4;

	$key = md5($key);

	$keya = md5(substr($key, 0, 16));

	$keyb = md5(substr($key, 16, 16));

	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);

	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;

	$string_length = strlen($string);

	$result = '';

	$box = range(0, 255);

	$rndkey = array();

	for($i = 0; $i <= 255; $i++) {

		$rndkey[$i] = ord($cryptkey[$i % $key_length]);

	}

	for($j = $i = 0; $i < 256; $i++) {

		$j = ($j + $box[$i] + $rndkey[$i]) % 256;

		$tmp = $box[$i];

		$box[$i] = $box[$j];

		$box[$j] = $tmp;

	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {

		$a = ($a + 1) % 256;

		$j = ($j + $box[$a]) % 256;

		$tmp = $box[$a];

		$box[$a] = $box[$j];

		$box[$j] = $tmp;

		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));

	}

	if($operation == 'DECODE') {

		if(((int)substr($result, 0, 10) == 0 || (int)substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {

			return substr($result, 26);

		} else {

			return '';

		}

	} else {

		return $keyc.str_replace('=', '', base64_encode($result));

	}

}



function random($length, $numeric = 0) {

	$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);

	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));

	$hash = '';

	$max = strlen($seed) - 1;

	for($i = 0; $i < $length; $i++) {

		$hash .= $seed[mt_rand(0, $max)];

	}

	return $hash;

}



function get_rand($proArr)

{

	$result = "";

	$proSum = array_sum($proArr);

	foreach ($proArr as $key => $proCur) {

		$randNum = mt_rand(1, $proSum);

		if ($randNum <= $proCur) {

			$result = $key;

			break;

		}

		$proSum -= $proCur;

	}

	unset($proArr);

	return $result;

}



function showmsg($content = 'Operation completed', $type = 4, $back = false)
{
	switch ((int)$type) {
		case 1:
			$panel = 'success';
		break;
		case 2:
			$panel = 'info';
		break;
		case 3:
			$panel = 'warning';
		break;
		case 4:
		default:
			$panel = 'danger';
		break;
	}
	echo '<div class="panel panel-'.$panel.'">';
	echo '<div class="panel-heading"><h3 class="panel-title">System Notice</h3></div>';
	echo '<div class="panel-body">';
	echo $content;
	if ($back) {
		echo '<hr/><a href="'.htmlspecialchars($back, ENT_QUOTES).'">&lt;&lt; Back</a>';
	} else {
		echo '<hr/><a href="javascript:history.back(-1)">&lt;&lt; Back</a>';
	}
echo '</div></div>';
exit;
}

if (!function_exists('is_https')) {
	function is_https()
	{
		if (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) {
			return true;
		}
		if (isset($_SERVER['HTTPS'])) {
			$https = strtolower((string)$_SERVER['HTTPS']);
			if ($https === 'on' || $https === '1') {
				return true;
			}
		}
		if (isset($_SERVER['HTTP_X_CLIENT_SCHEME']) && strtolower((string)$_SERVER['HTTP_X_CLIENT_SCHEME']) === 'https') {
			return true;
		}
		if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
			return true;
		}
		if (isset($_SERVER['REQUEST_SCHEME']) && strtolower((string)$_SERVER['REQUEST_SCHEME']) === 'https') {
			return true;
		}
		if (isset($_SERVER['HTTP_EWS_CUSTOME_SCHEME']) && strtolower((string)$_SERVER['HTTP_EWS_CUSTOME_SCHEME']) === 'https') {
			return true;
		}
		return false;
	}
}

function sysmsg($content = 'Operation completed', $is_exit = true)
{
	echo '<!DOCTYPE html>';
	echo '<html lang="zh-CN"><head><meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" /><title>System Notice</title>';
	echo '<style>html{background:#eee}body{background:#fff;color:#333;font-family:"Microsoft YaHei",sans-serif;margin:2em auto;padding:1em 2em;max-width:700px;box-shadow:10px 10px 10px rgba(0,0,0,.13);opacity:.95}h3{text-align:center;margin:0 0 20px}a{color:#21759B;text-decoration:none}a:hover{color:#D54E21}</style>';
	echo '</head><body id="error-page">';
	echo '<h3>System Notice</h3>';
	echo $content;
	echo '</body></html>';
	if($is_exit) exit();
	return 0;
}


function changeUserMoney($zid, $money, $add=true, $action=null, $bz=null, $orderid=null)

{

	global $DB;

	if($money<=0)return;

	$oldmoney = $DB->getColumn("SELECT rmb FROM pre_site WHERE zid=:zid LIMIT 1", [':zid' => $zid]);

	if($add == true){



		$newmoney = round($oldmoney+$money, 2);

	}else{



		$newmoney = round($oldmoney-$money, 2);

	}

	$action = addslashes($action);

	$bz = addslashes($bz);

	$res = $DB->exec("UPDATE pre_site SET rmb=:newmoney WHERE zid=:zid", [':newmoney' => $newmoney, ':zid' => $zid]);

	if ($orderid) {
		$DB->exec("INSERT INTO `pre_points` (`zid`, `action`, `point`, `bz`, `addtime`, `orderid`) VALUES (:zid, :action, :point, :bz, NOW(), :orderid)", [':zid'=>$zid, ':action'=>$action, ':point'=>$money, ':bz'=>$bz, ':orderid'=>$orderid]);
	} else {
		$DB->exec("INSERT INTO `pre_points` (`zid`, `action`, `point`, `bz`, `addtime`) VALUES (:zid, :action, :point, :bz, NOW())", [':zid'=>$zid, ':action'=>$action, ':point'=>$money, ':bz'=>$bz]);
	}

	return $res;

}

function q8_resolve_point_record_orderid($row)
{
	$orderid = isset($row['orderid']) ? trim((string)$row['orderid']) : '';
	if ($orderid !== '' && $orderid !== '0') {
		return $orderid;
	}

	$bz = isset($row['bz']) ? trim((string)$row['bz']) : '';
	if ($bz === '') {
		return '';
	}

	$patterns = array(
		'/\((\d{6,})\)\s*$/',
		'/(?:订单(?:号|ID)|ID)[:：#]?\s*(\d{6,})/u',
	);

	foreach ($patterns as $pattern) {
		if (preg_match($pattern, $bz, $matches)) {
			return $matches[1];
		}
	}

	return '';
}



function changeSupMoney($sid, $money, $add=true, $action=null, $bz=null)

{

    global $DB;

    if($money<=0)return;

    $oldmoney = $DB->getColumn("SELECT rmb FROM pre_supplier WHERE sid=:sid LIMIT 1", [':sid' => $sid]);

    if($add == true){



        $newmoney = round($oldmoney+$money, 2);

    }else{



        $newmoney = round($oldmoney-$money, 2);

    }

    $action = addslashes($action);

    $bz = addslashes($bz);

    $res = $DB->exec("UPDATE pre_supplier SET rmb=:newmoney WHERE sid=:sid", [':newmoney' => $newmoney, ':sid' => $sid]);

    $DB->exec("INSERT INTO `pre_suppoints` (`sid`, `action`, `point`, `bz`, `addtime`) VALUES (:sid, :action, :point, :bz, NOW())", [':sid'=>$sid, ':action'=>$action, ':point'=>$money, ':bz'=>$bz]);

    return $res;

}



function check_china(){

	$ip = gethostbyname('check.cccyun.cc');

	if($ip == '192.168.0.1'){

		return true;

	}else{

		return false;

	}

}



function yile_getSign($param, $key)

{

    $signPars = "";

    ksort($param);

    foreach ($param as $k => $v) {

        if ("sign" != $k && "" != $v) {

            $signPars .= $k . "=" . $v . "&";

        }

    }

    $signPars = trim($signPars, '&');

    $signPars .= $key;

    $sign = md5($signPars);

    return $sign;

}



function getServerIp(){

	$url = 'http://members.3322.org/dyndns/getip';

	$url2 = 'https://www.bt.cn/Api/getIpAddress';

	if($data = get_curl($url2)){

		return $data;

	}else{

		$data = get_curl($url);

		return $data;

	}

}



function getClassOptionList($selected = 0) {
    global $DB;
    $rs = $DB->query("SELECT * FROM pre_class WHERE active=1 ORDER BY sort ASC");
    $select = '';
    while($res = $rs->fetch()) {
        $name = htmlspecialchars($res['name'], ENT_QUOTES, 'UTF-8');
        if($res['is_disabled'] == 1) {
            $select .= '<option disabled style="color: blue;">-' . $name . '-</option>';
        } else {
            $select .= '<option value="' . intval($res['cid']) . '"' . ($selected == $res['cid'] ? ' selected' : '') . '>' . $name . '</option>';
        }
    }
    return $select;
}
