<?php
/*
 * 岁岁云商城自动同步 API
 * 功能：处理商品自动同步的核心逻辑，包括分类同步、商品同步、数据更新等。
 */
// 设置脚本执行时间和内存限制
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

// 模拟任务管理器功能
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
        if(strpos($message, '处理第') !== false || strpos($message, '开始') !== false || strpos($message, '完成') !== false || strpos($message, '异常') !== false || strpos($message, '失败') !== false || strpos($message, '调试') !== false || strpos($message, '调试') !== false || strpos($message, '异常') !== false || strpos($message, '失败') !== false) {
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
    $total_scanned = 0;
    $total_field_counts = [];
    $total_update_samples = [];

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

        if($shequ['type'] == 'daishua') {
            $task->updateProgress("开始同系统完整同步");
            $synced_count = syncDaishuaFull($shequ, $row, $task);
            $total_synced += $synced_count['synced'];
            $total_updated += $synced_count['updated'];
            $total_deleted += $synced_count['deleted'];
            $total_scanned += intval($synced_count['scanned'] ?? 0);
            if(!empty($synced_count['field_counts']) && is_array($synced_count['field_counts'])) {
                foreach($synced_count['field_counts'] as $field => $count) {
                    if(!isset($total_field_counts[$field])) $total_field_counts[$field] = 0;
                    $total_field_counts[$field] += intval($count);
                }
            }
            if(!empty($synced_count['samples']) && is_array($synced_count['samples'])) {
                $total_update_samples = array_slice(array_merge($total_update_samples, $synced_count['samples']), 0, 20);
            }
            $task->updateProgress("站点同步完成：已检索 {$synced_count['scanned']} 条，实际新增 {$synced_count['synced']} 条，实际更新 {$synced_count['updated']} 条，实际删除 {$synced_count['deleted']} 条");
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

        $task->updateProgress("站点同步完成：实际新增 {$synced_count['synced']} 条，实际更新 {$synced_count['updated']} 条，实际删除 {$synced_count['deleted']} 条");
    }

    $task->updateProgress("同步任务完成：已检索 {$total_scanned} 条，实际新增 {$total_synced} 条，实际更新 {$total_updated} 条，实际删除 {$total_deleted} 条");
    $syncLogDetail = json_encode(['scanned'=>$total_scanned,'added'=>$total_synced,'updated'=>$total_updated,'deleted'=>$total_deleted,'field_counts'=>$total_field_counts,'samples'=>$total_update_samples], JSON_UNESCAPED_UNICODE);
    q8_add_site_log('sync', 'sync_full', 'all', "自动同步完成：已检索 {$total_scanned} 条，实际新增 {$total_synced} 条，实际更新 {$total_updated} 条，实际删除 {$total_deleted} 条", $syncLogDetail);
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
function syncGenericCategoryValue($cat, $keys, $default = '')
{
    foreach ($keys as $key) {
        if (isset($cat[$key]) && $cat[$key] !== '') return $cat[$key];
    }
    return $default;
}

function syncGenericCategoryChildren($cat)
{
    foreach (['sub', 'children', 'child', 'sons', 'items', 'list'] as $key) {
        if (isset($cat[$key]) && is_array($cat[$key])) return $cat[$key];
    }
    return [];
}

function syncFlattenGenericCategories($categories, $parentRemoteId = 0, &$flat = null)
{
    if ($flat === null) $flat = [];
    if (!is_array($categories)) return $flat;
    foreach ($categories as $cat) {
        if (!is_array($cat)) continue;
        $remoteId = intval(syncGenericCategoryValue($cat, ['cid', 'id', 'category_id', 'categoryId', 'groupid', 'group_id'], 0));
        $name = trim((string)syncGenericCategoryValue($cat, ['name', 'title', 'category_name', 'class_name'], ''));
        if ($remoteId <= 0 || $name === '') continue;
        $remotePid = intval(syncGenericCategoryValue($cat, ['upcid', 'pid', 'parent_id', 'parentId', 'pcid'], $parentRemoteId));
        if ($remotePid <= 0 && $parentRemoteId > 0) $remotePid = intval($parentRemoteId);
        $flat[] = [
            'remote_cid' => $remoteId,
            'remote_pid' => $remotePid,
            'name' => $name,
            'sort' => intval(syncGenericCategoryValue($cat, ['sort', 'order', 'rank'], 0)),
            'active' => isset($cat['active']) ? intval($cat['active']) : 1,
            'level' => $remotePid > 0 ? 1 : 0
        ];
        syncFlattenGenericCategories(syncGenericCategoryChildren($cat), $remoteId, $flat);
    }
    return $flat;
}

function syncSortGenericCategories($flat)
{
    $byId = [];
    $children = [];
    foreach ($flat as $index => $class) {
        $remoteCid = intval($class['remote_cid']);
        $remotePid = intval($class['remote_pid']);
        $class['_index'] = $index;
        $byId[$remoteCid] = $class;
        if (!isset($children[$remotePid])) $children[$remotePid] = [];
        $children[$remotePid][] = $remoteCid;
    }

    $sorted = [];
    $visiting = [];
    $visited = [];
    $append = function ($remoteCid) use (&$append, &$byId, &$children, &$sorted, &$visiting, &$visited) {
        $remoteCid = intval($remoteCid);
        if (isset($visited[$remoteCid]) || isset($visiting[$remoteCid]) || !isset($byId[$remoteCid])) return;
        $visiting[$remoteCid] = true;
        $class = $byId[$remoteCid];
        unset($class['_index']);
        $sorted[] = $class;
        $visited[$remoteCid] = true;
        unset($visiting[$remoteCid]);
        if (isset($children[$remoteCid])) {
            foreach ($children[$remoteCid] as $childRemoteCid) $append($childRemoteCid);
        }
    };

    foreach ($flat as $class) {
        $remoteCid = intval($class['remote_cid']);
        $remotePid = intval($class['remote_pid']);
        if ($remotePid <= 0 || !isset($byId[$remotePid])) $append($remoteCid);
    }
    foreach ($flat as $class) $append(intval($class['remote_cid']));
    return $sorted;
}

function syncApplyGenericCategories($shequId, $categories, $config)
{
    global $DB;
    syncEnsureCategoryMapTable();
    $flat = syncSortGenericCategories(syncFlattenGenericCategories($categories));
    $map = [];
    $added = 0;
    $updated = 0;
    foreach ($flat as $class) {
        $remoteCid = intval($class['remote_cid']);
        $remotePid = intval($class['remote_pid']);
        $name = $class['name'];
        $sort = intval($class['sort']);
        $active = intval($class['active']);
        $localPid = $remotePid > 0 && isset($map[$remotePid]) ? intval($map[$remotePid]) : 0;

        $local = null;
        $mapped = $DB->getRow("SELECT * FROM pre_sync_category_map WHERE shequ_id=:sid AND remote_cid=:rcid LIMIT 1", [':sid' => $shequId, ':rcid' => $remoteCid]);
        if ($mapped) $local = $DB->getRow("SELECT * FROM pre_class WHERE cid=:cid LIMIT 1", [':cid' => $mapped['local_cid']]);
        if (!$local) $local = $DB->getRow("SELECT * FROM pre_class WHERE name=:name AND pid=:pid LIMIT 1", [':name' => $name, ':pid' => $localPid]);

        if ($local) {
            $localCid = intval($local['cid']);
            $changes = [];
            $params = [':cid' => $localCid];
            if (!empty($config['sync_sort']) && intval($local['sort']) !== $sort) {
                $changes[] = 'sort=:sort';
                $params[':sort'] = $sort;
            }
            if (!empty($config['sync_class']) && intval($local['pid']) !== $localPid) {
                $changes[] = 'pid=:pid';
                $params[':pid'] = $localPid;
            }
            if (!empty($changes)) {
                $DB->exec("UPDATE pre_class SET " . implode(',', $changes) . " WHERE cid=:cid", $params);
                $updated++;
            }
        } elseif (!empty($config['add_class'])) {
            $DB->exec("INSERT INTO pre_class (`zid`,`pid`,`sort`,`name`,`active`) VALUES (0,:pid,:sort,:name,:active)", [
                ':pid' => $localPid,
                ':sort' => $sort,
                ':name' => $name,
                ':active' => $active
            ]);
            $localCid = intval($DB->lastInsertId());
            $added++;
        } else {
            continue;
        }

        $DB->exec("INSERT INTO pre_sync_category_map (`shequ_id`,`remote_cid`,`remote_pid`,`local_cid`,`name`,`level`,`addtime`,`uptime`) VALUES (:sid,:rcid,:rpid,:lcid,:name,:level,NOW(),NOW())
            ON DUPLICATE KEY UPDATE remote_pid=VALUES(remote_pid),local_cid=VALUES(local_cid),name=VALUES(name),level=VALUES(level),uptime=VALUES(uptime)", [
            ':sid' => $shequId,
            ':rcid' => $remoteCid,
            ':rpid' => $remotePid,
            ':lcid' => $localCid,
            ':name' => $name,
            ':level' => intval($class['level'])
        ]);
        $map[$remoteCid] = $localCid;
    }
    return ['added' => $added, 'updated' => $updated, 'map' => $map, 'flat' => $flat];
}

