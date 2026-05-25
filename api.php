<?php
/*
岁岁云商城
维护：岁岁 @qqfaka
*/
$nosession = true;
include("./includes/common.php");
$act=isset($_GET['act'])?daddslashes($_GET['act']):null;
$url=daddslashes($_GET['url']);
$authcode=daddslashes($_GET['authcode']);

@header('Content-Type: application/json; charset=UTF-8');

if (!function_exists('q8_front_stock_count')) {
	function q8_front_stock_count($DB, $tool) {
		$local_stock = q8_local_faka_stock_count($DB, $tool['tid']);
		if ($tool['is_curl'] == 4) return $local_stock;
		if ($tool['stock'] !== null) return $local_stock + max(0, intval($tool['stock']));
		if ($local_stock > 0) return $local_stock;
		return null;
	}
}
if (!function_exists('q8_api_tool_isfaka')) {
	function q8_api_tool_isfaka($tool) {
		return intval(isset($tool['is_curl']) ? $tool['is_curl'] : 0) == 4 || intval(isset($tool['goods_type']) ? $tool['goods_type'] : 0) == 1 ? 1 : 0;
	}
}
if (!function_exists('q8_api_require_docking_site')) {
	function q8_api_require_docking_site($userrow) {
		if (!isset($userrow['power']) || intval($userrow['power']) < 1) {
			exit('{"code":-1,"message":"普通用户不支持API对接，请使用分站账号"}');
		}
	}
}
if (!function_exists('q8_api_docking_price')) {
	function q8_api_docking_price($price_obj, $tid) {
		return method_exists($price_obj, 'getManageSelfCostPrice') ? $price_obj->getManageSelfCostPrice($tid) : $price_obj->getToolPrice($tid);
	}
}
if (!function_exists('q8_api_check_repeat_order')) {
	function q8_api_check_repeat_order($DB, $tid, $input, $toolName) {
		$thtime = date("Y-m-d") . ' 00:00:00';
		$row = $DB->getRow("SELECT id,input,status,addtime FROM pre_orders WHERE tid=:tid AND input=:input ORDER BY id DESC LIMIT 1", array(':tid' => intval($tid), ':input' => $input));
		if ($row['input'] && $row['status'] == 0) {
			exit('{"code":-1,"message":"您今天添加的' . $toolName . '正在排队中，请勿重复提交！"}');
		} elseif ($row['addtime'] > $thtime) {
			exit('{"code":-1,"message":"您今天已添加过' . $toolName . '，请勿重复提交！"}');
		}
	}
}

