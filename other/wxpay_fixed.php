<?php
// 修复版微信支付页面 - 不使用autoloader，直接包含所有必要文件

// 开启所有错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('html_errors', 1);
ini_set('log_errors', 1);

// 显示基本信息
header('Content-Type: text/html; charset=UTF-8');
echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<title>微信支付</title>\n";
echo "<link href='assets/css/wechat_pay.css' rel='stylesheet' media='screen'>\n";
echo "</head>\n";
echo "<body>\n";
echo "<div class='body'>\n";
echo "<h1 class='mod-title'><span class='ico-wechat'></span><span class='text'>微信支付</span></h1>\n";

// 检查必要参数
if (empty($_GET['trade_no'])) {
    echo "<p>缺少必要参数: trade_no</p>\n";
    echo "</div></body></html>\n";
    exit;
}

$trade_no = $_GET['trade_no'];
echo "<p>订单号: $trade_no</p>\n";

// 1. 定义必要常量
echo "<h2>1. 定义常量</h2>\n";
define('SYSTEM_ROOT', dirname(__FILE__).'/');
define('ROOT', dirname(SYSTEM_ROOT).'/');
echo "<p>SYSTEM_ROOT: " . SYSTEM_ROOT . "</p>\n";
echo "<p>ROOT: " . ROOT . "</p>\n";

// 2. 直接包含必要文件
echo "<h2>2. 加载必要文件</h2>\n";

// 2.1 加载config.php
$config_path = ROOT . 'config.php';
echo "<p>config.php路径: $config_path</p>\n";
if (file_exists($config_path)) {
    require $config_path;
    echo "<p>✓ config.php加载成功</p>\n";
} else {
    echo "<p>✗ config.php不存在</p>\n";
    echo "</div></body></html>\n";
    exit;
}

// 2.2 加载数据库连接
$db_helper_path = ROOT . 'lib/PdoHelper.php';
echo "<p>PdoHelper.php路径: $db_helper_path</p>\n";
if (file_exists($db_helper_path)) {
    require $db_helper_path;
    $DB = new \lib\PdoHelper($dbconfig);
    echo "<p>✓ 数据库连接成功</p>\n";
} else {
    echo "<p>✗ PdoHelper.php不存在</p>\n";
    echo "</div></body></html>\n";
    exit;
}

// 2.3 加载微信支付SDK
echo "<h3>2.3 加载微信支付SDK</h3>\n";
$sdk_files = array(
    'WxPay.Exception.php',
    'WxPay.Config.php',
    'WxPay.Data.php',
    'WxPay.Api.php'
);

$sdk_loaded = true;
foreach ($sdk_files as $file) {
    $file_path = SYSTEM_ROOT . 'wxpay/' . $file;
    echo "<p>$file路径: $file_path</p>\n";
    if (file_exists($file_path)) {
        require_once $file_path;
        echo "<p>✓ $file加载成功</p>\n";
    } else {
        echo "<p>✗ $file不存在</p>\n";
        $sdk_loaded = false;
    }
}

if (!$sdk_loaded) {
    echo "<p>✗ SDK加载失败，无法继续</p>\n";
    echo "</div></body></html>\n";
    exit;
}

// 3. 查询订单
echo "<h2>3. 查询订单</h2>\n";
$row = $DB->getRow("SELECT * FROM pre_pay WHERE trade_no='{$trade_no}' LIMIT 1");
if (!$row) {
    echo "<p>✗ 订单不存在</p>\n";
    echo "</div></body></html>\n";
    exit;
}

// 4. 显示订单信息
echo "<h2>4. 订单信息</h2>\n";
echo "<div class='amount'>￥{$row['money']}</div>\n";

// 5. 创建支付订单
echo "<h2>5. 创建支付订单</h2>\n";

// 5.1 设置订单参数
$ordername = !empty($conf['ordername']) ? ordername_replace($conf['ordername'], $row['name'], $trade_no) : $row['name'];

