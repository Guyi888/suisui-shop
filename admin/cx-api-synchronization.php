<?php
/*
 * 自动同步API处理文件
 * 博客：zhonguo.ren
 * QQ群：915043052
 * 开发者：教主
 * 功能：处理商品自动同步的核心逻辑，包括分类同步、商品同步、数据更新等
 */
// 设置脚本执行时间和内存限制 - 教主优化，确保同步任务能完成
ini_set('max_execution_time', 3600); // 设置执行时间为1小时
ini_set('memory_limit', '512M'); // 设置内存限制为512M
ini_set('default_socket_timeout', 300); // 设置socket超时为5分钟

header("Content-Type: text/html;charset=utf-8");
include("../includes/common.php");

// 检查监控密钥
$monitor_key = $DB->getColumn("SELECT v FROM pre_config WHERE k='monitor_key'");
if($monitor_key && $_GET['key'] != $monitor_key) {
    exit('Access Denied');
}

// 设置同步任务开始时间
$DB->exec("REPLACE INTO pre_config SET k='last_sync_time',v='{$date}'");
echo "同步任务开始时间：{$date}<br/>";

// 模拟任务管理器功能 - 教主优化，提高性能
class SimpleTask {
    private $name;
    private $outputBuffer = [];
    private $bufferSize = 50; // 每50条输出一次，减少I/O操作
    private $lastFlushTime = 0;
    private $flushInterval = 5; // 每5秒至少刷新一次
    
    public function __construct($name) {
        $this->name = $name;
        $this->lastFlushTime = time();
        // 启用输出缓冲
        if(!ob_get_level()) {
            ob_start();
        }
    }
    
    public function updateProgress($message) {
        // 只记录关键信息，减少输出量
        if(strpos($message, '处理第') !== false || strpos($message, '开始') !== false || strpos($message, '完成') !== false) {
            $this->outputBuffer[] = $message . "<br/>";
            $this->checkFlush();
        }
    }
    
    public function checkTimeout() {
        // 简单实现，不做超时检查
    }
    
    public function cleanup() {
        // 清理时刷新所有缓冲区
        $this->flushOutput();
        // 关闭输出缓冲
        if(ob_get_level()) {
            ob_end_flush();
        }
    }
    
    private function checkFlush() {
        // 检查是否需要刷新缓冲区
        $currentTime = time();
        if(count($this->outputBuffer) >= $this->bufferSize || ($currentTime - $this->lastFlushTime) >= $this->flushInterval) {
            $this->flushOutput();
            $this->lastFlushTime = $currentTime;
        }
    }
    
    private function flushOutput() {
        if(!empty($this->outputBuffer)) {
            echo implode('', $this->outputBuffer);
            $this->outputBuffer = [];
            // 刷新输出缓冲区
            ob_flush();
            flush();
        }
    }
}

// 创建简单任务实例
$task = new SimpleTask('auto_sync');

try {
    // 获取所有启用的同步配置
    $configs = $DB->query("SELECT * FROM pre_sync_config WHERE status=1");
    $total_synced = 0;
    $total_updated = 0;
    $total_deleted = 0;
    
    while($row = $configs->fetch()) {
        $task->updateProgress("开始处理站点配置: ID=" . $row['shequ_id']);
        
        // 获取对接站点信息
        $shequ = $DB->getRow("SELECT * FROM pre_shequ WHERE id='" . $row['shequ_id'] . "'");
        if(!$shequ) {
            $task->updateProgress("跳过: 未找到站点信息 ID=" . $row['shequ_id']);
            continue;
        }
        
        // 检查插件是否存在
        $plugin_file = PLUGIN_ROOT . 'third_' . $shequ['type'] . '.php';
        if(!file_exists($plugin_file)) {
            $task->updateProgress("跳过: 插件文件不存在 {$plugin_file}");
            continue;
        }
        
        // 同步分类
        if($row['sync_class'] || $row['add_class']) {
            $task->updateProgress("开始同步分类信息");
            syncCategories($shequ, $row, $task);
        }
        
        // 同步商品
        $synced_count = syncProducts($shequ, $row, $task);
        $total_synced += $synced_count['synced'];
        $total_updated += $synced_count['updated'];
        $total_deleted += $synced_count['deleted'];
        
        $task->updateProgress("站点同步完成: 新增{$synced_count['synced']}, 更新{$synced_count['updated']}, 删除{$synced_count['deleted']}");
    }
    
    $task->updateProgress("同步任务完成: 总计新增{$total_synced}, 更新{$total_updated}, 删除{$total_deleted}");
    echo "同步任务结束时间: " . date('Y-m-d H:i:s') . "<br/>";
    
} catch(Exception $e) {
    $task->updateProgress("同步任务异常: " . $e->getMessage());
} finally {
    // 清理任务
    $task->cleanup();
    
    // 更新同步结束时间
    $DB->exec("REPLACE INTO pre_config SET k='last_sync_end_time',v='" . date('Y-m-d H:i:s') . "'");
}