function syncBuildGenericCategoryLookup($shequId, $categories)
{
    global $DB;
    syncEnsureCategoryMapTable();
    $flat = syncSortGenericCategories(syncFlattenGenericCategories($categories));
    $remoteNames = [];
    $remoteLocal = [];
    foreach ($flat as $class) {
        $remoteCid = intval($class['remote_cid']);
        $remoteNames[$remoteCid] = $class['name'];
    }
    if (!empty($remoteNames)) {
        $ids = array_keys($remoteNames);
        foreach (array_chunk($ids, 300) as $chunk) {
            $placeholders = implode(',', array_fill(0, count($chunk), '?'));
            $params = array_merge([$shequId], $chunk);
            $rs = $DB->query("SELECT remote_cid,local_cid FROM pre_sync_category_map WHERE shequ_id=? AND remote_cid IN ({$placeholders})", $params);
            while ($row = $rs->fetch()) $remoteLocal[intval($row['remote_cid'])] = intval($row['local_cid']);
        }
    }
    return ['names' => $remoteNames, 'local' => $remoteLocal];
}

function syncResolveGenericProductCid($product, $detail, $lookup)
{
    foreach ([$product, $detail] as $row) {
        if (!is_array($row)) continue;
        $remoteCid = intval(syncGenericCategoryValue($row, ['cid', 'category_id', 'categoryId', 'class_id', 'classid'], 0));
        if ($remoteCid > 0 && isset($lookup['local'][$remoteCid])) return intval($lookup['local'][$remoteCid]);
    }
    return 0;
}

