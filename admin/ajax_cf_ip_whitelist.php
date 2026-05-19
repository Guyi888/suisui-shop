<?php
include("../includes/common.php");
header('Content-Type: application/json; charset=utf-8');

$act = isset($_GET['act']) ? $_GET['act'] : null;
if ($islogin == 1) {
} else {
    exit(json_encode(array('code' => -1, 'msg' => '未登录')));
}
adminpermission('set', 2);

function cf_ip_json($code, $msg, $extra = array())
{
    exit(json_encode(array_merge(array('code' => $code, 'msg' => $msg), $extra), JSON_UNESCAPED_UNICODE));
}

function cf_ip_save_config($key, $value)
{
    global $DB;
    $DB->exec("INSERT INTO pre_config SET `k`=:k,`v`=:v ON DUPLICATE KEY UPDATE `v`=:v2", array(
        ':k' => $key,
        ':v' => $value,
        ':v2' => $value
    ));
}

function cf_ip_get_config($key)
{
    global $conf;
    return isset($conf[$key]) ? trim($conf[$key]) : '';
}

function cf_ip_call($method, $path, $body = null)
{
    $token = cf_ip_get_config('cf_api_token');
    if ($token === '') cf_ip_json(-1, '请先保存 Cloudflare API Token');

    $ch = curl_init('https://api.cloudflare.com/client/v4' . $path);
    $headers = array(
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $response = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno) cf_ip_json(-1, 'Cloudflare 请求失败：' . $error);
    $json = json_decode($response, true);
    if (!is_array($json)) cf_ip_json(-1, 'Cloudflare 返回异常：HTTP ' . $httpCode);
    if (empty($json['success'])) {
        $message = 'Cloudflare 操作失败';
        if (!empty($json['errors'][0]['message'])) {
            $message .= '：' . $json['errors'][0]['message'];
        }
        cf_ip_json(-1, $message, array('raw' => $json));
    }
    return $json['result'];
}

function cf_ip_zone_id()
{
    $zoneId = cf_ip_get_config('cf_zone_id');
    if ($zoneId !== '') return $zoneId;

    $zoneName = cf_ip_get_config('cf_zone_name');
    if ($zoneName === '') $zoneName = !empty($_SERVER['HTTP_HOST']) ? preg_replace('/^www\./', '', preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'])) : '';
    if ($zoneName === '') cf_ip_json(-1, '请先保存 Cloudflare Zone 域名');
    $result = cf_ip_call('GET', '/zones?name=' . urlencode($zoneName));
    if (empty($result[0]['id'])) cf_ip_json(-1, '未找到 Cloudflare Zone：' . $zoneName);
    cf_ip_save_config('cf_zone_id', $result[0]['id']);
    return $result[0]['id'];
}

switch ($act) {
    case 'save_config':
        $token = isset($_POST['cf_api_token']) ? trim($_POST['cf_api_token']) : '';
        $zoneId = isset($_POST['cf_zone_id']) ? trim($_POST['cf_zone_id']) : '';
        $zoneName = isset($_POST['cf_zone_name']) ? trim($_POST['cf_zone_name']) : '';
        if ($token === '') cf_ip_json(-1, 'Token 不能为空');
        if ($zoneName === '') $zoneName = !empty($_SERVER['HTTP_HOST']) ? preg_replace('/^www\./', '', preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'])) : '';
        if ($zoneName === '') cf_ip_json(-1, 'Zone 域名不能为空');
        cf_ip_save_config('cf_api_token', $token);
        cf_ip_save_config('cf_zone_id', $zoneId);
        cf_ip_save_config('cf_zone_name', $zoneName);
        $CACHE->clear();
        cf_ip_json(0, '保存成功');
        break;

    case 'add':
        $ip = isset($_POST['ip']) ? trim($_POST['ip']) : '';
        $note = isset($_POST['note']) ? trim($_POST['note']) : '';
        if (!filter_var($ip, FILTER_VALIDATE_IP)) cf_ip_json(-1, 'IP 格式不正确');
        if ($note === '') $note = '岁岁云商城后台添加';

        $zoneId = cf_ip_zone_id();
        $exists = cf_ip_call('GET', '/zones/' . $zoneId . '/firewall/access_rules/rules?per_page=100&configuration.target=ip&configuration.value=' . urlencode($ip));
        if (!empty($exists[0]['id'])) {
            cf_ip_json(0, '该 IP 已在白名单', array('rule' => $exists[0]));
        }
        $rule = cf_ip_call('POST', '/zones/' . $zoneId . '/firewall/access_rules/rules', array(
            'mode' => 'whitelist',
            'configuration' => array('target' => 'ip', 'value' => $ip),
            'notes' => $note
        ));
        cf_ip_json(0, '添加成功', array('rule' => $rule));
        break;

    case 'del':
        $id = isset($_POST['id']) ? trim($_POST['id']) : '';
        if ($id === '') cf_ip_json(-1, '规则ID不能为空');
        $zoneId = cf_ip_zone_id();
        cf_ip_call('DELETE', '/zones/' . $zoneId . '/firewall/access_rules/rules/' . rawurlencode($id));
        cf_ip_json(0, '删除成功');
        break;

    case 'list':
        $zoneId = cf_ip_zone_id();
        $rules = cf_ip_call('GET', '/zones/' . $zoneId . '/firewall/access_rules/rules?per_page=100');
        $items = array();
        foreach ($rules as $row) {
            if (isset($row['configuration']['target']) && $row['configuration']['target'] === 'ip' && $row['mode'] === 'whitelist') {
                $items[] = $row;
            }
        }
        cf_ip_json(0, 'succ', array('data' => $items));
        break;

    default:
        cf_ip_json(-4, 'No Act');
        break;
}
