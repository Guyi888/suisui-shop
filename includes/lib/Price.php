<?php

namespace lib;



class Price {

	private $zid;

	private $upzid;

	private $power;

	private $user;

	private $price_array = array();

	private $up_price_array = array();

	private $iprice_array = array();

	private $tool = array();

	private $site_prid = 0;

	private $site_rule_price_array = array();

	private $parent_power = 0;

	private $parent_site_prid = 0;

	private $parent_iprice_array = array();

	private $parent_manage_self_cost = 0;

	private $manage_self_cost = 0;

	private static $price_rules;



	public function __construct($zid,$siterow=null){

		global $DB;

		if($zid == 1)return;

		if(!$siterow)$siterow=$this->getSiteInfo($zid);

		$this->endtime = $siterow['endtime'];

		// 从新表中获取价格数据
		$this->price_array = [];
		$this->iprice_array = [];
		$this->up_price_array = [];

		if($siterow['power']==2){

			$this->zid = $zid;
			$this->power = $siterow['power'];
			$this->site_prid = isset($siterow['site_prid']) ? intval($siterow['site_prid']) : 0;

			$this->price_array = $this->loadSitePriceRows($zid);

			$this->iprice_array = $this->decodePriceArray(isset($siterow['iprice']) ? $siterow['iprice'] : '');

			if(intval($siterow['upzid']) > 1 && $data = $DB->getRow("SELECT zid,power,site_prid,iprice FROM pre_site WHERE zid='{$siterow['upzid']}' AND power=2 LIMIT 1")){
				$this->parent_power = intval($data['power']);
				$this->parent_site_prid = isset($data['site_prid']) ? intval($data['site_prid']) : 0;
				$this->parent_iprice_array = $this->decodePriceArray(isset($data['iprice']) ? $data['iprice'] : '');
				$this->upzid = intval($data['zid']);
				$this->up_price_array = $this->loadSitePriceRows($data['zid']);
			}

		}elseif($siterow['power']==1){

			$this->zid = $zid;
			$this->power = $siterow['power'];
			$this->site_prid = isset($siterow['site_prid']) ? intval($siterow['site_prid']) : 0;

			$this->price_array = $this->loadSitePriceRows($zid);

			$this->iprice_array = $this->decodePriceArray(isset($siterow['iprice']) ? $siterow['iprice'] : '');

			if($data = $DB->getRow("SELECT zid,price,site_prid,iprice FROM pre_site WHERE zid='{$siterow['upzid']}' AND power=2 LIMIT 1")){
				$this->parent_power = 2;
				$this->parent_site_prid = isset($data['site_prid']) ? intval($data['site_prid']) : 0;
				$this->parent_iprice_array = $this->decodePriceArray(isset($data['iprice']) ? $data['iprice'] : '');
				$this->up_price_array = $this->loadSitePriceRows($data['zid']);
				$this->upzid=$data['zid'];
			}

		}elseif($siterow['power']==0){

			$this->user = true;

			if($data = $DB->getRow("SELECT zid,upzid,power,price,iprice,site_prid FROM pre_site WHERE zid='{$siterow['upzid']}' LIMIT 1")){

				$this->zid = $data['zid'];
				$this->power = $data['power'];
				$this->site_prid = isset($data['site_prid']) ? intval($data['site_prid']) : 0;

				$this->price_array = $this->loadSitePriceRows($this->zid);

				$this->iprice_array = $this->decodePriceArray(isset($data['iprice']) ? $data['iprice'] : '');

				if($this->power > 0 && $data['upzid']>1 && $data = $DB->getRow("SELECT zid,price,site_prid,iprice FROM pre_site WHERE zid='{$data['upzid']}' and power=2 limit 1")){
					$this->parent_power = 2;
					$this->parent_site_prid = isset($data['site_prid']) ? intval($data['site_prid']) : 0;
					$this->parent_iprice_array = $this->decodePriceArray(isset($data['iprice']) ? $data['iprice'] : '');
					$this->up_price_array = $this->loadSitePriceRows($data['zid']);
					$this->upzid=$data['zid'];
				}

			}

		}

	}

