<?php
include("../includes/common.php");
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
$act=isset($_GET['act'])?daddslashes($_GET['act']):null;

@header('Content-Type: application/json; charset=UTF-8');

if(!checkRefererHost())exit('{"code":403}');

//发送订单邮件函数
function sendOrderEmail($row, $shoprow) {
    if($row['email_sent'] == 1 || !$row['input'] || !$shoprow['send_email'])
        return true;

    $email = $row['input'];
    if(!filter_var($email, FILTER_VALIDATE_EMAIL))
        return false;

    $mailconf = $DB->getRow("SELECT * FROM pre_config WHERE k='mail_config' limit 1");
    if(!$mailconf || !$mailconf['v'])
        return false;

    $mailconf = json_decode($mailconf['v'], true);
    if(!$mailconf['smtp_host'] || !$mailconf['smtp_user'] || !$mailconf['smtp_pwd'] || !$mailconf['from_email'])
        return false;

    require_once '../includes/mail.class.php';
    $mail = new Mailer($mailconf['smtp_host'], $mailconf['smtp_port'], $mailconf['smtp_ssl']);
    $mail->setAccount($mailconf['smtp_user'], $mailconf['smtp_pwd']);
    $mail->setFrom($mailconf['from_email'], $mailconf['from_name']);
    $mail->setReceiver($email);
    $mail->setMail('您的订单已完成 - '.date('Y-m-d H:i:s'), $shoprow['showcontent']);
    $result = $mail->sendMail();

    if($result['error'] == 0){
        $DB->query("update pre_orders set email_sent=1 where id='" . $row['id'] . "'");
        return true;
    }
    return false;
}

