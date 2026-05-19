<?php
include("../includes/common.php");
header('Content-Type: application/json; charset=utf-8');

$act = isset($_GET['act']) ? $_GET['act'] : null;
if ($islogin == 1) {
} else {
    exit(json_encode(array('code' => -1, 'msg' => '未登录')));
}
adminpermission('set', 2);

function cf_block_json($code, $msg, $extra = array())
{
    exit(json_encode(array_merge(array('code' => $code, 'msg' => $msg), $extra), JSON_UNESCAPED_UNICODE));
}

function cf_block_save_config($key, $value)
{
    global $DB;
    $DB->exec("INSERT INTO pre_config SET `k`=:k,`v`=:v ON DUPLICATE KEY UPDATE `v`=:v2", array(
        ':k' => $key,
        ':v' => $value,
        ':v2' => $value
    ));
}

function cf_block_get_config($key)
{
    global $conf;
    return isset($conf[$key]) ? trim($conf[$key]) : '';
}

function cf_block_headers()
{
    $email = cf_block_get_config('cf_account_email');
    $key = cf_block_get_config('cf_global_api_key');
    $token = cf_block_get_config('cf_api_token');

    if ($email !== '' && $key !== '') {
        return array(
            'X-Auth-Email: ' . $email,
            'X-Auth-Key: ' . $key,
            'Content-Type: application/json'
        );
    }
    if ($token !== '') {
        return array(
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        );
    }
    cf_block_json(-1, '请先保存 Cloudflare API 配置');
}

function cf_block_call($method, $path, $body = null)
{
    $ch = curl_init('https://api.cloudflare.com/client/v4' . $path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, cf_block_headers());
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $response = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno) cf_block_json(-1, 'Cloudflare 请求失败：' . $error);
    $json = json_decode($response, true);
    if (!is_array($json)) cf_block_json(-1, 'Cloudflare 返回异常：HTTP ' . $httpCode);
    if (empty($json['success'])) {
        $message = 'Cloudflare 操作失败';
        if (!empty($json['errors'][0]['message'])) {
            $message .= '：' . $json['errors'][0]['message'];
        }
        cf_block_json(-1, $message, array('raw' => $json));
    }
    return isset($json['result']) ? $json['result'] : array();
}

function cf_block_account_id()
{
    $accountId = cf_block_get_config('cf_account_id');
    if ($accountId !== '') return $accountId;

    $result = cf_block_call('GET', '/zones?per_page=1');
    if (empty($result[0]['account']['id'])) cf_block_json(-1, '未找到 Cloudflare Account ID');
    cf_block_save_config('cf_account_id', $result[0]['account']['id']);
    return $result[0]['account']['id'];
}

function cf_block_ip_target($ip)
{
    if (!filter_var($ip, FILTER_VALIDATE_IP)) cf_block_json(-1, 'IP 格式不正确');
    return strpos($ip, ':') === false ? 'ip' : 'ip6';
}

switch ($act) {
    case 'save_config':
        $email = isset($_POST['cf_account_email']) ? trim($_POST['cf_account_email']) : '';
        $key = isset($_POST['cf_global_api_key']) ? trim($_POST['cf_global_api_key']) : '';
        $accountId = isset($_POST['cf_account_id']) ? trim($_POST['cf_account_id']) : '';
        $token = isset($_POST['cf_api_token']) ? trim($_POST['cf_api_token']) : cf_block_get_config('cf_api_token');

        if (($email === '' || $key === '') && $token === '') {
            cf_block_json(-1, '请填写 Global API Key 与邮箱，或填写 API Token');
        }
        cf_block_save_config('cf_account_email', $email);
        cf_block_save_config('cf_global_api_key', $key);
        cf_block_save_config('cf_account_id', $accountId);
        cf_block_save_config('cf_api_token', $token);
        $CACHE->clear();
        cf_block_json(0, '保存成功');
        break;

    case 'add':
        $ip = isset($_POST['ip']) ? trim($_POST['ip']) : '';
        $note = isset($_POST['note']) ? trim($_POST['note']) : '';
        $target = cf_block_ip_target($ip);
        if ($note === '') $note = '岁岁云商城后台拉黑';

        $accountId = cf_block_account_id();
        $query = '/accounts/' . rawurlencode($accountId) . '/firewall/access_rules/rules?per_page=100&mode=block&configuration.target=' . rawurlencode($target) . '&configuration.value=' . rawurlencode($ip);
        $exists = cf_block_call('GET', $query);
        if (!empty($exists[0]['id'])) {
            cf_block_json(0, '该 IP 已在黑名单', array('rule' => $exists[0]));
        }
        $rule = cf_block_call('POST', '/accounts/' . rawurlencode($accountId) . '/firewall/access_rules/rules', array(
            'mode' => 'block',
            'configuration' => array('target' => $target, 'value' => $ip),
            'notes' => $note
        ));
        cf_block_json(0, '添加成功', array('rule' => $rule));
        break;

    case 'del':
        $id = isset($_POST['id']) ? trim($_POST['id']) : '';
        if ($id === '') cf_block_json(-1, '规则ID不能为空');
        $accountId = cf_block_account_id();
        cf_block_call('DELETE', '/accounts/' . rawurlencode($accountId) . '/firewall/access_rules/rules/' . rawurlencode($id));
        cf_block_json(0, '删除成功');
        break;

    case 'list':
        $accountId = cf_block_account_id();
        $rules = cf_block_call('GET', '/accounts/' . rawurlencode($accountId) . '/firewall/access_rules/rules?per_page=100&mode=block');
        $items = array();
        foreach ($rules as $row) {
            $target = isset($row['configuration']['target']) ? $row['configuration']['target'] : '';
            if ($row['mode'] === 'block' && ($target === 'ip' || $target === 'ip6')) {
                $items[] = $row;
            }
        }
        cf_block_json(0, 'succ', array('data' => $items));
        break;

    default:
        cf_block_json(-4, 'No Act');
        break;
}
