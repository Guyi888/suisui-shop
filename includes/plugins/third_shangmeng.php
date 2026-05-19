<?php
namespace plugins;

class third_shangmeng{

	private $config = [];

	static public $info = [
		'name'        => 'third_shangmeng',
		'type'        => 'third',
		'title'       => '商盟网',
		'author'      => '岁岁 @qqfaka',
		'version'     => '1.0',
		'link'        => '',
		'sort'        => 23,
		'showedit'    => false,
		'showip'      => false,
		'pricejk'     => 2,
		'input' => [
			'url' => '网站域名',
			'username' => '登录账号',
			'password' => '接口密钥',
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
		foreach ($input as $key=>$val){
			$input[$key] = urlencode($val);
		}

		$param = array('commodityBranchId'=>$goods_id, 'external_orderno'=>$tradeno, 'buyCount'=>$num, 'externalSellPrice'=>$money, 'template'=>json_encode($input));
		$post = json_encode($param);
		$sign = strtoupper(md5($post.$this->config['password']));
		$url = '/api/UserOrder/CreateOrder?userName='.urlencode($this->config['username']).'&sign='.$sign;

		$data = $this->get_curl($url, $post);
		$json = json_decode($data,true);
		if(isset($json['code']) && $json['code']==200){
			$result = array(
				'code' => 0,
				'id' => $json['data']['orderNo']
			);
		}elseif(isset($json['msg'])){
			$result['message'] = $json['msg'];
		}else{
			$result['message'] = $data;
		}
		return $result;
	}

	public function goods_list($page=1){
		$param = array('VirtualDelivery'=>3, 'page'=>$page);
		$post = json_encode($param);
		$sign = strtoupper(md5($post.$this->config['password']));
		$url = '/api/UserCommdity/GetCommodityList?userName='.urlencode($this->config['username']).'&sign='.$sign;
		$ret = $this->get_curl($url, $post);
		if (!$ret = json_decode($ret, true)) {
			return '打开对接网站失败';
		} else if ($ret['code'] != 200) {
			return $ret['msg'];
		} else {
			$list = array();
			foreach ($ret['data'] as $v) {
				$list[] = array(
					'id' => $v['branchId'],
					'name' => $v['name'].'-'.$v['branchName'],
					'shopimg' => $v['branchImg']?$v['branchImg']:$v['MainImg'],
					'price' => $v['price']
				);
			}
			return $list;
		}
	}

	public function goods_info($goods_id){
		$param = array('commodityBranchId'=>$goods_id);
		$post = json_encode($param);
		$sign = strtoupper(md5($post.$this->config['password']));
		$url = '/api/UserOrder/GetCommodityInfo?userName='.urlencode($this->config['username']).'&sign='.$sign;
		$data = $this->get_curl($url, $post);
		if (!$ret = json_decode($data, true)) {
			return '打开对接网站失败';
		} elseif ($ret['code'] != 200) {
			return $ret['msg'];
		} else {
			$return['id'] = $ret['data']['branchId'];
			$return['mainid'] = $ret['data']['mainId'];
			$return['name'] = $ret['data']['name'].'-'.$ret['data']['branchName'];
			$return['img'] = $ret['data']['branchImg']?$ret['data']['branchImg']:$ret['data']['MainImg'];
			$return['price'] = $ret['data']['price'];
			$template = json_decode($ret['data']['template'], true);
			$return['input'] = $template[0]['txt'];
			$inputs = '';
			foreach($template as $row){
				if($return['input'] == $row['txt'])continue;
				if($row['type'] == 'select' || $row['type'] == 'radio'){
					$options = '';
					foreach($row['selectData'] as $option){
						$options .= $option['value'].':'.$option['label'].',';
					}
					$inputs .= $row['txt'].'{'.trim($options,',').'}|';
				}else{
					$inputs .= $row['txt'].'|';
				}
			}
			$return['inputs'] = trim($inputs,'|');
			return $return;
		}
	}

	public function query_order($orderid, $goodsid, $value = []){
		$order_state = array(0=>'等待中',2=>'等待发货，等待处理',3=>'已发货，物流派送中',4=>'已完成',5=>'异常',6=>'正在处理');
		$param = array('orderNo'=>$orderid);
		$post = json_encode($param);
		$sign = strtoupper(md5($post.$this->config['password']));
		$url = '/api/UserOrder/QueryOrderModel?userName='.urlencode($this->config['username']).'&sign='.$sign;
		$data = $this->get_curl($url, $post);
		if (!$ret = json_decode($data, true)) {
			return false;
		} elseif ($ret['code'] != 200) {
			return $ret['msg'];
		} else{
			$v = $ret['data'];
			$result = array('订单状态'=>$order_state[$v['status']]);
			if($v['sellerMessage'][0]['message']){
				$result['卖家留言'] = $v['sellerMessage'][0]['message'];
			}
			return $result;
		}
	}

	public function pricejk_one($tool){
		global $DB,$conf;
		$success=0;
		$details = $this->goods_info($tool['goods_id']);
		if(is_array($details)){
			$rs2=$DB->query("SELECT * FROM pre_tools WHERE is_curl=2 AND shequ={$tool['shequ']} AND goods_id={$tool['goods_id']}");
			while($res2 = $rs2->fetch())
			{
				if($res2['price']==='0.00')continue;
				$price = ceil($details['price'] * 100)/100;
				if($conf['pricejk_edit']==1 && $price>$res2['price'] && $res2['prid']>0){
					$DB->exec("update `pre_tools` set `price` ='{$price}' where `tid`='{$res2['tid']}'");
					$success++;
				}elseif($conf['pricejk_edit']==0 && $price!=$res2['price'] && $res2['prid']>0){
					$DB->exec("update `pre_tools` set `price` ='{$price}' where `tid`='{$res2['tid']}'");
					$success++;
				}
				$DB->exec("update `pre_tools` set `uptime`='".time()."' where `tid`='{$res2['tid']}'");
			}
		}elseif(strpos($details,'商品不存在')!==false){
			$rs2=$DB->query("SELECT * FROM pre_tools WHERE is_curl=2 AND shequ={$tool['shequ']} AND goods_id={$tool['goods_id']}");
			while($res2 = $rs2->fetch())
			{
				$DB->exec("update `pre_tools` set `close`=1,`uptime`='".time()."' where `tid`='{$res2['tid']}'");
				$success++;
			}
		}
		return $success;
	}

	public function pricejk($shequid,&$success){
		global $DB,$conf;
		$pricejk_time = $conf['pricejk_time']?$conf['pricejk_time']:600;
		for($i=0;$i<30;$i++){
			$tool=$DB->getRow("SELECT * FROM pre_tools WHERE is_curl=2 AND shequ='{$shequid}' AND active=1 AND cid IN ({$conf['pricejk_cid']}) AND uptime<'".(time()-$pricejk_time)."' ORDER BY uptime ASC");
			if(!$tool)break;
			$count = $this->pricejk_one($tool);
			$success+=$count;
		}
		return true;
	}

	private function get_curl($path,$post=0,$referer=0,$cookie=0,$header=0,$addheader=0){
		$url = 'http://open.shangmeng.top' . $path;
		return shequ_get_curl($url,$post,$referer,$cookie,$header,$addheader);
	}
}