function syncCategories($shequ, $config, $task) {
    global $DB, $date, $conf;

    try {
        $categories = third_call($shequ['type'], $shequ, 'class_list');
        if(!is_array($categories)) {
            $task->updateProgress("获取分类列表失败: {$categories}");
            return;
        }
        if(empty($categories)) {
            $task->updateProgress("未获取到分类数据");
            return;
        }

        $syncCategoryResult = syncApplyGenericCategories(intval($shequ['id']), $categories, $config);
        $added = intval($syncCategoryResult['added']);
        $updated = intval($syncCategoryResult['updated']);
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

        // 预加载分类信息，减少重复查询
        $categories = third_call($shequ['type'], $shequ, 'class_list');
        $category_map = [];
        $category_lookup = ['names' => [], 'local' => []];
        if(is_array($categories)) {
            $category_lookup = syncBuildGenericCategoryLookup(intval($shequ['id']), $categories);
            $category_map = $category_lookup['names'];
        }

        // 预加载加价模板，减少重复查询
        $markup_template = null;
        if($config['markup_template']) {
    $markup_template = $DB->getRow("SELECT * FROM pre_price WHERE id=? AND zid=0", [$config['markup_template']]);
        }

        // 预加载默认分类，减少重复查询
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

            // 收集需要获取详情的商品ID
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

            // 批量获取商品详情
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

            // 批量查询现有商品
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

                // 新增商品逻辑
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

                        // 从缓存中获取商品详情
                        $cache_key = $product['id'];
                        if(isset($product_details_cache[$cache_key])) {
                            $cached_detail = $product_details_cache[$cache_key];
                            $product_detail = array_merge($product_detail, $cached_detail);
                        }

                        // 查找对应的本地分类
                        $cid = syncResolveGenericProductCid($product, $product_detail, $category_lookup);
                        $class_name = $product_detail['class_name'] ?? $product_detail['category'] ?? '';

                        if($cid == 0 && !empty($class_name)) {
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
                        $wholesale_prices = syncExtractWholesalePrices($product_detail, $product);
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
                            'prices' => $wholesale_prices !== null ? $wholesale_prices : '',
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

                // 从缓存中获取商品详情
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
                $wholesale_prices = syncExtractWholesalePrices($cached_detail, $product);
                if($wholesale_prices !== null) {
                    $update_data['prices'] = $wholesale_prices;
                }

                // 只有当勾选了价格同步选项时才同步价格
                if($config['sync_price']) {
                    $update_data['price'] = $price;
                    $update_data['cost'] = floatval($cost_price);
                }

                if($config['sync_class']) {
                    $resolvedCid = syncResolveGenericProductCid($product, $cached_detail, $category_lookup);
                    if($resolvedCid > 0 && $resolvedCid != intval($existing_product['cid'])) {
                        $update_data['cid'] = $resolvedCid;
                    }
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

            // 批量插入新商品
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

            // 批量更新商品
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

            // 批量更新站点价格
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
    // 使用 pre_tools 表而非 pre_goods 表
    $query = "SELECT tid, name, close FROM pre_tools WHERE is_curl=2 AND shequ=? AND active=1";
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
            // 下架商品
            if(intval($product['close']) == 0) {
                syncAddPublicToolLog('offline', $product['name'], $product['tid']);
                $DB->update('pre_tools', ['close' => 1, 'uptime' => $date], ['tid' => $product['tid']]);
                $task->updateProgress("下架商品: {$product['name']}");
                $count++;
            }
        } elseif($delete_rule == 2) {
            // 删除商品
            syncAddPublicToolLog('offline', $product['name'], $product['tid']);
            $DB->delete('pre_tools', ['tid' => $product['tid']]);
            $task->updateProgress("删除商品: {$product['name']}");
            $count++;
        }
    }

    return $count;
}



if (!function_exists('q8_add_site_log')) {
function q8_add_site_log($type, $action, $object_id, $summary, $detail = '', $operator = 'system') {
    global $DB, $clientip;
    try {
        $DB->exec("CREATE TABLE IF NOT EXISTS `pre_site_logs` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`type` varchar(32) NOT NULL DEFAULT '',`action` varchar(64) NOT NULL DEFAULT '',`object_id` varchar(64) DEFAULT '',`summary` varchar(255) NOT NULL DEFAULT '',`detail` text,`operator` varchar(64) DEFAULT 'system',`ip` varchar(45) DEFAULT '',`addtime` datetime DEFAULT NULL,PRIMARY KEY (`id`),KEY `type` (`type`),KEY `addtime` (`addtime`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $DB->exec("INSERT INTO pre_site_logs (`type`,`action`,`object_id`,`summary`,`detail`,`operator`,`ip`,`addtime`) VALUES (:type,:action,:object_id,:summary,:detail,:operator,:ip,NOW())", [
            ':type'=>$type, ':action'=>$action, ':object_id'=>$object_id, ':summary'=>$summary, ':detail'=>$detail, ':operator'=>$operator, ':ip'=>$clientip
        ]);
    } catch (Exception $e) {}
}
}

function syncExtractWholesalePrices() {
    $sources = func_get_args();
    $keys = ['prices', 'wholesale_prices', 'wholesale_price', 'wholesale', 'price_list', 'pricelist', 'price_arr', 'pricearr'];
    foreach($sources as $source) {
        if(!is_array($source)) continue;
        foreach($keys as $key) {
            if(!array_key_exists($key, $source)) continue;
            $value = $source[$key];
            if(is_array($value) || is_object($value)) {
                return json_encode($value, JSON_UNESCAPED_UNICODE);
            }
            return trim((string)$value);
        }
    }
    return null;
}

function syncDaishuaFull($shequ, $config, $task) {
    global $DB, $date;

    syncEnsureLogTables();
    syncEnsureCategoryMapTable();
    $lockName = 'q8_daishua_sync_' . intval($shequ['id']);
    $locked = intval($DB->getColumn("SELECT GET_LOCK(:lock_name, 5)", array(':lock_name' => $lockName)));
    if($locked !== 1) {
        throw new Exception('another sync task is running for this supplier');
    }
    register_shutdown_function(function() use ($DB, $lockName) {
        $DB->getColumn("SELECT RELEASE_LOCK(:lock_name)", array(':lock_name' => $lockName));
    });
    syncDeduplicateDaishuaTools(intval($shequ['id']));

    $classes = syncDaishuaApi($shequ, 'classlist');
    $goods = syncDaishuaApi($shequ, 'goodslist');
    if(!is_array($classes) || !isset($classes['data']) || !is_array($classes['data'])) {
        throw new Exception('获取分类列表失败');
    }
    if(!is_array($goods) || !isset($goods['data']) || !is_array($goods['data'])) {
        throw new Exception('获取商品列表失败');
    }

    $classMap = syncDaishuaCategories($shequ['id'], $classes['data'], $task);
    $goodsResult = syncDaishuaProducts($shequ, $config, $goods['data'], $classMap, $task);
    $DB->getColumn("SELECT RELEASE_LOCK(:lock_name)", array(':lock_name' => $lockName));
    return $goodsResult;
}