	public function setToolInfo($tid,$row=null){

		global $DB,$CACHE;

		if(!$row)$row=$this->getToolInfo($tid);

		$hasIprice = isset($this->iprice_array[$tid]) && floatval($this->iprice_array[$tid]) > 0;

		// 保存原始价格，用于后续计算
		$original_price = $row['price'];

		if($row['prid']==0){
			// 不加价，保持原始价格
		} elseif($price_rules = $this->getPriceRules($row['prid'])){
			// 应用加价模板
			if($price_rules['kind']==1){
				// 固定金额加价
				$row['price'] = round($original_price + $price_rules['p_0'], 2);
				$row['cost'] = round($original_price + $price_rules['p_1'], 2);
				$row['cost2'] = round($original_price + $price_rules['p_2'], 2);
			} else {
				// 倍数加价
				$row['price'] = round($original_price * $price_rules['p_0'], 2);
				$row['cost'] = round($original_price * $price_rules['p_1'], 2);
				$row['cost2'] = round($original_price * $price_rules['p_2'], 2);
			}
		} else {
			// 对应加价模板被删除或不存在
			$row['cost'] = $row['price'];
			$row['cost2'] = $row['price'];
		}

		// 确保价格为正数
		if($row['price'] <= 0) $row['price'] = $original_price;
		if($row['cost'] <= 0) $row['cost'] = $row['price'];
		if($row['cost2'] <= 0) $row['cost2'] = $row['cost'];

		$parentInheritedPrice = $this->buildParentProfessionalPrice($tid, $row);
		if($parentInheritedPrice){
			$this->up_price_array[$tid] = $parentInheritedPrice;
		}

		//应用自定义密价
		if($this->power==1 && $hasIprice){
			$row['cost'] = floatval($this->iprice_array[$tid]);
		}elseif($this->power==2 && $hasIprice){
			$row['cost2'] = floatval($this->iprice_array[$tid]);
		}

		if($this->power==2 && isset($this->up_price_array[$tid]['cost2']) && floatval($this->up_price_array[$tid]['cost2']) > 0){
			$parentCost2 = floatval($this->up_price_array[$tid]['cost2']);
			if($parentCost2 > floatval($row['cost2'])){
				$row['cost2'] = $parentCost2;
			}
			if(floatval($row['cost']) < floatval($row['cost2'])){
				$row['cost'] = $row['cost2'];
			}
		}

		$this->manage_self_cost = 0;
		if($this->power==2 && isset($row['cost2']) && $row['cost2']>0){
			$this->manage_self_cost = $row['cost2'];
		}elseif(isset($row['cost']) && $row['cost']>0){
			$this->manage_self_cost = $row['cost'];
		}elseif(isset($row['price']) && $row['price']>0){
			$this->manage_self_cost = $row['price'];
		}

		if(!$hasIprice && $this->site_prid > 0 && empty($this->price_array[$tid])){
			$site_base_price = 0;
			if($this->power == 2 && isset($row['cost2']) && $row['cost2'] > 0){
				$site_base_price = $row['cost2'];
			}elseif(isset($row['cost']) && $row['cost'] > 0){
				$site_base_price = $row['cost'];
			}else{
				$site_base_price = $row['price'];
			}

			$site_price = $this->buildPriceByRule($site_base_price, $this->site_prid);
			if($site_price){
				if($site_price['cost'] < $site_price['cost2'])$site_price['cost'] = $site_price['cost2'];
				if($site_price['price'] < $site_price['cost'])$site_price['price'] = $site_price['cost'];
				$this->site_rule_price_array[$tid] = $site_price;
			}
		}

		$this->tool=$row;

	}

	public function getMainPrice(){

		return $this->tool['price'];

	}

	public function getMainCost(){

		return $this->tool['cost'];

	}

	public function getMainCost2(){

		return $this->tool['cost2'];

	}

