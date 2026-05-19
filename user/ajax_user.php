<?php
include("../includes/common.php");

$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

@header('Content-Type: application/json; charset=UTF-8');

if (!checkRefererHost()) {
    exit('{"code":403}');
}
if (!$islogin2) {
    exit('{"code":-1,"msg":"未登录"}');
}

if (!function_exists('q8_user_batch_adjust_value')) {
    function q8_user_batch_adjust_value($currentValue, $operation, $batchType, $value)
    {
        if ($batchType === 'fixed') {
            return round($operation === 'add' ? $currentValue + $value : $currentValue - $value, 2);
        }
        $percentage = $value / 100;
        return round($operation === 'add' ? $currentValue * (1 + $percentage) : $currentValue * (1 - $percentage), 2);
    }
}

if (!function_exists('q8_user_batch_price_type_label')) {
    function q8_user_batch_price_type_label($priceType)
    {
        $labels = array(
            'price' => '销售价格',
            'cost2' => '下级专业版价格',
            'cost' => '下级普通版价格',
        );
        return isset($labels[$priceType]) ? $labels[$priceType] : '价格';
    }
}

if (!function_exists('q8_user_encode_price_history_payload')) {
    function q8_user_encode_price_history_payload($priceData, $affectedCount = 0)
    {
        return json_encode(array(
            'items' => is_array($priceData) ? $priceData : array(),
            'affected_count' => intval($affectedCount)
        ));
    }
}

if (!function_exists('q8_user_decode_price_history_payload')) {
    function q8_user_decode_price_history_payload($raw)
    {
        $decoded = json_decode($raw, true);
        if (is_array($decoded) && isset($decoded['items']) && is_array($decoded['items'])) {
            return array(
                'items' => $decoded['items'],
                'affected_count' => isset($decoded['affected_count']) ? intval($decoded['affected_count']) : count($decoded['items']),
                'accurate_count' => true
            );
        }
        if (!is_array($decoded)) {
            $decoded = @unserialize($raw);
        }
        if (is_array($decoded)) {
            return array(
                'items' => $decoded,
                'affected_count' => count($decoded),
                'accurate_count' => false
            );
        }
        return array(
            'items' => array(),
            'affected_count' => 0,
            'accurate_count' => false
        );
    }
}

if (!function_exists('q8_user_site_price_rule_validate')) {
    function q8_user_site_price_rule_validate($name, $p2, $p1, $p0)
    {
        if ($name === '' || trim((string)$p2) === '' || trim((string)$p1) === '' || trim((string)$p0) === '') {
            return '请确保各项不能为空';
        }
        $p2 = floatval($p2);
        $p1 = floatval($p1);
        $p0 = floatval($p0);
        if ($p2 > $p1) {
            return '下级专业版加价不能高于下级普通版加价';
        }
        if ($p2 > $p0) {
            return '下级专业版加价不能高于销售价格加价';
        }
        if ($p1 > $p0) {
            return '下级普通版加价不能高于销售价格加价';
        }
        return '';
    }
}

if (!function_exists('q8_user_site_price_rule_duplicate')) {
    function q8_user_site_price_rule_duplicate($zid, $name, $excludeId = 0)
    {
        global $DB;

        if (intval($excludeId) > 0) {
            return $DB->getRow(
                "SELECT id FROM pre_price WHERE zid=:zid AND id<>:id AND name=:name LIMIT 1",
                array(':zid' => intval($zid), ':id' => intval($excludeId), ':name' => $name)
            );
        }

        return $DB->getRow(
            "SELECT id FROM pre_price WHERE zid=:zid AND name=:name LIMIT 1",
            array(':zid' => intval($zid), ':name' => $name)
        );
    }
}

if (in_array($act, array('get_site_price_rule', 'add_site_price_rule', 'edit_site_price_rule', 'delete_site_price_rule', 'set_site_price_rule'), true)) {
    if ($userrow['power'] <= 0) {
        exit(json_encode(array('code' => -1, 'msg' => '没有权限使用此功能'), JSON_UNESCAPED_UNICODE));
    }

    if ($act === 'get_site_price_rule') {
        $id = intval(isset($_GET['id']) ? $_GET['id'] : 0);
        $row = q8_price_rule_fetch_row($id, $userrow['zid']);
        if (!$row) {
            exit(json_encode(array('code' => -1, 'msg' => '模板不存在'), JSON_UNESCAPED_UNICODE));
        }
        $row['code'] = 0;
        exit(json_encode($row, JSON_UNESCAPED_UNICODE));
    }

    if ($act === 'add_site_price_rule') {
        $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
        $kind = intval(isset($_POST['kind']) ? $_POST['kind'] : 0);
        $raw_p_2 = isset($_POST['p_2']) ? $_POST['p_2'] : '';
        $raw_p_1 = isset($_POST['p_1']) ? $_POST['p_1'] : '';
        $raw_p_0 = isset($_POST['p_0']) ? $_POST['p_0'] : '';
        $error = q8_user_site_price_rule_validate($name, $raw_p_2, $raw_p_1, $raw_p_0);
        if ($error !== '') {
            exit(json_encode(array('code' => -1, 'msg' => $error), JSON_UNESCAPED_UNICODE));
        }
        if (q8_user_site_price_rule_duplicate($userrow['zid'], $name)) {
            exit(json_encode(array('code' => -1, 'msg' => '模板名称已存在'), JSON_UNESCAPED_UNICODE));
        }

        $result = $DB->exec(
            "INSERT INTO pre_price (zid,kind,name,p_0,p_1,p_2) VALUES (:zid,:kind,:name,:p0,:p1,:p2)",
            array(
                ':zid' => intval($userrow['zid']),
                ':kind' => $kind,
                ':name' => $name,
                ':p0' => floatval($raw_p_0),
                ':p1' => floatval($raw_p_1),
                ':p2' => floatval($raw_p_2),
            )
        );
        if ($result !== false) {
            $CACHE->clear('pricerules');
            exit(json_encode(array('code' => 0, 'msg' => '分站加价模板新增成功'), JSON_UNESCAPED_UNICODE));
        }
        exit(json_encode(array('code' => -1, 'msg' => '分站加价模板新增失败：' . $DB->error()), JSON_UNESCAPED_UNICODE));
    }

    if ($act === 'edit_site_price_rule') {
        $id = intval(isset($_POST['prid']) ? $_POST['prid'] : 0);
        if (!q8_price_rule_exists_for_owner($id, $userrow['zid'])) {
            exit(json_encode(array('code' => -1, 'msg' => '模板不存在或无权操作'), JSON_UNESCAPED_UNICODE));
        }

        $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
        $kind = intval(isset($_POST['kind']) ? $_POST['kind'] : 0);
        $raw_p_2 = isset($_POST['p_2']) ? $_POST['p_2'] : '';
        $raw_p_1 = isset($_POST['p_1']) ? $_POST['p_1'] : '';
        $raw_p_0 = isset($_POST['p_0']) ? $_POST['p_0'] : '';
        $error = q8_user_site_price_rule_validate($name, $raw_p_2, $raw_p_1, $raw_p_0);
        if ($error !== '') {
            exit(json_encode(array('code' => -1, 'msg' => $error), JSON_UNESCAPED_UNICODE));
        }
        if (q8_user_site_price_rule_duplicate($userrow['zid'], $name, $id)) {
            exit(json_encode(array('code' => -1, 'msg' => '模板名称已存在'), JSON_UNESCAPED_UNICODE));
        }

        $result = $DB->exec(
            "UPDATE pre_price SET kind=:kind,name=:name,p_0=:p0,p_1=:p1,p_2=:p2 WHERE id=:id AND zid=:zid",
            array(
                ':kind' => $kind,
                ':name' => $name,
                ':p0' => floatval($raw_p_0),
                ':p1' => floatval($raw_p_1),
                ':p2' => floatval($raw_p_2),
                ':id' => $id,
                ':zid' => intval($userrow['zid']),
            )
        );
        if ($result !== false) {
            $CACHE->clear('pricerules');
            exit(json_encode(array('code' => 0, 'msg' => '分站加价模板修改成功'), JSON_UNESCAPED_UNICODE));
        }
        exit(json_encode(array('code' => -1, 'msg' => '分站加价模板修改失败：' . $DB->error()), JSON_UNESCAPED_UNICODE));
    }

    if ($act === 'delete_site_price_rule') {
        $id = intval(isset($_POST['id']) ? $_POST['id'] : 0);
        if (!q8_price_rule_exists_for_owner($id, $userrow['zid'])) {
            exit(json_encode(array('code' => -1, 'msg' => '模板不存在或无权操作'), JSON_UNESCAPED_UNICODE));
        }
        $DB->beginTransaction();
        try {
            if (intval($userrow['site_prid']) === $id) {
                $DB->exec("UPDATE pre_site SET site_prid=0 WHERE zid=:zid", array(':zid' => intval($userrow['zid'])));
            }
            $DB->exec(
                "DELETE FROM pre_price WHERE id=:id AND zid=:zid LIMIT 1",
                array(':id' => $id, ':zid' => intval($userrow['zid']))
            );
            $DB->commit();
            $CACHE->clear('pricerules');
            exit(json_encode(array('code' => 0, 'msg' => '分站加价模板删除成功'), JSON_UNESCAPED_UNICODE));
        } catch (Exception $e) {
            $DB->rollback();
            exit(json_encode(array('code' => -1, 'msg' => '分站加价模板删除失败：' . $e->getMessage()), JSON_UNESCAPED_UNICODE));
        }
    }

    $id = intval(isset($_POST['id']) ? $_POST['id'] : 0);
    if ($id < 0) {
        $id = 0;
    }
    if ($id > 0 && !q8_price_rule_exists_for_owner($id, $userrow['zid'])) {
        exit(json_encode(array('code' => -1, 'msg' => '模板不存在或无权使用'), JSON_UNESCAPED_UNICODE));
    }
    $result = $DB->exec(
        "UPDATE pre_site SET site_prid=:site_prid WHERE zid=:zid",
        array(':site_prid' => $id, ':zid' => intval($userrow['zid']))
    );
    if ($result !== false) {
        exit(json_encode(array('code' => 0, 'msg' => '当前分站加价模板已更新'), JSON_UNESCAPED_UNICODE));
    }
    exit(json_encode(array('code' => -1, 'msg' => '当前分站加价模板更新失败：' . $DB->error()), JSON_UNESCAPED_UNICODE));
}

