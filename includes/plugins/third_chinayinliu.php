<?php
namespace plugins;

class third_chinayinliu {

    private $config = [];

    static public $info = [
        'name'       => 'third_chinayinliu',
        'type'       => 'third',
        'title'      => 'chinayinliu对接',
        'author'     => 'custom',
        'version'    => '1.5',
        'link'       => '',
        'sort'       => 50,
        'showedit'   => false,
        'showip'     => false,
        'batchgoods' => true,
        'pricejk'    => 1,
        'input' => [
            'url'      => '网站地址',
            'username' => 'API Key',
            'password' => '随意填（不使用）',
            'paypwd'   => false,
            'paytype'  => false,
        ],
    ];

    public function __construct($config) {
        $this->config = $config;
    }

    private function api($params) {
        $params['key'] = $this->config['username'];
        $base = preg_replace('/^https?:\/\//i', '', $this->config['url']);
        $url  = ($this->config['protocol'] == 1 ? 'https://' : 'http://') . $base . '/api/v2';
        return get_curl($url, http_build_query($params));
    }

    private function _services() {
        static $cache = null;
        if ($cache === null) {
            $raw   = $this->api(['action' => 'services']);
            $cache = json_decode($raw, true);
            if (!is_array($cache)) $cache = [];
        }
        return $cache;
    }

    private function _cat_map() {
        $map = [];
        $cid = 1;
        foreach ($this->_services() as $s) {
            $cat = $s['category'] ?? '未分类';
            if (!isset($map[$cat])) $map[$cat] = $cid++;
        }
        return $map;
    }

    public function class_list() {
        $result = [];
        foreach ($this->_cat_map() as $name => $cid) {
            $result[] = ['cid' => $cid, 'name' => $name];
        }
        return $result ?: '获取分类失败';
    }

    public function goods_list_by_cid($cid) {
        $cat_map  = $this->_cat_map();
        $cat_name = array_search($cid, $cat_map);
        if ($cat_name === false) return [];
        $result = [];
        foreach ($this->_services() as $s) {
            if (($s['category'] ?? '未分类') !== $cat_name) continue;
            $isPackage = ($s['type'] === 'Package') || ($s['min'] == 1 && $s['max'] == 1);
            // Package类：固定价格，不需要数量输入
            // Default类：rate为每千个价格，开启数量输入
            $price = $isPackage ? floatval($s['rate']) : round(floatval($s['rate']) / 1000, 4);
            $multi = $isPackage ? 0 : 1;
            $min   = intval($s['min']);
            $max   = intval($s['max']);
            $value = 1; // 每份=1个，买家输入的份数即实际数量
            $result[] = [
                'tid'      => $s['service'],
                'name'     => $s['name'],
                'price'    => $price,
                'value'    => $value,
                'min'      => $min,
                'max'      => $max,
                'stock'    => -1,
                'close'    => 0,
                'shopimg'  => '',
                'input'    => '链接/账号',
                'inputs'   => '',
                'desc'     => '',
                'alert'    => '',
                'isfaka'   => 0,
                'repeat'   => 0,
                'multi'    => $multi,
                'validate' => '',
                'valiserv' => '',
            ];
        }
        return $result;
    }

    public function goods_list() {
        $cat_map = $this->_cat_map();
        $result  = [];
        foreach ($this->_services() as $s) {
            $cat       = $s['category'] ?? '未分类';
            $isPackage = ($s['type'] === 'Package') || ($s['min'] == 1 && $s['max'] == 1);
            $price     = $isPackage ? floatval($s['rate']) : round(floatval($s['rate']) / 1000, 4);
            $result[] = [
                'id'      => $s['service'],
                'cid'     => $cat_map[$cat],
                'name'    => $s['name'],
                'value'   => 1,
                'price'   => $price,
                'stock'   => -1,
                'close'   => 0,
                'shopimg' => '',
            ];
        }
        return $result ?: '获取商品列表失败';
    }

    public function batch_goods_list() {
        $list = $this->goods_list();
        if (!is_array($list)) return $list;
        $result = [];
        foreach ($list as $v) {
            $result[] = [
                'goods_id' => $v['id'],
                'cid'      => $v['cid'],
                'name'     => $v['name'],
                'value'    => $v['value'],
                'price'    => $v['price'],
                'stock'    => $v['stock'],
                'close'    => $v['close'],
                'shopimg'  => '',
            ];
        }
        return $result;
    }

    public function do_goods($goods_id, $goods_type, $goods_param, $num = 1, $input = [], $money, $tradeno, $inputsname) {
        $result = ['code' => -1];
        $link = isset($input[0]) ? trim($input[0]) : '';
        if (!$link) {
            $result['message'] = '请填写链接/账号';
            return $result;
        }
        // 从API获取该服务的最小数量
        $services = $this->_services();
        foreach ($services as $s) {
            if ($s['service'] == $goods_id) {
                $min_qty = intval($s['min']);
                if ($min_qty > 1 && $num < $min_qty) $num = $min_qty;
                break;
            }
        }
        $raw  = $this->api(['action' => 'add', 'service' => $goods_id, 'link' => $link, 'quantity' => $num]);
        $json = json_decode($raw, true);
        if (!empty($json['order'])) {
            $result = ['code' => 0, 'id' => $json['order']];
        } else {
            $result['message'] = $json['error'] ?? ('下单失败: ' . $raw);
        }
        return $result;
    }

    public function query_order($orderid, $goodsid, $value = []) {
        $raw = $this->api(['action' => 'status', 'order' => $orderid]);
        $arr = json_decode($raw, true);
        if (!$arr) return false;
        $map = [
            'Pending'     => '等待中',
            'In progress' => '进行中',
            'Processing'  => '处理中',
            'Completed'   => '已完成',
            'Partial'     => '部分完成',
            'Canceled'    => '已取消',
        ];
        return ['订单状态' => ($map[$arr['status']] ?? $arr['status']), '剩余数量' => ($arr['remains'] ?? '')];
    }

    public function pricejk($shequid, &$success) {
        global $DB, $conf;
        $list = $this->goods_list();
        if (!is_array($list)) return $list;
        $price_arr = [];
        foreach ($list as $row) $price_arr[$row['id']] = $row['price'];
        $rs = $DB->query("SELECT * FROM pre_tools WHERE is_curl=2 AND shequ='{$shequid}' AND active=1 AND cid IN ({$conf['pricejk_cid']})");
        while ($res = $rs->fetch()) {
            if ($res['price'] === '0.00') continue;
            if (isset($price_arr[$res['goods_id']]) && $price_arr[$res['goods_id']] > 0) {
                $price = ceil($price_arr[$res['goods_id']] * $res['value'] * 100) / 100;
                if (($conf['pricejk_edit'] == 1 && $price > $res['price']) ||
                    ($conf['pricejk_edit'] == 0 && $price != $res['price'])) {
                    $DB->exec("update `pre_tools` set `price`='{$price}' where `tid`='{$res['tid']}'");
                    $success++;
                }
            }
        }
        return true;
    }
}
