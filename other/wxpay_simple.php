<?php
// 极简微信支付页面 - 用于诊断问题

// 开启所有错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('html_errors', 1);

// 基本HTML结构
echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<title>微信支付测试</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 20px; }\n";
echo ".success { color: green; }\n";
echo ".error { color: red; }\n";
echo ".debug { background: #f0f0f0; padding: 10px; margin: 10px 0; }\n";
echo ".info { background: #e0f0ff; padding: 10px; margin: 10px 0; }\n";
echo "</style>\n";
echo "</head>\n";
echo "<body>\n";
echo "<h1>微信支付测试</h1>\n";

// 检查必要参数
if (empty($_GET['trade_no'])) {
    echo "<p class='error'>缺少必要参数: trade_no</p>\n";
    echo "</body></html>\n";
    exit;
}

$trade_no = $_GET['trade_no'];
echo "<p>订单号: $trade_no</p>\n";

// 尝试加载配置
echo "<h2>1. 加载配置</h2>\n";
$inc_path = __DIR__ . '/inc.php';
echo "<p>配置文件路径: $inc_path</p>\n";

if (file_exists($inc_path)) {
    echo "<p class='success'>✓ 配置文件存在</p>\n";

    // 使用简单的包含方式
    $config_loaded = false;
    try {
        require $inc_path;
        $config_loaded = true;
        echo "<p class='success'>✓ 配置文件加载成功</p>\n";

        // 显示基本配置
        echo "<div class='info'>\n";
        echo "<p>wxpay_api: " . (isset($conf['wxpay_api']) ? $conf['wxpay_api'] : '未设置') . "</p>\n";
        echo "<p>wxpay_appid: " . (isset($conf['wxpay_appid']) ? $conf['wxpay_appid'] : '未设置') . "</p>\n";
        echo "<p>wxpay_mchid: " . (isset($conf['wxpay_mchid']) ? $conf['wxpay_mchid'] : '未设置') . "</p>\n";
        echo "</div>\n";

    } catch (Exception $e) {
        echo "<p class='error'>✗ 配置文件加载异常: " . $e->getMessage() . "</p>\n";
    }
} else {
    echo "<p class='error'>✗ 配置文件不存在</p>\n";
    echo "</body></html>\n";
    exit;
}

// 查询订单
echo "<h2>2. 查询订单</h2>\n";
echo "<p>尝试查询订单信息...</p>\n";

if (isset($DB)) {
    try {
        $row = $DB->getRow("SELECT * FROM pre_pay WHERE trade_no='{$trade_no}' LIMIT 1");
        if ($row) {
            echo "<p class='success'>✓ 找到订单</p>\n";
            echo "<div class='info'>\n";
            echo "<p>商品名称: " . $row['name'] . "</p>\n";
            echo "<p>金额: " . $row['money'] . " 元</p>\n";
            echo "<p>状态: " . $row['status'] . "</p>\n";
            echo "<p>添加时间: " . $row['addtime'] . "</p>\n";
            echo "</div>\n";

            // 检查支付接口是否开启
            if ($conf['wxpay_api'] != 1 && $conf['wxpay_api'] != 3) {
                echo "<p class='error'>✗ 当前支付接口未开启 (wxpay_api: {$conf['wxpay_api']})</p>\n";
            } else {
                echo "<p class='success'>✓ 支付接口已开启</p>\n";

                // 检查SDK文件
                echo "<h2>3. 检查微信支付SDK</h2>\n";
                $sdk_files = array(
                    'WxPay.Api.php',
                    'WxPay.Exception.php',
                    'WxPay.Data.php',
                    'WxPay.Config.php'
                );

                $all_files_exist = true;
                foreach ($sdk_files as $file) {
                    $file_path = __DIR__ . '/wxpay/' . $file;
                    if (file_exists($file_path)) {
                        echo "<p class='success'>✓ $file 存在</p>\n";
                    } else {
                        echo "<p class='error'>✗ $file 不存在</p>\n";
                        $all_files_exist = false;
                    }
                }

                if ($all_files_exist) {
                    echo "<p class='success'>✓ 所有SDK文件都存在</p>\n";

                    // 尝试简单加载SDK
                    echo "<h2>4. 尝试加载SDK</h2>\n";

                    // 只加载必要的文件，避免复杂依赖
                    try {
                        require_once __DIR__ . '/wxpay/WxPay.Exception.php';
                        echo "<p class='success'>✓ WxPay.Exception.php 加载成功</p>\n";

                        require_once __DIR__ . '/wxpay/WxPay.Data.php';
                        echo "<p class='success'>✓ WxPay.Data.php 加载成功</p>\n";

                        require_once __DIR__ . '/wxpay/WxPay.Config.php';
                        echo "<p class='success'>✓ WxPay.Config.php 加载成功</p>\n";

                        require_once __DIR__ . '/wxpay/WxPay.Api.php';
                        echo "<p class='success'>✓ WxPay.Api.php 加载成功</p>\n";

                        // 检查关键类是否存在
                        if (class_exists('WxPayUnifiedOrder') && class_exists('WxPayApi')) {
                            echo "<p class='success'>✓ 关键类存在</p>\n";
                            echo "<p class='info'>SDK加载成功，准备创建支付订单...</p>\n";

                            // 这里可以添加创建订单的逻辑，如果需要的话
                            echo "<p class='success'>测试完成！所有组件都正常工作。</p>\n";
                        } else {
                            echo "<p class='error'>✗ 关键类不存在</p>\n";
                            echo "<p>WxPayUnifiedOrder: " . (class_exists('WxPayUnifiedOrder') ? '存在' : '不存在') . "</p>\n";
                            echo "<p>WxPayApi: " . (class_exists('WxPayApi') ? '存在' : '不存在') . "</p>\n";
                        }

                    } catch (Exception $e) {
                        echo "<p class='error'>✗ SDK加载异常: " . $e->getMessage() . "</p>\n";
                    }

                } else {
                    echo "<p class='error'>✗ 缺少SDK文件，无法继续</p>\n";
                }
            }

        } else {
            echo "<p class='error'>✗ 订单不存在</p>\n";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ 查询订单异常: " . $e->getMessage() . "</p>\n";
    }
} else {
    echo "<p class='error'>✗ 数据库连接失败</p>\n";
}

echo "</body></html>\n";
