<?php
include("../includes/common.php");
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
$act=isset($_REQUEST['act'])?daddslashes($_REQUEST['act']):null;

@header('Content-Type: application/json; charset=UTF-8');

if(!checkRefererHost())exit('{"code":403}');

switch($act){
case 'getTool':
	$tid=intval($_GET['tid']);
	$rows=$DB->getRow("select * from pre_tools where tid=:tid limit 1", array(':tid' => $tid));
	if(!$rows)
		exit('{"code":-1,"msg":"商品不存在"}');
	$scriptpath=str_replace('\\','/',$_SERVER['SCRIPT_NAME']);
	$scriptpath = substr($scriptpath, 0, strrpos($scriptpath, '/'));
	$scriptpath = substr($scriptpath, 0, strrpos($scriptpath, '/'));
	$siteurl = (is_https() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$scriptpath.'/';
	$rows['link'] = $siteurl.'?cid='.$rows['cid'].'&tid='.$rows['tid'];
	$result=array("code"=>0,"msg"=>"succ","data"=>$rows);
	exit(json_encode($result));
break;
case 'getTools':
	$cid=intval($_POST['cid']);
	$rs=$DB->query("SELECT tid,name FROM pre_tools WHERE cid=:cid AND active=1 ORDER BY sort ASC, tid DESC", array(':cid' => $cid));
	$data = array();
	while($res=$rs->fetch()){
		$data[] = array('tid' => $res['tid'], 'name' => $res['name']);
	}
	$result=array("code"=>0,"msg"=>"succ","data"=>$data);
	exit(json_encode($result));
break;
case 'getPrice':
	$tid=intval($_GET['tid']);
	$rows=$DB->getRow("select * from pre_tools where tid=:tid limit 1", array(':tid' => $tid));
	if(!$rows)
		exit('{"code":-1,"msg":"商品不存在"}');
	$display_price = round(floatval($rows['price']), 2);
	$display_cost = round(floatval($rows['cost']), 2);
	$display_cost2 = round(floatval($rows['cost2']), 2);
	if(intval($rows['prid']) > 0){
		$rule = $DB->getRow("SELECT kind,p_0,p_1,p_2 FROM pre_price WHERE id=:id LIMIT 1", array(':id' => intval($rows['prid'])));
		if($rule){
			if(intval($rule['kind']) == 1){
				$display_price = round(floatval($rows['price']) + floatval($rule['p_0']), 2);
				$display_cost = round(floatval($rows['price']) + floatval($rule['p_1']), 2);
				$display_cost2 = round(floatval($rows['price']) + floatval($rule['p_2']), 2);
			}else{
				$display_price = round(floatval($rows['price']) * floatval($rule['p_0']), 2);
				$display_cost = round(floatval($rows['price']) * floatval($rule['p_1']), 2);
				$display_cost2 = round(floatval($rows['price']) * floatval($rule['p_2']), 2);
			}
		}
	}
	if($display_cost <= 0) $display_cost = $display_price;
	if($display_cost2 <= 0) $display_cost2 = $display_cost;
	if($_SESSION['priceselect']){
		$priceselect = $_SESSION['priceselect'];
	}else{
		$rs=$DB->query("SELECT * FROM pre_price order by id asc");
		$priceselect='<option value="0">不使用加价模板</option>';
		while($res = $rs->fetch()){
			$kind = $res['kind']==1?'元':'倍';
			$priceselect.='<option value="'.$res['id'].'" kind="'.$res['kind'].'" p_2="'.$res['p_2'].'" p_1="'.$res['p_1'].'" p_0="'.$res['p_0'].'" >'.$res['name'].'('.$res['p_2'].$kind.'|'.$res['p_1'].$kind.'|'.$res['p_0'].$kind.')</option>';
		}
	}
	$data = '<div class="form-group"><div class="input-group"><div class="input-group-addon">成本价格</div><input type="text" id="price" value="'.$rows['price'].'" class="form-control" required onkeyup="changePrice()" disabled/></div></div>
	<div class="form-group"><div class="input-group"><div class="input-group-addon">加价模板</div><select class="form-control" id="prid" onchange="changePrice()">'.$priceselect.'</select></div></div>
<table class="table table-striped table-bordered table-condensed">
<tbody>
<tr align="center"><td>销售价格</td><td>普及版价格</td><td>专业版价格</td></tr>
<tr>
<td><input type="text" id="price_s" value="'.$display_price.'" class="form-control input-sm" disabled/></td>
<td><input type="text" id="cost_s" value="'.$display_cost.'" class="form-control input-sm" disabled/></td>
<td><input type="text" id="cost2_s" value="'.$display_cost2.'" class="form-control input-sm" disabled/></td>
</tr>
</table>
	<input type="submit" id="save" onclick="editPrice('.$tid.')" class="btn btn-primary btn-block" value="保存">
	<script>$("#prid").val('.$rows['prid'].');</script>';
	$result=array("code"=>0,"msg"=>"succ","data"=>$data);
	exit(json_encode($result));
break;
case 'editPrice':
	adminpermission('shop', 2);
	$tid=intval($_POST['tid']);
	$rows=$DB->getRow("select * from pre_tools where tid=:tid limit 1", array(':tid' => $tid));
	if(!$rows)
		exit('{"code":-1,"msg":"商品不存在"}');
	$prid=intval($_POST['prid']);
	if($prid==0){
		$price=$_POST['price_s'];
		$cost=$_POST['cost_s'];
		$cost2=$_POST['cost2_s'];
	}else{
		$price=$_POST['price'];
		$cost=0;
		$cost2=0;
	}
	if($DB->exec("UPDATE `pre_tools` SET `price`=:price,`cost`=:cost,`cost2`=:cost2,`prid`=:prid WHERE `tid`=:tid", array(':price' => $price, ':cost' => $cost, ':cost2' => $cost2, ':prid' => $prid, ':tid' => $tid))!==false)
		exit('{"code":0,"msg":"succ"}');
	else
		exit('{"code":-1,"msg":"修改商品失败！'.$DB->error().'"}');
break;
case 'getAllPrice':
	if($_SESSION['priceselect']){
		$priceselect = $_SESSION['priceselect'];
	}else{
		$rs=$DB->query("SELECT * FROM pre_price order by id asc");
		$priceselect='<option value="0">不使用加价模板</option>';
		while($res = $rs->fetch()){
			$kind = $res['kind']==1?'元':'倍';
			$priceselect.='<option value="'.$res['id'].'" kind="'.$res['kind'].'" p_2="'.$res['p_2'].'" p_1="'.$res['p_1'].'" p_0="'.$res['p_0'].'" >'.$res['name'].'('.$res['p_2'].$kind.'|'.$res['p_1'].$kind.'|'.$res['p_0'].$kind.')</option>';
		}
	}
	$data = '<div class="form-group"><div class="input-group"><select class="form-control" name="prid_n">'.$priceselect.'</select></div></div>
	<input type="submit" id="save" onclick="editAllPrice()" class="btn btn-primary btn-block" value="保存">';
	$result=array("code"=>0,"msg"=>"succ","data"=>$data);
	exit(json_encode($result));
break;
case 'editAllPrice':
	adminpermission('shop', 2);
	$prid=intval($_POST['prid']);
	$checkbox=$_POST['checkbox'];
	$i=0;
	foreach($checkbox as $tid){
		$DB->exec("update pre_tools set prid=:prid,`cost`='0',`cost2`='0' where tid=:tid limit 1", array(':prid' => $prid, ':tid' => $tid));
		$i++;
	}
	exit('{"code":0,"msg":"成功改变'.$i.'个商品"}');
break;
case 'reset_sort':
	$cid = intval($_POST['cid']);
	$sds = $DB->exec("UPDATE pre_tools SET sort=tid WHERE cid=:cid", array(':cid' => $cid));
	if ($sds!==false) {
		exit('{"code":0,"msg":"重置成功"}');
	} else {
		exit('{"code":-1,"msg":"重置失败'.$DB->error().'"}');
	}
break;
case 'change_shopname':
	$oldName=trim($_POST['oldName']);
	$newName=trim($_POST['newName']);
	if(!$newName||!$oldName)exit('{"code":-1,"msg":"不能为空"}');
	$sds = $DB->exec("UPDATE pre_tools SET name=replace(name,:oldName,:newName) WHERE 1", [':oldName'=>$oldName, ':newName'=>$newName]);
	if ($sds!==false) {
		exit('{"code":0,"msg":"批量替换成功"}');
	} else {
		exit('{"code":-1,"msg":"批量替换失败'.$DB->error().'"}');
	}
break;
case 'change_inputs':
	$oldName=trim($_POST['oldName']);
	$newName=trim($_POST['newName']);
	if(!$newName||!$oldName)exit('{"code":-1,"msg":"不能为空"}');
	if($oldName=='下单账号')
	$sds = $DB->exec("UPDATE pre_tools SET input=replace(input,:oldName,:newName) WHERE 1", [':oldName'=>$oldName, ':newName'=>$newName]);
	$sds2 = $DB->exec("UPDATE pre_tools SET inputs=replace(inputs,:oldName,:newName) WHERE 1", [':oldName'=>$oldName, ':newName'=>$newName]);
	if ($sds!==false && $sds2!==false) {
		exit('{"code":0,"msg":"批量替换成功"}');
	} else {
		exit('{"code":-1,"msg":"批量替换失败'.$DB->error().'"}');
	}
break;
case 'shop_move':
	adminpermission('shop', 2);
	$cid=intval($_POST['cid']);
	if(!$cid)exit('{"code":-1,"msg":"请选择分类"}');
	$checkbox=$_POST['checkbox'];
	$i=0;
	foreach($checkbox as $tid){
		$DB->exec("update pre_tools set cid=:cid where tid=:tid limit 1", array(':cid' => $cid, ':tid' => $tid));
		$i++;
	}
	exit('{"code":0,"msg":"成功移动'.$i.'个商品"}');
break;
case 'shop_change':
	adminpermission('shop', 2);
	$aid=$_POST['aid'];
	$checkbox=$_POST['checkbox'];
	$i=0;
	foreach($checkbox as $tid){
		if($aid==1){
			$tools = $DB->getRow("SELECT * FROM pre_tools WHERE tid=:tid", array(':tid' => $tid));
			$DB->exec("update pre_tools set active=1 where tid=:tid limit 1", array(':tid' => $tid));
			// 记录上架日志
			$content = "上架：{$tools['name']}";
			$DB->exec("INSERT INTO `pre_toollogs` (`content`,`date`,`addtime`,`active`) VALUES (:content, CURDATE(), NOW(), 1)", array(':content' => $content));
		}elseif($aid==2){
			$DB->exec("update pre_tools set active=0 where tid=:tid limit 1", array(':tid' => $tid));
		}elseif($aid==3){
			$tools = $DB->getRow("SELECT * FROM pre_tools WHERE tid=:tid", array(':tid' => $tid));
			$DB->exec("update pre_tools set close=0 where tid=:tid limit 1", array(':tid' => $tid));
			// 记录上架日志
			$content = "商品上架：{$tools['name']}";
			$DB->exec("INSERT INTO `pre_toollogs` (`content`,`date`,`addtime`,`active`) VALUES (:content, CURDATE(), NOW(), 1)", array(':content' => $content));
		}elseif($aid==4){
			$DB->exec("update pre_tools set close=1 where tid=:tid limit 1", array(':tid' => $tid));
		}elseif($aid==5){
			$DB->exec("DELETE FROM pre_tools WHERE tid=:tid limit 1", array(':tid' => $tid));
		}elseif($aid==6){
			$DB->exec("insert into `pre_tools` (`cid`,`name`,`price`,`cost`,`cost2`,`prid`,`prices`,`input`,`inputs`,`desc`,`alert`,`shopimg`,`value`,`is_curl`,`curl`,`shequ`,`goods_id`,`goods_type`,`goods_param`,`repeat`,`multi`,`min`,`max`,`validate`,`valiserv`,`sort`,`active`) select `cid`,`name`,`price`,`cost`,`cost2`,`prid`,`prices`,`input`,`inputs`,`desc`,`alert`,`shopimg`,`value`,`is_curl`,`curl`,`shequ`,`goods_id`,`goods_type`,`goods_param`,`repeat`,`multi`,`min`,`max`,`validate`,`valiserv`,`sort`,`active` from `pre_tools` where `tid` = :tid", array(':tid' => $tid));
		}
		$i++;
	}
	exit('{"code":0,"msg":"成功改变'.$i.'个商品"}');
break;
case 'delTool':
	adminpermission('shop', 2);
	$tid=intval($_GET['tid']);
	if($DB->exec("DELETE FROM pre_tools WHERE tid=:tid limit 1", array(':tid' => $tid))!==false){
		$DB->exec("DELETE FROM pre_orders WHERE tid=:tid", array(':tid' => $tid));
		exit('{"code":0,"msg":"删除商品成功！"}');
	}else{
		exit('{"code":-1,"msg":"删除商品失败！' . $DB->error() . '"}');
	}
break;
case 'setTools': //商品上下架
	adminpermission('shop', 2);
	$tid=intval($_GET['tid']);
	$tools = $DB->getRow("SELECT * FROM pre_tools WHERE tid=:tid", array(':tid' => $tid));
	if(isset($_GET['active'])){
		$active=intval($_GET['active']);
		$DB->exec("update pre_tools set active=:active where tid=:tid", array(':active' => $active, ':tid' => $tid));
		// 记录上架日志
		if($active == 1) {
			$content = "商品上架：{$tools['name']}";
			$DB->exec("INSERT INTO `pre_toollogs` (`content`,`date`,`addtime`,`active`) VALUES (:content, CURDATE(), NOW(), 1)", array(':content' => $content));
		}
	}else{
		$close=intval($_GET['close']);
		$DB->exec("update pre_tools set close=:close where tid=:tid", array(':close' => $close, ':tid' => $tid));
		// 记录上架日志
		if($close == 0) {
			$content = "商品上架：{$tools['name']}";
			$DB->exec("INSERT INTO `pre_toollogs` (`content`,`date`,`addtime`,`active`) VALUES (:content, CURDATE(), NOW(), 1)", array(':content' => $content));
		}
	}
	exit('{"code":0,"msg":"succ"}');
break;
case 'setToolSort': //排序操作
	adminpermission('shop', 2);
	$cid=intval($_GET['cid']);
	$tid=intval($_GET['tid']);
	$sort=intval($_GET['sort']);
	if(setToolSort($cid,$tid,$sort)){
		exit('{"code":0,"msg":"succ"}');
	}else{
		exit('{"code":-1,"msg":"失败"}');
	}
break;
case 'setStock': //设置商品库存
	adminpermission('shop', 2);
	$tid=intval($_POST['tid']);
	$num=trim($_POST['num']);
	if($num==''){
		$DB->exec("update pre_tools set stock=NULL where tid=:tid", array(':tid' => $tid));
	}else{
		$num=intval($num);
		$DB->exec("update pre_tools set stock=:num where tid=:tid", array(':num' => $num, ':tid' => $tid));
	}
	exit('{"code":0,"msg":"设置库存成功"}');
break;
case 'editAllStock': //批量设置商品库存
	adminpermission('shop', 2);
	$num=trim($_POST['stock']);
	$checkbox=$_POST['checkbox'];
	$i=0;
	foreach($checkbox as $tid){
		if($num==''){
			$DB->exec("update pre_tools set stock=NULL where tid=:tid and is_curl!=4", array(':tid' => $tid));
		}else{
			$num=intval($num);
			$DB->exec("update pre_tools set stock=:num where tid=:tid and is_curl!=4", array(':num' => $num, ':tid' => $tid));
		}
		$i++;
	}
	exit('{"code":0,"msg":"成功改变'.$i.'个商品"}');
break;
case 'getGoodsList': //获取对接商品列表
	$shequ=intval($_POST['shequ']);
	$row=$DB->getRow("select * from pre_shequ where id=:id limit 1", array(':id' => $shequ));
	$list = third_call($row['type'], $row, 'goods_list');
	if($list === false) exit('{"code":-1,"msg":"请直接在参数名处填写下单页面地址"}');
	if(!is_array($list))$result=array('code'=>-1,'msg'=>$list);
	else $result=array('code'=>0,'msg'=>'succ','type'=>$row['type'],'data'=>$list);
	exit(json_encode($result));
break;
case 'getGoodsParam': //获取对接参数名
	$shequ=intval($_POST['shequ']);
	$goodsid=daddslashes($_POST['goodsid']); // 保留原始类型，支持字符串商品ID
	$row=$DB->getRow("select * from pre_shequ where id='$shequ' limit 1");
	$result = third_call($row['type'], $row, 'goods_info', [$goodsid]);
	if(!is_array($result)){
		$error = $result;
		$result=array();
		$result['code'] = -1;
		$result['msg'] = $error;
	}else{
		$result['code'] = 0;
	}
	exit(json_encode($result));
break;
case 'getKyxCategory':
	$shequ=intval($_POST['shequ']);
	$row=$DB->getRow("select * from pre_shequ where id='$shequ' limit 1");
	$data = third_call('kayixin', $row, 'getKyxCategory');
	if(!is_array($data)){
		$result=array();
		$result['code'] = -1;
		$result['msg'] = $data;
	}else{
		$result['code'] = 0;
		$result['data'] = $data;
	}
	exit(json_encode($result));
break;
case 'getKyxProductList':
	$shequ=intval($_POST['shequ']);
	$categoryid=intval($_POST['categoryid']);
	$row=$DB->getRow("select * from pre_shequ where id='$shequ' limit 1");
	$data = third_call('kayixin', $row, 'getKyxProductList', [$categoryid]);
	if(!is_array($data)){
		$result=array();
		$result['code'] = -1;
		$result['msg'] = $data;
	}else{
		$result['code'] = 0;
		$result['data'] = $data;
	}
	exit(json_encode($result));
break;


case 'addPriceRule': //添加加价模板
	adminpermission('price', 2);
	$name=trim(daddslashes($_POST['name']));
	$kind=intval($_POST['kind']);
	$p_2=floatval($_POST['p_2']);
	$p_1=floatval($_POST['p_1']);
	$p_0=floatval($_POST['p_0']);
	if(empty($name) || $p_2=='' || $p_1=='' || $p_0==''){
		exit('{"code":-1,"msg":"请确保各项不能为空！"}');
	}elseif($p_2>$p_1){
		exit('{"code":-1,"msg":"专业版加价不能高于普及版加价"}');
	}elseif($p_2>$p_0){
		exit('{"code":-1,"msg":"专业版加价不能高于普通用户加价"}');
	}elseif($p_1>$p_0){
		exit('{"code":-1,"msg":"普及版加价不能高于普通用户加价"}');
	}elseif($DB->getRow("select * from pre_price where name='$name' limit 1")){
		exit('{"code":-1,"msg":"模板名称已存在"}');
	}
	$sql="insert into `pre_price` (`kind`,`name`,`p_0`,`p_1`,`p_2`) values ('".$kind."','".$name."','".$p_0."','".$p_1."','".$p_2."')";
	if($DB->exec($sql)!==false){
		$CACHE->clear('pricerules');
		exit('{"code":0,"msg":"添加加价模板成功！"}');
	}else{
		exit('{"code":-1,"msg":"添加加价模板失败！'.$DB->error().'"}');
	}
break;
case 'editPriceRule': //修改加价模板
	adminpermission('price', 2);
	$id=intval($_POST['prid']);
	$name=trim(daddslashes($_POST['name']));
	$kind=intval($_POST['kind']);
	$p_2=floatval($_POST['p_2']);
	$p_1=floatval($_POST['p_1']);
	$p_0=floatval($_POST['p_0']);
	if(empty($name) || $p_2=='' || $p_1=='' || $p_0==''){
		exit('{"code":-1,"msg":"请确保各项不能为空！"}');
	}elseif($p_2>$p_1){
		exit('{"code":-1,"msg":"专业版加价不能高于普及版加价"}');
	}elseif($p_2>$p_0){
		exit('{"code":-1,"msg":"专业版加价不能高于普通用户加价"}');
	}elseif($p_1>$p_0){
		exit('{"code":-1,"msg":"普及版加价不能高于普通用户加价"}');
	}elseif($DB->getRow("select * from pre_price where id!=$id and name='$name' limit 1")){
		exit('{"code":-1,"msg":"模板名称已存在"}');
	}
	$sql="update pre_price set kind='$kind',name='$name',p_0='$p_0',p_1='$p_1',p_2='$p_2' where id='{$id}'";
	if($DB->exec($sql)!==false){
		$CACHE->clear('pricerules');
		exit('{"code":0,"msg":"修改加价模板成功！"}');
	}else{
		exit('{"code":-1,"msg":"修改加价模板失败！'.$DB->error().'"}');
	}
break;
case 'getPriceRule':
	$id=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_price where id='$id' limit 1");
	$row['code']=0;
	exit(json_encode($row));
break;
case 'delPriceRule':
	adminpermission('price', 2);
	$id=intval($_GET['id']);
	$sql="DELETE FROM pre_price WHERE id='$id' limit 1";
	if($DB->exec($sql)!==false){
		$CACHE->clear('pricerules');
		exit('{"code":0,"msg":"删除成功！"}');
	}else{
		exit('{"code":-1,"msg":"删除失败！'.$DB->error().'"}');
	}
break;
case 'batchPriceOperation': //批量操作加价模板
	adminpermission('price', 2);
	$aid=intval($_POST['aid']);
	$checkbox=$_POST['checkbox'];
	$i=0;
	foreach($checkbox as $id){
		$id=intval($id);
		if($aid==1){
			$DB->exec("DELETE FROM pre_price WHERE id=:id limit 1", array(':id' => $id));
		}
		$i++;
	}
	$CACHE->clear('pricerules');
	exit('{"code":0,"msg":"成功改变'.$i.'个模板"}');
break;
case 'changePriceRule':
	adminpermission('price', 2);
	$id=intval($_POST['id']);
	$scope = isset($_POST['scope']) ? trim($_POST['scope']) : '';
	$cids = isset($_POST['cids']) ? $_POST['cids'] : array();
	if($id<=0){
		exit('{"code":-1,"msg":"请选择有效的加价模板"}');
	}
	if($scope === 'all'){
		$count = $DB->exec("UPDATE pre_tools SET prid=:id", array(':id' => $id));
		if($count!==false){
			exit('{"code":0,"msg":"成功更改'.$count.'个商品的加价模板"}');
		}else{
			exit('{"code":-1,"msg":"更改失败：'.$DB->error().'"}');
		}
	}
	if(!is_array($cids) && $cids !== ''){
		$cids = array($cids);
	}

	// 检查cids是否为数组，确保至少有一个分类被选择
	if(!is_array($cids) || empty($cids)){
		exit('{"code":-1,"msg":"请选择至少一个分类！"}');
	}

	// 过滤并验证分类ID
	$validCids = [];
	foreach($cids as $cid){
		$cid = intval($cid);
		if($cid > 0){
			$validCids[] = $cid;
		}
	}

	if(empty($validCids)){
		exit('{"code":-1,"msg":"请选择有效的分类！"}');
	}

	// 构建SQL语句
	$cidsStr = implode(',', $validCids);
	$sql="UPDATE pre_tools SET prid='".intval($id)."' WHERE cid IN ($cidsStr)";
	$count = $DB->exec($sql);
	if($count!==false){
		exit('{"code":0,"msg":"成功更改'.$count.'个商品的加价模板"}');
	}else{
		exit('{"code":-1,"msg":"更改失败！'.$DB->error().'"}');
	}
break;

case 'goodslistbycid':
	$shequ=intval($_POST['shequ']);
	$cid=isset($_POST['cid'])?$_POST['cid']:0;
	$row=$DB->getRow("select * from pre_shequ where id='$shequ' limit 1");
	if(!$row) {
		exit('{"code":-1,"msg":"对接站点不存在"}');
	}
	// 检查是否支持批量对接 - 先检查特定类型，再检查插件配置
	$supports_batch = false;
	if($row['type']=='daishua' || $row['type']=='third_yike' || $row['type']=='yike'){
		$supports_batch = true;
	} else {
		// 检查插件是否支持批量对接
		try {
			$getInfo = \lib\Plugin::getConfig("third_" . $row['type']);
			if(isset($getInfo['batchgoods']) && $getInfo['batchgoods'] == true){
				$supports_batch = true;
			}
		} catch (Exception $e) {
			// 插件不存在或加载失败
		}
	}

	if($supports_batch){
		try {
			$rows = third_call($row['type'], $row, 'goods_list_by_cid', [$cid]);
			if(!is_array($rows)){
				$result = [];
				$result['code'] = -1;
				$result['msg'] = $rows;
			} else {
				$result['code'] = 0;
				$result['msg'] = 'succ';
				$result['data'] = $rows;
			}
		} catch (Exception $e) {
			$result = [];
			$result['code'] = -1;
			$result['msg'] = '获取商品列表异常: ' . $e->getMessage();
		}
	} else {
		exit('{"code":-1,"msg":"该对接网站类型不支持批量添加商品"}');
	}
	exit(json_encode($result));
break;

case 'batchaddgoods':
	$shequ=isset($_POST['shequ'])?intval($_POST['shequ']):exit('{"code":-1,"msg":"no shequ"}');
	$mcid=isset($_POST['mcid'])?$_POST['mcid']:exit('{"code":-1,"msg":"no mcid"}');
	$parent_cid=isset($_POST['parent_cid'])?intval($_POST['parent_cid']):0;
	$prid=isset($_POST['prid'])?intval($_POST['prid']):exit('{"code":-1,"msg":"no prid"}');
	if(count($_POST['list'])==0)exit('{"code":-1,"msg":"请至少选中一个商品"}');

	// 分类映射：原始分类名 => 本地分类ID
	$category_map = array();
	$add_success = 0;
	$update_success = 0;
	$new_category_count = 0;

	foreach($_POST['list'] as $res){
		$row = json_decode($res, true);
		if(!$row || !$row['tid'])continue;

		// 确定当前商品要使用的分类ID
		$current_mcid = $mcid;

		// 如果不是"新建同名分类"，确保mcid是整数
		if($mcid != 'new'){
			$current_mcid = intval($current_mcid);
		}

		// 如果是"新建同名分类"，则为每个商品创建对应的原始分类
		if($mcid == 'new' && !empty($row['original_cname'])){
			$original_cname = $row['original_cname'];

			// 检查分类是否已存在于映射中
			if(!isset($category_map[$original_cname])){
				// 检查数据库中是否已存在该分类
				// 考虑父级分类ID，支持二级分类
				$existing_cid = $DB->getColumn("SELECT cid FROM pre_class WHERE name=:name AND pid=:pid LIMIT 1", [':name'=>$original_cname, ':pid'=>$parent_cid]);

				if($existing_cid){
					// 使用已存在的分类
					$category_map[$original_cname] = $existing_cid;
				} else {
					// 创建新分类
					$sort = $DB->getColumn("select sort from pre_class WHERE pid=:pid order by sort desc limit 1", [':pid'=>$parent_cid]);
					// 如果是二级分类，获取父级分类下的最大排序值
					if($sort === null) {
						$sort = 0;
					}
					// 插入分类，支持设置pid作为父级分类ID
					$sql="insert into `pre_class` (`pid`,`name`,`shopimg`,`sort`,`active`) values (:pid,:name,:shopimg,:sort,1)";
					if($DB->exec($sql, [':pid'=>$parent_cid, ':name'=>$original_cname, ':shopimg'=>$row['shopimg']?:$_POST['cimg'], ':sort'=>$sort+1])){
						$category_map[$original_cname] = $DB->lastInsertId();
						$new_category_count++;
					} else {
						// 创建失败，使用默认分类
						$category_map[$original_cname] = $mcid;
					}
				}
			}
			// 使用映射的分类ID
			$current_mcid = $category_map[$original_cname];
		}

		// 检查商品是否已存在
		$tool=$DB->getRow("SELECT * FROM pre_tools WHERE shequ=:shequ AND goods_id=:goods_id LIMIT 1", [':shequ'=>$shequ, ':goods_id'=>$row['tid']]);

		// 获取默认数量信息，优先使用对接站返回的value
		$goods_value = isset($row['value']) && $row['value'] > 0 ? intval($row['value']) : 1;

		if($tool){
			// 更新现有商品
			$sql = "UPDATE `pre_tools` SET `cid`=:cid,`name`=:name,`price`=:price,`prid`=:prid,`cost`=:cost,`cost2`=:cost2,`prices`=:prices,`input`=:input,`inputs`=:inputs,`desc`=:desc,`alert`=:alert,`shopimg`=:shopimg,`value`=:value,`is_curl`=:is_curl,`curl`=:curl,`shequ`=:shequ,`goods_id`=:goods_id,`goods_type`=:goods_type,`goods_param`=:goods_param,`repeat`=:repeat,`multi`=:multi,`min`=:min,`max`=:max,`validate`=:validate,`valiserv`=:valiserv,`close`=:close WHERE `tid`=:tid";
			$data = [':cid'=>$current_mcid, ':name'=>$row['name'], ':price'=>$row['price'], ':cost'=>0, ':cost2'=>0, ':prid'=>$prid, ':prices'=>'', ':input'=>$row['input'], ':inputs'=>$row['inputs'], ':desc'=>$row['desc'], ':alert'=>$row['alert'], ':shopimg'=>$row['shopimg'], ':value'=>$goods_value, ':is_curl'=>2, ':curl'=>null, ':shequ'=>$shequ, ':goods_id'=>$row['tid'], ':goods_type'=>$row['isfaka']?'1':'0', ':goods_param'=>null, ':repeat'=>$row['repeat'], ':multi'=>$row['multi'], ':min'=>$row['min'], ':max'=>$row['max'], ':validate'=>$row['validate'], ':valiserv'=>$row['valiserv'], ':close'=>$row['close'], ':tid'=>$tool['tid']];
			$DB->exec($sql, $data);
			$update_success++;
			// 记录上架日志
			$content = "商品上架：{$row['name']}";
			$DB->exec("INSERT INTO `pre_toollogs` (`content`,`date`,`addtime`,`active`) VALUES (:content, CURDATE(), NOW(), 1)", array(':content' => $content));
		} else {
			// 添加新商品
			$sql="INSERT INTO `pre_tools` (`cid`,`name`,`price`,`cost`,`cost2`,`prid`,`prices`,`input`,`inputs`,`desc`,`alert`,`shopimg`,`value`,`is_curl`,`curl`,`shequ`,`goods_id`,`goods_type`,`goods_param`,`repeat`,`multi`,`min`,`max`,`validate`,`valiserv`,`close`,`active`,`addtime`) VALUES (:cid,:name,:price,:cost,:cost2,:prid,:prices,:input,:inputs,:desc,:alert,:shopimg,:value,:is_curl,:curl,:shequ,:goods_id,:goods_type,:goods_param,:repeat,:multi,:min,:max,:validate,:valiserv,:close,:active,NOW())";
			$data = [':cid'=>$current_mcid, ':name'=>$row['name'], ':price'=>$row['price'], ':cost'=>0, ':cost2'=>0, ':prid'=>$prid, ':prices'=>'', ':input'=>$row['input'], ':inputs'=>$row['inputs'], ':desc'=>$row['desc'], ':alert'=>$row['alert'], ':shopimg'=>$row['shopimg'], ':value'=>$goods_value, ':is_curl'=>2, ':curl'=>null, ':shequ'=>$shequ, ':goods_id'=>$row['tid'], ':goods_type'=>$row['isfaka']?'1':'0', ':goods_param'=>null, ':repeat'=>$row['repeat'], ':multi'=>$row['multi'], ':min'=>$row['min'], ':max'=>$row['max'], ':validate'=>$row['validate'], ':valiserv'=>$row['valiserv'], ':close'=>$row['close'], ':active'=>1];
			$DB->exec($sql, $data);
			$add_success++;
			// 记录上架日志
			$content = "商品上架：{$row['name']}";
			$DB->exec("INSERT INTO `pre_toollogs` (`content`,`date`,`addtime`,`active`) VALUES (:content, CURDATE(), NOW(), 1)", array(':content' => $content));
		}
	}

	// 生成结果消息
	$msg = '成功添加'.$add_success.'个商品，更新'.$update_success.'个商品！';
	if($new_category_count > 0){
		if($parent_cid > 0) {
			$parent_name = $DB->getColumn("SELECT name FROM pre_class WHERE cid=:cid LIMIT 1", [':cid'=>$parent_cid]);
			$msg .= ' 新建了'.$new_category_count.'个二级分类到"'.$parent_name.'"下！';
		} else {
			$msg .= ' 新建了'.$new_category_count.'个一级分类！';
		}
	}

	$result=['code'=>0, 'msg'=>$msg];
	exit(json_encode($result));
break;
case 'getBatchGoodsList': //获取对接商品列表
	$shequ=intval($_POST['shequ']);
	$row=$DB->getRow("select * from pre_shequ where id='$shequ' limit 1");
	$list = third_call($row['type'], $row, 'batch_goods_list');
	if($list === false) exit('{"code":-1,"msg":"请直接在参数名处填写下单页面地址"}');
	if(!is_array($list))$result=array('code'=>-1,'msg'=>$list);
	else $result=array('code'=>0,'msg'=>'succ','type'=>$row['type'],'data'=>$list);
	exit(json_encode($result));
break;
case 'batch_goodsid':
	adminpermission('shop', 2);
	$cid=intval($_POST['cid']);
	$delete=intval($_POST['delete']);
	if($delete==1)$DB->exec("DELETE FROM pre_tools WHERE cid='$cid'");
	$batchgoods=$_POST['batchgoods'];
	$num=0;
	$goodsid_list='';
	foreach($batchgoods as $goodsid){
		$goodsid_list.=$goodsid.'|';
		$num++;
	}
	$goodsid_list = trim($goodsid_list, '|');
	$result=array('code'=>0,'msg'=>'succ','num'=>$num,'goodsid_list'=>$goodsid_list);
	exit(json_encode($result));
break;
case 'batch_merchandise':
	adminpermission('shop', 2);
	$cid=$_POST['cid'];
	$name=$_POST['name'];
	$prid=$_POST['prid'];
	$price=$_POST['price'];
	$input=$_POST['input'];
	$inputs=$_POST['inputs'];
	$desc=$_POST['desc'];
	$alert=$_POST['alert'];
	$shopimg=$_POST['shopimg'];
	$value=$_POST['min'];
	$min=$_POST['min'];
	$max=$_POST['max'];
	$repeat=$_POST['repeat'];
	$shequ=$_POST['shequ'];
	$goods_id=$_POST['goods_id'];
	$goods_type=$_POST['goods_type'];
	$goods_param=$_POST['goods_param'];
	$shequrow=$DB->getRow("select * from pre_shequ where id='$shequ' limit 1");
	if($shequrow['type']=='kakayun' || $shequrow['type']=='yunshanggou'){
		$getinfo = $arr;
	}else{
		$getinfo = third_call($shequrow['type'], $shequrow, 'batch_goods_info', [$goods_id]);
	}
	if($price=='undefined' || $price==''){
		$price=$getinfo['price'];
	}
	if($input=='undefined' || $input==''){
		$input=$getinfo['input'];
	}
	if($inputs=='undefined' || $inputs==''){
		$inputs=$getinfo['inputs'];
	}
	if($desc=='undefined' || $desc==''){
		$desc=$getinfo['desc'];
	}
	if($alert=='undefined' || $alert==''){
		$alert=$getinfo['alert'];
	}
	if($shopimg=='undefined' || $shopimg==''){
		$shopimg=$getinfo['shopimg'];
	}
	if($min=='undefined' || $min==''){
		$value=$getinfo['min'];
		$min=$getinfo['min'];
	}
	if($max=='undefined' || $max==''){
		$max=$getinfo['max'];
	}
	if($goods_type=='undefined' || $goods_type==''){
		$goods_type=$getinfo['goods_type'];
	}
	if($goods_type=='undefined' || $goods_type==''){
		$goods_param=$getinfo['goods_param'];
	}
	if($price=='undefined' || $price==''){
		$price='0.00';
	}
	if($input=='undefined' || $input==''){
		$input='';
	}
	if($inputs=='undefined' || $inputs==''){
		$inputs='';
	}
	if($desc=='undefined' || $desc==''){
		$desc='';
	}
	if($alert=='undefined' || $alert==''){
		$alert='';
	}
	if($shopimg=='undefined' || $shopimg==''){
		$shopimg='';
	}
	if($min=='undefined' || $min==''){
		$value=1;
		$min='';
	}
	if($max=='undefined' || $max=='' || $max==0){
		$max=1;
	}
	if($goods_type=='undefined' || $goods_type==''){
		$goods_type='';
	}
	if($goods_param=='undefined' || $goods_param==''){
		$goods_param='';
	}
	if($value>0){
		$price=round($price*$value, 2);
		if($min == $max || $value >= $max){
			$multi=0;
			$min='';
			$max='';
		}else{
			$multi=1;
			$min='';
			$max=round($max/$value);
		}
	}
	$sort=$DB->getColumn("select sort from pre_tools order by sort desc limit 1");
	$sql="INSERT INTO `pre_tools` (`cid`,`name`,`price`,`prid`,`input`,`inputs`,`desc`,`alert`,`shopimg`,`repeat`,`value`,`is_curl`,`shequ`,`goods_id`,`goods_type`,`goods_param`,`multi`,`min`,`max`,`sort`,`active`) VALUES ('".$cid."','".$name."','".$price."','".$prid."','".$input."','".$inputs."','".addslashes($desc)."','".addslashes($alert)."','".$shopimg."','".$repeat."','".$value."','2','".$shequ."','".$goods_id."','".$goods_type."','".$goods_param."','".$multi."','".$min."','".$max."','".($sort+1)."','1')";
	if($DB->exec($sql)!==false){
		// 记录上架日志
			$content = "上架：{$name}";
			$DB->exec("INSERT INTO `pre_toollogs` (`content`,`date`,`addtime`,`active`) VALUES ('{$content}', CURDATE(), NOW(), 1)");
		$result=['code'=>0, 'msg'=>'上架成功！', 'name'=>$name];
	}else{
		$result=['code'=>-1, 'msg'=>'上架失败！'.$DB->error().''];
	}
	exit(json_encode($result));
break;

default:
	exit('{"code":-4,"msg":"No Act"}');
break;
}