if($act=='clone')
{
	$key=daddslashes($_GET['key']);
	if(!$key)exit('{"code":-5,"msg":"确保各项不能为空"}');
	if($key!==md5($password_hash.md5(SYS_KEY).$conf['apikey']))exit('{"code":-4,"msg":"克隆密钥错误"}');
	$rs=$DB->query("SELECT * FROM pre_class ORDER BY cid ASC");
	$class=array();
	while($res = $rs->fetch()){
		$class[]=$res;
	}
	$rs=$DB->query("SELECT * FROM pre_tools ORDER BY tid ASC");
	$tools=array();
	while($res = $rs->fetch()){
		$tools[]=$res;
	}
	$rs=$DB->query("SELECT id,url,type FROM pre_shequ ORDER BY id ASC");
	$shequ=array();
	while($res = $rs->fetch()){
		$shequ[]=$res;
	}
	$rs=$DB->query("SELECT * FROM pre_price ORDER BY id ASC");
	$price=array();
	while($res = $rs->fetch()){
		$price[]=$res;
	}
	$result=array("code"=>1,"class"=>$class,"tools"=>$tools,"shequ"=>$shequ,"price"=>$price);
}
elseif($act=='tools')
{
	$key=daddslashes($_GET['key']);
	$limit=isset($_GET['limit'])?intval($_GET['limit']):50;
	if(!$key)exit('{"code":-5,"msg":"确保各项不能为空"}');
	if($key!=$conf['apikey'])exit('{"code":-4,"msg":"API对接密钥错误，请在后台设置密钥"}');
	$rs=$DB->query("SELECT * FROM pre_tools WHERE active=1 ORDER BY tid ASC LIMIT $limit");
	while($res = $rs->fetch()){
		$data[]=array('tid'=>$res['tid'],'cid'=>$res['cid'],'sort'=>$res['sort'],'name'=>$res['name'],'price'=>$res['price']);
	}
	exit(json_encode($data));
}
elseif($act=='orders')
{
	$tid=intval($_GET['tid']);
	$key=daddslashes($_GET['key']);
	$limit=isset($_GET['limit'])?intval($_GET['limit']):50;
	$format=isset($_GET['format'])?daddslashes($_GET['format']):'json';
	if(!$key)exit('{"code":-5,"msg":"确保各项不能为空"}');
	if($key!=$conf['apikey'])exit('{"code":-4,"msg":"API对接密钥错误，请在后台设置密钥"}');
	if($tid){
		$tool=$DB->getRow("SELECT tid,value FROM pre_tools WHERE tid='$tid' AND active=1 LIMIT 1");
		if(!$tool)exit('{"code":-5,"msg":"商品ID不存在"}');
		$sqls=" and tid='$tid'";
		$value=$tool['value']>0?$tool['value']:1;
	}
	$rs=$DB->query("SELECT * FROM pre_orders WHERE status=0{$sqls} ORDER BY id ASC LIMIT $limit");
	while($res = $rs->fetch()){
		$data[]=array('id'=>$res['id'],'tid'=>$res['tid'],'input'=>$res['input'],'input2'=>$res['input2'],'input3'=>$res['input3'],'input4'=>$res['input4'],'input5'=>$res['input5'],'value'=>$res['value'],'status'=>$res['status']);
		if($_GET['sign']==1)$DB->exec("UPDATE `pre_orders` SET status=1 WHERE `id`='{$res['id']}'");
	}
	if($format=='text'){
		$txt = '';
		foreach($data as $row){
			$txt .= $row['input'] . ($row['input2']?'----'.$row['input2']:null) . ($row['input3']?'----'.$row['input3']:null) . ($row['input4']?'----'.$row['input4']:null) . ($row['input5']?'----'.$row['input5']:null) . '----' . $row['value'] . "\r\n";
		}
		exit($txt);
	}else{
		exit(json_encode($data));
	}
}
elseif($act=='change')
{
	$id=intval($_GET['id']);
	$key=daddslashes($_GET['key']);
	$status=intval($_GET['zt']); //1:已完成,2:正在处理,3:异常,4:待处理
	if(!$id || !$key)exit('{"code":-5,"msg":"确保各项不能为空"}');
	if($key!=$conf['apikey'])exit('{"code":-4,"msg":"API对接密钥错误，请在后台设置密钥"}');
	$row=$DB->getRow("SELECT id FROM pre_orders WHERE id='$id' LIMIT 1");
	if($row) {
		$sql="UPDATE `pre_orders` SET `status`='$status' WHERE `id`='{$id}' LIMIT 1";
		if($DB->exec($sql)!==false){
			$result=array("code"=>1,"msg"=>"修改成功","id"=>$id);
		}else{
			$result=array("code"=>-2,"msg"=>"修改失败","id"=>$id);
		}
	}
	else
	{
		$result=array("code"=>-5,"msg"=>"订单ID不存在");
	}
}
elseif($act == 'classlist')
{
	$rs=$DB->query("SELECT * FROM pre_class WHERE active=1 ORDER BY sort ASC");
	$data = array();
	while($res = $rs->fetch(PDO::FETCH_ASSOC)){
		$data[]=$res;
	}
	$result=array("code"=>0,"msg"=>"succ","data"=>$data,"count"=>count($data));
	exit(json_encode($result));
}
elseif($act == 'goodslistbycid')
{
	if(isset($_POST['user']) && isset($_POST['pass'])){
		$user = trim(daddslashes($_POST['user']));
		$pass = trim(daddslashes($_POST['pass']));
		$userrow = $DB->getRow("SELECT * FROM `pre_site` WHERE `user` = '{$user}' LIMIT 1");
		if ($userrow && $userrow['user'] == $user && $userrow['pwd'] == $pass && $userrow['status'] == 1) {
			q8_api_require_docking_site($userrow);
			$islogin2 = 1;
			$price_obj = new \lib\Price($userrow['zid'],$userrow);
		} elseif ($userrow && $userrow['status'] == 0) {
			exit('{"code":-1,"message":"该账户已被封禁"}');
		} else {
			exit('{"code":-1,"message":"用户名或密码不正确"}');
		}
	}
	$cid=isset($_POST['cid'])?intval($_POST['cid']):0;
	$rs=$DB->query("SELECT * FROM pre_tools WHERE cid='$cid' AND active=1 ORDER BY sort ASC");
	$data = array();
	while($res = $rs->fetch(PDO::FETCH_ASSOC)){
		if(isset($price_obj)){
			$price_obj->setToolInfo($res['tid'],$res);
			$price=q8_api_docking_price($price_obj, $res['tid']);
		}else $price=$res['price'];
		$isfaka = q8_api_tool_isfaka($res);
		if($res['is_curl']==4){
			$res['input'] = getFakaInput();
		}
		$data[]=array('tid'=>$res['tid'],'cid'=>$res['cid'],'sort'=>$res['sort'],'name'=>$res['name'],'value'=>$res['value'],'price'=>$price,'input'=>$res['input'],'inputs'=>$res['inputs'],'desc'=>$res['desc'],'alert'=>$res['alert'],'shopimg'=>$res['shopimg'],'validate'=>$res['validate'],'valiserv'=>$res['valiserv'],'repeat'=>$res['repeat'],'multi'=>$res['multi'],'close'=>$res['close'],'prices'=>$res['prices'],'min'=>$res['min'],'max'=>$res['max'],'sales'=>$res['sales'],'isfaka'=>$isfaka,'goods_type'=>$res['goods_type'],'stock'=>q8_front_stock_count($DB, $res));
	}
	$result=array("code"=>0,"msg"=>"succ","data"=>$data,"count"=>count($data));
	exit(json_encode($result));
}
elseif($act == 'goodslist')
{
	$result['code'] = 0;
	if(isset($_POST['user']) && isset($_POST['pass'])){
		$user = trim(daddslashes($_POST['user']));
		$pass = trim(daddslashes($_POST['pass']));
		$userrow = $DB->getRow("SELECT * FROM `pre_site` WHERE `user` = '{$user}' LIMIT 1");
		if ($userrow && $userrow['user'] == $user && $userrow['pwd'] == $pass && $userrow['status'] == 1) {
			q8_api_require_docking_site($userrow);
			$islogin2 = 1;
			$price_obj = new \lib\Price($userrow['zid'],$userrow);
		} elseif ($userrow && $userrow['status'] == 0) {
			exit('{"code":-1,"message":"该账户已被封禁"}');
		} else {
			exit('{"code":-1,"message":"用户名或密码不正确"}');
		}
	}
	$rs=$DB->query("SELECT * FROM `pre_tools` WHERE `active` = 1 ORDER BY `cid` ASC,`sort` ASC");
	while($res = $rs->fetch()){
		if($islogin2 == 1 && isset($price_obj)){
			$price_obj->setToolInfo($res['tid'],$res);
			$price = q8_api_docking_price($price_obj, $res['tid']);
		}else{
			$price = $res['price'];
		}
		$isfaka = q8_api_tool_isfaka($res);
		if($res['is_curl']==4){
			$count = q8_local_faka_stock_count($DB, $res['tid']);
			//if($count==0)$res['close']=1;
		}else{
			$count = q8_front_stock_count($DB, $res);
		}
		$data[] = array('tid' => $res['tid'] , 'cid' => $res['cid'] , 'sort' => $res['sort'], 'name' => $res['name'] , 'value' => $res['value'] , 'shopimg' => $res['shopimg'] , 'close' => $res['close'] , 'price' => $price , 'isfaka' => $isfaka , 'goods_type' => $res['goods_type'], 'multi' => $res['multi'], 'min' => $res['min'], 'max' => $res['max'], 'repeat' => $res['repeat'], 'validate' => $res['validate'], 'valiserv' => $res['valiserv'], 'input' => $res['input'], 'inputs' => $res['inputs'], 'alert' => $res['alert'], 'prices' => $res['prices'], 'stock' => $count);
	}
	$result['data'] = $data;
	exit(json_encode($result));
}
elseif($act == 'goodsdetails')
{
	$result['code'] = 0;
	$tid = intval($_POST['tid']);
	if(!$tid)exit('{"code":-1,"message":"商品ID不能为空"}');
	if(isset($_POST['user']) && isset($_POST['pass'])){
		$user = trim(daddslashes($_POST['user']));
		$pass = trim(daddslashes($_POST['pass']));
		$userrow = $DB->getRow("SELECT * FROM `pre_site` WHERE `user` = '{$user}' LIMIT 1");
		if ($userrow && $userrow['user'] == $user && $userrow['pwd'] == $pass && $userrow['status'] == 1) {
			q8_api_require_docking_site($userrow);
			$islogin2 = 1;
			$price_obj = new \lib\Price($userrow['zid'],$userrow);
		} elseif ($userrow && $userrow['status'] == 0) {
			exit('{"code":-1,"message":"该账户已被封禁"}');
		} else {
			exit('{"code":-1,"message":"用户名或密码不正确"}');
		}
	}
	$tool = $DB->getRow("SELECT * FROM `pre_tools` WHERE `tid` = {$tid} LIMIT 1");
	if(!$tool)exit('{"code":-1,"message":"商品不存在"}');
	if($islogin2 == 1 && isset($price_obj)){
		$price_obj->setToolInfo($tid, $tool);
		$price = q8_api_docking_price($price_obj, $tid);
	}else{
		$price = $tool['price'];
	}
	$isfaka = q8_api_tool_isfaka($tool);
	if($tool['is_curl']==4){
		$count = q8_local_faka_stock_count($DB, $tool['tid']);
		if($count==0)$tool['close']=1;
		$tool['input'] = getFakaInput();
	}else{
		$count = q8_front_stock_count($DB, $tool);
		if(empty($tool['input']))$tool['input']='下单账号';
	}
	$data = array('tid'=>$tool['tid'],'cid'=>$tool['cid'],'sort'=>$tool['sort'],'name'=>$tool['name'],'value'=>$tool['value'],'price'=>$price,'prices'=>$tool['prices'],'input'=>$tool['input'],'inputs'=>$tool['inputs'],'desc'=>$tool['desc'],'alert'=>$tool['alert'],'shopimg'=>$tool['shopimg'],'repeat'=>$tool['repeat'],'multi'=>$tool['multi'],'min'=>$tool['min'],'max'=>$tool['max'],'close'=>$tool['close'],'isfaka'=>$isfaka,'goods_type'=>$tool['goods_type'],'stock'=>$count);
	$result['data'] = $data;
	exit(json_encode($result));
}
elseif($act == 'getleftcount')
{
	$tid=trim($_POST['tid']);
	if(!$tid)exit('{"code":-1,"message":"商品ID不能为空"}');
	if(strpos($tid,',')){
		$tids = explode(',',$tid);
		if(count($tids)>20)exit('{"code":-1,"message":"每次最多只能查询20个商品的库存"}');
	}
	if(isset($tids) && count($tids)>0){
		$data = [];
		foreach($tids as $tid){
			$tool = $DB->getRow("SELECT * FROM `pre_tools` WHERE `tid` = ".intval($tid)." LIMIT 1");
			if(!$tool)continue;
			if($tool['is_curl']==4){
				$count = q8_local_faka_stock_count($DB, $tid);
			}elseif(($count = q8_front_stock_count($DB, $tool)) !== null){
				$count = $count;
			}else{
				$count = null;
			}
			$data[] = ['tid'=>$tid,'stock'=>$count];
		}
		exit(json_encode(['code'=>0, 'data'=>$data]));
	}else{
		$tool = $DB->getRow("SELECT * FROM `pre_tools` WHERE `tid` = ".intval($tid)." LIMIT 1");
		if(!$tool)exit('{"code":-1,"message":"商品不存在"}');
		if($tool['is_curl']==4){
			$count = q8_local_faka_stock_count($DB, $tid);
		}elseif(($count = q8_front_stock_count($DB, $tool)) !== null){
			$count = $count;
		}else{
			exit('{"code":-2,"message":"该商品不限库存"}');
		}
		exit(json_encode(["code"=>0,"count"=>$count]));
	}
}
elseif($act == 'pay')
{
	$result['code'] = -1;
	$tid = intval($_POST['tid']);
	if(!$tid)exit('{"code":-1,"message":"商品ID不能为空"}');
	$user = trim(daddslashes($_POST['user']));
	$pass = trim(daddslashes($_POST['pass']));
	$input1 = isset($_POST['input1']) ? htmlspecialchars(trim(strip_tags(daddslashes($_POST['input1'])))) : exit('{"code":-1,"message":"首个参数值不能为空"}');
	$input2 = htmlspecialchars(trim(strip_tags(daddslashes($_POST['input2']))));
	$input3 = htmlspecialchars(trim(strip_tags(daddslashes($_POST['input3']))));
	$input4 = htmlspecialchars(trim(strip_tags(daddslashes($_POST['input4']))));
	$input5 = htmlspecialchars(trim(strip_tags(daddslashes($_POST['input5']))));
	$num = isset($_POST['num']) ? intval($_POST['num']) : 1;
	$tool = $DB->getRow("SELECT * FROM `pre_tools` WHERE `tid` = {$tid} LIMIT 1");
	if ($tool && $tool['active'] == 1) {
		if($tool['close']==1)exit('{"code":-1,"message":"当前商品维护中，停止下单！"}');
		$inputs=explode('|',$tool['inputs']);
		if($inputs[0] && empty($input2) || $inputs[1] && empty($input3) || $inputs[2] && empty($input4) || $inputs[3] && empty($input5)){
			exit('{"code":-1,"message":"请确保各项不能为空"}');
		}
		if(!$inputs[0] && !empty($input2) || !$inputs[1] && !empty($input3) || !$inputs[2] && !empty($input4) || !$inputs[3] && !empty($input5)){
			exit('{"code":-1,"message":"验证失败"}');
		}
		$userrow = $DB->getRow("SELECT * FROM `pre_site` WHERE `user` = '{$user}' LIMIT 1");
		if ($userrow && $userrow['user'] == $user && $userrow['pwd'] == $pass && $userrow['status'] == 1) {
			q8_api_require_docking_site($userrow);
			$result['code'] = 0;
			if(in_array($input1,explode("|",$conf['blacklist']))) exit('{"code":-1,"message":"你的下单账号已被拉黑，无法下单！"}');
			$front_stock_count = q8_front_stock_count($DB, $tool);
			$nums=($tool['value']>1?$tool['value']:1)*$num;
			if($tool['repeat']==0){
				q8_api_check_repeat_order($DB, $tid, $input1, $tool['name']);
			}
			if($tool['is_curl']==4){
				$count = q8_local_faka_stock_count($DB, $tid);
				$nums=($tool['value']>1?$tool['value']:1)*$num;
				if($count==0)exit('{"code":-1,"message":"该商品库存卡密不足，请联系站长加卡！"}');
				if($nums>$count)exit('{"code":-1,"message":"你所购买的数量超过库存数量！"}');
			}
			elseif($front_stock_count!==null){
				if($front_stock_count==0)exit('{"code":-1,"message":"该商品库存不足，请联系站长增加库存！"}');
				if($nums>$front_stock_count)exit('{"code":-1,"message":"你所购买的数量超过库存数量！"}');
			}
			if($tool['validate']==1 && is_numeric($input1)){
				if(validate_qzone($input1)==false) exit('{"code":-1,"message":"你的QQ空间设置了访问权限，无法下单！"}');
			}elseif(($tool['validate']==2 || $tool['validate']==3) && is_numeric($input1)){
				$services = getservices($input1);
				if($services['code']!=0)exit('{"code":-1,"message":"'.$services['msg'].'"}');
				$qqservices = ['vip'=>'QQ会员','svip'=>'超级会员','bigqqvip'=>'大会员','red'=>'红钻贵族','green'=>'绿钻贵族','sgreen'=>'绿钻豪华版','yellow'=>'黄钻贵族','syellow'=>'豪华黄钻','hollywood'=>'腾讯视频VIP','qqmsey'=>'付费音乐包','qqmstw'=>'豪华付费音乐包','weiyun'=>'微云会员','sweiyun'=>'微云超级会员'];
				if(in_array($tool['valiserv'], $services['data'])){
					if($tool['validate']==2){
						exit('{"code":-1,"message":"您的QQ已经开通了'.$qqservices[$tool['valiserv']].'，该商品无法购买！"}');
					}else{
						$blockdj=1;
					}
				}
			}
			if($tool['multi'] == 0 || $num < 1) $num = 1;
			if($tool['multi']==1 && $tool['min']>0 && $num<$tool['min'])exit('{"code":-1,"message":"当前商品最小下单数量为'.$tool['min'].'"}');
			if($tool['multi']==1 && $tool['max']>0 && $num>$tool['max'])exit('{"code":-1,"message":"当前商品最大下单数量为'.$tool['max'].'"}');

			$islogin2 = 1;
			$price_obj = new \lib\Price($userrow['zid'],$userrow);
			$price_obj->setToolInfo($tid,$tool);
			$price = q8_api_docking_price($price_obj, $tid);
			$price=$price_obj->getFinalPrice($price, $num);
			if(!$price)exit('{"code":-1,"message":"当前商品批发价格优惠设置不正确"}');

			$i=2;
			$neednum = $num;
			foreach($inputs as $inputname){
				if(strpos($inputname,'[multi]')!==false && isset(${'input'.$i}) && is_numeric(${'input'.$i})){
					$val = intval(${'input'.$i});
					if($val>0){
						$neednum = $neednum * $val;
					}
				}
				$i++;
			}

			$need = $price * $neednum;
			if($need == 0) exit('{"code":-2,"message":"不支持免费商品对接"}');
			if ($userrow['rmb'] < $need) exit('{"code":-2,"message":"余额不足，购买此商品还差' . ($need - $userrow['rmb']) . '元"}');
			$need = sprintf('%.2f', round(floatval($need), 2));
			$zid = intval($userrow['zid']);

			$trade_no = date("YmdHis").rand(111,999).'RMB';
			$input = $input1 . ($input2 ? '|' . $input2 : null) . ($input3 ? '|' . $input3 : null) . ($input4 ? '|' . $input4 : null) . ($input5 ? '|' . $input5 : null);
			$sql="INSERT INTO `pre_pay` (`trade_no`,`type`,`tid`,`zid`,`input`,`num`,`name`,`money`,`ip`,`userid`,`addtime`,`blockdj`,`status`) VALUES (:trade_no, :type, :tid, :zid, :input, :num, :name, :money, :ip, :userid, NOW(), :blockdj, 0)";
			$data = [':trade_no'=>$trade_no, ':type'=>'rmb', ':tid'=>$tid, ':zid'=>$zid, ':input'=>$input, ':num'=>$num, ':name'=>$tool['name'], ':money'=>$need, ':ip'=>$clientip, ':userid'=>$zid, ':blockdj'=>$blockdj?$blockdj:0];
			if ($DB->exec($sql, $data)) {
				$deducted = $DB->exec("UPDATE `pre_site` SET `rmb` = `rmb` - {$need} WHERE `zid` = '{$zid}' AND `rmb` >= {$need}");
				if ($deducted && $DB->exec("UPDATE `pre_pay` SET `status` = 1 WHERE `trade_no` = '{$trade_no}'")) {
					addPointRecord($zid, $need, '消费', 'API购买 '.$tool['name']);
					$srow['tid'] = $tid;
					$srow['num'] = $num;
					$srow['input'] = $input;
					$srow['zid'] = $zid;
					$srow['money'] = $need;
					$srow['trade_no'] = $trade_no;
					$srow['userid'] = $zid;
					if($orderid = processOrder($srow)){
						$result['code'] = 0;
						$result['message'] = 'success';
						$result['orderid'] = $orderid;
						$orderrow = $DB->getRow("SELECT money,cost,result FROM pre_orders WHERE id = '$orderid' LIMIT 1");
						if($orderrow){
							$result['money'] = round(floatval($orderrow['money']), 2);
							$result['cost'] = round(floatval($orderrow['cost']), 2);
						}
						$djzt = $DB->getColumn("SELECT djzt FROM pre_orders WHERE id = '$orderid' LIMIT 1");
						if($djzt == 3){
							$rs=$DB->query("SELECT * FROM pre_faka WHERE tid='$tid' AND orderid='$orderid' ORDER BY kid ASC");
							$kmdata=array();
							while($res = $rs->fetch())
							{
								if(!empty($res['pw'])){
									$kmdata[]=array('card'=>$res['km'],'pass'=>$res['pw']);
								}else{
									$kmdata[]=array('card'=>$res['km']);
								}
							}
							if(empty($kmdata) && !empty($orderrow['result'])){
								$rows = q8_extract_remote_faka_rows(array('订单结果' => $orderrow['result']));
								foreach($rows as $row){
									$item = array('card'=>$row['card']);
									if(!empty($row['pass'])) $item['pass'] = $row['pass'];
									$kmdata[] = $item;
								}
							}
							if(!empty($kmdata)){
								$result['faka']=true;
								$result['kmdata']=$kmdata;
							}
						}
					} else {
						$result['message'] = '下单失败 : ' . $DB->error();
					}
				} elseif (!$deducted) {
					$result['message'] = '余额不足或余额已变化，请刷新后重试';
				} else {
					$result['message'] = '下单失败 : ' . $DB->error();
				}
			} else {
				$result['message'] = '下单失败 : ' . $DB->error();
			}
		} elseif ($userrow && $userrow['status'] == 0) {
			$result['message'] = '该账户已被封禁';
		} else {
			$result['message'] = '用户名或密码不正确';
		}
	} else {
		$result['message'] = '商品ID不存在';
	}
}
elseif($act == 'search')
{
	$result['code'] = -1;
	$id = isset($_POST['id'])?intval($_POST['id']):intval($_GET['id']);
	$row = $DB->getRow("SELECT * FROM `pre_orders` WHERE `id` = {$id} LIMIT 1");
	if ($row){
		$tool = $DB->getRow("SELECT * FROM pre_tools WHERE tid='{$row['tid']}' LIMIT 1");
		if($tool['is_curl']==2){
			$shequ = $DB->getRow("SELECT * FROM pre_shequ WHERE id='{$tool['shequ']}' LIMIT 1");
			$list = third_call($shequ['type'], $shequ, 'query_order', [$row['djorder'], $tool['goods_id'], [$row['input'], $row['input2'], $row['input3'], $row['input4'], $row['input5']]]);
			if($list && is_array($list)){
				$remote_kmdata = q8_sync_remote_faka_to_order($DB, $date, $id, $row['tid'], $list);
				if($remote_kmdata){
					$row['status'] = 1;
					$row['djzt'] = 3;
					$row['result'] = $remote_kmdata;
					$list['订单结果'] = $remote_kmdata;
				}elseif(q8_remote_order_completed($list) && $row['status']==2){
					$DB->exec("UPDATE `pre_orders` SET `uptime`=".time()." WHERE id='{$id}'");
				}
				if(q8_remote_order_failed($list) && $row['status']<3){
					$DB->exec("UPDATE `pre_orders` SET `status`=3 WHERE id='{$id}'");
					$row['status'] = 3;
				}
			}else{
				$list = false;
			}
		}
		if($row['result']){
			if (!is_array($list)) $list = array();
			$list['订单结果'] = $row['result'];
		} else {
			$faka_result = q8_order_faka_result($DB, $id);
			if ($faka_result !== '') {
				if (!is_array($list)) $list = array();
				$list['订单结果'] = $faka_result;
				$row['result'] = $faka_result;
				if ($row['status'] == 1) {
					$DB->exec("UPDATE `pre_orders` SET `result`=:result WHERE `id`=:id", array(':result' => $faka_result, ':id' => $id));
				}
			}
		}
		$result['code'] = 0;
		$result['message'] = 'success';
		$result['type'] = $tool['is_curl'];
		$result['status'] = $row['status'];
		$result['data'] = $list;
	} else {
		$result['message'] = '订单不存在';
	}
}
elseif($act=='siteinfo')
{
	$count1=$DB->getColumn("SELECT count(*) from pre_orders");
	$count2=$DB->getColumn("SELECT count(*) from pre_orders where status>=1");
	$count3=$DB->getColumn("SELECT count(*) from pre_site");
	$result=array('sitename'=>$conf['sitename'],'kfqq'=>$conf['qq']?$conf['qq']:$conf['kfqq'],'anounce'=>$conf['anounce'],'modal'=>$conf['modal'],'bottom'=>$conf['bottom'],'alert'=>$conf['alert'],'gg_search'=>$conf['gg_search'],'gg_panel'=>$conf['gg_panel'],'version'=>VERSION,'build'=>$conf['build'],'orders'=>$count1,'orders1'=>$count2,'sites'=>$count3,'appalert'=>$conf['appalert']);
}
elseif($act=='token')
{
	$key = isset($_GET['key'])?$_GET['key']:exit('No key');
	$result=array('token'=>get_app_token($key),'time'=>time());
}

else
{
	$result=array("code"=>-5,"msg"=>"No Act!");
}

echo json_encode($result);
$DB->close();
?>