function syncDaishuaApi($shequ, $act) {
    $base = preg_replace('/^https?:\/\//i', '', $shequ['url']);
    $url = ($shequ['protocol'] == 1 ? 'https://' : 'http://') . $base . '/api.php?act=' . $act;
    $post = '';
    if($act == 'goodslist') {
        $post = 'user=' . urlencode($shequ['username']) . '&pass=' . urlencode($shequ['password']);
    }

    $last = '';
    $cacheFile = '/tmp/sync_daishua_' . intval($shequ['id']) . '_' . $act . '.json';
    if($act == 'goodslist' && is_file($cacheFile) && filemtime($cacheFile) >= time() - 600) {
        $cached = file_get_contents($cacheFile);
        $json = json_decode($cached, true);
        if(is_array($json)) return $json;
    }
    for($try = 1; $try <= 3; $try++) {
        $cmd = 'curl -ks --connect-timeout 30 --max-time 240';
        if($post !== '') {
            $cmd .= ' -X POST -d ' . escapeshellarg($post);
        }
        $cmd .= ' ' . escapeshellarg($url);
        $ret = shell_exec($cmd);
        if(is_string($ret) && $ret !== '') {
            $json = json_decode($ret, true);
            if(is_array($json)) {
                @file_put_contents($cacheFile, $ret);
                if(isset($json['code']) && intval($json['code']) != 0 && intval($json['code']) != 1) {
                    throw new Exception(isset($json['message']) ? $json['message'] : (isset($json['msg']) ? $json['msg'] : 'supplier api error'));
                }
                return $json;
            }
            $last = mb_substr(strip_tags($ret), 0, 120);
        }
        sleep(2);
    }

    $ret = get_curl($url, $post);
    if(is_string($ret) && $ret !== '') {
        $json = json_decode($ret, true);
        if(is_array($json)) {
            if(isset($json['code']) && intval($json['code']) != 0 && intval($json['code']) != 1) {
                throw new Exception(isset($json['message']) ? $json['message'] : (isset($json['msg']) ? $json['msg'] : 'supplier api error'));
            }
            return $json;
        }
        $last = mb_substr(strip_tags($ret), 0, 120);
    }

    if(is_file($cacheFile) && filemtime($cacheFile) >= time() - 21600) {
        $cached = file_get_contents($cacheFile);
        $json = json_decode($cached, true);
        if(is_array($json)) return $json;
    }

    throw new Exception('supplier api empty or invalid: ' . $act . ' ' . $last);
}

