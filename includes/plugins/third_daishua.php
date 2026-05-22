<?php
namespace plugins;

class third_daishua{

	private $config = [];

	static public $info = [
		'name'        => 'third_daishua',
		'type'        => 'third',
		'title'       => '同系统对接',
		'author'      => '岁岁 @qqfaka',
		'version'     => '1.0',
		'link'        => '',
		'sort'        => 32,
		'showedit'    => false,
		'showip'      => false,
		'batchgoods'    => true,
		'pricejk'     => 1,
		'input' => [
			'url' => '网站域名',
			'username' => '登录账号',
			'password' => '登录密码',
			'paypwd' => false,
			'paytype' => false,
		],
	];

	public function __construct($config)
	{
		$this->config = $config;
	}

	public function do_goods($goods_id, $goods_type, $goods_param, $num = 1, $input = array(), $money, $tradeno, $inputsname)
	{
		$result['code'] = -1;
		$url = '/api.php?act=pay';
		$param = array();

		// 如果goods_id为0，尝试从goods_param中提取gid
		if($goods_id == 0 && !empty($goods_param)){
			$goods_param_parts = explode('#', $goods_param);
			$goods_id = isset($goods_param_parts[0]) ? $goods_param_parts[0] : 0;
		}

		$param['tid'] = $goods_id;
		$param['user'] = $this->config['username'];
		$param['pass'] = $this->config['password'];
		$param['num'] = $num;
		if(is_array($input) && $input) {
			$i = 1;
			foreach ($input as $val) {
				if($val){
					$param[ 'input' . $i ] = $val;
					$i++;
				}
			}
		}
		$post = http_build_query($param);

		$data = $this->get_curl($url,$post);

		$json = json_decode($data,true);
		if (isset($json['orderid'])) {
			$result = array(
				'code' => 0,
				'id' => $json['orderid']
			);
			if($json['faka']==true){
				$result['faka']=true;
				$result['kmdata']=$json['kmdata'];
			}
		} elseif(isset($json['message'])){
			$result['message'] = $json['message'];
		} else{
			$result['message'] = $data;
		}
		return $result;
	}

	public function goods_list(){
		$url = '/api.php?act=goodslist';
		$post = 'user='.urlencode($this->config['username']).'&pass='.urlencode($this->config['password']);
		$ret = $this->get_curl($url, $post);
		if (!$ret = json_decode($ret, true)) {
			return '打开对接网站失败';
		} elseif ($ret['code'] != 0) {
			return $ret['message'];
		} else {
			$list = array();
			foreach ($ret['data'] as $v) {
				$list[] = $this->format_goods_row($v, 'id');
			}
			return $list;
		}
	}

	public function goods_info($goods_id){
		$url = '/api.php?act=goodsdetails';
		$post = 'tid='.$goods_id.'&user='.urlencode($this->config['username']).'&pass='.urlencode($this->config['password']);
		$data = $this->get_curl($url, $post);
		if (!$ret = json_decode($data, true)) {
			return '打开对接网站失败';
		} elseif ($ret['code'] != 0) {
			return $ret['message'];
		} else {
			if($ret['data']['shopimg']&&substr($ret['data']['shopimg'],0,4)!='http'&&substr($ret['data']['shopimg'],0,2)!='//')$ret['data']['shopimg'] = ($this->config['protocol']==1?'https://':'http://') . $this->config['url'] .'/'.$ret['data']['shopimg'];

			// 获取分类名称 - 岁岁 @qqfaka修改，岁岁 @qqfaka
			if(isset($ret['data']['cid']) && !empty($ret['data']['cid'])){
				$class_list = $this->class_list();
				if(is_array($class_list)){
					foreach($class_list as $class){
						if($class['cid'] == $ret['data']['cid']){
							$ret['data']['class_name'] = $class['name'];
							$ret['data']['category'] = $class['name'];
							break;
						}
					}
				}
			}

			return $ret['data'];
		}
	}

