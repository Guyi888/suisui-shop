<?php
// 开启日志记录
$log_file = __DIR__ . '/getshop.log';
$log = fopen($log_file, 'a');
fwrite($log, "\n\n" . str_repeat('=', 50) . "\n");
fwrite($log, '请求时间: ' . date('Y-m-d H:i:s') . "\n");
fwrite($log, '客户端IP: ' . $_SERVER['REMOTE_ADDR'] . "\n");
fwrite($log, '请求数据: ' . json_encode($_GET) . "\n");

require './inc.php';
fwrite($log, '配置加载成功\n');

$trade_no=isset($_GET['trade_no'])?daddslashes($_GET['trade_no']):exit('No trade_no!');
fwrite($log, '订单号: ' . $trade_no . "\n");

@header('Content-Type: text/html; charset=UTF-8');

// 查询本地订单状态
$row=$DB->getRow("SELECT * FROM pre_pay WHERE trade_no='{$trade_no}' LIMIT 1");
fwrite($log, '本地订单查询结果: ' . ($row ? '找到' : '未找到') . "\n");
if($row) {
    fwrite($log, '本地订单状态: ' . $row['status'] . "\n");
}

// 如果本地订单已支付，直接返回结果
if($row && $row['status']>=1){
    fwrite($log, '本地订单已支付，直接返回结果\n');
    goto return_result;
}