	public function getToolPrice($tid){

		global $islogin2,$conf,$date,$userrow;

		if($islogin2==1){

		    if($userrow['power'] < 1){

		        $this->user = true;

		    }

			if($this->user==true && $conf['user_level']==1){

				return $this->getToolCost($tid);

			}elseif($this->user==true || $conf['fenzhan_expiry']>0 && $this->endtime<$date){

			}elseif($this->power==1 && !$this->user){

				return $this->getToolCost($tid);

			}elseif($this->power==2 && !$this->user){

				return $this->getToolCost2($tid);

			}else{

			    $row = $this->getToolInfo($tid);

            if($row['prid'] == 0){

                return $row['price'];

            }else{

                $priceTemplate = $this->getPriceRules($row['prid']);

                if($priceTemplate && is_array($priceTemplate)){
                    if($priceTemplate['kind'] == 1){
                        return round($row['price'] + $priceTemplate['p_0'], 2);
                    }else{
                        return round($row['price'] * $priceTemplate['p_0'], 2);
                    }
                }else{
                    return $row['price'];
                }

            }

			}

		}

		$cost = $this->getToolCost($tid);

		$hasOwnTemplatePrice = empty($this->price_array[$tid]) && isset($this->site_rule_price_array[$tid]['price']) && $this->site_rule_price_array[$tid]['price'] >= $cost && $cost > 0;

		if(isset($this->price_array[$tid]['price']) && $this->price_array[$tid]['price'] && $this->price_array[$tid]['price']>=$cost && $cost>0){

			$price=$this->price_array[$tid]['price'];

		}elseif($hasOwnTemplatePrice){

			$price = $this->site_rule_price_array[$tid]['price'];

		}elseif(isset($this->up_price_array[$tid]['price']) && $this->up_price_array[$tid]['price'] && $this->up_price_array[$tid]['price']>=$cost && $cost>0){

			$price = $this->up_price_array[$tid]['price'];

		}elseif($cost>0 && $cost>$this->tool['price']){

			$price=$cost;

		}else{

			$price=$this->tool['price'];

		}

		return $price;

	}

	public function getToolCost($tid){
		if(isset($this->iprice_array[$tid]) && $this->iprice_array[$tid]>0){
			if($this->power==1 || ($this->power==2 && (!isset($this->price_array[$tid]['cost']) || $this->price_array[$tid]['cost']<=0))){
				return $this->iprice_array[$tid];
			}
		}
		$cost2 = $this->getToolCost2($tid);
		if($this->power<2 && isset($this->up_price_array[$tid]['cost']) && $this->up_price_array[$tid]['cost'] && $this->up_price_array[$tid]['cost']>=$cost2){
			$cost = $this->up_price_array[$tid]['cost'];
		}elseif($this->power==2 && isset($this->price_array[$tid]['cost']) && $this->price_array[$tid]['cost'] && $this->price_array[$tid]['cost']>=$cost2){
			$cost = $this->price_array[$tid]['cost'];
		}elseif($this->power==2 && isset($this->up_price_array[$tid]['cost2']) && $this->up_price_array[$tid]['cost2'] && $cost2>$this->tool['cost']){
			$cost = $cost2;
		}elseif($this->tool['cost']>0){
			$cost = $this->tool['cost'];
		}else{
			$cost = $this->tool['price'];
		}
		return $cost;
	}

	public function getToolCost2($tid){
		if($this->power==2 && isset($this->iprice_array[$tid]) && $this->iprice_array[$tid]>0){
			return $this->iprice_array[$tid];
		}
		$baseCost2 = 0;
		if(isset($this->tool['cost2']) && $this->tool['cost2']>0){
			$baseCost2 = $this->tool['cost2'];
		}elseif(isset($this->tool['cost']) && $this->tool['cost']>0){
			$baseCost2 = $this->tool['cost'];
		}else{
			$baseCost2 = $this->tool['price'];
		}

		if(isset($this->price_array[$tid]['cost2']) && $this->price_array[$tid]['cost2']>=$baseCost2){
			$cost = $this->price_array[$tid]['cost2'];
		}elseif($this->power==2 && isset($this->up_price_array[$tid]['cost2']) && $this->up_price_array[$tid]['cost2']>=$baseCost2){
			$cost = $this->up_price_array[$tid]['cost2'];
		}elseif($this->tool['cost2']>0){
			$cost = $this->tool['cost2'];
		}elseif($this->tool['cost']>0){
			$cost = $this->tool['cost'];
		}else{
			$cost = $this->tool['price'];
		}
		return $cost;
	}

	public function getManageSelfCostPrice($tid){
		if($this->power==2){
			if($this->manage_self_cost > 0){
				return $this->manage_self_cost;
			}
			if(isset($this->tool['cost2']) && $this->tool['cost2']>0){
				return $this->tool['cost2'];
			}elseif(isset($this->tool['cost']) && $this->tool['cost']>0){
				return $this->tool['cost'];
			}
		}
		return $this->getToolCost($tid);
	}

	public function getManageChildProfessionalPrice($tid){
		if(empty($this->price_array[$tid]) && isset($this->site_rule_price_array[$tid]['cost2']) && $this->site_rule_price_array[$tid]['cost2']>0){
			return $this->site_rule_price_array[$tid]['cost2'];
		}
		return $this->getToolCost2($tid);
	}

	public function getManageChildNormalPrice($tid){
		if(empty($this->price_array[$tid]) && isset($this->site_rule_price_array[$tid]['cost']) && $this->site_rule_price_array[$tid]['cost']>0){
			return $this->site_rule_price_array[$tid]['cost'];
		}
		return $this->getToolCost($tid);
	}