	public function query_order($orderid, $goodsid, $value = []){
		$order_state = array(0=>'待处理',1=>'已完成',2=>'正在处理',3=>'异常',4=>'已退单');
		$url = '/api.php?act=search&id='.$orderid;
		$post = 'user='.urlencode($this->config['username']).'&pass='.urlencode($this->config['password']);
		$data = $this->get_curl($url, $post);
		if (!$arr = json_decode($data , true)) {
			return false;
		} elseif (isset($arr['code']) && $arr['code'] == 0) {
			if (isset($arr['data']) && is_array($arr['data'])) {
				return $arr['data'];
			} elseif(isset($arr['status'])) {
				return ['订单状态'=>$order_state[$arr['status']]];
			}
		} elseif (isset($arr['message'])) {
			return $arr['message'];
		}
	}

	public function class_list(){
		$url = '/api.php?act=classlist';
		$ret = $this->get_curl($url);
		if (!$ret = json_decode($ret, true)) {
			return '打开对接网站失败';
		}  elseif ($ret['code'] == -5) {
			return '对方网站未更新最新版本';
		} elseif ($ret['code'] != 0) {
			return $ret['message'];
		} else {
			return $ret['data'];
		}
	}

	public function goods_list_by_cid($cid){
		$url = '/api.php?act=goodslistbycid';
		$post = 'cid='.$cid.'&user='.urlencode($this->config['username']).'&pass='.urlencode($this->config['password']);
		$ret = $this->get_curl($url, $post);
		if (!$ret = json_decode($ret, true)) {
			return '打开对接网站失败';
		} elseif ($ret['code'] == -5) {
			return '对方网站未更新最新版本';
		} elseif ($ret['code'] != 0) {
			return $ret['message'];
		} else {
			$list = [];
			foreach ($ret['data'] as $v) {
				if($v['shopimg']&&substr($v['shopimg'],0,4)!='http'&&substr($v['shopimg'],0,2)!='//')$v['shopimg'] = ($this->config['protocol']==1?'https://':'http://') . $this->config['url'] .'/'.$v['shopimg'];
				$list[] = $v;
			}
			return $list;
		}
	}

	public function pricejk($shequid,&$success){
		global $DB,$conf;
		$list = $this->goods_list();
		if(is_array($list)){
			$price_arr = array();
			$goods_status_arr = array();
			$stock_arr = array();
			$value_arr = array();
			foreach($list as $row){
				$price_arr[$row['id']] = $row['price'];
				$goods_status_arr[$row['id']] = $row['close'];
				$stock_arr[$row['id']] = $row['stock'];
				$value_arr[$row['id']] = $row['value'];
			}
			$rs2=$DB->query("SELECT * FROM pre_tools WHERE is_curl=2 AND shequ='{$shequid}' AND active=1 AND cid IN ({$conf['pricejk_cid']})");
			while($res2 = $rs2->fetch())
			{
				if($res2['price']==='0.00')continue;
				if(isset($price_arr[$res2['goods_id']]) && $price_arr[$res2['goods_id']]>0){
					$price = ceil($price_arr[$res2['goods_id']] * $res2['value'] * 100)/100;

					$has_custom_price = $DB->getColumn("SELECT COUNT(*) FROM pre_site_price WHERE tid='{$res2['tid']}'");

					if($conf['pricejk_edit']==1 && $price>$res2['price']){
						if($has_custom_price > 0){
							$DB->exec("UPDATE pre_site_price SET price ='{$price}', update_time = NOW() WHERE tid='{$res2['tid']}'");
						} else {
							$DB->exec("update `pre_tools` set `price` ='{$price}' where `tid`='{$res2['tid']}'");
						}
						$success++;
					}elseif($conf['pricejk_edit']==0 && $price!=$res2['price']){
						if($has_custom_price > 0){
							$DB->exec("UPDATE pre_site_price SET price ='{$price}', update_time = NOW() WHERE tid='{$res2['tid']}'");
						} else {
							$DB->exec("update `pre_tools` set `price` ='{$price}' where `tid`='{$res2['tid']}'");
						}
						$success++;
					}
				}
				if(isset($goods_status_arr[$res2['goods_id']])){
					if($goods_status_arr[$res2['goods_id']]==1 && $res2['close']==0){
						$DB->exec("update `pre_tools` set `close`=1 where `tid`='{$res2['tid']}'");
					}elseif($goods_status_arr[$res2['goods_id']]==0 && $res2['close']==1){
						$DB->exec("update `pre_tools` set `close`=0 where `tid`='{$res2['tid']}'");
					}
				}else{
					$DB->exec("update `pre_tools` set `close`=1 where `tid`='{$res2['tid']}'");
				}
				if(isset($stock_arr[$res2['goods_id']]) && $stock_arr[$res2['goods_id']]!==null && $res2['stock']!=$stock_arr[$res2['goods_id']]){
					$DB->exec("update `pre_tools` set `stock`=:stock where `tid`='{$res2['tid']}'", [':stock'=>$stock_arr[$res2['goods_id']]]);
				}
				if(isset($value_arr[$res2['goods_id']]) && $value_arr[$res2['goods_id']] > 0 && $res2['value'] != $value_arr[$res2['goods_id']]){
					$DB->exec("update `pre_tools` set `value`=:value where `tid`='{$res2['tid']}'", [':value'=>$value_arr[$res2['goods_id']]]);
				}
			}
			return true;
		}else{
			return $list;
		}
	}