// 如果本地订单未支付，根据支付类型查询对应平台的订单状态
// 查询支付宝订单状态
if($conf['alipay_api']==3 && $row && $row['status']==0) {
    fwrite($log, '开始直接查询支付宝订单状态\n');

    // 定义支付宝配置
    $config = array(
        'sign_type' => 'RSA2',
        'alipay_public_key' => $conf['alipay_publickey'],
        'merchant_private_key' => $conf['alipay_privatekey'],
        'charset' => 'UTF-8',
        'gatewayUrl' => 'https://openapi.alipay.com/gateway.do',
        'app_id' => $conf['alipay_appid'],
    );

    // 检查并引入支付宝SDK文件
    if(file_exists(__DIR__ . "/alipay/AlipayTradeService.php")) {
        require_once(__DIR__ . "/alipay/AlipayTradeService.php");
        fwrite($log, '支付宝SDK文件加载成功\n');

        // 实例化AlipayTradeService
        $alipaySevice = new AlipayTradeService($config);
        fwrite($log, 'AlipayTradeService实例化成功\n');

        // 构建订单查询参数
        $queryBuilder = array();
        $queryBuilder['out_trade_no'] = $trade_no;
        fwrite($log, '订单查询参数: ' . json_encode($queryBuilder) . "\n");

        // 调用订单查询接口
                        try {
                            $queryResponse = $alipaySevice->orderQuery($queryBuilder);
                            fwrite($log, '支付宝订单查询结果: ' . json_encode($queryResponse, JSON_UNESCAPED_UNICODE) . "\n");

                            // 检查查询结果
                            if(isset($queryResponse->code) && $queryResponse->code == '10000') {
                                // 查询成功
                                $trade_status = isset($queryResponse->trade_status) ? $queryResponse->trade_status : 'unknown';
                                fwrite($log, '支付宝订单状态: ' . $trade_status . "\n");
                                fwrite($log, '支付宝返回的完整响应: ' . json_encode($queryResponse, JSON_UNESCAPED_UNICODE) . "\n");

                                // 根据不同的订单状态进行处理
                                switch($trade_status) {
                                    case 'TRADE_SUCCESS':
                                    case 'TRADE_FINISHED':
                                        // 支付成功
                                        fwrite($log, '支付宝订单已支付，状态: ' . $trade_status . "\n");

                                        // 获取支付宝交易号
                                        $api_trade_no = isset($queryResponse->trade_no) ? $queryResponse->trade_no : '';
                                        fwrite($log, '支付宝交易号: ' . $api_trade_no . "\n");

                                        // 更新本地订单状态为已支付
                                        $update_sql = "UPDATE `pre_pay` SET `status` ='1' WHERE `trade_no`='{$trade_no}'";
                                        fwrite($log, '执行SQL: ' . $update_sql . "\n");
                                        $update_result = $DB->exec($update_sql);
                                        fwrite($log, 'SQL执行结果: ' . $update_result . "\n");

                                        if($update_result !== false && $update_result > 0){
                                            $update_sql2 = "UPDATE `pre_pay` SET `endtime` ='$date',`api_trade_no` ='$api_trade_no' WHERE `trade_no`='{$trade_no}'";
                                            fwrite($log, '执行SQL: ' . $update_sql2 . "\n");
                                            $update_result2 = $DB->exec($update_sql2);
                                            fwrite($log, 'SQL执行结果: ' . $update_result2 . "\n");
                                            fwrite($log, '本地订单状态更新成功\n');

                                            // 重新查询本地订单状态
                                            $row=$DB->getRow("SELECT * FROM pre_pay WHERE trade_no='{$trade_no}' LIMIT 1");
                                            if($row) {
                                                fwrite($log, '更新后本地订单状态: ' . $row['status'] . "\n");
                                            }

                                            // 处理订单
                                            fwrite($log, '开始处理订单\n');
                                            processOrder($row);
                                            fwrite($log, '订单处理完成\n');
                                        } else {
                                            fwrite($log, '本地订单状态更新失败，可能是订单已经被处理\n');
                                        }
                                        break;
                                    case 'WAIT_BUYER_PAY':
                                        // 已被扫描，等待支付
                                        fwrite($log, '支付宝订单已被扫描，等待买家支付，状态: ' . $trade_status . "\n");
                                        // 可以选择更新本地订单状态为"已扫描"或其他中间状态
                                        break;
                                    case 'TRADE_CLOSED':
                                        // 订单已关闭
                                        fwrite($log, '支付宝订单已关闭，状态: ' . $trade_status . "\n");
                                        // 更新本地订单状态为已关闭
                                        $update_sql = "UPDATE `pre_pay` SET `status` ='-1' WHERE `trade_no`='{$trade_no}'";
                                        fwrite($log, '执行SQL: ' . $update_sql . "\n");
                                        $update_result = $DB->exec($update_sql);
                                        fwrite($log, '本地订单状态更新为已关闭，结果: ' . $update_result . "\n");
                                        break;
                                    default:
                                        // 其他状态
                                        fwrite($log, '支付宝订单状态未知，状态: ' . $trade_status . "\n");
                                        break;
                                }
                } else {
                    $error_msg = isset($queryResponse->msg) ? $queryResponse->msg : '未知错误';
                    $sub_msg = isset($queryResponse->sub_msg) ? $queryResponse->sub_msg : '';
                    fwrite($log, '支付宝订单查询失败: ' . $error_msg . ' ' . $sub_msg . "\n");
                    fwrite($log, '错误码: ' . (isset($queryResponse->sub_code) ? $queryResponse->sub_code : '未知') . "\n");
                }
            } catch (Exception $e) {
                fwrite($log, '调用orderQuery方法失败: ' . $e->getMessage() . "\n");
                fwrite($log, '错误堆栈: ' . $e->getTraceAsString() . "\n");
            }
    } else {
        fwrite($log, '支付宝SDK文件缺失，无法直接查询支付宝订单状态
');
    }
}

// 查询微信订单状态
if(($conf['wxpay_api']==1 || $conf['wxpay_api']==3) && $row && $row['status']==0) {
    fwrite($log, '开始直接查询微信订单状态
');

    // 检查并引入微信支付SDK文件
    if(file_exists(SYSTEM_ROOT . "wxpay/WxPay.Api.php")) {
        require_once SYSTEM_ROOT . "wxpay/WxPay.Api.php";
        fwrite($log, '微信支付SDK文件加载成功
');

        try {
            // 构建订单查询参数
            $input = new WxPayOrderQuery();
            $input->SetOut_trade_no($trade_no);

            // 调用订单查询接口
            $result = WxPayApi::orderQuery($input);
            fwrite($log, '微信订单查询结果: ' . json_encode($result, JSON_UNESCAPED_UNICODE) . "\n");

            // 检查查询结果
            if(isset($result['return_code']) && $result['return_code'] == 'SUCCESS') {
                if(isset($result['result_code']) && $result['result_code'] == 'SUCCESS') {
                    // 查询成功，获取交易状态
                    $trade_state = isset($result['trade_state']) ? $result['trade_state'] : 'UNKNOWN';
                    fwrite($log, '微信订单状态: ' . $trade_state . "\n");

                    // 根据不同的交易状态进行处理
                    switch($trade_state) {
                        case 'SUCCESS':
                            // 支付成功
                            fwrite($log, '微信订单已支付，开始更新本地订单状态\n');

                            // 更新本地订单状态为已支付
                            $update_sql = "UPDATE `pre_pay` SET `status` ='1' WHERE `trade_no`='{$trade_no}'";
                            fwrite($log, '执行SQL: ' . $update_sql . "\n");
                            $update_result = $DB->exec($update_sql);
                            fwrite($log, 'SQL执行结果: ' . $update_result . "\n");

                            if($update_result !== false && $update_result > 0){
                                $api_trade_no = isset($result['transaction_id']) ? $result['transaction_id'] : '';
                                $update_sql2 = "UPDATE `pre_pay` SET `endtime` ='$date',`api_trade_no` ='$api_trade_no' WHERE `trade_no`='{$trade_no}'";
                                fwrite($log, '执行SQL: ' . $update_sql2 . "\n");
                                $update_result2 = $DB->exec($update_sql2);
                                fwrite($log, '本地订单时间和交易号更新成功\n');

                                // 重新查询本地订单状态
                                $row=$DB->getRow("SELECT * FROM pre_pay WHERE trade_no='{$trade_no}' LIMIT 1");
                                if($row) {
                                    fwrite($log, '更新后本地订单状态: ' . $row['status'] . "\n");
                                }

                                // 处理订单
                                fwrite($log, '开始处理订单\n');
                                processOrder($row);
                                fwrite($log, '订单处理完成\n');
                            } else {
                                fwrite($log, '本地订单状态更新失败，可能是订单已经被处理\n');
                            }
                            break;
                        case 'REFUND':
                        case 'CLOSED':
                        case 'REVOKED':
                            // 订单已关闭或撤销
                            fwrite($log, '微信订单已关闭，状态: ' . $trade_state . "\n");
                            // 更新本地订单状态为已关闭
                            $update_sql = "UPDATE `pre_pay` SET `status` ='-1' WHERE `trade_no`='{$trade_no}'";
                            fwrite($log, '执行SQL: ' . $update_sql . "\n");
                            $update_result = $DB->exec($update_sql);
                            fwrite($log, '本地订单状态更新为已关闭，结果: ' . $update_result . "\n");
                            break;
                        case 'NOTPAY':
                        case 'USERPAYING':
                            // 未支付或支付中
                            fwrite($log, '微信订单未支付或支付中，状态: ' . $trade_state . "\n");
                            break;
                        default:
                            // 其他状态
                            fwrite($log, '微信订单状态未知，状态: ' . $trade_state . "\n");
                            break;
                    }
                } else {
                    $error_msg = isset($result['err_code_des']) ? $result['err_code_des'] : '未知错误';
                    $error_code = isset($result['err_code']) ? $result['err_code'] : '未知错误码';
                    fwrite($log, '微信订单查询失败: ' . $error_code . ' ' . $error_msg . "\n");
                }
            } else {
                $error_msg = isset($result['return_msg']) ? $result['return_msg'] : '未知错误';
                fwrite($log, '微信订单查询失败: ' . $error_msg . "\n");
            }
        } catch (Exception $e) {
            fwrite($log, '调用微信订单查询方法失败: ' . $e->getMessage() . "\n");
            fwrite($log, '错误堆栈: ' . $e->getTraceAsString() . "\n");
        }
    } else {
        fwrite($log, '微信支付SDK文件缺失，无法直接查询微信订单状态\n');
    }
}

// 重新查询本地订单状态
$row=$DB->getRow("SELECT * FROM pre_pay WHERE trade_no='{$trade_no}' LIMIT 1");
fwrite($log, '最终本地订单状态: ' . ($row ? $row['status'] : '未找到') . "\n");

return_result:
// 生成返回结果
if($row['domain'] && $row['domain']!=$_SERVER['HTTP_HOST'] && strpos($row['domain'],'.')!==false){
	$baseurl = 'http://'.$row['domain'].'/';
	fwrite($log, '使用自定义域名: ' . $baseurl . "\n");
}else{
	$baseurl = '../';
	fwrite($log, '使用默认域名: ' . $baseurl . "\n");
}

if($row['tid']==-1)$link = $baseurl.'user/';
elseif($row['tid']==-2)$link = $baseurl.'user/regok.php?orderid='.$trade_no;
else $link = $baseurl.'?buyok=1';
fwrite($log, '跳转链接: ' . $link . "\n");

// 根据订单状态返回不同的响应
if($row) {
    switch($row['status']) {
        case 1:
        case 2:
            // 已支付
            $response = '{"code":1,"msg":"付款成功","backurl":"'.$link.'"}';
            break;
        case -1:
            // 已关闭
            $response = '{"code":-2,"msg":"订单已关闭"}';
            break;
        case 0:
        default:
            // 未支付，需要检查支付宝实际状态
            // 如果是主动查询模式，可能需要返回不同状态
            $response = '{"code":-1,"msg":"未付款"}';
            break;
    }
} else {
    $response = '{"code":-3,"msg":"订单不存在"}';
}

fwrite($log, '返回结果: ' . $response . "\n");
fclose($log);
exit($response);
?>