	public function getManageSalePrice($tid){
		return $this->getToolPrice($tid);
	}

	public function getToolDel($tid){
		if(isset($this->up_price_array[$tid]['del']) && intval($this->up_price_array[$tid]['del']) === 1){
			return 1;
		}
		return isset($this->price_array[$tid]['del']) ? intval($this->price_array[$tid]['del']) : 0;
	}

	public function getFinalPrice($price, $num){

		if(!empty($this->tool['prices'])){

			$prices = explode(',',$this->tool['prices']);

			foreach($prices as $item){

				$arrs = explode('|',$item);

				if($num>=$arrs[0])$discount=$arrs[1];

			}

			$price -= $discount;

			if($price<=0)return false;

		}

		return $price;

	}

	public function getTooliPrice($tid){

		if($this->power>0 && $this->iprice_array[$tid]>0){

			return $this->iprice_array[$tid];

		}else{

			return null;

		}

	}

	public function setToolProfit($tid,$num,$name,$money,$orderid,$userid=0,$orderCost=0){

		global $DB,$islogin2,$conf,$date;

		if(is_numeric($userid) && strlen($userid)!=32)$islogin2=1;

		$toolPrice = $this->getFinalPrice($this->getToolPrice($tid), $num);
		$num = floatval($num);
		if($num <= 0)$num = 1;
		$orderCost = floatval($orderCost);
		$actualUnitCost = $orderCost > 0 ? round($orderCost / $num, 4) : 0;

		if(round($toolPrice*$num,2) != round($money,2))return false;

		if($this->power==2){

			$selfCost = $actualUnitCost > 0 ? $actualUnitCost : $this->getManageSelfCostPrice($tid);
			if($selfCost <= 0){
				$selfCost = $this->getToolCost2($tid);
			}
			$profit=$toolPrice - $selfCost;

			if($profit>0 && $profit<$money){

				$tc_point=round($profit*$num, 2);

				$rs=$this->changeUserMoney($this->zid, $tc_point, '提成', '你网站用户下单 '.$name.' 获得'.$tc_point.'元提成（成交价'.$toolPrice.'，实际成本'.round($selfCost,2).'）', $orderid);

			}

			if($this->upzid>1 && $this->parent_manage_self_cost>0){

				$profit2 = $selfCost - $this->parent_manage_self_cost;

				if($profit2>0 && $profit2<$money){

					$tc_point=round($profit2*$num, 2);

					$rs=$this->changeUserMoney($this->upzid, $tc_point, '提成', '下级网站(ZID:'.$this->zid.')用户下单 '.$name.' 获得'.$tc_point.'元提成（下级拿货价'.round($selfCost,2).'，实际成本'.round($this->parent_manage_self_cost,2).'）', $orderid);

				}

			}

		}elseif($this->power==1){

			$profit=$toolPrice - $this->getToolCost($tid);

			if($profit>0 && $profit<$money){

				$tc_point=round($profit*$num, 2);

				$rs=$this->changeUserMoney($this->zid, $tc_point, '提成', '你网站用户下单 '.$name.' 获得'.$tc_point.'元提成（成交价'.$toolPrice.'，拿货价'.round($this->getToolCost($tid),2).'）', $orderid);

			}

			$upstreamCost = $actualUnitCost > 0 ? $actualUnitCost : $this->getToolCost2($tid);
			$profit2=$this->getToolCost($tid) - $upstreamCost;

			if($profit2>0 && $profit2<$money && $this->upzid>1){

				$tc_point=round($profit2*$num, 2);

				$rs=$this->changeUserMoney($this->upzid, $tc_point, '提成', '你下级网站(ZID:'.$this->zid.')用户下单 '.$name.' 获得'.$tc_point.'元提成（下级拿货价'.round($this->getToolCost($tid),2).'，实际成本'.round($upstreamCost,2).'）', $orderid);

			}

		}

		return $rs;

	}

