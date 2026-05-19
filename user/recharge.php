<?php
/**
 * 充值余额
**/
include("../includes/common.php");
$title='充值余额';
include './head.php';
if($islogin2==1){}else exit("<script language='javascript'>window.location.href='./login.php?back=recharge';</script>");
?>
<link rel="stylesheet" href="./public/css/blue_theme.css">
<style>
img.logo{width: 20px;margin: -2px 5px 0 5px;}
</style>
<div class="wrapper">
<div class="col-md-6">
<div class="panel panel-default">
    <div class="panel-heading font-bold">
		充值余额
	</div>
	<div class="panel-body text-center">
<?php
	$rebate_enabled = isset($conf['recharge_rebate_enabled']) ? intval($conf['recharge_rebate_enabled']) : 0;
	$rebate_rate = isset($conf['recharge_rebate_rate']) ? floatval($conf['recharge_rebate_rate']) : 0;
	$rebate_min = isset($conf['recharge_rebate_min']) ? floatval($conf['recharge_rebate_min']) : 0;
	$rebate_rules = isset($conf['recharge_rebate_rules']) ? trim($conf['recharge_rebate_rules']) : '';
	if ($rebate_enabled == 1 && ($rebate_rules !== '' || $rebate_rate > 0)) {
	    $example_money = 100;
	    $example_rebate = q8_calc_online_recharge_bonus($example_money, $conf);
	    $example_total = $example_money + $example_rebate;
	    echo '<div class="alert alert-success text-left">
	<b>&#20805;&#20540;&#36820;&#21033;&#27963;&#21160;&#65306;</b>' . ($rebate_rules !== '' ? '&#20805;&#20540;&#28385;100&#20803;&#36820;1%&#65292;&#28385;5000&#20803;&#36820;5%&#65292;&#28385;10000&#20803;&#36820;8%' : (($rebate_min > 0 ? '&#20805;&#20540;&#28385;' . $rebate_min . '&#20803;&#21487;' : '&#20805;&#20540;&#21487;') . '&#33719;&#24471;' . $rebate_rate . '%&#39069;&#22806;&#36820;&#21033;')) . '<br/>
	&#20363;&#22914;&#65306;&#20805;&#20540;' . $example_money . '&#20803;&#65292;&#23454;&#38469;&#21040;&#36134;' . $example_total . '&#20803;<br/>
	&#36820;&#21033;&#37329;&#39069;&#23558;&#22312;&#20805;&#20540;&#25104;&#21151;&#21518;&#33258;&#21160;&#21457;&#25918;&#21040;&#24744;&#30340;&#36134;&#25143;&#20313;&#39069;&#20013;
	</div>';
	}
	?>
			<b>我当前的账户余额：<span style="font-size:16px; color:#FF6133;"><?php echo $userrow['rmb']?></span> 元</b>
			<hr>
			<input type="text" class="form-control" name="value" id="rechargeAmount" autocomplete="off" placeholder="输入要充值的余额" oninput="calculateRebate()"><br>
			<div class="alert alert-info text-left" id="rebateInfo" style="display:none;">
				<b>预计返利金额：</b><span id="rebateAmount" style="font-size:16px; color:#FF6133;">0.00</span> 元<br>
				<b>实际到账金额：</b><span id="totalAmount" style="font-size:16px; color:#FF6133;">0.00</span> 元
			</div>
<?php
$usdtPayParts = explode('|', isset($conf['codepay_id']) ? $conf['codepay_id'] : '');
$usdtPayEnabled = isset($conf['usdtpay_api']) && intval($conf['usdtpay_api']) == 1 && !empty($conf['codepay_key']) && isset($usdtPayParts[0], $usdtPayParts[1]) && floatval($usdtPayParts[0]) > 0 && floatval($usdtPayParts[1]) > 0;
if($usdtPayEnabled)echo '<button type="submit" class="btn btn-default" id="buy_usdt"><img src="../other/usdt-trc20/static/img/tether.svg" class="logo">USDT - TRC20</button>&nbsp;';
if($conf['alipay_api'])echo '<button type="submit" class="btn btn-default" id="buy_alipay"><img src="../assets/img/alipay.png" class="logo">支付宝</button>&nbsp;';
if($conf['qqpay_api'])echo '<button type="submit" class="btn btn-default" id="buy_qqpay"><img src="../assets/img/qqpay.png" class="logo">QQ钱包</button>&nbsp;';
if($conf['wxpay_api'])echo '<button type="submit" class="btn btn-default" id="buy_wxpay"><img src="../assets/img/wxpay.png" class="logo">微信支付</button>&nbsp;';
?>
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModa4" id="alink" style="visibility: hidden;"></button>
<hr>
<?php if($conf['tixian_limit']==1){?><small style="color:red;">提示：充值的余额只能用于消费，无法提现</small><?php }?><?php if($conf['recharge_min']>0){?><small style="color:red;">，最低充值<?php echo $conf['recharge_min']?>元</small><?php }?>

	</div>