switch ($act) {
    case 'refund':
        // 禁用用户自助退款功能
        exit('{"code":-1,"msg":"当前站点未开启用户自助退款功能"}');
        break;
    case 'setpwd':
        if (substr($userrow['user'], 0, 3) != 'qq_') {
            exit('{"code":-1,"msg":"请勿重复提交"}');
        }
        $user = trim(htmlspecialchars(strip_tags(daddslashes($_POST['user']))));
        $pwd  = trim(htmlspecialchars(strip_tags(daddslashes($_POST['pwd']))));
        if (!preg_match('/^[a-zA-Z0-9\x7f-\xff]+$/', $user)) {
            exit('{"code":-1,"msg":"用户名只能为英文、数字与汉字！"}');
        } elseif ($DB->getRow("SELECT zid FROM pre_site WHERE user=:user LIMIT 1", [':user' => $user])) {
            exit('{"code":-1,"msg":"用户名已存在！"}');
        } elseif (strlen($pwd) < 6) {
            exit('{"code":-1,"msg":"密码不能低于6位"}');
        } elseif ($pwd == $user) {
            exit('{"code":-1,"msg":"用户名和密码不能相同！"}');
        }
        if ($DB->exec("UPDATE pre_site SET user=:user,pwd=:pwd WHERE zid=:zid",
            [':user' => $user, ':pwd' => $pwd, ':zid' => $userrow['zid']])) {
            $session = md5($user . $pwd . $password_hash);
            $token   = authcode("{$userrow['zid']}\t{$session}", 'ENCODE', SYS_KEY);
            ob_clean();
            setcookie("user_token", $token, time() + 604800, '/');
            exit('{"code":0,"msg":"保存成功"}');
        } else {
            exit('{"code":-1,"msg":"保存失败！' . $DB->error() . '"}');
        }
        break;
    case 'up_price':
        unset($islogin2);
        $price_obj = new \lib\Price($userrow['zid'], $userrow);
        $up        = intval($_POST['up']);
        $cid       = intval($_POST['cid']);
        $kw        = trim(daddslashes($_POST['kw']));

        if ($up <= 0) {
            exit('{"code":-1,"msg":"输入值不正确"}');
        }
        if ($conf['fenzhan_pricelimit'] == 1 && $up > 100) {
            exit('{"code":-1,"msg":"商品售价最高不能超过原售价的2倍"}');
        }

        // 构建查询条件
        $where = "active=1";
        if ($cid > 0) {
            $where .= " AND cid='{$cid}'";
        }
        if (!empty($kw)) {
            $where .= " AND name LIKE '%{$kw}%'";
        }

        $sql  = $DB->query("select * from pre_tools where {$where}");
        $data = [];
        while ($row = $sql->fetch()) {
            if ($row['price'] == 0) {
                continue;
            }
            if (strpos($row['name'], '免费') !== false) {
                continue;
            }
            $price_obj->setToolInfo($row['tid'], $row);
            $price                      = $price_obj->getManageSalePrice($row['tid']);
            $a                          = (float)$up / 100;
            $new_price                  = round($price * ($a + 1), 2);
            if ($new_price == $price) {
                continue;
            }
            $data[$row['tid']]['price'] = $new_price;
        }

        // 保存历史记录
        $price_history = $DB->query("SELECT tid, price, cost, cost2, del FROM pre_site_price WHERE zid='{$userrow['zid']}'");
        $original_price = [];
        while ($row = $price_history->fetch()) {
            $original_price[$row['tid']] = [
                'price' => $row['price'],
                'cost' => $row['cost'],
                'cost2' => $row['cost2'],
                'del' => $row['del']
            ];
        }

        $history_desc = "价格提升：" . $up . "%";
        $history_data = q8_user_encode_price_history_payload($original_price, count($data));

        // 检查并创建历史记录表
        $DB->exec("CREATE TABLE IF NOT EXISTS pre_price_history (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            zid INT UNSIGNED NOT NULL,
            price_data TEXT NOT NULL,
            description VARCHAR(255) NOT NULL,
            create_time DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // 插入历史记录
        $DB->exec("INSERT INTO pre_price_history (zid, price_data, description, create_time) VALUES ('{$userrow['zid']}', '{$history_data}', '{$history_desc}', NOW())");

        // 使用事务确保操作的原子性
        $DB->beginTransaction();
        try {
            $updated_count = 0;
            foreach ($data as $tid => $price_info) {
                $price = $price_info['price'];

                // 使用INSERT ON DUPLICATE KEY UPDATE语法，确保数据的唯一性
                $sql = "INSERT INTO pre_site_price (zid, tid, price, create_time, update_time)
                       VALUES (:zid, :tid, :price, NOW(), NOW())
                       ON DUPLICATE KEY UPDATE
                       price = :price, update_time = NOW()";

                $data_insert = [
                    ':zid' => $userrow['zid'],
                    ':tid' => $tid,
                    ':price' => $price
                ];

                $DB->exec($sql, $data_insert);
                $updated_count++;
            }

            $DB->commit();
            exit('{"code":0,"msg":"价格提升成功，共修改了'.$updated_count.'个商品"}');
        } catch (Exception $e) {
            $DB->rollback();
            exit('{"code":-1,"msg":"价格提升失败：' . $e->getMessage() . '"}');
        }
        break;

    case 'reset_price':
        unset($islogin2);
        $cid = intval($_POST['cid']);
        $name = trim(daddslashes($_POST['name']));
        $status = intval($_POST['status']);

        // 构建查询条件
        $where = "active=1";

        // 分类条件
        if (!empty($cid) && $cid != '0') {
            // 处理多选分类，转换为数组
            $cid_array = explode(',', $cid);
            // 过滤掉无效值
            $cid_array = array_filter($cid_array, function($id) {
                return is_numeric($id) && $id > 0;
            });
            if (!empty($cid_array)) {
                $where .= " AND cid IN ('" . implode("','", $cid_array) . "')";
            }
        }

        // 名称条件
        if (!empty($name)) {
            $where .= " AND name LIKE '%$name%'";
        }

        // 状态条件
        if ($status == 1) {
            $where .= " AND close=0";
        } elseif ($status == 2) {
            $where .= " AND close=1";
        }

        // 查询符合条件的商品
        $sql = $DB->query("SELECT tid FROM pre_tools WHERE {$where}");
        $tids = [];
        while ($row = $sql->fetch()) {
            $tids[] = $row['tid'];
        }

        $reset_count = 0;
        if (!empty($tids)) {
            // 从pre_site_price表中删除这些商品的价格记录
            $tid_list = implode(',', $tids);
            $reset_count = $DB->exec("DELETE FROM pre_site_price WHERE zid='{$userrow['zid']}' AND tid IN ({$tid_list})");
        }

        if ($reset_count > 0) {
            exit('{"code":0,"msg":"成功恢复 '.$reset_count.' 个商品的价格"}');
        } else {
            exit('{"code":0,"msg":"没有找到需要恢复价格的商品"}');
        }
        break;

    case 'batch_update_price':
        unset($islogin2);
        $price_obj = new \lib\Price($userrow['zid'], $userrow);

        $operation = isset($_POST['operation']) ? trim($_POST['operation']) : '';
        $price_type = isset($_POST['price_type']) ? trim($_POST['price_type']) : '';
        $batch_type = isset($_POST['batch_type']) ? trim($_POST['batch_type']) : '';
        $raw_value = isset($_POST['value']) ? trim($_POST['value']) : '';
        $cid = isset($_POST['cid']) ? trim($_POST['cid']) : '';
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $status = isset($_POST['status']) ? intval($_POST['status']) : 0;

        if ($operation !== 'add' && $operation !== 'subtract') {
            exit('{"code":-1,"msg":"操作类型错误"}');
        }

        $allowed_price_types = $userrow['power'] == 2 ? array('price', 'cost2', 'cost') : array('price');
        if (!in_array($price_type, $allowed_price_types, true)) {
            exit('{"code":-1,"msg":"价格类型错误"}');
        }

        if ($batch_type !== 'fixed' && $batch_type !== 'percent') {
            exit('{"code":-1,"msg":"批量类型错误"}');
        }

        if ($raw_value === '' || !is_numeric($raw_value)) {
            exit('{"code":-1,"msg":"修改值必须为数字"}');
        }

        $value = round(floatval($raw_value), 2);
        if ($value < 0) {
            exit('{"code":-1,"msg":"修改值不能为负数"}');
        }
        if ($batch_type === 'percent' && $value <= 0) {
            exit('{"code":-1,"msg":"百分比必须为正数"}');
        }

        $where = "active=1";
        if (!empty($cid) && $cid !== '0') {
            $cid_array = explode(',', $cid);
            $cid_array = array_filter($cid_array, function ($id) {
                return is_numeric($id) && $id > 0;
            });
            if (!empty($cid_array)) {
                $where .= " AND cid IN ('" . implode("','", $cid_array) . "')";
            }
        }
        if (!empty($name)) {
            $where .= " AND name LIKE '%" . daddslashes($name) . "%'";
        }
        if ($status === 1) {
            $where .= " AND close=0";
        } elseif ($status === 2) {
            $where .= " AND close=1";
        }

        $sql = $DB->query("SELECT * FROM pre_tools WHERE {$where}");
        $data = array();
        $updated_count = 0;

        while ($row = $sql->fetch()) {
            if ($row['price'] == 0 || strpos($row['name'], '免费') !== false) {
                continue;
            }

            $tid = intval($row['tid']);
            $price_obj->setToolInfo($tid, $row);

            $current_sale_price = round($price_obj->getManageSalePrice($tid), 2);
            $current_child_normal_price = $userrow['power'] == 2 ? round($price_obj->getManageChildNormalPrice($tid), 2) : 0;
            $current_child_pro_price = $userrow['power'] == 2 ? round($price_obj->getManageChildProfessionalPrice($tid), 2) : null;
            $current_self_cost_price = round($price_obj->getManageSelfCostPrice($tid), 2);
            $current_delete_status = intval($price_obj->getToolDel($tid));
            $current_min_sale_price = $userrow['power'] == 2 ? $current_child_normal_price : $current_self_cost_price;

            if ($price_type === 'cost2') {
                $current_value = $current_child_pro_price;
            } elseif ($price_type === 'cost') {
                $current_value = $current_child_normal_price;
            } else {
                $current_value = $current_sale_price;
            }

            $new_value = q8_user_batch_adjust_value($current_value, $operation, $batch_type, $value);
            if ($new_value < 0) {
                continue;
            }

            $next_price_info = array(
                'price' => $current_sale_price,
                'cost' => $current_child_normal_price,
                'cost2' => $current_child_pro_price,
                'del' => $current_delete_status,
            );

            if ($price_type === 'cost2') {
                if ($new_value < $current_self_cost_price) {
                    continue;
                }

                $next_price_info['cost2'] = $new_value;
                if ($next_price_info['cost'] < $next_price_info['cost2']) {
                    $next_price_info['cost'] = $next_price_info['cost2'];
                }
                if ($next_price_info['price'] < $next_price_info['cost']) {
                    $next_price_info['price'] = $next_price_info['cost'];
                }

                $main_cost2 = round($price_obj->getMainCost2(), 2);
                $main_cost = round($price_obj->getMainCost(), 2);
                $main_price = round($price_obj->getMainPrice(), 2);
                if ($conf['fenzhan_pricelimit'] == 1) {
                    if ($main_cost2 > 0 && (($main_cost2 > 1 && $next_price_info['cost2'] > $main_cost2 * 2) || ($main_cost2 <= 1 && $next_price_info['cost2'] > 2))) {
                        continue;
                    }
                    if ($main_cost > 0 && (($main_cost > 1 && $next_price_info['cost'] > $main_cost * 2) || ($main_cost <= 1 && $next_price_info['cost'] > 2))) {
                        continue;
                    }
                    if (($main_price > 1 && $next_price_info['price'] > $main_price * 2) || ($main_price <= 1 && $next_price_info['price'] > 2)) {
                        continue;
                    }
                }
            } elseif ($price_type === 'cost') {
                if ($new_value < $current_child_pro_price) {
                    continue;
                }

                $next_price_info['cost'] = $new_value;
                if ($next_price_info['price'] < $next_price_info['cost']) {
                    $next_price_info['price'] = $next_price_info['cost'];
                }

                $main_cost2 = round($price_obj->getMainCost2(), 2);
                $main_cost = round($price_obj->getMainCost(), 2);
                $main_price = round($price_obj->getMainPrice(), 2);
                if ($conf['fenzhan_pricelimit'] == 1) {
                    if ($main_cost2 > 0 && (($main_cost2 > 1 && $next_price_info['cost2'] > $main_cost2 * 2) || ($main_cost2 <= 1 && $next_price_info['cost2'] > 2))) {
                        continue;
                    }
                    if ($main_cost > 0 && (($main_cost > 1 && $next_price_info['cost'] > $main_cost * 2) || ($main_cost <= 1 && $next_price_info['cost'] > 2))) {
                        continue;
                    }
                    if (($main_price > 1 && $next_price_info['price'] > $main_price * 2) || ($main_price <= 1 && $next_price_info['price'] > 2)) {
                        continue;
                    }
                }
            } else {
                if ($new_value < $current_min_sale_price) {
                    continue;
                }

                $next_price_info['price'] = $new_value;

                $main_price = round($price_obj->getMainPrice(), 2);
                if ($conf['fenzhan_pricelimit'] == 1) {
                    if (($main_price > 1 && $next_price_info['price'] > $main_price * 2) || ($main_price <= 1 && $next_price_info['price'] > 2)) {
                        continue;
                    }
                }
            }

            if ($userrow['power'] == 2) {
                if (
                    round($next_price_info['price'], 2) == $current_sale_price
                    && round($next_price_info['cost'], 2) == $current_child_normal_price
                    && round(floatval($next_price_info['cost2']), 2) == round(floatval($current_child_pro_price), 2)
                ) {
                    continue;
                }
            } elseif (round($next_price_info['price'], 2) == $current_sale_price) {
                continue;
            }

            $data[$tid] = $next_price_info;
            $updated_count++;
        }

        if ($updated_count <= 0) {
            exit('{"code":-1,"msg":"没有找到符合条件的商品或修改后价格不符合要求"}');
        }

        $price_history = $DB->query("SELECT tid, price, cost, cost2, del FROM pre_site_price WHERE zid='{$userrow['zid']}'");
        $original_price = array();
        while ($row = $price_history->fetch()) {
            $original_price[$row['tid']] = array(
                'price' => $row['price'],
                'cost' => $row['cost'],
                'cost2' => $row['cost2'],
                'del' => $row['del']
            );
        }

        $history_desc = "批量" . ($operation === 'add' ? "加价" : "降价") . "：" . q8_user_batch_price_type_label($price_type) . " " . ($batch_type === 'fixed' ? $value . "元" : $value . "%");
        $history_data = q8_user_encode_price_history_payload($original_price, $updated_count);

        $DB->exec("CREATE TABLE IF NOT EXISTS pre_price_history (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            zid INT UNSIGNED NOT NULL,
            price_data TEXT NOT NULL,
            description VARCHAR(255) NOT NULL,
            create_time DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $DB->exec("INSERT INTO pre_price_history (zid, price_data, description, create_time) VALUES ('{$userrow['zid']}', '{$history_data}', '{$history_desc}', NOW())");

        $DB->beginTransaction();
        try {
            foreach ($data as $tid => $price_info) {
                $saved = $price_obj->setPriceInfo(
                    $tid,
                    $price_info['del'],
                    $price_info['price'],
                    $price_info['cost'],
                    $price_info['cost2']
                );
                if (!$saved) {
                    throw new Exception($DB->error());
                }
            }
            $DB->commit();
            exit("{\"code\":0,\"msg\":\"批量修改成功，共更新了 {$updated_count} 个商品\"}");
        } catch (Exception $e) {
            $DB->rollback();
            exit('{"code":-1,"msg":"批量修改失败：' . $e->getMessage() . '"}');
        }
        break;
    case 'batch_update_price_legacy':
        unset($islogin2);
        $price_obj = new \lib\Price($userrow['zid'], $userrow);

        // 获取参数
        $operation = $_POST['operation'];
        $price_type = $_POST['price_type'];
        $batch_type = $_POST['batch_type'];
        $value = floatval($_POST['value']);
        $cid = trim($_POST['cid']);
        $name = trim($_POST['name']);
        $status = intval($_POST['status']);

        // 验证参数
        if ($operation != 'add' && $operation != 'subtract') {
            exit('{"code":-1,"msg":"操作类型错误"}');
        }

        if ($price_type != 'price') {
            exit('{"code":-1,"msg":"价格类型错误"}');
        }

        if ($batch_type != 'fixed' && $batch_type != 'percent') {
            exit('{"code":-1,"msg":"批量类型错误"}');
        }

        if ($batch_type == 'percent' && $value <= 0) {
            exit('{"code":-1,"msg":"百分比必须为正数"}');
        }

        // 构建查询条件
        $where = "active=1";

        // 分类条件
        if (!empty($cid) && $cid != '0') {
            // 处理多选分类，转换为数组
            $cid_array = explode(',', $cid);
            // 过滤掉无效值
            $cid_array = array_filter($cid_array, function($id) {
                return is_numeric($id) && $id > 0;
            });
            if (!empty($cid_array)) {
                $where .= " AND cid IN ('" . implode("','", $cid_array) . "')";
            }
        }

        // 名称条件
        if (!empty($name)) {
            $where .= " AND name LIKE '%" . daddslashes($name) . "%'";
        }

        // 状态条件
        if ($status == 1) {
            $where .= " AND close=0";
        } elseif ($status == 2) {
            $where .= " AND close=1";
        }

        // 查询符合条件的商品数量，移除数量限制
        $total_count = $DB->getColumn("SELECT count(*) FROM pre_tools WHERE {$where}");

        // 查询符合条件的商品
        $sql = $DB->query("SELECT * FROM pre_tools WHERE {$where}");
        $data = [];
        $updated_count = 0;

        while ($row = $sql->fetch()) {
            // 跳过价格为0或名称包含"免费"的商品
            if ($row['price'] == 0 || strpos($row['name'], '免费') !== false) {
                continue;
            }

            // 获取当前价格
            $price_obj->setToolInfo($row['tid'], $row);
            $current_price = $price_obj->getToolPrice($row['tid']);

            // 计算新价格
            $new_price = 0;
            if ($batch_type == 'fixed') {
                if ($operation == 'add') {
                    $new_price = round($current_price + $value, 2);
                } else {
                    $new_price = round($current_price - $value, 2);
                }
            } else {
                $percentage = $value / 100;
                if ($operation == 'add') {
                    $new_price = round($current_price * (1 + $percentage), 2);
                } else {
                    $new_price = round($current_price * (1 - $percentage), 2);
                }
            }

            // 确保价格不低于成本价
            $cost_price = $price_obj->getToolCost($row['tid']);
            if ($new_price < $cost_price) {
                continue;
            }

            // 应用价格限制
            $main_price = $price_obj->getMainPrice();
            if ($conf['fenzhan_pricelimit'] == 1) {
                if ($main_price > 1 && $new_price > $main_price * 2) {
                    continue;
                }
                if ($main_price <= 1 && $new_price > 2) {
                    continue;
                }
            }

            // 更新价格
            $data[$row['tid']]['price'] = $new_price;
            $updated_count++;
        }

        // 保存更新
        if ($updated_count > 0) {
            // 添加调试日志
            error_log("[DEBUG] 开始批量修改价格，ZID: {$userrow['zid']}, 待更新商品数量: {$updated_count}");

            // 从pre_site_price表中获取当前价格数据作为备份
            $price_history = $DB->query("SELECT tid, price, cost, cost2, del FROM pre_site_price WHERE zid='{$userrow['zid']}'");
            $original_price = [];
            while ($row = $price_history->fetch()) {
                $original_price[$row['tid']] = [
                    'price' => $row['price'],
                    'cost' => $row['cost'],
                    'cost2' => $row['cost2'],
                    'del' => $row['del']
                ];
            }

            // 记录原始数据信息
            error_log("[DEBUG] 原始数据数量: " . count($original_price));

            // 保存历史记录
            $history_desc = "批量" . ($operation == 'add' ? "加价" : "降价") . "：" . ($batch_type == 'fixed' ? $value . "元" : $value . "%");
            $history_data = json_encode($original_price); // 使用JSON格式保存原始数据作为历史记录

            // 检查并创建历史记录表
            $DB->exec("CREATE TABLE IF NOT EXISTS pre_price_history (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                zid INT UNSIGNED NOT NULL,
                price_data TEXT NOT NULL,
                description VARCHAR(255) NOT NULL,
                create_time DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // 插入历史记录
            $DB->exec("INSERT INTO pre_price_history (zid, price_data, description, create_time) VALUES ('{$userrow['zid']}', '{$history_data}', '{$history_desc}', NOW())");

            // 使用事务确保操作的原子性
            $DB->beginTransaction();
            try {
                // 将批量修改的价格数据保存到新表中
                foreach ($data as $tid => $price_info) {
                    $price = $price_info['price'];

                    // 使用INSERT ON DUPLICATE KEY UPDATE语法，确保数据的唯一性
                    $sql = "INSERT INTO pre_site_price (zid, tid, price, create_time, update_time)
                           VALUES (:zid, :tid, :price, NOW(), NOW())
                           ON DUPLICATE KEY UPDATE
                           price = :price, update_time = NOW()";

                    $data_insert = [
                        ':zid' => $userrow['zid'],
                        ':tid' => $tid,
                        ':price' => $price
                    ];

                    $DB->exec($sql, $data_insert);
                }

                $DB->commit();
                error_log("[DEBUG] 批量修改成功，共更新了 {$updated_count} 个商品");
                exit("{\"code\":0,\"msg\":\"批量修改成功，共更新了 {$updated_count} 个商品\"}");
            } catch (Exception $e) {
                $DB->rollback();
                error_log("[DEBUG] 批量修改失败: " . $e->getMessage());
                exit('{"code":-1,"msg":"批量修改失败：' . $e->getMessage() . '"}');
            }
        } else {
            exit('{"code":-1,"msg":"没有找到符合条件的商品或修改后价格不符合要求"}');
        }
        break;
    case 'create_url':
        $force = trim(daddslashes($_GET['force']));
        if (!$userrow['domain']) {
            exit('{"code":-1,"msg":"当前分站还未绑定域名"}');
        }
        $url = 'http://' . $userrow['domain'] . '/';
        if ($force == 1) {
            $turl = fanghongdwz($url, true);
        } else {
            $turl = fanghongdwz($url);
        }
        if ($turl == $url) {
            $result = ['code' => -1, 'msg' => '生成失败，请联系站长更换接口'];
        } elseif (strpos($turl, '/')) {
            $result = ['code' => 0, 'msg' => 'succ', 'url' => $turl];
        } else {
            $result = ['code' => -1, 'msg' => '生成失败：' . $turl];
        }
        exit(json_encode($result));
        break;
    case 'qiandao':
        if (!$conf['qiandao_reward']) {
            exit('{"code":-1,"msg":"当前站点未开启签到功能"}');
        }
        if (!isset($_SESSION['isqiandao']) || $_SESSION['isqiandao'] != $userrow['zid']) {
            exit('{"code":-1,"msg":"校验失败，请刷新页面重试"}');
        }
        $day     = date("Y-m-d");
        $lastday = date("Y-m-d", strtotime("-1 day"));

        if ($DB->getRow("SELECT * FROM pre_qiandao WHERE zid='{$userrow['zid']}' AND date='$day' ORDER BY id DESC LIMIT 1")) {
            exit('{"code":-1,"msg":"今天已经签到过了, 明天在来吧！"}');
        }
        if ($conf['qiandao_limitip'] == 1 && $DB->getRow("SELECT * FROM pre_qiandao WHERE ip='{$clientip}' AND date='$day' ORDER BY id DESC LIMIT 1")) {
            exit('{"code":-1,"msg":"您的IP今天已经签到过了，明天在来吧！"}');
        }
        if ($row = $DB->getRow("SELECT * FROM pre_qiandao WHERE zid='{$userrow['zid']}' AND date='$lastday' ORDER BY id DESC LIMIT 1")) {
            $continue = $row['continue'] + 1;
        } else {
            $continue = 1;
        }
        if ($continue > $conf['qiandao_day']) {
            $continue = $conf['qiandao_day'];
        }
        $reward = $conf['qiandao_reward'];
        if (strpos($reward, '|')) {
            $reward = explode('|', $reward);
            $reward = $reward[$userrow['power']];
            if (!$reward) {
                exit('{"code":-1,"msg":"未配置好签到奖励余额初始值"}');
            }
        }
        if ($conf['qiandao_mult'] > 0) {
            for ($i = 1; $i < $continue; $i++) {
                $reward *= $conf['qiandao_mult'];
            }
        }
        $reward = round($reward, 2);
        $sql    = "INSERT INTO `pre_qiandao` (`zid`,`qq`,`reward`,`date`,`time`,`continue`,`ip`) VALUES ('" . $userrow['zid'] . "','" . $userrow['qq'] . "','" . $reward . "','" . $day . "','" . $date . "','" . $continue . "','" . $clientip . "')";
        if ($DB->exec($sql)) {
            unset($_SESSION['isqiandao']);
            changeUserMoney($userrow['zid'], $reward, true, '赠送', '您今天签到获得了' . $reward . '元奖励');
            $result = ['code' => 0, 'msg' => '签到成功，获得' . $reward . '元现金奖励！'];
        } else {
            $result = ['code' => -1, 'msg' => '签到失败' . $DB->error()];
        }
        exit(json_encode($result));
        break;
    case 'qdcount':
        $day         = date("Y-m-d");
        $lastday     = date("Y-m-d", strtotime("-1 day"));
        $count1      = $DB->getColumn("SELECT count(*) FROM pre_qiandao WHERE date='$day'");
        $count2      = $DB->getColumn("SELECT count(*) FROM pre_qiandao WHERE date='$lastday'");
        $count3      = $DB->getColumn("SELECT count(*) FROM pre_qiandao");
        $rewardcount = $DB->getColumn("SELECT sum(reward) FROM pre_qiandao WHERE zid='{$userrow['zid']}'");
        $result      = [
            "count1"      => $count1,
            "count2"      => $count2,
            "count3"      => $count3,
            "rewardcount" => round($rewardcount, 2)
        ];
        exit(json_encode($result));
        break;
    case 'msg':
        if ($userrow['power'] == 2) {
            $type = '0,2,4';
        } elseif ($userrow['power'] == 1) {
            $type = '0,2,3';
        } else {
            $type = '0,1';
        }
        $msgread = trim($userrow['msgread'], ',');
        if (empty($msgread)) {
            $msgread = '0';
        }
        $count        = $DB->getColumn("SELECT count(*) FROM pre_message WHERE id NOT IN ($msgread) and type IN ($type)");
        $count2       = $DB->getColumn("SELECT count(*) FROM pre_workorder WHERE zid='{$userrow['zid']}' AND status=1");
        $thtime       = date("Y-m-d") . ' 00:00:00';
        $income_today = $DB->getColumn("SELECT sum(point) FROM pre_points WHERE zid='{$userrow['zid']}' AND action='提成' AND addtime>'$thtime'");
        exit('{"code":0,"count":' . $count . ',"count2":' . $count2 . ',"income_today":"' . round($income_today,
                2) . '"}');
        break;
    case 'msginfo':
        if ($userrow['power'] == 2) {
            $type = [0, 2, 4];
        } elseif ($userrow['power'] == 1) {
            $type = [0, 2, 3];
        } else {
            $type = [0, 1];
        }
        $id  = intval($_GET['id']);
        $row = $DB->getRow("SELECT * FROM pre_message WHERE id='$id' AND active=1 LIMIT 1");
        if (!$row) {
            exit('{"code":-1,"msg":"当前消息不存在！"}');
        }
        if (!in_array($row['type'], $type)) {
            exit('{"code":-1,"msg":"你没有权限查看此消息内容"}');
        }
        if (!in_array($id, explode(',', $userrow['msgread']))) {
            $msgread_n = $userrow['msgread'] . $id . ',';
            $DB->exec("UPDATE pre_message SET count=count+1 WHERE id='$id'");
            $DB->exec("UPDATE pre_site SET msgread='" . $msgread_n . "' WHERE zid='{$userrow['zid']}'");
        }
        $result = [
            "code"    => 0,
            "msg"     => "succ",
            "title"   => $row['title'],
            "type"    => $row['type'],
            "content" => $row['content'],
            "date"    => $row['addtime']
        ];
        exit(json_encode($result));
        break;
    case 'msg_read_all':
        if ($userrow['power'] == 2) {
            $type = [0, 2, 4];
        } elseif ($userrow['power'] == 1) {
            $type = [0, 2, 3];
        } else {
            $type = [0, 1];
        }
        $type = implode(',', $type);
        $rs   = $DB->query("SELECT id FROM pre_message WHERE `type` in ({$type})");
        $id   = "";
        foreach ($rs as $key => $value) {
            $id .= $value['id'] . ',';
        }

        if ($id) {
            $DB->exec("UPDATE pre_site SET msgread='" . $id . "' WHERE zid='{$userrow['zid']}'");
        }
        $result = ["code" => 0, "msg" => "succ"];
        exit(json_encode($result));
        break;
    case 'recharge':
        $value    = daddslashes($_GET['value']);
        $trade_no = date("YmdHis") . rand(111, 999);
        if (!is_numeric($value) || !preg_match('/^[0-9.]+$/', $value)) {
            exit('{"code":-1,"msg":"提交参数错误！"}');
        }
        if ($conf['recharge_min'] > 0 && $value < $conf['recharge_min']) {
            exit('{"code":-1,"msg":"最低充值' . $conf['recharge_min'] . '元！"}');
        }
        $sql  = "INSERT INTO `pre_pay` (`trade_no`,`tid`,`input`,`name`,`money`,`ip`,`addtime`,`status`) VALUES (:trade_no, :tid, :input, :name, :money, :ip, NOW(), 0)";
        $data = [
            ':trade_no' => $trade_no,
            ':tid'      => -1,
            ':input'    => (string)$userrow['zid'],
            ':name'     => '在线充值余额',
            ':money'    => $value,
            ':ip'       => $clientip
        ];
        if ($DB->exec($sql, $data)) {
            exit('{"code":0,"msg":"提交订单成功！","trade_no":"' . $trade_no . '","money":"' . $value . '","name":"在线充值余额"}');
        } else {
            exit('{"code":-1,"msg":"提交订单失败！' . $DB->error() . '"}');
        }
        break;
    case 'setClass':
        $cid       = intval($_GET['cid']);
        $active    = intval($_GET['active']);
        $classhide = explode(',', $userrow['class']);
        if ($active == 1 && in_array($cid, $classhide)) {
            $classhide = array_diff($classhide, [$cid]);
        } elseif ($active == 0 && !in_array($cid, $classhide)) {
            $classhide[] = $cid;
        }
        $class = implode(',', $classhide);
        $DB->exec("UPDATE `pre_site` SET `class`='{$class}' WHERE zid='{$userrow['zid']}'");
        exit('{"code":0}');
        break;
    case 'uploadimg':
        if ($_POST['do'] == 'upload') {
            // 检查文件类型和大小
            $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp');
            $file_type = $_FILES['file']['type'];
            $file_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            $allowed_exts = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp');
            $max_size = 2 * 1024 * 1024; // 2MB

            // 检查文件类型和扩展名
            if (!in_array($file_type, $allowed_types) || !in_array($file_ext, $allowed_exts)) {
                exit('{"code":-1,"msg":"只允许上传JPG、PNG、GIF、WEBP、BMP格式的图片文件！"}');
            }

            // 检查文件大小
            if ($_FILES['file']['size'] > $max_size) {
                exit('{"code":-1,"msg":"文件大小不能超过2MB！"}');
            }

            // 检查文件是否为真实图片
            $image_info = getimagesize($_FILES['file']['tmp_name']);
            if (!$image_info) {
                exit('{"code":-1,"msg":"请上传真实的图片文件！"}');
            }

            // 生成更安全的文件名：结合时间戳、随机数和文件内容哈希
            $filename = md5(uniqid(mt_rand(), true) . time() . file_get_contents($_FILES['file']['tmp_name'])) . '.' . $file_ext;
            $fileurl  = 'assets/img/workorder/' . $filename;

            // 确保上传目录存在
            if (!is_dir(ROOT . 'assets/img/workorder/')) {
                mkdir(ROOT . 'assets/img/workorder/', 0755, true);
            }

            // 使用move_uploaded_file更安全
            if (move_uploaded_file($_FILES['file']['tmp_name'], ROOT . $fileurl)) {
                exit('{"code":0,"msg":"succ","url":"' . $fileurl . '"}');
            } else {
                exit('{"code":-1,"msg":"上传失败，请确保有本地写入权限"}');
            }
        }
        exit('{"code":-1,"msg":"null"}');
        break;
    case 'usekm':
        if (!$conf['fenzhan_jiakuanka']) {
            exit('{"code":-1,"msg":"未开启使用加款卡功能"}');
        }
        if (function_exists('q8_kms_ensure_use_columns')) q8_kms_ensure_use_columns();
        $km    = trim(daddslashes($_POST['km']));
        $myrow = $DB->getRow("SELECT * FROM pre_kms WHERE km='$km' LIMIT 1");
        if (!$myrow) {
            exit('{"code":-1,"msg":"此卡密不存在！"}');
        } elseif ($myrow['status'] == 1 || intval($myrow['use_count']) >= max(1, intval($myrow['use_limit']))) {
            exit('{"code":-1,"msg":"此卡密已达到可用次数！"}');
        }
        $money = $myrow['money'];
        $rebate_money = q8_calc_online_recharge_bonus($money, $conf);
        $rebate_rate = function_exists('q8_get_recharge_rebate_rate') ? q8_get_recharge_rebate_rate($money, $conf) : (isset($conf['recharge_rebate_rate']) ? floatval($conf['recharge_rebate_rate']) : 0);
        if ($DB->exec("UPDATE `pre_kms` SET `use_count`=`use_count`+1,`zid`='{$userrow['zid']}',`usetime`='" . $date . "',`status`=IF(`use_count`+1>=`use_limit`,1,0) WHERE `kid`='{$myrow['kid']}' AND `use_count`<`use_limit`")) {
            // 充值主金额
            $rs = changeUserMoney($userrow['zid'], $money, true, '充值', '你使用加款卡充值了' . $money . '元余额');
            if ($rebate_money > 0) {
                changeUserMoney($userrow['zid'], $rebate_money, true, hex2bin('e8b5a0e98081'), hex2bin('e58aa0e6acbee58da1e58585e580bc') . $money . hex2bin('e58583e8b5a0e98081') . $rebate_rate . '%=' . $rebate_money . hex2bin('e58583'));
            }
            if ($rs) {
                if ($rebate_money > 0) {
                    exit('{"code":0,"msg":"成功充值' . $money . '元余额，额外获得' . $rebate_money . '元返利！"}');
                } else {
                    exit('{"code":0,"msg":"成功充值' . $money . '元余额！"}');
                }
            }
        }
        exit('{"code":-1,"msg":"充值失败' . $DB->error() . '"}');
        break;
    case 'app_upload':
        if (!$conf['appcreate_open'] || !$conf['appcreate_key']) {
            exit('{"code":-1,"msg":"未开启分站自助生成APP功能"}');
        }
        if (!$conf['appcreate_diy']) {
            exit('{"code":-1,"msg":"未开启自定义图标和启动图"}');
        }
        $file = $_FILES['file'];
        $type = strtolower(substr($file['name'], strrpos($file['name'], '.') + 1));
        if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
            exit(json_encode(['code' => -1, 'msg' => '上传图片格式错误']));
        }
        $path = sys_get_temp_dir() . '/' . md5_file($file['tmp_name']) . '.' . $type;
        if (!move_uploaded_file($file['tmp_name'], $path)) {
            exit(json_encode(['code' => -1, 'msg' => '上传失败']));
        }
        $app = new \lib\AppCreate($conf['appcreate_key']);
        if ($app->uploadimg($path)) {
            exit(json_encode(['code' => 0, 'msg' => '图片上传成功', 'fileid' => $app->fileid]));
        } else {
            exit(json_encode(['code' => -1, 'msg' => $app->msg]));
        }
        break;
    case 'app_submit':
        if (!$conf['appcreate_open'] || !$conf['appcreate_key']) {
            exit('{"code":-1,"msg":"未开启分站自助生成APP功能"}');
        }
        $price = $userrow['power'] == 2 ? $conf['appcreate_price2'] : $conf['appcreate_price'];
        if ($price > 0 && $userrow['rmb'] < $price) {
            exit('{"code":-1,"msg":"你的余额不足，生成APP需要' . $price . '元"}');
        }
        $app  = new \lib\AppCreate($conf['appcreate_key']);
        $name = trim(daddslashes($_POST['name']));
        $url  = trim(daddslashes($_POST['url']));
        if (empty($name)) {
            exit('{"code":-1,"msg":"应用名称不能为空"}');
        }
        if (!preg_match('/^[a-zA-Z0-9\x7f-\xff\.\-\! ]+$/', $name) || strlen($name) < 3) {
            exit('{"code":-1,"msg":"应用名称不合法"}');
        }
        if (mb_strlen($name, "UTF-8") > 12) {
            exit('{"code":-1,"msg":"应用名称长度不能超过12个字"}');
        }
        if (empty($url)) {
            exit('{"code":-1,"msg":"应用网址不能为空"}');
        }
        if (!strpos($url, '.')) {
            exit('{"code":-1,"msg":"应用网址不正确"}');
        }
        if (isset($_SESSION['appurl']) && $_SESSION['appurl'] == $url) {
            exit(json_encode(['code' => -1, 'msg' => '你已经生成过了，请在"我的生成"中查看。']));
        }
        if ($conf['appcreate_diy'] == 1) {
            $icon       = !empty($_POST['icon']) ? trim($_POST['icon']) : '1';
            $background = !empty($_POST['background']) ? trim($_POST['background']) : '2';
        } else {
            $icon       = '1';
            $background = '2';
        }
        $theme = $conf['appcreate_theme'];
        if ($app->submittask($name, $url, $icon, $background, $theme, $conf['appcreate_nonav'])) {
            $_SESSION['appurl'] = $url;
            if ($price > 0) {
                changeUserMoney($userrow['zid'], $price, false, '消费', '自助生成APP');
            }
            exit(json_encode(['code' => 0, 'msg' => '成功提交生成任务，生成大约需要半分钟，生成成功后请在"我的生成"中查看。', 'taskid' => $app->taskid]));
        } else {
            exit(json_encode(['code' => -1, 'msg' => $app->msg]));
            exit('{"code":-1,"msg":"' . $app->msg . '"}');
        }
        break;
    case 'app_query':
        if (!$conf['appcreate_open'] || !$conf['appcreate_key']) {
            exit('{"code":-1,"msg":"未开启分站自助生成APP功能"}');
        }
        $app    = new \lib\AppCreate($conf['appcreate_key']);
        $url    = 'http://' . $userrow['domain'];
        $url    = isset($_SESSION['appurl']) ? $_SESSION['appurl'] : $url;
        $domain = parse_url($url)['host'];
        $res    = $app->queryurl($url);
        if ($res && is_array($res)) {
            $appurl = "";
            if ($res['status'] == 1) {
                $android_url = $res['lanzou_url'] ? $res['lanzou_url'] : $res['android_url'];
                $ios_url     = $res['ios_url'];
                $approw      = $DB->find('apps', '*', ['domain' => $domain]);
                if ($approw) {
                    $id = $approw['id'];
                    $DB->update('apps', [
                        'taskid'      => $res['id'],
                        'domain'      => $domain,
                        'name'        => $res['name'],
                        'package'     => $res['package'],
                        'android_url' => $android_url,
                        'ios_url'     => $ios_url,
                        'icon'        => $res['icon'],
                        'addtime'     => $res['created_at'],
                        'status'      => 1
                    ], ['id' => $id]);
                } else {
                    $id = $DB->insert('apps', [
                        'taskid'      => $res['id'],
                        'domain'      => $domain,
                        'name'        => $res['name'],
                        'package'     => $res['package'],
                        'android_url' => $android_url,
                        'ios_url'     => $ios_url,
                        'icon'        => $res['icon'],
                        'addtime'     => $res['created_at'],
                        'status'      => 1
                    ]);
                }
                $appurl = '/?mod=app&id=' . $id;
                $DB->exec("UPDATE `pre_site` SET `appurl`=:appurl WHERE `zid`='{$userrow['zid']}'",
                    [':appurl' => $appurl]);
            }
            $result = [
                "code"              => 0,
                "msg"               => "succ",
                "url"               => $url,
                "download_url"      => $appurl,
                "download_url_show" => $url . $appurl,
                "android_url"       => $android_url,
                "ios_url"           => $ios_url,
                "data"              => $res
            ];
            exit(json_encode($result));
        } else {
            exit(json_encode(['code' => -1, 'msg' => $app->msg]));
        }
        break;
    case 'tixian_note':
        $id     = intval($_POST['id']);
        $rows   = $DB->getRow("select * from pre_tixian where id='$id' and zid='{$userrow['zid']}' limit 1");
        $result = ["code" => 0, "msg" => "succ", "result" => $rows['note']];
        exit(json_encode($result));
        break;
    case 'get_price_history':
        // 获取价格修改历史记录
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $pagesize = 10;
        $offset = ($page - 1) * $pagesize;

        // 获取总记录数
        $total = $DB->getColumn("SELECT count(*) FROM pre_price_history WHERE zid='{$userrow['zid']}'");

        // 获取历史记录
        $sql = $DB->query("SELECT * FROM pre_price_history WHERE zid='{$userrow['zid']}' ORDER BY create_time DESC LIMIT $offset,$pagesize");
        $history = [];
        while ($row = $sql->fetch()) {
            $history_payload = q8_user_decode_price_history_payload($row['price_data']);
            $history[] = [
                'id' => $row['id'],
                'description' => $row['description'],
                'create_time' => $row['create_time'],
                'price_data_count' => $history_payload['affected_count'],
                'price_data_count_accurate' => $history_payload['accurate_count'] ? 1 : 0
            ];
        }

        exit(json_encode([
            'code' => 0,
            'msg' => 'success',
            'data' => $history,
            'total' => $total,
            'page' => $page,
            'pagesize' => $pagesize
        ]));
        break;
    case 'restore_last_price':
        // 恢复到上一次修改
        $last_history = $DB->getRow("SELECT * FROM pre_price_history WHERE zid='{$userrow['zid']}' ORDER BY create_time DESC LIMIT 1");
        if (!$last_history) {
            exit('{"code":-1,"msg":"没有找到历史记录"}');
        }

        // 保存当前状态到历史记录
        $current_price = $DB->query("SELECT tid, price, cost, cost2, del FROM pre_site_price WHERE zid='{$userrow['zid']}'");
        $current_price_data = [];
        while ($row = $current_price->fetch()) {
            $current_price_data[$row['tid']] = [
                'price' => $row['price'],
                'cost' => $row['cost'],
                'cost2' => $row['cost2'],
                'del' => $row['del']
            ];
        }
        $current_price_json = q8_user_encode_price_history_payload($current_price_data, count($current_price_data));
        $DB->exec("INSERT INTO pre_price_history (zid, price_data, description, create_time) VALUES ('{$userrow['zid']}', '{$current_price_json}', '恢复到上一次修改前的状态', NOW())");

        // 使用事务确保操作的原子性
        $DB->beginTransaction();
        try {
            // 清空当前价格数据
            $DB->exec("DELETE FROM pre_site_price WHERE zid='{$userrow['zid']}'");

            // 恢复上一次的价格数据
            $last_price_payload = q8_user_decode_price_history_payload($last_history['price_data']);
            if (is_array($last_price_payload['items'])) {
                foreach ($last_price_payload['items'] as $tid => $price_info) {
                    $sql = "INSERT INTO pre_site_price (zid, tid, price, cost, cost2, del, create_time, update_time)
                           VALUES (:zid, :tid, :price, :cost, :cost2, :del, NOW(), NOW())";

                    $data_insert = [
                        ':zid' => $userrow['zid'],
                        ':tid' => $tid,
                        ':price' => $price_info['price'],
                        ':cost' => isset($price_info['cost']) ? $price_info['cost'] : 0,
                        ':cost2' => isset($price_info['cost2']) ? $price_info['cost2'] : 0,
                        ':del' => isset($price_info['del']) ? $price_info['del'] : 0
                    ];

                    $DB->exec($sql, $data_insert);
                }
            }

            $DB->commit();
            exit('{"code":0,"msg":"已成功恢复到上一次修改的状态"}');
        } catch (Exception $e) {
            $DB->rollback();
            exit('{"code":-1,"msg":"恢复失败：' . $e->getMessage() . '"}');
        }
        break;
    case 'restore_from_history':
        // 从指定历史记录恢复
        $history_id = intval($_POST['id']);
        $history = $DB->getRow("SELECT * FROM pre_price_history WHERE id='$history_id' AND zid='{$userrow['zid']}' LIMIT 1");
        if (!$history) {
            exit('{"code":-1,"msg":"历史记录不存在"}');
        }

        // 保存当前状态到历史记录
        $current_price = $DB->query("SELECT tid, price, cost, cost2, del FROM pre_site_price WHERE zid='{$userrow['zid']}'");
        $current_price_data = [];
        while ($row = $current_price->fetch()) {
            $current_price_data[$row['tid']] = [
                'price' => $row['price'],
                'cost' => $row['cost'],
                'cost2' => $row['cost2'],
                'del' => $row['del']
            ];
        }
        $current_price_json = q8_user_encode_price_history_payload($current_price_data, count($current_price_data));
        $DB->exec("INSERT INTO pre_price_history (zid, price_data, description, create_time) VALUES ('{$userrow['zid']}', '{$current_price_json}', '恢复到指定历史版本前的状态', NOW())");

        // 使用事务确保操作的原子性
        $DB->beginTransaction();
        try {
            // 清空当前价格数据
            $DB->exec("DELETE FROM pre_site_price WHERE zid='{$userrow['zid']}'");

            // 恢复指定历史版本的价格数据
            $history_price_payload = q8_user_decode_price_history_payload($history['price_data']);
            if (is_array($history_price_payload['items'])) {
                foreach ($history_price_payload['items'] as $tid => $price_info) {
                    $sql = "INSERT INTO pre_site_price (zid, tid, price, cost, cost2, del, create_time, update_time)
                           VALUES (:zid, :tid, :price, :cost, :cost2, :del, NOW(), NOW())";

                    $data_insert = [
                        ':zid' => $userrow['zid'],
                        ':tid' => $tid,
                        ':price' => $price_info['price'],
                        ':cost' => isset($price_info['cost']) ? $price_info['cost'] : 0,
                        ':cost2' => isset($price_info['cost2']) ? $price_info['cost2'] : 0,
                        ':del' => isset($price_info['del']) ? $price_info['del'] : 0
                    ];

                    $DB->exec($sql, $data_insert);
                }
            }

            $DB->commit();
            exit('{"code":0,"msg":"已成功恢复到指定历史版本"}');
        } catch (Exception $e) {
            $DB->rollback();
            exit('{"code":-1,"msg":"恢复失败：' . $e->getMessage() . '"}');
        }
        break;
    case 'get_site_price_rule':
        if ($userrow['power'] <= 0) {
            exit('{"code":-1,"msg":"没有权限使用此功能"}');
        }
        $id = intval(isset($_GET['id']) ? $_GET['id'] : 0);
        $row = q8_price_rule_fetch_row($id, $userrow['zid']);
        if (!$row) {
            exit('{"code":-1,"msg":"模板不存在"}');
        }
        $row['code'] = 0;
        exit(json_encode($row, JSON_UNESCAPED_UNICODE));
        break;
    case 'add_site_price_rule':
        if ($userrow['power'] <= 0) {
            exit('{"code":-1,"msg":"没有权限使用此功能"}');
        }
        $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
        $kind = intval(isset($_POST['kind']) ? $_POST['kind'] : 0);
        $raw_p_2 = isset($_POST['p_2']) ? $_POST['p_2'] : '';
        $raw_p_1 = isset($_POST['p_1']) ? $_POST['p_1'] : '';
        $raw_p_0 = isset($_POST['p_0']) ? $_POST['p_0'] : '';
        $error = q8_user_site_price_rule_validate($name, $raw_p_2, $raw_p_1, $raw_p_0);
        if ($error !== '') {
            exit(json_encode(array('code' => -1, 'msg' => $error), JSON_UNESCAPED_UNICODE));
        }
        $p_2 = floatval($raw_p_2);
        $p_1 = floatval($raw_p_1);
        $p_0 = floatval($raw_p_0);
        if (q8_user_site_price_rule_duplicate($userrow['zid'], $name)) {
            exit('{"code":-1,"msg":"模板名称已存在"}');
        }
        $result = $DB->exec(
            "INSERT INTO pre_price (zid,kind,name,p_0,p_1,p_2) VALUES (:zid,:kind,:name,:p0,:p1,:p2)",
            array(
                ':zid' => intval($userrow['zid']),
                ':kind' => $kind,
                ':name' => $name,
                ':p0' => $p_0,
                ':p1' => $p_1,
                ':p2' => $p_2,
            )
        );
        if ($result !== false) {
            $CACHE->clear('pricerules');
            exit('{"code":0,"msg":"分站加价模板新增成功"}');
        }
        exit(json_encode(array('code' => -1, 'msg' => '分站加价模板新增失败：' . $DB->error()), JSON_UNESCAPED_UNICODE));
        break;
    case 'edit_site_price_rule':
        if ($userrow['power'] <= 0) {
            exit('{"code":-1,"msg":"没有权限使用此功能"}');
        }
        $id = intval(isset($_POST['prid']) ? $_POST['prid'] : 0);
        if (!q8_price_rule_exists_for_owner($id, $userrow['zid'])) {
            exit('{"code":-1,"msg":"模板不存在或无权操作"}');
        }
        $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
        $kind = intval(isset($_POST['kind']) ? $_POST['kind'] : 0);
        $raw_p_2 = isset($_POST['p_2']) ? $_POST['p_2'] : '';
        $raw_p_1 = isset($_POST['p_1']) ? $_POST['p_1'] : '';
        $raw_p_0 = isset($_POST['p_0']) ? $_POST['p_0'] : '';
        $error = q8_user_site_price_rule_validate($name, $raw_p_2, $raw_p_1, $raw_p_0);
        if ($error !== '') {
            exit(json_encode(array('code' => -1, 'msg' => $error), JSON_UNESCAPED_UNICODE));
        }
        $p_2 = floatval($raw_p_2);
        $p_1 = floatval($raw_p_1);
        $p_0 = floatval($raw_p_0);
        if (q8_user_site_price_rule_duplicate($userrow['zid'], $name, $id)) {
            exit('{"code":-1,"msg":"模板名称已存在"}');
        }
        $result = $DB->exec(
            "UPDATE pre_price SET kind=:kind,name=:name,p_0=:p0,p_1=:p1,p_2=:p2 WHERE id=:id AND zid=:zid",
            array(
                ':kind' => $kind,
                ':name' => $name,
                ':p0' => $p_0,
                ':p1' => $p_1,
                ':p2' => $p_2,
                ':id' => $id,
                ':zid' => intval($userrow['zid']),
            )
        );
        if ($result !== false) {
            $CACHE->clear('pricerules');
            exit('{"code":0,"msg":"分站加价模板修改成功"}');
        }
        exit(json_encode(array('code' => -1, 'msg' => '分站加价模板修改失败：' . $DB->error()), JSON_UNESCAPED_UNICODE));
        break;
    case 'delete_site_price_rule':
        if ($userrow['power'] <= 0) {
            exit('{"code":-1,"msg":"没有权限使用此功能"}');
        }
        $id = intval(isset($_POST['id']) ? $_POST['id'] : 0);
        if (!q8_price_rule_exists_for_owner($id, $userrow['zid'])) {
            exit('{"code":-1,"msg":"模板不存在或无权操作"}');
        }
        $DB->beginTransaction();
        try {
            if (intval($userrow['site_prid']) === $id) {
                $DB->exec("UPDATE pre_site SET site_prid=0 WHERE zid=:zid", array(':zid' => intval($userrow['zid'])));
            }
            $DB->exec(
                "DELETE FROM pre_price WHERE id=:id AND zid=:zid LIMIT 1",
                array(':id' => $id, ':zid' => intval($userrow['zid']))
            );
            $DB->commit();
            $CACHE->clear('pricerules');
            exit('{"code":0,"msg":"分站加价模板删除成功"}');
        } catch (Exception $e) {
            $DB->rollback();
            exit(json_encode(array('code' => -1, 'msg' => '分站加价模板删除失败：' . $e->getMessage()), JSON_UNESCAPED_UNICODE));
        }
        break;
    case 'set_site_price_rule':
        if ($userrow['power'] <= 0) {
            exit('{"code":-1,"msg":"没有权限使用此功能"}');
        }
        $id = intval(isset($_POST['id']) ? $_POST['id'] : 0);
        if ($id < 0) {
            $id = 0;
        }
        if ($id > 0 && !q8_price_rule_exists_for_owner($id, $userrow['zid'])) {
            exit('{"code":-1,"msg":"模板不存在或无权使用"}');
        }
        $result = $DB->exec(
            "UPDATE pre_site SET site_prid=:site_prid WHERE zid=:zid",
            array(':site_prid' => $id, ':zid' => intval($userrow['zid']))
        );
        if ($result !== false) {
            exit('{"code":0,"msg":"当前分站加价模板已更新"}');
        }
        exit(json_encode(array('code' => -1, 'msg' => '当前分站加价模板更新失败：' . $DB->error()), JSON_UNESCAPED_UNICODE));
        break;
    default:
        exit('{"code":-4,"msg":"No Act"}');
        break;
}