/**
 * 同步分类信息
 */
function syncCategories($shequ, $config, $task) {
    global $DB, $date, $conf;
    
    try {
        // 使用 third_call 函数调用插件方法
        $categories = third_call($shequ['type'], $shequ, 'class_list');
        
        // 检查分类列表是否为数组
        if(!is_array($categories)) {
            $task->updateProgress("获取分类列表失败: {$categories}");
            return;
        }
        
        if(empty($categories)) {
            $task->updateProgress("未获取到分类数据");
            return;
        }
        
        $added = 0;
        $updated = 0;
        
        foreach($categories as $cat) {
            // 检查分类是否已存在 - 教主修改，博客地址：6v6.ren Q群：941535592
            $existing_cat = $DB->getRow("SELECT * FROM pre_class WHERE `name`=? LIMIT 1", [$cat['name']]);
            
            if($existing_cat) {
                // 更新分类 - 教主修改，博客地址：6v6.ren Q群：941535592
                if($config['sync_sort'] && $existing_cat['sort'] != ($cat['sort'] ?? 0)) {
                    $sort = intval($cat['sort'] ?? 0);
                    $name = addslashes($cat['name']);
                    $DB->exec("UPDATE `pre_class` SET `sort`='{$sort}' WHERE `cid`='{$existing_cat['cid']}'");
                    $updated++;
                }
            } elseif($config['add_class']) {
                // 新增分类 - 教主修改，博客地址：6v6.ren Q群：941535592
                $sort = intval($cat['sort'] ?? 0);
                $name = addslashes($cat['name']);
                $DB->exec("INSERT INTO `pre_class` (`sort`,`name`,`pid`,`active`) VALUES ('{$sort}','{$name}','0','1')");
                $added++;
            }
        }
        
        $task->updateProgress("分类同步完成: 新增{$added}, 更新{$updated}");
        
    } catch(Exception $e) {
        $task->updateProgress("分类同步失败: " . $e->getMessage());
    }
}

/**
 * 同步商品信息
 */