function syncEnsureCategoryMapTable() {
    global $DB;
    $DB->exec("CREATE TABLE IF NOT EXISTS `pre_sync_category_map` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `shequ_id` int(11) NOT NULL,
        `remote_cid` int(11) NOT NULL,
        `remote_pid` int(11) NOT NULL DEFAULT 0,
        `local_cid` int(11) NOT NULL,
        `name` varchar(255) NOT NULL,
        `level` tinyint(1) NOT NULL DEFAULT 1,
        `addtime` datetime DEFAULT NULL,
        `uptime` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `shequ_remote` (`shequ_id`,`remote_cid`),
        KEY `local_cid` (`local_cid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function syncEnsureLogTables() {
    global $DB;
    $DB->exec("CREATE TABLE IF NOT EXISTS `pre_toollogs_offline` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `content` longtext NOT NULL,
        `date` date DEFAULT NULL,
        `addtime` datetime DEFAULT NULL,
        `active` tinyint(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `date` (`date`),
        KEY `active` (`active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function syncDeduplicateDaishuaTools($shequId) {
    global $DB;

    $shequId = intval($shequId);
    if($shequId <= 0) return 0;

    $groups = $DB->getAll("SELECT goods_id,COUNT(*) AS total FROM pre_tools WHERE shequ=:shequ AND goods_id>0 GROUP BY goods_id HAVING total>1 LIMIT 5000", array(':shequ' => $shequId));
    if(empty($groups)) return 0;

    $removed = 0;
    foreach($groups as $group) {
        $goodsId = intval($group['goods_id']);
        if($goodsId <= 0) continue;

        $rows = $DB->getAll("SELECT tid,active,close,addtime,uptime FROM pre_tools WHERE shequ=:shequ AND goods_id=:goods_id ORDER BY tid ASC", array(':shequ' => $shequId, ':goods_id' => $goodsId));
        if(count($rows) < 2) continue;

        $tids = array();
        foreach($rows as $row) {
            $tid = intval($row['tid']);
            if($tid > 0) $tids[] = $tid;
        }
        if(count($tids) < 2) continue;

        $orderCounts = syncDaishuaCountReferences('pre_orders', 'tid', $tids);
        $priceCounts = syncDaishuaCountReferences('pre_site_price', 'tid', $tids);
        $keepTid = 0;
        $keepScore = null;

        foreach($rows as $row) {
            $tid = intval($row['tid']);
            $score = intval(isset($orderCounts[$tid]) ? $orderCounts[$tid] : 0) * 100000;
            $score += intval(isset($priceCounts[$tid]) ? $priceCounts[$tid] : 0) * 1000;
            if(intval($row['active']) == 1 && intval($row['close']) == 0) $score += 100;
            $score -= $tid / 1000000;
            if($keepScore === null || $score > $keepScore) {
                $keepScore = $score;
                $keepTid = $tid;
            }
        }

        if($keepTid <= 0) continue;
        $deleteTids = array_values(array_diff($tids, array($keepTid)));
        if(empty($deleteTids)) continue;
        $deleteList = implode(',', array_map('intval', $deleteTids));

        $DB->exec("UPDATE pre_orders SET tid=:keep_tid WHERE tid IN ({$deleteList})", array(':keep_tid' => $keepTid));
        $DB->exec("UPDATE pre_site_price SET tid=:keep_tid WHERE tid IN ({$deleteList})", array(':keep_tid' => $keepTid));
        $DB->exec("DELETE FROM pre_tools WHERE tid IN ({$deleteList})");
        $removed += count($deleteTids);
    }

    return $removed;
}

function syncDaishuaCountReferences($table, $field, $ids) {
    global $DB;

    $safeTable = preg_match('/^pre_[a-z0-9_]+$/i', $table) ? $table : '';
    $safeField = preg_match('/^[a-z0-9_]+$/i', $field) ? $field : '';
    $ids = array_values(array_unique(array_filter(array_map('intval', (array)$ids))));
    if($safeTable === '' || $safeField === '' || empty($ids)) return array();

    $result = array();
    foreach(array_chunk($ids, 800) as $chunk) {
        $list = implode(',', $chunk);
        try {
            $rs = $DB->query("SELECT {$safeField} AS ref_id,COUNT(*) AS total FROM {$safeTable} WHERE {$safeField} IN ({$list}) GROUP BY {$safeField}");
            while($row = $rs->fetch()) {
                $result[intval($row['ref_id'])] = intval($row['total']);
            }
        } catch (Exception $e) {
            return array();
        }
    }
    return $result;
}

function syncNormalizeImage($shequ, $path) {
    if(empty($path)) return '';
    if(substr($path, 0, 4) == 'http' || substr($path, 0, 2) == '//') return $path;
    $base = preg_replace('/^https?:\/\//i', '', $shequ['url']);
    return ($shequ['protocol'] == 1 ? 'https://' : 'http://') . $base . '/' . ltrim($path, '/');
}

function syncResolveDaishuaCategory($config, $classMap, $remoteCid, $currentCid = 0) {
    $remoteCid = intval($remoteCid);
    $currentCid = intval($currentCid);
    $syncClass = !empty($config['sync_class']);

    if($remoteCid > 0 && isset($classMap[$remoteCid])) {
        return ['cid' => intval($classMap[$remoteCid]), 'missing' => false];
    }

    if($syncClass) {
        return ['cid' => 0, 'missing' => $remoteCid > 0];
    }

    return ['cid' => $currentCid, 'missing' => false];
}

function syncRepairDaishuaCategoryAssignments($shequ, $config, $remoteGoods, $classMap, $task) {
    global $DB, $date;

    if(empty($config['sync_class'])) {
        return ['fixed' => 0, 'to_unclassified' => 0, 'samples' => []];
    }

    $targets = [];
    foreach($remoteGoods as $g) {
        if(!isset($g['tid'])) continue;
        $gid = intval($g['tid']);
        if($gid < 1) continue;
        $target = syncResolveDaishuaCategory($config, $classMap, isset($g['cid']) ? $g['cid'] : 0, 0);
        $targets[$gid] = [
            'cid' => intval($target['cid']),
            'missing' => !empty($target['missing'])
        ];
    }

    if(empty($targets)) {
        return ['fixed' => 0, 'to_unclassified' => 0, 'samples' => []];
    }

    $fixed = 0;
    $toUnclassified = 0;
    $samples = [];
    $pendingByTarget = [];

    foreach(array_chunk(array_keys($targets), 800) as $chunk) {
        $placeholders = implode(',', array_fill(0, count($chunk), '?'));
        $params = array_merge([intval($shequ['id'])], $chunk);
        $rs = $DB->query("SELECT tid,goods_id,cid,name FROM pre_tools WHERE shequ=? AND goods_id IN ({$placeholders})", $params);
        while($row = $rs->fetch()) {
            $gid = intval($row['goods_id']);
            if(!isset($targets[$gid])) continue;
            $targetCid = intval($targets[$gid]['cid']);
            $currentCid = intval($row['cid']);
            if($currentCid === $targetCid) continue;

            $fixed++;
            if($targetCid === 0) $toUnclassified++;
            if(!isset($pendingByTarget[$targetCid])) {
                $pendingByTarget[$targetCid] = [];
            }
            $pendingByTarget[$targetCid][$gid] = $gid;

            if(count($samples) < 20) {
                $samples[] = [
                    'tid' => intval($row['tid']),
                    'goods_id' => $gid,
                    'name' => function_exists('mb_substr') ? mb_substr((string)$row['name'], 0, 80, 'UTF-8') : substr((string)$row['name'], 0, 80),
                    'fields' => ['cid']
                ];
            }
        }
    }

    foreach($pendingByTarget as $targetCid => $goodsIds) {
        if(empty($goodsIds)) continue;
        foreach(array_chunk(array_values($goodsIds), 500) as $goodsChunk) {
            $goodsChunk = array_values(array_filter(array_map('intval', $goodsChunk)));
            if(empty($goodsChunk)) continue;
            $goodsList = implode(',', $goodsChunk);
            $DB->exec("UPDATE pre_tools SET cid=:targetCid,uptime=:uptime WHERE shequ=:shequId AND goods_id IN ({$goodsList}) AND cid<>:compareCid", [
                ':targetCid' => intval($targetCid),
                ':uptime' => $date,
                ':shequId' => intval($shequ['id']),
                ':compareCid' => intval($targetCid)
            ]);
        }
    }

    return ['fixed' => $fixed, 'to_unclassified' => $toUnclassified, 'samples' => $samples];
}

function syncDaishuaClassParentId($class) {
    foreach(['upcid', 'pid', 'parent_id', 'parentId', 'pcid'] as $key) {
        if(isset($class[$key]) && intval($class[$key]) > 0) {
            return intval($class[$key]);
        }
    }
    return 0;
}

function syncDaishuaCategories($shequId, $remoteClasses, $task) {
    global $DB, $date;

    usort($remoteClasses, function($a, $b) {
        $ap = syncDaishuaClassParentId($a);
        $bp = syncDaishuaClassParentId($b);
        if($ap == 0 && $bp != 0) return -1;
        if($ap != 0 && $bp == 0) return 1;
        $as = intval(isset($a['sort']) ? $a['sort'] : 0);
        $bs = intval(isset($b['sort']) ? $b['sort'] : 0);
        if($as == $bs) return intval($a['cid']) - intval($b['cid']);
        return $as - $bs;
    });

    $map = [];
    $remoteIds = [];
    $added = 0;
    $updated = 0;
    $deferred = [];

    for($pass = 0; $pass < 3; $pass++) {
        foreach($remoteClasses as $class) {
            $remoteCid = intval($class['cid']);
            if(isset($map[$remoteCid])) continue;
            $remotePid = syncDaishuaClassParentId($class);
            if($remotePid > 0 && !isset($map[$remotePid])) {
                $deferred[$remoteCid] = $class;
                continue;
            }
            $remoteIds[] = $remoteCid;
            $localPid = $remotePid > 0 ? intval($map[$remotePid]) : 0;
            $name = trim($class['name']);
            $sort = intval(isset($class['sort']) ? $class['sort'] : 0);
            $active = isset($class['active']) ? intval($class['active']) : 1;
            $mapped = $DB->getRow("SELECT * FROM pre_sync_category_map WHERE shequ_id=:sid AND remote_cid=:rcid LIMIT 1", [':sid'=>$shequId, ':rcid'=>$remoteCid]);
            $local = null;
            if($mapped) {
                $local = $DB->getRow("SELECT * FROM pre_class WHERE cid=:cid LIMIT 1", [':cid'=>$mapped['local_cid']]);
            }
            if(!$local) {
                $local = $DB->getRow("SELECT * FROM pre_class WHERE name=:name AND pid=:pid LIMIT 1", [':name'=>$name, ':pid'=>$localPid]);
            }
            if($local) {
                $localCid = intval($local['cid']);
                $classChanged = (string)$local['name'] !== (string)$name
                    || intval($local['pid']) !== intval($localPid)
                    || intval($local['sort']) !== intval($sort)
                    || intval($local['active']) !== intval($active);
                if($classChanged) {
                    $DB->exec("UPDATE pre_class SET name=:name,pid=:pid,sort=:sort,active=:active WHERE cid=:cid", [
                        ':name'=>$name, ':pid'=>$localPid, ':sort'=>$sort, ':active'=>$active, ':cid'=>$localCid
                    ]);
                    $updated++;
                }
            } else {
                $DB->exec("INSERT INTO pre_class (`zid`,`pid`,`sort`,`name`,`active`) VALUES (1,:pid,:sort,:name,:active)", [
                    ':pid'=>$localPid, ':sort'=>$sort, ':name'=>$name, ':active'=>$active
                ]);
                $localCid = $DB->lastInsertId();
                $added++;
            }
            $DB->exec("INSERT INTO pre_sync_category_map (`shequ_id`,`remote_cid`,`remote_pid`,`local_cid`,`name`,`level`,`addtime`,`uptime`) VALUES (:sid,:rcid,:rpid,:lcid,:name,:level,:now,:now)
                ON DUPLICATE KEY UPDATE remote_pid=VALUES(remote_pid),local_cid=VALUES(local_cid),name=VALUES(name),level=VALUES(level),uptime=VALUES(uptime)", [
                ':sid'=>$shequId, ':rcid'=>$remoteCid, ':rpid'=>$remotePid, ':lcid'=>$localCid, ':name'=>$name,
                ':level'=>$remotePid > 0 ? 2 : 1, ':now'=>$date
            ]);
            $map[$remoteCid] = $localCid;
        }
    }

    if(!empty($remoteIds)) {
        $currentLocalIds = array_values(array_unique(array_map('intval', array_values($map))));
        $placeholders = implode(',', array_fill(0, count($remoteIds), '?'));
        $params = array_merge([$shequId], $remoteIds);
        $rs = $DB->query("SELECT remote_cid,local_cid FROM pre_sync_category_map WHERE shequ_id=? AND remote_cid NOT IN ({$placeholders})", $params);
        $staleRemoteIds = [];
        while($row = $rs->fetch()) {
            $localCid = intval($row['local_cid']);
            $staleRemoteIds[] = intval($row['remote_cid']);
            if(!in_array($localCid, $currentLocalIds, true)) {
                $DB->exec("UPDATE pre_class SET active=0 WHERE cid=:cid", [':cid'=>$localCid]);
            }
        }
        if(!empty($staleRemoteIds)) {
            $stalePlaceholders = implode(',', array_fill(0, count($staleRemoteIds), '?'));
            $DB->query("DELETE FROM pre_sync_category_map WHERE shequ_id=? AND remote_cid IN ({$stalePlaceholders})", array_merge([$shequId], $staleRemoteIds));
        }
    }

    $task->updateProgress("分类同步完成: 新增{$added}, 更新{$updated}");
    return $map;
}

function syncDaishuaProducts($shequ, $config, $remoteGoods, $classMap, $task) {
    global $DB, $date;

    $synced = 0;
    $updated = 0;
    $deleted = 0;
    $categoryFixed = 0;
    $categoryToUnclassified = 0;
    $scanned = 0;
    $fieldCounts = [];
    $updateSamples = [];
    $seen = [];
    $priceTemplate = null;
    if(!empty($config['markup_template'])) {
        $priceTemplate = $DB->getRow("SELECT * FROM pre_price WHERE id=:id AND zid=0 LIMIT 1", [':id'=>$config['markup_template']]);
    }

    $ids = [];
    foreach($remoteGoods as $g) {
        if(isset($g['tid'])) {
            $gid = intval($g['tid']);
            if($gid > 0) $ids[$gid] = $gid;
        }
    }
    $ids = array_values($ids);
    $existing = [];
    if(!empty($ids)) {
        foreach(array_chunk($ids, 800) as $chunk) {
            $placeholders = implode(',', array_fill(0, count($chunk), '?'));
            $params = array_merge([intval($shequ['id'])], $chunk);
            $rs = $DB->query("SELECT * FROM pre_tools WHERE shequ=? AND goods_id IN ({$placeholders})", $params);
            while($row = $rs->fetch()) $existing[intval($row['goods_id'])] = $row;
        }
    }

    usort($remoteGoods, function($a, $b) {
        $ac = intval(isset($a['cid']) ? $a['cid'] : 0);
        $bc = intval(isset($b['cid']) ? $b['cid'] : 0);
        if($ac != $bc) return $ac - $bc;
        $as = intval(isset($a['sort']) ? $a['sort'] : 0);
        $bs = intval(isset($b['sort']) ? $b['sort'] : 0);
        if($as == $bs) return intval($a['tid']) - intval($b['tid']);
        return $as - $bs;
    });

    foreach($remoteGoods as $g) {
        if(!isset($g['tid'])) continue;
        $gid = intval($g['tid']);
        if($gid <= 0) continue;
        if(isset($seen[$gid])) continue;
        $seen[$gid] = $gid;
        $scanned++;
        $old = isset($existing[$gid]) ? $existing[$gid] : null;
        $remoteCid = intval(isset($g['cid']) ? $g['cid'] : 0);
        $categoryTarget = syncResolveDaishuaCategory($config, $classMap, $remoteCid, $old ? intval($old['cid']) : 0);
        $localCid = intval($categoryTarget['cid']);
        $remoteClose = intval(isset($g['close']) ? $g['close'] : 0);
        $remoteActive = intval(isset($g['active']) ? $g['active'] : 1);
        if($remoteActive != 1) $remoteClose = 1;
        $remotePrice = floatval(isset($g['price']) ? $g['price'] : 0);
        $templateId = intval(isset($config['markup_template']) ? $config['markup_template'] : 0);
        // Keep the upstream base price in pre_tools; the template is applied later by the pricing layer.
        $priceSet = syncBuildStoredPriceSet($remotePrice, $templateId);
        $name = isset($g['name']) ? $g['name'] : '';
        $shopimg = syncNormalizeImage($shequ, isset($g['shopimg']) ? $g['shopimg'] : '');
        $remotePrices = syncExtractWholesalePrices($g);
        $isFakaGoods = !empty($g['isfaka']) || ($old && intval($old['goods_type']) == 1);
        $remoteMulti = $isFakaGoods ? 1 : intval(isset($g['multi']) ? $g['multi'] : 0);
        $value = intval(isset($g['value']) ? $g['value'] : 1);
        if($value < 1) $value = intval(isset($g['min']) ? $g['min'] : 1);
        if($value < 1) $value = 1;

        if($old) {
            $fields = ['uptime=:uptime','stock=:stock','close=:close','active=1'];
            $params = [
                ':uptime'=>$date,
                ':stock'=>isset($g['stock']) && $g['stock'] !== '' ? intval($g['stock']) : 9999,
                ':close'=>$remoteClose,
                ':tid'=>$old['tid']
            ];
            if(!empty($config['sync_goods_sort']) && isset($g['sort'])) { $fields[] = 'sort=:sort'; $params[':sort'] = intval($g['sort']); }
            if(!empty($config['sync_class'])) { $fields[] = 'cid=:cid'; $params[':cid'] = $localCid; }
            if(!empty($config['sync_name'])) { $fields[] = 'name=:name'; $params[':name'] = $name; }
            if(!empty($config['sync_price'])) { $fields[] = 'price=:price'; $params[':price'] = $priceSet['price']; }
            if($remotePrices !== null) { $fields[] = 'prices=:prices'; $params[':prices'] = $remotePrices; }
            if(!empty($config['sync_cost'])) { $fields[] = 'cost=:cost'; $fields[] = 'cost2=:cost2'; $params[':cost'] = $priceSet['cost']; $params[':cost2'] = $priceSet['cost2']; }
            if(!empty($config['sync_desc'])) { $fields[] = '`desc`=:desc'; $params[':desc'] = isset($g['desc']) ? $g['desc'] : ''; }
            if(!empty($config['sync_image'])) { $fields[] = 'shopimg=:shopimg'; $params[':shopimg'] = $shopimg; }
            if(!empty($config['sync_workorder'])) {
                $fields[] = 'input=:input'; $fields[] = 'inputs=:inputs'; $fields[] = 'alert=:alert';
                $params[':input'] = isset($g['input']) ? $g['input'] : '';
                $params[':inputs'] = isset($g['inputs']) ? $g['inputs'] : '';
                $params[':alert'] = isset($g['alert']) ? $g['alert'] : '';
            }
            $fields[] = 'value=:value'; $fields[] = 'min=:min'; $fields[] = 'max=:max';
            $fields[] = '`repeat`=:repeat'; $fields[] = 'multi=:multi'; $fields[] = 'validate=:validate'; $fields[] = 'valiserv=:valiserv';
            $params[':value'] = $value;
            $params[':min'] = intval(isset($g['min']) ? $g['min'] : 1);
            if($params[':min'] < 1) $params[':min'] = 1;
            $params[':max'] = intval(isset($g['max']) ? $g['max'] : 0);
            $params[':repeat'] = intval(isset($g['repeat']) ? $g['repeat'] : 0);
            $params[':multi'] = $remoteMulti;
            $params[':validate'] = intval(isset($g['validate']) ? $g['validate'] : 0);
            $params[':valiserv'] = isset($g['valiserv']) ? $g['valiserv'] : '';
            $changedFields = [];
            if(intval($old['active']) != 1) $changedFields[] = 'active';
            $compareMap = [
                ':stock'=>'stock', ':close'=>'close', ':sort'=>'sort', ':cid'=>'cid', ':name'=>'name',
                ':price'=>'price', ':cost'=>'cost', ':cost2'=>'cost2', ':desc'=>'desc', ':shopimg'=>'shopimg',
                ':prices'=>'prices', ':input'=>'input', ':inputs'=>'inputs', ':alert'=>'alert', ':value'=>'value', ':min'=>'min',
                ':max'=>'max', ':repeat'=>'repeat', ':multi'=>'multi', ':validate'=>'validate', ':valiserv'=>'valiserv'
            ];
            foreach($compareMap as $paramKey => $fieldName) {
                if(!array_key_exists($paramKey, $params)) continue;
                $oldValue = isset($old[$fieldName]) ? (string)$old[$fieldName] : '';
                $newValue = (string)$params[$paramKey];
                if(in_array($fieldName, ['price','cost','cost2'], true)) {
                    if(abs(floatval($oldValue) - floatval($newValue)) > 0.00001) $changedFields[] = $fieldName;
                } else {
                    if($oldValue !== $newValue) $changedFields[] = $fieldName;
                }
            }
            if(!empty($changedFields)) {
                $DB->exec("UPDATE pre_tools SET " . implode(',', $fields) . " WHERE tid=:tid", $params);
                if(intval($old['close']) == 1 && $remoteClose == 0) syncAddPublicToolLog('online', $name, $old['tid']);
                if(intval($old['close']) == 0 && $remoteClose == 1) syncAddPublicToolLog('offline', $name, $old['tid']);
                $updated++;
                $changedFields = array_values(array_unique($changedFields));
                foreach($changedFields as $fieldName) {
                    if(!isset($fieldCounts[$fieldName])) $fieldCounts[$fieldName] = 0;
                    $fieldCounts[$fieldName]++;
                }
                if(count($updateSamples) < 20) {
                    $updateSamples[] = ['tid'=>intval($old['tid']), 'goods_id'=>$gid, 'name'=>mb_substr($name, 0, 80, 'UTF-8'), 'fields'=>$changedFields];
                }
            }
        } else {
            if(empty($config['add_goods'])) continue;
            if($localCid <= 0 && empty($config['sync_class'])) {
                $localCid = intval($DB->getColumn("SELECT cid FROM pre_class WHERE active=1 ORDER BY pid ASC,sort ASC,cid ASC LIMIT 1"));
            }
            $insert = [
                ':sort'=>intval(isset($g['sort']) ? $g['sort'] : 0),
                ':cid'=>$localCid,
                ':name'=>$name,
                ':price'=>$priceSet['price'],
                ':cost'=>$priceSet['cost'],
                ':cost2'=>$priceSet['cost2'],
                ':prid'=>$templateId,
                ':prices'=>$remotePrices !== null ? $remotePrices : '',
                ':input'=>isset($g['input']) ? $g['input'] : '',
                ':inputs'=>isset($g['inputs']) ? $g['inputs'] : '',
                ':desc'=>isset($g['desc']) ? $g['desc'] : '',
                ':alert'=>isset($g['alert']) ? $g['alert'] : '',
                ':shopimg'=>$shopimg,
                ':value'=>$value,
                ':shequ'=>intval($shequ['id']),
                ':goods_id'=>$gid,
                ':goods_type'=>!empty($g['isfaka']) ? 1 : 0,
                ':repeat'=>intval(isset($g['repeat']) ? $g['repeat'] : 0),
                ':multi'=>$remoteMulti,
                ':min'=>max(1, intval(isset($g['min']) ? $g['min'] : 1)),
                ':max'=>intval(isset($g['max']) ? $g['max'] : 0),
                ':validate'=>intval(isset($g['validate']) ? $g['validate'] : 0),
                ':valiserv'=>isset($g['valiserv']) ? $g['valiserv'] : '',
                ':close'=>$remoteClose,
                ':active'=>1,
                ':stock'=>isset($g['stock']) && $g['stock'] !== '' ? intval($g['stock']) : 9999,
                ':addtime'=>$date,
                ':uptime'=>$date
            ];
            $DB->exec("INSERT INTO pre_tools (`sort`,`cid`,`name`,`price`,`cost`,`cost2`,`prid`,`prices`,`input`,`inputs`,`desc`,`alert`,`shopimg`,`value`,`is_curl`,`curl`,`shequ`,`goods_id`,`goods_type`,`goods_param`,`repeat`,`multi`,`min`,`max`,`validate`,`valiserv`,`close`,`active`,`stock`,`addtime`,`uptime`) VALUES (:sort,:cid,:name,:price,:cost,:cost2,:prid,:prices,:input,:inputs,:desc,:alert,:shopimg,:value,2,'',:shequ,:goods_id,:goods_type,'',:repeat,:multi,:min,:max,:validate,:valiserv,:close,:active,:stock,:addtime,:uptime)", $insert);
            $newTid = intval($DB->lastInsertId());
            if($newTid <= 0) {
                $newTid = intval($DB->getColumn("SELECT tid FROM pre_tools WHERE shequ=:shequ AND goods_id=:goods_id ORDER BY tid DESC LIMIT 1", [':shequ'=>intval($shequ['id']), ':goods_id'=>$gid]));
            }
            if($newTid > 0) {
                $newRow = $DB->getRow("SELECT * FROM pre_tools WHERE tid=:tid LIMIT 1", [':tid'=>$newTid]);
                if($newRow) $existing[$gid] = $newRow;
            }
            if($remoteClose == 0) syncAddPublicToolLog('online', $name, $newTid);
            $synced++;
        }
    }

    $categoryRepair = syncRepairDaishuaCategoryAssignments($shequ, $config, $remoteGoods, $classMap, $task);
    $categoryFixed = intval($categoryRepair['fixed']);
    $categoryToUnclassified = intval($categoryRepair['to_unclassified']);
    if($categoryFixed > 0) {
        if(!isset($fieldCounts['cid'])) $fieldCounts['cid'] = 0;
        $fieldCounts['cid'] += $categoryFixed;
        if(!empty($categoryRepair['samples'])) {
            foreach($categoryRepair['samples'] as $sample) {
                if(count($updateSamples) >= 20) break;
                $updateSamples[] = $sample;
            }
        }
    }

    if(!empty($seen) && intval($config['delete_rule']) > 0) {
        foreach(array_chunk($seen, 800) as $i => $chunk) {
            if($i == 0) {
                $placeholders = implode(',', array_fill(0, count($seen), '?'));
                $params = array_merge([intval($shequ['id'])], $seen);
                $rs = $DB->query("SELECT tid,name,close FROM pre_tools WHERE shequ=? AND goods_id NOT IN ({$placeholders})", $params);
                while($row = $rs->fetch()) {
                    if(intval($config['delete_rule']) == 2) {
                        syncAddPublicToolLog('offline', $row['name'], $row['tid']);
                        $DB->exec("DELETE FROM pre_tools WHERE tid=:tid", [':tid'=>$row['tid']]);
                        $deleted++;
                    } else {
                        if(intval($row['close']) == 0) {
                            syncAddPublicToolLog('offline', $row['name'], $row['tid']);
                            $DB->exec("UPDATE pre_tools SET close=1,uptime=:uptime WHERE tid=:tid", [':uptime'=>$date, ':tid'=>$row['tid']]);
                            $deleted++;
                        }
                    }
                }
            }
        }
    }

    $task->updateProgress("商品同步完成：已检索 {$scanned} 条，实际新增 {$synced} 条，实际更新 {$updated} 条，分类修正 {$categoryFixed} 条，转未分类 {$categoryToUnclassified} 条，实际删除 {$deleted} 条");
    return ['scanned'=>$scanned, 'synced'=>$synced, 'updated'=>$updated, 'deleted'=>$deleted, 'field_counts'=>$fieldCounts, 'samples'=>$updateSamples];
}

function syncApplyPriceTemplate($remotePrice, $template) {
    $price = round($remotePrice, 2);
    $cost = round($remotePrice, 2);
    $cost2 = 0;
    if($template) {
        if(intval($template['kind']) == 1) {
            $price = round($remotePrice + floatval($template['p_0']), 2);
            $cost = round($remotePrice + floatval($template['p_1']), 2);
            $cost2 = round($remotePrice + floatval($template['p_2']), 2);
        } elseif(floatval($template['p_0']) > 0) {
            $price = round($remotePrice * floatval($template['p_0']), 2);
            $cost = round($remotePrice * floatval($template['p_1']), 2);
            $cost2 = round($remotePrice * floatval($template['p_2']), 2);
        }
    }
    return ['price'=>$price, 'cost'=>$cost, 'cost2'=>$cost2];
}

function syncBuildStoredPriceSet($remotePrice, $templateId = 0) {
    $basePrice = round(floatval($remotePrice), 2);
    if($basePrice < 0) {
        $basePrice = 0;
    }
    if(intval($templateId) > 0) {
        return ['price'=>$basePrice, 'cost'=>0, 'cost2'=>0];
    }
    return ['price'=>$basePrice, 'cost'=>$basePrice, 'cost2'=>0];
}

function syncAddPublicToolLog($type, $name, $tid = 0) {
    if(function_exists('q8_toollog_append_group')) {
        q8_toollog_append_group($type, $name, $tid);
        return;
    }
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
