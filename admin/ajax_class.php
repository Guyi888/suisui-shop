<?php
include("../includes/common.php");
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
$act=isset($_GET['act'])?daddslashes($_GET['act']):null;

@header('Content-Type: application/json; charset=UTF-8');

if(!checkRefererHost())exit('{"code":403}');

switch($act){
case 'addClass':	adminpermission('shop', 2);
	$name=trim($_POST['name']);
	if($name==null)
		exit('{"code":-1,"msg":"分类名不能为空"}');
	// 获取pid参数，默认为0（一级分类）
	$pid=intval($_POST['pid']);
	// 如果是子分类，检查父分类是否存在
	if($pid>0){
		$parent=$DB->getRow("select * from shua_class where cid='$pid' limit 1");
		if(!$parent)
			exit('{"code":-1,"msg":"父分类不存在"}');
	}
	// 检查分类名称是否已存在
	$rows=$DB->getRow("select * from shua_class where name='$name' and pid='$pid' limit 1");
	if($rows)
		exit('{"code":-1,"msg":"当前分类名称已存在"}');
	$sort = $DB->getColumn("select sort from shua_class order by sort desc limit 1");
	$sql="insert into `shua_class` (`name`,`sort`,`active`,`pid`) values ('".$name."','".($sort+1)."','1','$pid')";
	if($DB->exec($sql)!==false){
		$cid=$DB->lastInsertId();
		exit('{"code":0,"msg":"添加分类成功！"}');
	}else
		exit('{"code":-1,"msg":"添加分类失败！'.$DB->error().'"}');
break;
case 'editClass':	adminpermission('shop', 2);
	$cid=intval($_GET['cid']);
	$rows=$DB->getRow("select * from shua_class where cid='$cid' limit 1");
	if(!$rows)
		exit('{"code":-1,"msg":"分类不存在"}');
	$name=$_POST['name'];
	if($name==null)
		exit('{"code":-1,"msg":"分类名不能为空"}');
	if($DB->exec("update shua_class set name='$name' where cid='{$cid}'")!==false)
		exit('{"code":0,"msg":"修改分类成功！"}');
	else
		exit('{"code":-1,"msg":"修改分类失败！'.$DB->error().'"}');
break;
case 'delClass':	adminpermission('shop', 2);
	$cid=intval($_GET['cid']);
	// 检查是否有子分类
	$subClasses = $DB->getColumn("select count(*) from shua_class where pid='$cid'");
	if($subClasses > 0){
		exit('{"code":-1,"msg":"请先删除该分类下的所有子分类"}');
	}
	// 删除分类下的所有商品
	$DB->exec("DELETE FROM shua_tools WHERE cid='$cid'");
	// 删除分类
	$sql="DELETE FROM shua_class WHERE cid='$cid'";
	if($DB->exec($sql)!==false)
		exit('{"code":0,"msg":"删除分类成功！"}');
	else
		exit('{"code":-1,"msg":"删除分类失败！'.$DB->error().'"}');
break;
case 'editClassAll':	adminpermission('shop', 2);
	foreach($_POST['name'] as $cid=>$name){
		if(isset($_POST['sort'][$cid]) && $_POST['sort'][$cid]>0){
			$sort = intval($_POST['sort'][$cid]);
			$DB->exec("update shua_class set name='$name',sort='$sort' where cid='{$cid}'");
		}else{
			$DB->exec("update shua_class set name='$name' where cid='{$cid}'");
		}
	}
	exit('{"code":0,"msg":"修改分类成功！"}');
break;
case 'editClassImages':	adminpermission('shop', 2);
	foreach($_POST['img'] as $k=>$v){
		$DB->exec("update shua_class set shopimg='$v' where cid='{$k}'");
	}
	exit('{"code":0,"msg":"修改分类成功！"}');
break;
case 'getClassImage':	$cid=intval($_GET['cid']);
	$rows=$DB->getRow("select shopimg from shua_tools where cid='$cid' and shopimg is not null limit 1");
	if(!$rows)
		exit('{"code":-1,"msg":"分类不存在"}');
	exit('{"code":0,"msg":"succ","url":"'.$rows['shopimg'].'"}');
break;

case 'setBlock':	adminpermission('shop', 2);
	$cid=intval($_POST['cid']);
	$data=trim($_POST['data']);
	if($DB->exec("update shua_class set block='$data' where cid='{$cid}'")!==false)
		exit('{"code":0,"msg":"设置成功"}');
	else
		exit('{"code":-1,"msg":"设置失败！'.$DB->error().'"}');
break;
case 'getBlock':	$cid=intval($_POST['cid']);
	$rows=$DB->getRow("select * from shua_class where cid='$cid' limit 1");
	if(!$rows)
		exit('{"code":-1,"msg":"当前分类不存在！"}');
	$result=array("code"=>0,"msg"=>"succ","data"=>$rows['block']);
	exit(json_encode($result));
break;
case 'setBlockPay':	adminpermission('shop', 2);
	$cid=intval($_POST['cid']);
	$paytype=implode(',',$_POST['paytype']);
	if($DB->exec("update shua_class set blockpay='$paytype' where cid='{$cid}'")!==false)
		exit('{"code":0,"msg":"设置成功"}');
	else
		exit('{"code":-1,"msg":"设置失败！'.$DB->error().'"}');
break;
case 'getBlockPay':	$cid=intval($_POST['cid']);
	$rows=$DB->getRow("select * from shua_class where cid='$cid' limit 1");
	if(!$rows)
		exit('{"code":-1,"msg":"当前分类不存在！"}');
	$result=array("code"=>0,"msg"=>"succ","data"=>explode(',',$rows['blockpay']));
	exit(json_encode($result));
break;
case 'setClass': //分类上下架
	adminpermission('shop', 2);
	$cid=intval($_GET['cid']);
	$active=intval($_GET['active']);
	$DB->exec("update shua_class set active='$active' where cid='{$cid}'");
	exit('{"code":0,"msg":"succ"}');
break;
case 'setClassSort': //排序操作
	adminpermission('shop', 2);
	$cid=intval($_GET['cid']);
	$sort=intval($_GET['sort']);
	if(setClassSort($cid,$sort)){
		exit('{"code":0,"msg":"succ"}');
	}else{
		exit('{"code":-1,"msg":"失败"}');
	}
break;
case 'setDiscount': //设置商品分类折扣
	adminpermission('shop', 2);
	$cid=intval($_POST['cid']);
	$discount=trim($_POST['discount']);
	if($discount==''){
		$DB->exec("update shua_class set discount=100 where cid='{$cid}'");
	}else{
		$discount=round($discount);
		$DB->exec("update shua_class set discount='$discount' where cid='{$cid}'");
	}
	exit('{"code":0,"msg":"设置折扣成功"}');
break;
case 'batchOperation': //批量操作分类
	adminpermission('shop', 2);
	$aid=intval($_POST['aid']);
	$checkbox=isset($_POST['checkbox']) && is_array($_POST['checkbox']) ? $_POST['checkbox'] : array();
	$cids=array();
	foreach($checkbox as $cid){
		$cid=intval($cid);
		if($cid>0)$cids[$cid]=$cid;
	}
	if(empty($cids)){
		exit('{"code":-1,"msg":"请至少选择一个分类"}');
	}
	if($aid==1 || $aid==2){
		$i=0;
		foreach($cids as $cid){
			if($DB->exec("update shua_class set active=:active where cid=:cid limit 1", array(':active' => $aid==1 ? 1 : 0, ':cid' => $cid))!==false)$i++;
		}
		exit('{"code":0,"msg":"成功改变'.$i.'个分类"}');
	}elseif($aid==3){
		$all=$cids;
		do{
			$before=count($all);
			$placeholders=implode(',', array_fill(0, count($all), '?'));
			$rs=$DB->query("SELECT cid FROM shua_class WHERE pid IN ({$placeholders})", array_values($all));
			while($row=$rs->fetch()){
				$subCid=intval($row['cid']);
				if($subCid>0)$all[$subCid]=$subCid;
			}
		}while(count($all)>$before);

		$ids=array_values($all);
		$placeholders=implode(',', array_fill(0, count($ids), '?'));
		$DB->exec("DELETE FROM shua_tools WHERE cid IN ({$placeholders})", $ids);
		$rs=$DB->query("SELECT cid FROM shua_class WHERE cid IN ({$placeholders}) ORDER BY pid DESC,cid DESC", $ids);
		$deleteIds=array();
		while($row=$rs->fetch()){
			$deleteIds[]=intval($row['cid']);
		}
		$deleted=0;
		foreach($deleteIds as $cid){
			$ret=$DB->exec("DELETE FROM shua_class WHERE cid=:cid limit 1", array(':cid'=>$cid));
			if($ret!==false)$deleted++;
		}
		if($deleted<1){
			exit('{"code":-1,"msg":"没有删除任何分类，请刷新后重试"}');
		}
		exit('{"code":0,"msg":"成功删除'.$deleted.'个分类"}');
	}
	exit('{"code":-1,"msg":"请选择批量操作"}');
break;

// 设置二级分类提示语 - 岁岁 @qqfaka修改，岁岁 @qqfaka
case 'setNotice':
	adminpermission('shop', 2);
	$cid=intval($_POST['cid']);
	$notice=trim($_POST['notice']);
	// 防止XSS攻击，对提示语进行转义
	$notice=htmlspecialchars($notice, ENT_QUOTES, 'UTF-8');
	if($DB->exec("update shua_class set notice='$notice' where cid='{$cid}'")!==false)
		exit('{"code":0,"msg":"设置提示语成功！"}');
	else
		exit('{"code":-1,"msg":"设置失败！'.$DB->error().'"}');
break;

// 获取二级分类提示语 - 岁岁 @qqfaka修改，岁岁 @qqfaka
case 'getNotice':
	$cid=intval($_POST['cid']);
	$rows=$DB->getRow("select notice from shua_class where cid='$cid' limit 1");
	if(!$rows)
		exit('{"code":-1,"msg":"当前分类不存在！"}');
	$result=array("code"=>0,"msg"=>"succ","data"=>$rows['notice']);
	exit(json_encode($result));
break;

default:
	exit('{"code":-4,"msg":"No Act"}');
break;
}