	public function batch_goods_list(){
		$url = '/api.php?act=goodslist';
		$post = 'user='.urlencode($this->config['username']).'&pass='.urlencode($this->config['password']);
		$ret = $this->get_curl($url, $post);
		if (!$ret = json_decode($ret, true)) {
			return '打开对接网站失败';
		} elseif ($ret['code'] != 0) {
			return $ret['message'];
		} else {
			$list = array();
			foreach ($ret['data'] as $v) {
				$list[] = $this->format_goods_row($v, 'goods_id');
			}
			return $list;
		}
	}

	private function format_goods_row($v, $id_key = 'id'){
		if(isset($v['shopimg']) && $v['shopimg'] && substr($v['shopimg'],0,4)!='http' && substr($v['shopimg'],0,2)!='//') {
			$v['shopimg'] = ($this->config['protocol']==1?'https://':'http://') . $this->config['url'] . '/' . $v['shopimg'];
		}
		$row = array(
			$id_key => isset($v['tid']) ? $v['tid'] : (isset($v['id']) ? $v['id'] : (isset($v['goods_id']) ? $v['goods_id'] : 0)),
			'cid' => isset($v['cid']) ? $v['cid'] : 0,
			'name' => isset($v['name']) ? $v['name'] : '',
			'value' => isset($v['value']) ? $v['value'] : 1,
			'shopimg' => isset($v['shopimg']) && $v['shopimg'] ? $v['shopimg'] : '',
			'close' => isset($v['close']) ? $v['close'] : 0,
			'price' => isset($v['price']) ? $v['price'] : 0,
			'stock' => array_key_exists('stock', $v) ? $v['stock'] : null
		);
		foreach(array('multi','min','max','isfaka','goods_type','repeat','validate','valiserv','input','inputs','alert','prices') as $key) {
			if(array_key_exists($key, $v)) $row[$key] = $v[$key];
		}
		if(!isset($row['isfaka']) && isset($row['goods_type'])) {
			$row['isfaka'] = intval($row['goods_type']) == 1 ? 1 : 0;
		}
		if(!isset($row['goods_type']) && isset($row['isfaka'])) {
			$row['goods_type'] = intval($row['isfaka']) == 1 ? 1 : 0;
		}
		return $row;
	}

	private function get_curl($path,$post=0){
		// 确保 URL 不包含重复的协议前缀
		$base_url = $this->config['url'];
		// 移除可能存在的协议前缀
		$base_url = preg_replace('/^(https?:\/\/)/i', '', $base_url);
		$url = ($this->config['protocol']==1?'https://':'http://') . $base_url . $path;
		return get_curl($url,$post);
	}
}