switch($act){
case 'setStatus':
	adminpermission('order', 2);
	$id=intval($_GET['name']);
	$status=intval($_GET['status']);
	if($status==5){
		if($DB->exec("DELETE FROM pre_orders WHERE id='$id'"))
			exit('{"code":200}');
		else
			exit('{"code":400,"msg":"删除订单失败！'.$DB->error().'"}');
	}else{
		if($DB->exec("update pre_orders set status='$status',result=NULL where id='$id'"))
			exit('{"code":200}');
		else
			exit('{"code":400,"msg":"修改订单失败！'.$DB->error().'"}');
	}
break;
case 'order':
	adminpermission('order', 2);
	$id=intval($_GET['id']);
	$rows=$DB->getRow("select * from pre_orders where id='$id' limit 1");
	if(!$rows)
		exit('{"code":-1,"msg":"当前订单不存在！"}');
	$tool=$DB->getRow("select * from pre_tools where tid='{$rows['tid']}' limit 1");
	if(strpos($rows['tradeno'],'kid')!==false){
		$kid=explode(':',$rows['tradeno']);
		$kid=$kid[1];
		$trade=$DB->getRow("select * from pre_kms where kid='$kid' limit 1");
		$trade['type']='卡密';
		$addstr='<li class="list-group-item"><b>使用卡密：</b>'.$trade['km'].'</li>';
	}elseif(strpos($rows['tradeno'],'invite')!==false){
		$trade['type']='推广赠送';
	}elseif(!empty($rows['tradeno'])){
		$trade=$DB->getRow("select * from pre_pay where trade_no='{$rows['tradeno']}' limit 1");
		$addstr='<li class="list-group-item"><b>支付订单号：</b>'.$trade['trade_no'].'</li><li class="list-group-item"><b>支付金额：</b>'.$trade['money'].' 元'.($trade['tid']==-3?'（'.$trade['num'].'件商品）':null).'</li><li class="list-group-item"><b>获得利润：</b>'.($rows['money'] - $rows['cost']).' 元</li><li class="list-group-item"><b>支付IP：</b><a href="https://m.ip138.com/iplookup.asp?ip='.$trade['ip'].'" target="_blank" rel="noreferrer">'.$trade['ip'].'</a></li>';
		if($trade['type']=='rmb'||is_numeric($rows['userid']))$addstr.='<li class="list-group-item"><b>支付用户ID：</b>'.($rows['userid']!=$rows['zid']?'<a href ="userlist.php?zid='.$rows['userid'].'" target="_blank">'.$rows['userid'].'</a>':'<a href ="sitelist.php?zid='.$rows['zid'].'" target="_blank">'.$rows['zid'].'</a>').'</li>';
	}else{
		$trade['type']='默认';
	}
	// 卡密信息
	$km_info = '';
	$km_rows = $DB->getAll("select * from pre_faka where orderid='$id' limit 10");
	if($km_rows){
		$km_info = '<li class="list-group-item"><b>卡密信息：</b><br/>';
		foreach($km_rows as $km_row){
			$km_info .= '卡号：'.$km_row['km'].'<br/>密码：'.$km_row['pw'].'<br/><br/>';
		}
		$km_info .= '</li>';
	}
	$input=$tool['input']?htmlspecialchars($tool['input']):'下单QQ';
	$inputs=explode('|',$tool['inputs']);
	$value=$tool['value']>0?$tool['value']:1;
	$data = '<li class="list-group-item"><b>商品名称：</b>'.htmlspecialchars($tool['name']).'</li><li class="list-group-item" style="word-break:break-all;"><b>下单数据：</b><br/>'.$input.'：'.htmlspecialchars($rows['input']).($rows['input2']?'<br/>'.htmlspecialchars($inputs[0]).'：'.htmlspecialchars($rows['input2']):null).($rows['input3']?'<br/>'.htmlspecialchars($inputs[1]).'：'.htmlspecialchars($rows['input3']):null).($rows['input4']?'<br/>'.htmlspecialchars($inputs[2]).'：'.htmlspecialchars($rows['input4']):null).($rows['input5']?'<br/>'.htmlspecialchars($inputs[3]).'：'.htmlspecialchars($rows['input5']):null).'</li><li class="list-group-item"><b>下单数量：</b>'.($rows['value']*$value).'</li><li class="list-group-item"><b>站点ID：</b>'.$rows['zid'].'</li><li class="list-group-item"><b>下单时间：</b>'.$rows['addtime'].'</li><li class="list-group-item"><b>购买方式：</b>'.$trade['type'].'</li>'.$addstr.$km_info;
	$result=array("code"=>0,"msg"=>"succ","data"=>$data);
	exit(json_encode($result));
break;
case 'order2':
	adminpermission('order', 2);
	$id=intval($_GET['id']);
	$rows=$DB->getRow("select * from pre_orders where id='$id' limit 1");
	if(!$rows)
		exit('{"code":-1,"msg":"当前订单不存在！"}');
	$tool=$DB->getRow("select * from pre_tools where tid='{$rows['tid']}' limit 1");
	$input=$tool['input']?htmlspecialchars($tool['input']):'下单ＱＱ';
	$inputs=explode('|',$tool['inputs']);
	if(strpos($input,'[')!==false && strpos($input,']')!==false)$input = explode('[',$input)[0];
	$data = '<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname">'.$input.'</div><input type="text" id="inputvalue" value="'.htmlspecialchars($rows['input']).'" class="form-control" required/></div></div>';
	$i=2;
	foreach($inputs as $input){
		if(!$input)continue;
		if(strpos($input,'{')!==false && strpos($input,'}')!==false){
			$inputname = substr($input,0,strpos($input,'{'));
			$arr = explode(',',getSubstr($input,'{','}'));
			$select='<option value="'.htmlspecialchars($rows['input'.$i]).'">'.htmlspecialchars($rows['input'.$i]).'</option>';
			foreach($arr as $option){
				if(strpos($option,':')!==false){
					$select.='<option value="'.htmlspecialchars(explode(':',$option)[0]).'">'.htmlspecialchars($option).'</option>';
				}else{
					$select.='<option value="'.htmlspecialchars($option).'">'.htmlspecialchars($option).'</option>';
				}
			}
			$data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname'.$i.'">'.htmlspecialchars($inputname).'</div><select id="inputvalue'.$i.'" class="form-control">'.$select.'</select></div></div>';
		}else{
			$data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname'.$i.'">'.htmlspecialchars($input).'</div><input type="text" id="inputvalue'.$i.'" value="'.htmlspecialchars($rows['input'.$i]).'" class="form-control" required/></div></div>';
		}
		$i++;
	}
	$data .= '<input type="submit" id="save" onclick="saveOrder('.$id.')" class="btn btn-primary btn-block" value="保存">';
	$result=array("code"=>0,"msg"=>"succ","data"=>$data);
	exit(json_encode($result));
break;
case 'order3':
	adminpermission('order', 2);
	$id=intval($_GET['id']);
	$rows=$DB->getRow("select * from pre_orders where id='$id' limit 1");
	if(!$rows)
		exit('{"code":-1,"msg":"当前订单不存在！"}');
	$data = '<div class="form-group"><div class="input-group"><div class="input-group-addon">份数</div><input type="text" id="num" value="'.$rows['value'].'" class="form-control" required/></div></div>';
	$data .= '<input type="submit" id="save" onclick="saveOrderNum('.$id.')" class="btn btn-primary btn-block" value="保存">';
	$result=array("code"=>0,"msg"=>"succ","data"=>$data);
	exit(json_encode($result));
break;
case 'editOrder':
	adminpermission('order', 2);
	$id=intval($_POST['id']);
	$inputvalue=trim(daddslashes($_POST['inputvalue']));
	$inputvalue2=trim(daddslashes($_POST['inputvalue2']));
	$inputvalue3=trim(daddslashes($_POST['inputvalue3']));
	$inputvalue4=trim(daddslashes($_POST['inputvalue4']));
	$inputvalue5=trim(daddslashes($_POST['inputvalue5']));
	$sds=$DB->exec("update `pre_orders` set `input`='$inputvalue',`input2`='$inputvalue2',`input3`='$inputvalue3',`input4`='$inputvalue4',`input5`='$inputvalue5' where `id`='$id'");
	if($sds!==false)
		exit('{"code":0,"msg":"修改订单成功！"}');
	else
		exit('{"code":-1,"msg":"修改订单失败！'.$DB->error().'"}');
break;
case 'editOrderNum':
	adminpermission('order', 2);
	$id=intval($_POST['id']);
	$num=intval($_POST['num']);
	$sds=$DB->exec("update `pre_orders` set `value`='$num' where `id`='$id'");
	if($sds!==false)
		exit('{"code":0,"msg":"修改订单成功！"}');
	else
		exit('{"code":-1,"msg":"修改订单失败！'.$DB->error().'"}');
break;
case 'operation':
	adminpermission('order', 2);
	$status=$_POST['status'];
	$checkbox=$_POST['checkbox'];
	$i=0;
	$statuss=$conf['shequ_status']?$conf['shequ_status']:1;
	foreach($checkbox as $id){
		if($status=='操作订单')continue;
		if($status==4)$DB->exec("DELETE FROM pre_orders WHERE id='$id'");
		elseif($status==5){
			$result = do_goods($id);
		}elseif($status==6){
			$row=$DB->getRow("select * from pre_orders where id='$id' limit 1");
			if($row && ($row['zid']>1 || is_numeric($row['userid']))){
				if($row['money']==0){
					$tool=$DB->getRow("select * from pre_tools where tid='" . $row['tid'] . "' limit 1");
					$money=$tool['price'];
					$money=$row['value']*$money;
				}else{
					$money=$row['money'];
				}
				if(is_numeric($row['userid'])){
					$zid = intval($row['userid']);
					changeUserMoney($zid, $money, true, '退款', '订单(ID'.$id.')已退款到余额');
				}
				rollbackPoint($id);
				$DB->exec("update pre_orders set status='4',result=NULL where id='$id'");
			}
		}
		else $DB->exec("update pre_orders set status='$status' where id='$id' limit 1");
		$i++;
	}
	exit('{"code":0,"msg":"成功改变'.$i.'条订单状态"}');
break;
case 'result':
	$id=intval($_POST['id']);
	$rows=$DB->getRow("select * from pre_orders where id='$id' limit 1");
	if(!$rows)
		exit('{"code":-1,"msg":"当前订单不存在！"}');
	exit('{"code":0,"result":"'.$rows['result'].'"}');
break;
case 'setresult':
	adminpermission('order', 2);
	$id=intval($_POST['id']);
	$rows=$DB->getRow("select * from pre_orders where id='$id' limit 1");
	if(!$rows)
		exit('{"code":-1,"msg":"当前订单不存在！"}');
	$result=str_replace(array("\r\n","\n"),'',$_POST['result']);
	if($DB->exec("update pre_orders set result='$result' where id='$id'")!==false)
		exit('{"code":0,"msg":"修改订单成功！"}');
	else
		exit('{"code":-1,"msg":"修改订单失败！'.$DB->error().'"}');
break;
case 'manualFaka':
	adminpermission('order', 2);
	$id = intval($_POST['id']);
	$confirm_upstream = isset($_POST['confirm_upstream']) ? intval($_POST['confirm_upstream']) : 0;
	$kmdata_input = isset($_POST['kmdata']) ? trim((string)$_POST['kmdata']) : '';
	$row = $DB->getRow("select * from pre_orders where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前订单不存在！"}');
	if(intval($row['status']) == 4)
		exit('{"code":-1,"msg":"该订单已退单，不能手动发卡"}');
	if(intval($DB->getColumn("SELECT COUNT(*) FROM pre_faka WHERE orderid='$id'")) > 0)
		exit('{"code":-1,"msg":"该订单已经存在卡密，请勿重复发卡"}');
	if(!empty($row['djorder']) && $confirm_upstream != 1)
		exit('{"code":-2,"msg":"该订单已有上游订单号，确认手动发卡可能造成重复发货。是否继续？"}');
	if($kmdata_input === '')
		exit('{"code":-1,"msg":"请先填写卡密内容"}');
	$cards = function_exists('q8_extract_remote_faka_rows') ? q8_extract_remote_faka_rows(array('kmdata' => $kmdata_input)) : array();
	if(empty($cards))
		exit('{"code":-1,"msg":"没有识别到有效卡密，请每行填写一条卡密"}');
	$tool = $DB->getRow("select * from pre_tools where tid='{$row['tid']}' limit 1");
	if(!$tool)
		exit('{"code":-1,"msg":"订单商品不存在，无法发卡"}');
	$result_text = '';
	$insert_count = 0;
	foreach($cards as $card_row){
		$card = trim((string)$card_row['card']);
		$pass = trim((string)$card_row['pass']);
		if($card === '') continue;
		$card_sql = daddslashes($card);
		$pass_sql = daddslashes($pass);
		$DB->query("INSERT INTO `pre_faka` (`tid`,`km`,`pw`,`orderid`,`addtime`,`usetime`) VALUES ('{$row['tid']}','{$card_sql}','{$pass_sql}','{$id}',NOW(),NOW())");
		$result_text .= q8_build_faka_text($card, $pass);
		$insert_count++;
	}
	if($insert_count <= 0 || $result_text === '')
		exit('{"code":-1,"msg":"没有写入有效卡密"}');
	if($DB->exec("UPDATE `pre_orders` SET `status`='1',`djzt`='3',`result`='" . daddslashes($result_text) . "',`uptime`='" . time() . "' WHERE `id`='{$id}'") !== false){
		if(function_exists('q8_send_faka_mail')){
			$input = array($row['input'], $row['input2'], $row['input3'], $row['input4'], $row['input5']);
			q8_send_faka_mail($conf, $tool, $input, $result_text, $date);
		}
		exit(json_encode(array('code'=>0,'msg'=>'手动发卡成功，已写入'.$insert_count.'条卡密')));
	}
	exit('{"code":-1,"msg":"订单状态更新失败！'.$DB->error().'"}');
break;
case 'getmoney': //退款查询
	adminpermission('refund', 2);
	$id=intval($_POST['id']);
	$row=$DB->getRow("select * from pre_orders where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前订单不存在！"}');
	if($row['zid']<1 && !is_numeric($row['userid']))exit('{"code":-1,"msg":"退款失败，该订单属于主站"}');
	if($row['status']==4)exit('{"code":-1,"msg":"该订单已退款请勿重复提交"}');
	//if($row['status']!=0&&$row['status']!=3)exit('{"code":-1,"msg":"只有未处理和异常的订单才支持退款"}');

	if($row['money']==0){
	    $tool=$DB->getRow("select * from pre_tools where tid='" . $row['tid'] . "' limit 1");
		$money=$tool['price'];
		$money=$row['value']*$money;
	}else{
		$money=$row['money'];
	}

	//$tc_point=$DB->getColumn("select point from pre_points where zid='" . $row['zid'] . "' and action='提成' and orderid='$id' limit 1");
	//if($tc_point>0)$money-=$tc_point;
	if($money==0)exit('{"code":-1,"msg":"该订单为0元"}');
	exit('{"code":0,"money":"'.$money.'"}');
break;
case 'refund': //退款操作
	adminpermission('refund', 2);
	$id=intval($_POST['id']);
	$money=trim(daddslashes($_POST['money']));
	$row=$DB->getRow("select * from pre_orders where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前订单不存在！"}');
	if($row['zid']<1 && !is_numeric($row['userid']))exit('{"code":-1,"msg":"退款失败，该订单属于主站"}');
	if($row['status']==4)exit('{"code":-1,"msg":"该订单已退款请勿重复提交"}');
	if($row['status']!=0&&$row['status']!=3)exit('{"code":-1,"msg":"只有未处理和异常的订单才支持退款"}');
	if($money<=0)$money=$row['money'];
	if(is_numeric($row['userid'])){
		$zid = intval($row['userid']);
		changeUserMoney($zid, $money, true, '退款', '订单(ID'.$id.')已退款到余额');
	}
	$tool=$DB->getRow("select * from pre_tools where tid='{$row['tid']}' limit 1");
	if($tool['is_curl'] == 4 && $tool['goods_sid'] != 0){
	    $trade=$DB->getRow("select * from pre_pay where trade_no='{\$row['tradeno']}' limit 1");
	    changeSupMoney($tool['goods_sid'], $tool['sup_price'], false, "退款", "订单号：".$trade['trade_no']."|站长已经退款，扣除您本次的销售金额" . $tool['sup_price'] . "元");
	}
	if(rollbackPoint($id)){
		$addstr = '上级提成扣除成功';
	}else{
		$addstr = '但扣除上级提成失败';
	}
	$DB->exec("update pre_orders set status='4',result=NULL where id='{$id}'");
	if(is_numeric($row['userid'])){
		exit('{"code":0,"msg":"该订单已成功退款给UID'.$zid.'！'.$addstr.'"}');
	}else{
		exit('{"code":0,"msg":"该订单属于未注册用户，需要手动退款！'.$addstr.'"}');
	}
break;
case 'djOrder': //重新下单
	// 移除不必要的权限检查，或确保用户有足够的权限
	// adminpermission('order', 2);
	$id=intval($_GET['id']);
	// 处理 GET 请求，确保 $url 和 $post 参数为空时也能正常执行
	$url=$_GET['url'] ?? '';
	$post=$_GET['post'] ?? '';
	$result = do_goods($id,$url,$post);
	if(strpos($result,'成功')!==false){
		exit('{"code":0,"msg":"下单成功！"}');
	} else {
		exit('{"code":-1,"msg":"'.$result.'"}');
	}
break;
case 'showStatus': //订单进度查询
	adminpermission('order', 2);
	$id=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_orders where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前订单不存在！"}');
	$tool=$DB->getRow("select * from pre_tools where tid='{$row['tid']}' limit 1");
	if(!$tool['shequ'])exit('{"code":-1,"msg":"非对接商品,无法查询进度"}');
	$shequ=$DB->getRow("select * from pre_shequ where id='{$tool['shequ']}' limit 1");
	$list = third_call($shequ['type'], $shequ, 'query_order', [$row['djorder'], $tool['goods_id'], [$row['input'], $row['input2'], $row['input3'], $row['input4'], $row['input5']]]);

	if($list === false){
		exit('{"code":-1,"msg":"该对接暂不支持查询订单进度"}');
	}

	if(is_array($list)){
		$shopurl = '';
		if($shequ['type']=='yile'){
			$shopurl = 'http://'.$shequ['url'].'/home/order/'.$tool['goods_id'];
		}elseif($shequ['type']=='jiuwu'){
			$shopurl = 'http://'.$shequ['url'].'/index.php?m=home&c=goods&a=detail&id='.$tool['goods_id'].'&goods_type='.$tool['goods_type'];
		}elseif($shequ['type']=='shangmeng'){
			$shopurl = 'http://'.$shequ['url'].'/#/goodsDetail?id='.$tool['goods_id'];
		}elseif($shequ['type']=='kashangwl'){
			$shopurl = 'http://'.$shequ['url'].'/buy/'.$tool['goods_id'];
		}elseif($shequ['type']=='shangzhanwl'||$shequ['type']=='mengchuang'){
			$shopurl = 'http://'.$shequ['url'].'/product/'.$tool['goods_id'].'.html';
		}elseif($shequ['type']=='daishua'){
			$shopurl = 'http://'.$shequ['url'].'/?tid='.$tool['tid'];
		}elseif($shequ['type']=='zhike'){
			$shopurl = 'http://'.$shequ['url'].'/shop/goods/detail/?sn='.$tool['goods_param'];
		}elseif($shequ['type']=='xingouka'){
			$shopurl = 'http://'.$shequ['url'].'/buy/'.$tool['goods_id'].'.html';
		}elseif($shequ['type']=='kakayun'){
			$shopurl = 'http://'.$shequ['url'].'/pg/'.$tool['goods_id'].'.html';
		}elseif($shequ['type']=='yunbao'){
			$shopurl = 'http://'.$shequ['url'].'/index/p/id/'.$tool['goods_id'];
		}elseif($shequ['type']=='fanqin'){
			$shopurl = 'http://'.$shequ['url'].'/goods/showGoodsDetail?shopGoodsId='.$tool['goods_id'];
		}

		if(($list['order_state']=='已完成'||$list['order_state']=='订单已完成'||$list['订单状态']=='已完成'||$list['订单状态']=='已发货'||$list['订单状态']=='交易成功'||$list['订单状态']=='已支付') && $row['status']==2){
			$DB->exec("UPDATE `pre_orders` SET `status`=1 WHERE id='" . $id . "'");
			// 订单完成后发送邮件
			$shoprow = $DB->getRow("SELECT * FROM pre_tools WHERE tid='" . $row['tid'] . "' LIMIT 1");
			if($shoprow && $shoprow['send_email'] == 1){
				sendOrderEmail($row, $shoprow);
			}
		}
		if((strpos($list['order_state'],'异常')!==false||strpos($list['order_state'],'退单')!==false||strpos($list['order_state'],'退款')!==false||$list['订单状态']=='异常'||$list['订单状态']=='已退单'||$list['订单状态']=='支付失败') && $row['status']<3){
			$DB->exec("UPDATE `pre_orders` SET `status`=3 WHERE id='$id'");
		}
		$list['对接订单号'] = $row['djorder'];
		$result=array('code'=>0,'msg'=>'succ','domain'=>$shequ['url'],'shopid'=>$tool['goods_id'],'shopurl'=>$shopurl,'list'=>$list);
	}elseif($list){
		$result=array('code'=>-1,'msg'=>$list);
	}else{
		$result=array('code'=>-1,'msg'=>'获取数据失败');
	}
	exit(json_encode($result));
break;

case 'fillPayOrder':
	$trade_no = trim($_POST['trade_no']);
	$srow = $DB->getRow("SELECT * FROM pre_pay WHERE trade_no=:trade_no", [':trade_no'=>$trade_no]);
	if (!$srow) exit(json_encode(array('code'=>-1,'msg'=>'记录不存在')));
	if($srow['status']==0){
		if($DB->exec("UPDATE `pre_pay` SET `status`='1',`endtime`=NOW() WHERE `trade_no`=:trade_no", [':trade_no'=>$trade_no])){
			$conf['message_duijie']=0;
			$conf['message_buy']=0;
			$orderid = processOrder($srow);
			exit(json_encode(array('code'=>0,'msg'=>'补单成功！','orderid'=>$orderid)));
		}
	}else{
		exit(json_encode(array('code'=>-1,'msg'=>'该订单已经是完成状态')));
	}
break;

case 'delPayOrder':
	$trade_no = trim($_POST['trade_no']);
	if($DB->exec("DELETE FROM pre_pay WHERE trade_no=:trade_no", [':trade_no'=>$trade_no])!==false)
		exit('{"code":0,"msg":"删除支付记录成功！"}');
	else
		exit('{"code":-1,"msg":"删除支付记录失败！'.$DB->error().'"}');
break;

default:
	exit('{"code":-4,"msg":"No Act"}');
break;
}