function syncProducts($shequ, $config, $task) {
    global $DB, $date, $conf;
    
    $synced = 0;
    $updated = 0;
    $deleted = 0;
    
    try {
        // 使用 third_call 函数调用插件方法获取商品列表
        $products = third_call($shequ['type'], $shequ, 'goods_list');
        
        // 检查商品列表是否为数组
        if(!is_array($products)) {
            $task->updateProgress("获取商品列表失败: {$products}");
            return ['synced' => 0, 'updated' => 0, 'deleted' => 0];
        }
        
        if(empty($products)) {
            $task->updateProgress("未获取到商品数据");
            return ['synced' => 0, 'updated' => 0, 'deleted' => 0];
        }
        
        $total_products = count($products);
        $task->updateProgress("获取到 {$total_products} 个商品，开始分批处理");
        
        // 预加载分类信息，减少重复查询 - 教主优化，提高性能
        $categories = third_call($shequ['type'], $shequ, 'class_list');
        $category_map = [];
        if(is_array($categories)) {
            foreach($categories as $cat) {
                $category_map[$cat['cid']] = $cat['name'];
            }
        }
        
        // 预加载加价模板，减少重复查询 - 教主优化，提高性能
        $markup_template = null;
        if($config['markup_template']) {
            $markup_template = $DB->getRow("SELECT * FROM pre_price WHERE id=?", [$config['markup_template']]);
        }
        
        // 预加载默认分类，减少重复查询 - 教主优化，提高性能
        $default_class = $DB->getRow("SELECT * FROM pre_class WHERE `name`=? LIMIT 1", ['默认分类']);
        $default_cid = $default_class ? $default_class['cid'] : 0;
        
        // 分批处理商品，每批50个
        $batch_size = 50;
        $batches = array_chunk($products, $batch_size);
        $total_batches = count($batches);
        
        $all_shequ_product_ids = [];
        
        foreach($batches as $batch_index => $batch_products) {
            $batch_number = $batch_index + 1;
            $task->updateProgress("处理第 {$batch_number}/{$total_batches} 批商品，共 " . count($batch_products) . " 个");
            
            // 重置批次相关变量
            $shequ_product_ids = [];
            $new_products = []; // 用于批量插入的商品数据
            $product_ids = []; // 用于批量查询现有商品
            $update_products = []; // 用于批量更新的商品数据
            $update_site_prices = []; // 用于批量更新的站点价格数据
            
            // 收集需要获取详情的商品ID - 教主优化，实现批量API调用
            $need_detail_ids = [];
            $need_price_ids = [];
            $need_desc_ids = [];
            
            foreach($batch_products as $product) {
                $shequ_product_ids[] = $product['id'];
                $product_ids[] = $product['id'];
                $all_shequ_product_ids[] = $product['id'];
                
                // 检查是否需要获取商品详情
                if(empty($product['cid']) || !isset($category_map[$product['cid']])) {
                    $need_detail_ids[] = $product['id'];
                }
                
                // 检查是否需要同步价格
                if($config['sync_price']) {
                    $need_price_ids[] = $product['id'];
                }
                
                // 检查是否需要同步描述
                if($config['sync_desc']) {
                    $need_desc_ids[] = $product['id'];
                }
            }
            
            // 合并需要调用goods_info的商品ID，去重
            $all_need_detail_ids = array_unique(array_merge($need_detail_ids, $need_price_ids, $need_desc_ids));
            $product_details_cache = [];
            
            // 批量获取商品详情 - 教主优化，减少API调用
            if(!empty($all_need_detail_ids)) {
                $task->updateProgress("开始批量获取商品详情，共 " . count($all_need_detail_ids) . " 个商品");
                
                // 检查插件是否支持批量goods_info调用
                $batch_supported = false;
                try {
                    // 尝试批量调用
                    $batch_result = third_call($shequ['type'], $shequ, 'goods_info_batch', [$all_need_detail_ids]);
                    if(is_array($batch_result) && !empty($batch_result)) {
                        $batch_supported = true;
                        foreach($batch_result as $goodsSN => $detail) {
                            if(isset($detail['goodsSN'])) {
                                $product_details_cache[$detail['goodsSN']] = $detail;
                            }
                        }
                        $task->updateProgress("批量获取商品详情成功，共 " . count($product_details_cache) . " 个商品");
                    }
                } catch(Exception $e) {
                    // 批量调用失败，使用单个调用
                    $batch_supported = false;
                }
                
                // 如果不支持批量调用，使用单个调用但优化为批量处理
                if(!$batch_supported) {
                    $api_batch_size = 10; // 每批处理10个商品
                    $api_batches = array_chunk($all_need_detail_ids, $api_batch_size);
                    $total_api_batches = count($api_batches);
                    
                    foreach($api_batches as $api_batch_index => $api_batch_ids) {
                        $task->updateProgress("处理第 " . ($api_batch_index + 1) . "/" . $total_api_batches . " 批API请求");
                        
                        foreach($api_batch_ids as $product_id) {
                            try {
                                $detail = third_call($shequ['type'], $shequ, 'goods_info', [$product_id]);
                                if(is_array($detail)) {
                                    $product_details_cache[$product_id] = $detail;
                                }
                            } catch(Exception $e) {
                                // 获取商品详情失败，继续处理
                            }
                        }
                    }
                    
                    $task->updateProgress("单个获取商品详情完成，共 " . count($product_details_cache) . " 个商品");
                }
                

            }
            
            // 批量查询现有商品 - 教主优化，提高性能
            $existing_products = [];
            if(!empty($product_ids)) {
                $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
                $query = "SELECT * FROM pre_tools WHERE is_curl=2 AND shequ=? AND goods_id IN ({$placeholders})";
                $params = array_merge([$shequ['id']], $product_ids);
                $stmt = $DB->query($query, $params);
                while($row = $stmt->fetch()) {
                    $existing_products[$row['goods_id']] = $row;
                }
            }
            
            foreach($batch_products as $product) {
                // 检查任务超时
                $task->checkTimeout();
                
                // 检查商品是否已存在 - 使用批量查询的结果
                $existing_product = isset($existing_products[$product['id']]) ? $existing_products[$product['id']] : null;
                
                // 新增商品逻辑 - 教主修改，博客地址：6v6.ren Q群：941535592
                if(!$existing_product) {
                    // 检查是否允许新增商品
                    if($config['add_goods']) {
                        $task->updateProgress("开始处理新增商品: {$product['name']} (ID: {$product['id']})");
                        
                        // 构建商品详情数据
                        $product_detail = [
                            'name' => $product['name'],
                            'price' => $product['price'],
                            'shopimg' => $product['shopimg'],
                            'close' => $product['close'],
                            'stock' => $product['stock'],
                            'class_name' => '',
                            'category' => ''
                        ];
                        
                        // 从商品列表中获取分类名称
                        if(isset($product['cid']) && !empty($product['cid']) && isset($category_map[$product['cid']])) {
                            $product_detail['class_name'] = $category_map[$product['cid']];
                            $product_detail['category'] = $category_map[$product['cid']];
                        }
                        
                        // 从缓存中获取商品详情 - 教主优化，使用缓存数据
                        $cache_key = $product['id'];
                        if(isset($product_details_cache[$cache_key])) {
                            $cached_detail = $product_details_cache[$cache_key];
                            $product_detail = array_merge($product_detail, $cached_detail);
                        }
                        
                        // 查找对应的本地分类
                        $cid = 0;
                        $class_name = $product_detail['class_name'] ?? $product_detail['category'] ?? '';
                        
                        if(!empty($class_name)) {
                            $class_info = $DB->getRow("SELECT * FROM pre_class WHERE `name`=? LIMIT 1", [$class_name]);
                            if($class_info) {
                                $cid = $class_info['cid'];
                            } elseif($config['add_class']) {
                                // 如果允许新增分类，则创建新分类
                                $sort = 0;
                                $name = addslashes($class_name);
                                $DB->exec("INSERT INTO `pre_class` (`sort`,`name`,`pid`,`active`) VALUES ('{$sort}','{$name}','0','1')");
                                $cid = $DB->lastInsertId();
                            }
                        }
                        
                        // 如果没有找到分类，使用默认分类
                        if($cid == 0) {
                            if($default_cid) {
                                $cid = $default_cid;
                            } elseif($config['add_class']) {
                                // 创建默认分类
                                $sort = 0;
                                $default_class_name = '默认分类';
                                $name = addslashes($default_class_name);
                                $DB->exec("INSERT INTO `pre_class` (`sort`,`name`,`pid`,`active`) VALUES ('{$sort}','{$name}','0','1')");
                                $cid = $DB->lastInsertId();
                                $default_cid = $cid; // 更新默认分类ID
                            }
                        }
                        
                        // 计算价格（应用加价模板）
                        $cost_price = $product_detail['price'] ?? $product['price'] ?? 0;
                        // 应用加价模板到成本价
                        if($markup_template) {
                            $cost_price = calculateMarkupPrice($cost_price, $markup_template);
                        }
                        // 获取默认下单数量 - 使用商品自身的默认数量，支持不同字段名
                        $default_num = intval($product_detail['value'] ?? $product_detail['unitnum'] ?? $product_detail['limit_min'] ?? $product_detail['buy_min_limit'] ?? $product_detail['default_num'] ?? $product_detail['default_quantity'] ?? $product_detail['buy_num'] ?? $product_detail['min_buy'] ?? $product_detail['buy_min'] ?? $product_detail['quantity'] ?? $product_detail['default_qty'] ?? $product_detail['qty'] ?? 1);
                        // 确保默认下单数量至少为1
                        if($default_num < 1) {
                            $default_num = 1;
                        }
                        // 对于价格非常小的商品，使用最小下单数量作为默认数量
                        if($default_num == 1 && floatval($product_detail['price'] ?? 0) < 0.01) {
                            $default_num = intval($product_detail['min'] ?? $product_detail['limit_min'] ?? $product_detail['buy_min_limit'] ?? $product_detail['min_buy'] ?? $product_detail['buy_min'] ?? 100);
                            if($default_num < 1) {
                                $default_num = 100;
                            }
                        }
                        // 确保默认下单数量至少为最小下单数量
                        $min_num = intval($product_detail['min'] ?? $product_detail['limit_min'] ?? $product_detail['buy_min_limit'] ?? $product_detail['min_buy'] ?? $product_detail['buy_min'] ?? 1);
                        if($default_num < $min_num) {
                            $default_num = $min_num;
                        }
                        // 计算后的价钱 = 成本价 × 默认下单数量
                        $price = $cost_price * $default_num;
                        
                        // 价格精度处理：当计算后的价格小于0.01时，调整下单数量
                        if($price < 0.01) {
                            // 计算需要的调整倍数
                            $adjustment_factor = ceil(0.01 / $price);
                            // 确保调整倍数是10的倍数
                            $adjustment_factor = ceil($adjustment_factor / 10) * 10;
                            // 调整最少下单数量和默认下单数量
                            $min_num = $min_num * $adjustment_factor;
                            $default_num = $default_num * $adjustment_factor;
                            // 重新计算价格
                            $price = $cost_price * $default_num;
                        }
                        
                        // 处理图片
                        $shopimg = '';
                        if(isset($product_detail['shopimg']) && !empty($product_detail['shopimg'])) {
                            $shopimg = $product_detail['shopimg'];
                        } elseif(isset($product_detail['image']) && !empty($product_detail['image'])) {
                            $shopimg = $product_detail['image'];
                        }
                        
                        // 构建新商品数据
                        $new_product_data = [
                            'sort' => 0,
                            'cid' => $cid,
                            'name' => $product_detail['name'] ?? $product['name'] ?? '未命名商品',
                            'price' => floatval($price),
                            'cost' => floatval($cost_price),
                            'cost2' => 0,
                            'prid' => intval($config['markup_template'] ?? 0),
                            'prices' => '',
                            'input' => $product_detail['input'] ?? '',
                            'inputs' => $product_detail['inputs'] ?? '',
                            'desc' => $product_detail['desc'] ?? $product_detail['content'] ?? $product_detail['info'] ?? '',
                            'alert' => $product_detail['alert'] ?? '',
                            'shopimg' => $shopimg,
                            'value' => $default_num,
                            'is_curl' => 2,
                            'curl' => '',
                            'shequ' => intval($shequ['id']),
                            'goods_id' => strval($product['id']),
                            'goods_type' => 0,
                            'goods_param' => isset($product_detail['goods_param']) ? $product_detail['goods_param'] : (isset($product_detail['id']) ? $product_detail['id'] : ''),
                            'repeat' => intval($product_detail['repeat'] ?? 0),
                            'multi' => intval($product_detail['multi'] ?? 0),
                            'min' => 1,
                        'max' => intval($product_detail['max'] ?? $product_detail['limit_max'] ?? $product_detail['buy_max_limit'] ?? $product_detail['max_buy'] ?? $product_detail['buy_max'] ?? 0),
                            'validate' => $product_detail['validate'] ?? '',
                            'valiserv' => $product_detail['valiserv'] ?? '',
                            'close' => intval($product['close'] ?? 0),
                            'stock' => intval($product['stock'] ?? 9999),
                            'active' => 1,
                            'addtime' => $date,
                            'uptime' => $date
                        ];
                        
                        // 添加到批量插入数组
                        $new_products[] = $new_product_data;
                    } else {
                        $task->updateProgress("不允许新增商品，跳过: {$product['name']}");
                    }
                    continue;
                }
                
                // 构建商品数据
                $cost_price = $product['price'];
                
                // 从缓存中获取商品详情 - 教主优化，使用缓存数据
                $cache_key = $product['id'];
                $cached_detail = isset($product_details_cache[$cache_key]) ? $product_details_cache[$cache_key] : [];
                
                // 如果需要同步价格，使用缓存的价格信息
                if($config['sync_price'] && isset($cached_detail['price'])) {
                    $cost_price = $cached_detail['price'];
                }
                
                // 处理加价模板
                if($markup_template) {
                    $cost_price = calculateMarkupPrice($cost_price, $markup_template);
                }
                
                // 获取默认下单数量 - 使用商品自身的默认数量，支持不同字段名
                $default_num = intval($cached_detail['value'] ?? $cached_detail['unitnum'] ?? $cached_detail['limit_min'] ?? $cached_detail['buy_min_limit'] ?? $cached_detail['default_num'] ?? $cached_detail['default_quantity'] ?? $cached_detail['buy_num'] ?? $cached_detail['min_buy'] ?? $cached_detail['quantity'] ?? $cached_detail['default_qty'] ?? $cached_detail['qty'] ?? 1);
                // 确保默认下单数量至少为1
                if($default_num < 1) {
                    $default_num = 1;
                }
                // 对于价格非常小的商品，使用最小下单数量作为默认数量
                if($default_num == 1 && floatval($cached_detail['price'] ?? 0) < 0.01) {
                    $default_num = intval($cached_detail['min'] ?? $cached_detail['limit_min'] ?? $cached_detail['buy_min_limit'] ?? 100);
                    if($default_num < 1) {
                        $default_num = 100;
                    }
                }
                // 确保默认下单数量至少为最小下单数量
                $min_num = intval($cached_detail['min'] ?? $cached_detail['limit_min'] ?? $cached_detail['buy_min_limit'] ?? $cached_detail['min_buy'] ?? $cached_detail['buy_min'] ?? 1);
                if($default_num < $min_num) {
                    $default_num = $min_num;
                }
                // 计算后的价钱 = 成本价 × 默认下单数量
                $price = $cost_price * $default_num;
                
                // 价格精度处理：当计算后的价格小于0.01时，调整下单数量
                if($price < 0.01) {
                    // 计算需要的调整倍数
                    $adjustment_factor = ceil(0.01 / $price);
                    // 确保调整倍数是10的倍数
                    $adjustment_factor = ceil($adjustment_factor / 10) * 10;
                    // 调整最少下单数量和默认下单数量
                    $min_num = $min_num * $adjustment_factor;
                    $default_num = $default_num * $adjustment_factor;
                    // 重新计算价格
                    $price = $cost_price * $default_num;
                }
                
                // 检查是否有自定义价格记录
                $has_custom_price = $DB->getColumn("SELECT COUNT(*) FROM pre_site_price WHERE tid=?", [$existing_product['tid']]);
                
                // 构建更新数据
                $update_data = [
                    'tid' => $existing_product['tid'],
                    'close' => $product['close'],
                    'stock' => $product['stock'] ?? 9999,
                    'uptime' => $date
                ];
                
                // 同步商品参数
                if(isset($cached_detail['goods_param'])) {
                    $update_data['goods_param'] = $cached_detail['goods_param'];
                }
                
                // 只有当勾选了价格同步选项时才同步价格
                if($config['sync_price']) {
                    $update_data['price'] = $price;
                    $update_data['cost'] = floatval($cost_price);
                }
                
                // 同步商品名称
                if($config['sync_name'] && isset($product['name'])) {
                    $update_data['name'] = $product['name'];
                }
                
                // 同步商品图片
                if($config['sync_image'] && isset($product['shopimg']) && !empty($product['shopimg'])) {
                    $update_data['shopimg'] = $product['shopimg'];
                }
                
                // 同步商品简介
                if($config['sync_desc'] && !empty($cached_detail)) {
                    // 使用缓存的商品详情，包括简介
                    $desc_content = '';
                    if(isset($cached_detail['desc'])) {
                        $desc_content = $cached_detail['desc'];
                    } elseif(isset($cached_detail['content'])) {
                        $desc_content = $cached_detail['content'];
                    } elseif(isset($cached_detail['info'])) {
                        $desc_content = $cached_detail['info'];
                    } elseif(isset($cached_detail['description'])) {
                        $desc_content = $cached_detail['description'];
                    }
                    
                    if(!empty($desc_content)) {
                        $update_data['desc'] = $desc_content;
                    }
                }
                
                // 同步默认数量信息
                $update_data['value'] = $default_num;
                
                // 同步最少下单数量（固定为1）和最大下单数量，支持不同字段名
                $update_data['min'] = 1;
                $update_data['max'] = intval($cached_detail['max'] ?? $cached_detail['limit_max'] ?? $cached_detail['buy_max_limit'] ?? $cached_detail['max_buy'] ?? $cached_detail['buy_max'] ?? 0);
                
                // 收集需要更新的商品数据
                if(!empty($update_data)) {
                    $update_products[] = $update_data;
                }
                
                // 收集需要更新的站点价格数据
                if($config['sync_price'] && $has_custom_price > 0) {
                    $update_site_prices[] = [
                        'tid' => $existing_product['tid'],
                        'price' => $price
                    ];
                }
                
                $task->updateProgress("收集更新商品: {$product['name']}");
            }
            
            // 批量插入新商品 - 教主优化，提高性能
                if(!empty($new_products)) {
                    $task->updateProgress("开始批量插入新商品，共 " . count($new_products) . " 个");
                
                try {
                    // 开始事务
                    $DB->beginTransaction();
                    
                    foreach($new_products as $new_product_data) {
                        // 使用直接SQL语句插入，避免表名前缀问题
                        $fields = array_keys($new_product_data);
                        $values = array_values($new_product_data);
                        $placeholders = implode(',', array_fill(0, count($values), '?'));
                        
                        $sql = "INSERT INTO pre_tools (`" . implode('`,`', $fields) . "`) VALUES ({$placeholders})";
                        $stmt = $DB->query($sql, $values);
                        
                        if($stmt !== false) {
                                $new_tid = $DB->lastInsertId();
                                $synced++;
                                // 记录上架日志
                                if($config['sync_log']) {
                                    $log_content = "自动同步新增商品";
                                    $log_sql = "INSERT INTO pre_tools_log (`tid`,`type`,`content`,`addtime`) VALUES (?,?,?,?)";
                                    $DB->query($log_sql, [$new_tid, 1, $log_content, $date]);
                                }
                            } else {
                                // 商品插入失败，继续处理
                            }
                    }
                    
                    // 提交事务
                    $DB->commit();
                    $task->updateProgress("批量插入完成，成功插入 " . count($new_products) . " 个商品");
                } catch(Exception $e) {
                    // 回滚事务
                    $DB->rollback();
                    $task->updateProgress("批量插入异常: " . $e->getMessage());
                }
            }
            
            // 批量更新商品 - 教主优化，提高性能
            if(!empty($update_products)) {
                $task->updateProgress("开始批量更新商品，共 " . count($update_products) . " 个");
                
                try {
                    // 开始事务
                    $DB->beginTransaction();
                    
                    foreach($update_products as $update_data) {
                        $tid = $update_data['tid'];
                        unset($update_data['tid']); // 移除tid字段，因为它是WHERE条件
                        
                        // 构建SQL更新语句
                        $set_clause = [];
                        $params = [];
                        foreach($update_data as $field => $value) {
                            $set_clause[] = "`{$field}` = ?";
                            $params[] = $value;
                        }
                        $params[] = $tid;
                        
                        $sql = "UPDATE pre_tools SET " . implode(', ', $set_clause) . " WHERE tid = ?";
                        $stmt = $DB->query($sql, $params);
                        
                        if($stmt !== false) {
                            $rowCount = $stmt->rowCount();
                            if($rowCount > 0) {
                                $updated++;
                                // 记录更新日志
                                if($config['sync_log']) {
                                    $log_content = "自动同步更新商品";
                                    $log_sql = "INSERT INTO pre_tools_log (`tid`,`type`,`content`,`addtime`) VALUES (?,?,?,?)";
                                    $DB->query($log_sql, [$tid, 2, $log_content, $date]);
                                }
                            }
                        } else {
                            // 商品更新失败，继续处理
                        }
                    }
                    
                    // 提交事务
                    $DB->commit();
                    $task->updateProgress("批量更新完成，成功更新 " . count($update_products) . " 个商品");
                } catch(Exception $e) {
                    // 回滚事务
                    $DB->rollback();
                    $task->updateProgress("批量更新异常: " . $e->getMessage());
                }
            }
            
            // 批量更新站点价格 - 教主优化，提高性能
            if(!empty($update_site_prices)) {
                $task->updateProgress("开始批量更新站点价格，共 " . count($update_site_prices) . " 个");
                
                try {
                    // 开始事务
                    $DB->beginTransaction();
                    
                    foreach($update_site_prices as $site_price) {
                        $sql = "UPDATE pre_site_price SET price = ?, update_time = NOW() WHERE tid = ?";
                        $stmt = $DB->query($sql, [$site_price['price'], $site_price['tid']]);
                    }
                    
                    // 提交事务
                    $DB->commit();
                    $task->updateProgress("批量更新站点价格完成");
                } catch(Exception $e) {
                    // 回滚事务
                    $DB->rollback();
                    $task->updateProgress("批量更新站点价格异常: " . $e->getMessage());
                }
            }
            
            // 清理内存
            unset($new_products, $update_products, $update_site_prices, $product_details_cache, $existing_products);
            gc_collect_cycles();
            
            $task->updateProgress("第 {$batch_number}/{$total_batches} 批处理完成");
        }
        
        // 使用去重后的所有商品ID处理下架商品
        $all_shequ_product_ids = array_unique($all_shequ_product_ids);
        
        // 处理下架商品
        if($config['delete_rule'] > 0) {
            $deleted_count = handleDeletedProducts($shequ['id'], $all_shequ_product_ids, $config['delete_rule'], $task);
            $deleted += $deleted_count;
        }
        
    } catch(Exception $e) {
        $task->updateProgress("商品同步失败: " . $e->getMessage());
        echo "商品同步失败: " . $e->getMessage() . "<br/>";
    }
    
    return ['synced' => $synced, 'updated' => $updated, 'deleted' => $deleted];
}