</div>
<?php if($conf['fenzhan_jiakuanka']){?>
<div class="panel panel-default">
    <div class="panel-heading font-bold">
		使用加款卡充值余额
	</div>
	<div class="panel-body text-center">
			<input type="text" class="form-control" name="km" autocomplete="off" placeholder="请输入加款卡密"><br>
			<button type="submit" class="btn btn-primary" id="usekm">立即使用</button>
							<a href="https://chongzk.xmkj01.cn/" class="btn btn-default"><i class="fa fa-list-alt"></i> 零手续加款卡</a>
	</div>
</div>
<?php }?>
</div>
<div class="col-md-6">
	<div class="panel panel-default">
    <div class="panel-heading font-bold">充值记录</div>
		  <div class="panel-body">

      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>充值金额</th><th>充值时间</th><th>状态</th></tr></thead>
          <tbody>
<?php
$flag=false;
$rs=$DB->query("SELECT * FROM pre_points WHERE zid='{$userrow['zid']}' AND action='充值' ORDER BY id DESC LIMIT 10");
while($res = $rs->fetch())
{
$flag=true;
echo '<tr><td><b>'.$res['point'].'</b></td><td>'.$res['addtime'].'</td><td><font color="green">已完成</font></td></tr>';
}
if(!$flag)echo '<tr class="no-records-found"><td colspan="99">暂无充值记录</td></tr>';
?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
 </div>
</div>
<?php include './foot.php';?>
<script>
function calculateRebate() {
	var value = parseFloat($("input[name='value']").val());
	var rebateInfo = document.getElementById('rebateInfo');
	var rebateAmount = document.getElementById('rebateAmount');
	var totalAmount = document.getElementById('totalAmount');
	var rebateEnabled = <?php echo isset($conf['recharge_rebate_enabled']) ? $conf['recharge_rebate_enabled'] : 1;?>;
	var rebateRate = <?php echo isset($conf['recharge_rebate_rate']) ? $conf['recharge_rebate_rate'] : 0;?>;
		var rebateMin = <?php echo isset($conf['recharge_rebate_min']) ? floatval($conf['recharge_rebate_min']) : 0;?>;
		var rebateRules = <?php echo isset($conf['recharge_rebate_rules']) ? json_encode((string)$conf['recharge_rebate_rules']) : '""'; ?>;

	if (rebateEnabled == 1 && !isNaN(value) && value > 0) {
		var currentRate = 0;
		if (rebateRules) {
			String(rebateRules).split('|').forEach(function(item) {
				var parts = item.split(':');
				if (parts.length !== 2) return;
				var threshold = parseFloat(parts[0]);
				var rate = parseFloat(parts[1]);
				if (!isNaN(threshold) && !isNaN(rate) && value >= threshold) currentRate = rate;
			});
		} else if (rebateMin <= 0 || value >= rebateMin) {
			currentRate = rebateRate;
		}
		if (currentRate <= 0) {
			rebateInfo.style.display = 'none';
			return;
		}
		var rebate = value * (currentRate / 100);
		var total = value + rebate;
		rebateAmount.textContent = rebate.toFixed(2);
		totalAmount.textContent = total.toFixed(2);
		rebateInfo.style.display = 'block';
	} else {
		rebateInfo.style.display = 'none';
	}
}

function dopay(type){
	var value=$("input[name='value']").val();
	if(value=='' || value==0){layer.alert('充值金额不能为空');return false;}
	$.get("ajax_user.php?act=recharge&type="+type+"&value="+value, function(data) {
		if(data.code == 0){
			window.location.href='../other/submit.php?type='+type+'&orderid='+data.trade_no;
		}else{
			layer.alert(data.msg);
		}
	}, 'json');
}
$(document).ready(function(){
	calculateRebate();
$("#buy_usdt").click(function(){
	dopay('usdt_pay')
});
$("#buy_alipay").click(function(){
	dopay('alipay')
});
$("#buy_qqpay").click(function(){
	dopay('qqpay')
});
$("#buy_wxpay").click(function(){
	dopay('wxpay')
});
$("#usekm").click(function(){
	var km = $("input[name='km']").val();
	$.ajax({
		type : "POST",
		url : "ajax_user.php?act=usekm",
		data : {km:km},
		dataType : 'json',
		async: true,
		success : function(data) {
			if(data.code == 0){
				layer.alert(data.msg, {icon:1}, function(){ window.location.reload() });
			}else{
				layer.alert(data.msg, {icon:2});
			}
		}
	});
});
})
</script>