	public function setPriceInfo($tid,$del,$price,$cost=0,$cost2=null){
		global $DB;

		// 确保price_array[$tid]存在
		if(!isset($this->price_array[$tid])) {
			$this->price_array[$tid] = array();
		}

		$selfCost = 0;
		if($this->power==2 && $this->manage_self_cost > 0){
			$selfCost = $this->manage_self_cost;
		}elseif($this->power==2 && isset($this->tool['cost2']) && $this->tool['cost2']>0){
			$selfCost = $this->tool['cost2'];
		}elseif(isset($this->tool['cost']) && $this->tool['cost']>0){
			$selfCost = $this->tool['cost'];
		}elseif(isset($this->tool['price']) && $this->tool['price']>0){
			$selfCost = $this->tool['price'];
		}

		if($cost2 !== null && $selfCost > 0 && $cost2 < $selfCost){
			$cost2 = $selfCost;
		}
		if($cost2 !== null && $cost > 0 && $cost < $cost2){
			$cost = $cost2;
		}
		if($cost > 0 && $price < $cost){
			$price = $cost;
		}elseif($cost2 !== null && $price < $cost2){
			$price = $cost2;
		}

		// 更新内存中的价格数据
		$this->price_array[$tid]['price'] = $price;
		if($this->power==2) {
			$this->price_array[$tid]['cost'] = $cost;
			if($cost2 !== null) {
				$this->price_array[$tid]['cost2'] = $cost2;
			}
		}
		$this->price_array[$tid]['del'] = $del;

		// 将价格数据保存到新表中
		$cost2 = $cost2 !== null ? $cost2 : (isset($this->price_array[$tid]['cost2']) ? $this->price_array[$tid]['cost2'] : 0);

		// 使用INSERT ON DUPLICATE KEY UPDATE语法，确保数据的唯一性
		$sql = "INSERT INTO pre_site_price (zid, tid, price, cost, cost2, del, create_time, update_time)
		       VALUES (:zid, :tid, :price, :cost, :cost2, :del, NOW(), NOW())
		       ON DUPLICATE KEY UPDATE
		       price = :price, cost = :cost, cost2 = :cost2, del = :del, update_time = NOW()";

		$data = [
			':zid' => $this->zid,
			':tid' => $tid,
			':price' => $price,
			':cost' => $cost,
			':cost2' => $cost2,
			':del' => $del
		];

		return $DB->exec($sql, $data);
	}

	public function setiPriceInfo($tid,$price){

		global $DB;

		if($price==0){

			unset($this->iprice_array[$tid]);

		}else{

			$this->iprice_array[$tid] = $price;

		}

		$iprice_data = serialize($this->iprice_array);

		return $DB->exec("UPDATE pre_site SET iprice='$iprice_data' WHERE zid='{$this->zid}'");

	}

	public function getPower(){

		return $this->power;

	}

	private function changeUserMoney($zid, $money, $action=null, $desc = null, $orderid=null){

		global $DB,$conf;

		if($money<=0)return;

		if(!$conf['tixian_limit'] || $conf['tixian_limit']==1 && !$conf['tixian_days']){

			$sqls=",`rmbtc`=`rmbtc`+{$money}";

			$status=1;

		}else{

			$status=0;

		}

		$rs=$DB->exec("UPDATE `pre_site` SET `rmb`=`rmb`+{$money}{$sqls} WHERE `zid`='{$zid}'");

		$DB->exec("INSERT INTO `pre_points` (`zid`, `action`, `point`, `bz`, `addtime`, `orderid`, `status`) VALUES (:zid, :action, :point, :bz, NOW(), :orderid, :status)", [':zid'=>$zid, ':action'=>$action, ':point'=>$money, ':bz'=>$desc, ':orderid'=>$orderid, ':status'=>$status]);

		return $rs;

	}

	private function getSiteInfo($zid){

		global $DB;

		$data = $DB->getRow("SELECT zid,upzid,power,price,iprice,endtime,site_prid FROM pre_site WHERE zid='$zid' LIMIT 1");

		return $data;

	}

	private function getToolInfo($tid){

		global $DB;

		$row=$DB->getRow("SELECT * FROM pre_tools WHERE tid='$tid' LIMIT 1");

		return $row;

	}

	private function getPriceRules($id){

		global $DB,$CACHE;

		// 先检查缓存是否已加载到内存
		if(self::$price_rules && isset(self::$price_rules[$id])) {
			return self::$price_rules[$id];
		}

		// 尝试从缓存中读取
		$price_rules = unserialize($CACHE->read('pricerules'));

		if(!$price_rules || !is_array($price_rules)){
			// 缓存不存在或无效，重新生成
			$this->updatePriceRules();
			// 检查更新后是否存在该规则
			if(self::$price_rules && isset(self::$price_rules[$id])) {
				return self::$price_rules[$id];
			}
		} else {
			// 缓存有效，保存到内存
			self::$price_rules = $price_rules;
			// 检查是否存在该规则
			if(isset(self::$price_rules[$id])) {
				return self::$price_rules[$id];
			}
		}

		// 如果规则不存在，返回false
		return false;

	}