/**
 * 处理下架商品
 */
function handleDeletedProducts($shequ_id, $active_product_ids, $delete_rule, $task) {
    global $DB, $date;
    
    $count = 0;
    // 使用 pre_tools 表而非 pre_goods 表 - 教主修改，博客地址：6v6.ren Q群：941535592
    $query = "SELECT tid, name FROM pre_tools WHERE is_curl=2 AND shequ=? AND active=1";
    $params = [$shequ_id];
    
    if(!empty($active_product_ids)) {
        // 使用参数化查询处理IN子句，防止SQL注入
        $placeholders = implode(',', array_fill(0, count($active_product_ids), '?'));
        $query .= " AND goods_id NOT IN ({$placeholders})";
        $params = array_merge($params, $active_product_ids);
    }
    
    $products = $DB->query($query, $params);
    
    while($product = $products->fetch()) {
        if($delete_rule == 1) {
            // 下架商品 - 教主修改，博客地址：6v6.ren Q群：941535592
            $DB->update('pre_tools', ['close' =>1, 'uptime' => $date], ['tid' => $product['tid']]);
            $task->updateProgress("下架商品: {$product['name']}");
        } elseif($delete_rule == 2) {
            // 删除商品 - 教主修改，博客地址：6v6.ren Q群：941535592
            $DB->delete('pre_tools', ['tid' => $product['tid']]);
            $task->updateProgress("删除商品: {$product['name']}");
        }
        $count++;
    }
    
    return $count;
}