// 5.2 创建统一订单对象
$input = new WxPayUnifiedOrder();
$input->SetBody($ordername);
$input->SetOut_trade_no($trade_no);
$input->SetTotal_fee($row['money']*100);
$input->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetNotify_url($siteurl.'other/wxpay_notify.php');
$input->SetTrade_type("NATIVE");
$input->SetProduct_id("01001");

// 5.3 调用统一下单API
echo "<p>调用统一下单API...</p>\n";
$result = WxPayApi::unifiedOrder($input);
echo "<p>API返回: " . json_encode($result) . "</p>\n";

// 5.4 处理返回结果
$code_url = '';
if ($result["return_code"] == 'SUCCESS') {
    if ($result["result_code"] == 'SUCCESS') {
        $code_url = $result['code_url'];
        echo "<p>✓ 下单成功，code_url: " . $code_url . "</p>\n";

        // 显示二维码
        echo "<div class='qr-image' id='qrcode'></div>\n";

        // 6. 显示订单详情
        echo "<div class='detail' id='orderDetail'>\n";
        echo "<dl class='detail-ct' style='display: none;'>\n";
        echo "<dt>商家</dt>\n";
        echo "<dd id='storeName'>{$conf['sitename']}</dd>\n";
        echo "<dt>购买物品</dt>\n";
        echo "<dd id='productName'>{$row['name']}</dd>\n";
        echo "<dt>商户订单号</dt>\n";
        echo "<dd id='billId'>{$row['trade_no']}</dd>\n";
        echo "<dt>创建时间</dt>\n";
        echo "<dd id='createTime'>{$row['addtime']}</dd>\n";
        echo "</dl>\n";
        echo "<a href='javascript:void(0)' class='arrow'><i class='ico-arrow'></i></a>\n";
        echo "</div>\n";

        // 7. 显示提示信息
        echo "<div class='tip'>\n";
        echo "<span class='dec dec-left'></span>\n";
        echo "<span class='dec dec-right'></span>\n";
        echo "<div class='ico-scan'></div>\n";
        echo "<div class='tip-text'>\n";
        echo "<p>请使用微信扫一扫</p>\n";
        echo "<p>扫描二维码完成支付</p>\n";
        echo "</div>\n";
        echo "</div>\n";

        // 8. 加载二维码生成脚本
        echo "<script src='//lib.baomitu.com/jquery/1.12.4/jquery.min.js'></script>\n";
        echo "<script src='//lib.baomitu.com/jquery.qrcode/1.0/jquery.qrcode.min.js'></script>\n";
        echo "<script>\n";
        echo "    $(document).ready(function() {\n";
        echo "        $('#qrcode').qrcode({\n";
        echo "            text: '{$code_url}',\n";
        echo "            width: 230,\n";
        echo "            height: 230,\n";
        echo "            foreground: '#000000',\n";
        echo "            background: '#ffffff',\n";
        echo "            typeNumber: -1\n";
        echo "        });\n";
        echo "    });\n";
        echo "</script>\n";

    } else {
        $error_msg = isset($result["err_code_des"]) ? $result["err_code_des"] : '未知错误';
        echo "<p>✗ 下单失败: $error_msg</p>\n";
    }
} else {
    $error_msg = isset($result["return_msg"]) ? $result["return_msg"] : '未知错误';
    echo "<p>✗ 下单失败: $error_msg</p>\n";
}

// 9. 显示底部信息
echo "<div class='foot'>\n";
echo "<div class='inner'>\n";
echo "<p>如有疑问，请联系客服</p>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "</body></html>\n";

// 简单的订单名称替换函数
function ordername_replace($template, $name, $trade_no) {
    $replacements = array(
        '{name}' => $name,
        '{trade_no}' => $trade_no,
        '{ordername}' => $name,
        '{orderid}' => $trade_no
    );
    return strtr($template, $replacements);
}