	private function buildParentProfessionalPrice($tid, $row){

		if($this->power < 1 || $this->parent_power != 2){
			return false;
		}

		$base = array(
			'price' => isset($row['price']) ? floatval($row['price']) : 0,
			'cost' => isset($row['cost']) ? floatval($row['cost']) : 0,
			'cost2' => isset($row['cost2']) ? floatval($row['cost2']) : 0,
			'del' => 0
		);
		if($base['cost'] <= 0)$base['cost'] = $base['price'];
		if($base['cost2'] <= 0)$base['cost2'] = $base['cost'];

		$parentHasIprice = isset($this->parent_iprice_array[$tid]) && floatval($this->parent_iprice_array[$tid]) > 0;
		if($parentHasIprice){
			$base['cost2'] = floatval($this->parent_iprice_array[$tid]);
			if($base['cost'] < $base['cost2'])$base['cost'] = $base['cost2'];
		}
		$this->parent_manage_self_cost = floatval($base['cost2']);

		if(!$parentHasIprice && $this->parent_site_prid > 0 && empty($this->up_price_array[$tid])){
			$sitePrice = $this->buildPriceByRule($base['cost2'], $this->parent_site_prid);
			if($sitePrice){
				$base['price'] = floatval($sitePrice['price']);
				$base['cost'] = floatval($sitePrice['cost']);
				$base['cost2'] = floatval($sitePrice['cost2']);
			}
		}

		if(isset($this->up_price_array[$tid]) && is_array($this->up_price_array[$tid])){
			$custom = $this->up_price_array[$tid];
			if(isset($custom['cost2']) && floatval($custom['cost2']) >= $base['cost2']){
				$base['cost2'] = floatval($custom['cost2']);
			}
			if(isset($custom['cost']) && floatval($custom['cost']) >= $base['cost2']){
				$base['cost'] = floatval($custom['cost']);
			}
			if(isset($custom['price']) && floatval($custom['price']) >= $base['cost']){
				$base['price'] = floatval($custom['price']);
			}
			$base['del'] = isset($custom['del']) ? intval($custom['del']) : 0;
		}

		if($base['cost'] < $base['cost2'])$base['cost'] = $base['cost2'];
		if($base['price'] < $base['cost'])$base['price'] = $base['cost'];

		return $base;

	}

	private function buildPriceByRule($basePrice, $ruleId){

		$basePrice = floatval($basePrice);
		$price_rules = $this->getPriceRules($ruleId);

		if($basePrice <= 0 || !$price_rules || !is_array($price_rules)){
			return false;
		}

		if($price_rules['kind']==1){
			return array(
				'price' => round($basePrice + $price_rules['p_0'], 2),
				'cost' => round($basePrice + $price_rules['p_1'], 2),
				'cost2' => round($basePrice + $price_rules['p_2'], 2)
			);
		}

		return array(
			'price' => round($basePrice * $price_rules['p_0'], 2),
			'cost' => round($basePrice * $price_rules['p_1'], 2),
			'cost2' => round($basePrice * $price_rules['p_2'], 2)
		);

	}

	private function loadSitePriceRows($zid){

		global $DB;

		$prices = array();
		$zid = intval($zid);
		if($zid <= 0)return $prices;

		$rs = $DB->query("SELECT tid, price, cost, cost2, del FROM pre_site_price WHERE zid='{$zid}'");
		while ($row = $rs->fetch()) {
			$prices[$row['tid']] = array(
				'price' => $row['price'],
				'cost' => $row['cost'],
				'cost2' => $row['cost2'],
				'del' => $row['del']
			);
		}

		return $prices;

	}

	private function decodePriceArray($raw){

		if(empty($raw))return array();

		$data = json_decode($raw, true);
		if(!is_array($data)){
			$data = @unserialize($raw);
		}

		return is_array($data) ? $data : array();

	}

	private function updatePriceRules(){

		global $DB,$CACHE;

		$array = array();

		$rs=$DB->query("SELECT * FROM pre_price ORDER BY id ASC");

		while($res = $rs->fetch()){

			$array[$res['id']] = array('kind'=>$res['kind'], 'p_2'=>$res['p_2'], 'p_1'=>$res['p_1'], 'p_0'=>$res['p_0']);

		}

		$CACHE->save('pricerules', $array);

		self::$price_rules = $array;

	}

}