/**
 * 根据加价模板计算价格
 */
function calculateMarkupPrice($base_price, $markup_template) {
    $price = $base_price;
    
    if($markup_template['type'] == 1) {
        // 固定金额加价
        $price += $markup_template['value'];
    } elseif($markup_template['type'] == 2) {
        // 百分比加价
        $price = $price * (1 + $markup_template['value'] / 100);
    }
    
    // 价格精度处理
    $price = round($price, 2);
    
    return $price;
}

/**
 * 保存远程图片到本地
 */
function saveRemoteImage($url) {
    try {
        $img_dir = SYS_ROOT . 'assets/upload/image/';
        $img_url = $conf['local'] . 'assets/upload/image/';
        
        // 创建目录
        if(!is_dir($img_dir)) {
            mkdir($img_dir, 0755, true);
        }
        
        // 获取文件名和扩展名
        $path_info = pathinfo($url);
        $ext = isset($path_info['extension']) ? '.' . $path_info['extension'] : '.jpg';
        $filename = md5($url . time()) . $ext;
        $filepath = $img_dir . $filename;
        $fileurl = $img_url . $filename;
        
        // 下载图片
        $img_data = @file_get_contents($url);
        if($img_data) {
            @file_put_contents($filepath, $img_data);
            if(file_exists($filepath)) {
                return $fileurl;
            }
        }
        
        return false;
    } catch(Exception $e) {
        return false;
    }
}
 
?>