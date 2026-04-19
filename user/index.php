<?php
/* 
用户后台首页 - 蓝色微蓝色主题
*/
require '../includes/common.php';
if($islogin2==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");

if($_GET['mod']=='faka'){
exit("<script language='javascript'>window.location.href='../?mod=faka&&id={$_GET['id']}&skey={$_GET['skey']}';</script>");
}
$title = '平台首页';
include 'head.php';
?>
<link rel="stylesheet" href="<?php echo $cdnpublic?>toastr.js/latest/css/toastr.min.css">
<link rel="stylesheet" href="./public/css/blue_theme.css">
<style>
img.logo{width:14px;height:14px;margin:0 5px 0 3px;}
.span_position{display:inline;background:red;border-radius:50%;width:10px;height:10px;position:absolute}
.nickname{overflow: hidden;text-overflow: ellipsis;white-space: nowrap;max-width:100px;}
/* 提升用户信息面板层级，确保充值和提现按钮正常点击 */
.panel-heading.font-bold {
    position: relative;
    z-index: 1000;
    overflow: visible;
}
.widget-content {
    position: relative;
    z-index: 1001;
}
.widget-content a.btn {
    position: relative;
    z-index: 1002;
    cursor: pointer;
    display: inline-block;
    padding: 5px 10px;
    margin: 0 2px;
    text-decoration: none;
}
</style>
<div class="wrapper">
<div class="col-sm-12">
<?php
if($userrow['rmb']>4){
if(strlen($userrow['pwd'])<6 || is_numeric($userrow['pwd']) && strlen($userrow['pwd'])<=10 || $userrow['pwd']===$userrow['qq']){
echo '<div class="alert alert-danger"><span class="btn-sm btn-danger">重要</span>&nbsp;你的密码过于简单，请不要使用较短的纯数字或自己的QQ号当做密码，以免造成资金损失！ <a href="uset.php?mod=user">点此修改密码</a></div>';
}elseif($userrow['user']===$userrow['pwd']){
echo '<div class="alert alert-danger"><span class="btn-sm btn-danger">重要</span>&nbsp;你的用户名与密码相同，极易被黑客破解，请及时修改密码 <a href="uset.php?mod=user">点此修改密码</a></div>';
}
}
?>
</div>
	<div class="col-lg-12 col-md-12 col-sm-12">
	<!-- 简约风格面板设计 -->
	<div class="panel panel-default simple-panel">
		<div class="panel-heading font-bold simple-panel-header">
			<h3 class="panel-title text-center"><i class="fa fa-user-circle"></i>&nbsp;&nbsp;<b>我的信息与站点管理</b></h3>
		</div>
		<div class="panel-body simple-panel-body">
			<div class="row">
				<!-- 左侧：用户信息 -->
				<div class="col-lg-6 col-md-12 col-sm-12 simple-column">
					<div class="widget-content text-right clearfix simple-profile-section" style="margin-bottom: 20px;">
						<img src="<?php echo $faceimg ?>" alt="Avatar" width="80" class="img-circle img-thumbnail img-thumbnail-avatar pull-left simple-avatar">
						<h4 class="simple-balance"><b>余额：<span class="balance-amount"><?php echo $userrow['rmb']?>元</span></b></h4>
						<span class="text-muted simple-actions">
							<a href="recharge.php" class="btn btn-xs btn-success simple-action-btn"><b>充值余额</b></a>
							<a href="tixian.php" class="btn btn-xs btn-info simple-action-btn">申请提现</a>
						</span>
					</div>
					
					<!-- 用户基本信息卡片 -->
					<div class="simple-info-cards">
						<div class="row">
							<div class="col-xs-4 simple-info-card">
								<div class="simple-info-label">用户名</div>
								<div class="simple-info-value"><?php echo $nickname?></div>
							</div>
							<div class="col-xs-4 simple-info-card">
								<div class="simple-info-label">UID</div>
								<div class="simple-info-value"><?php echo $userrow['zid']?></div>
							</div>
							<div class="col-xs-4 simple-info-card">
								<div class="simple-info-label">今日收益</div>
								<div class="simple-info-value income-amount" id="income_today">0元</div>
							</div>
						</div>
					</div>
					
					<!-- 功能按钮网格 -->
					<div class="simple-function-grid">
						<div class="row">
							<div class="col-xs-4 simple-function-item">
								<a href="/user/shop.php" class="simple-function-link">
									<i class="fa fa-shopping-cart simple-function-icon"></i>
									<div class="simple-function-label"><?php echo $userrow['power']>0?'低价下单':'自助下单';?></div>
								</a>
							</div>
							<div class="col-xs-4 simple-function-item">
								<?php if($conf['qiandao_reward']){?>
								<a href="./qiandao.php" class="simple-function-link">
									<i class="fa fa-check-square simple-function-icon"></i>
									<div class="simple-function-label">每日签到</div>
								</a>
								<?php }else{?>
								<a href="recharge.php" class="simple-function-link">
									<i class="fa fa-money simple-function-icon"></i>
									<div class="simple-function-label">充值余额</div>
								</a>
								<?php }?>
							</div>
							<div class="col-xs-4 simple-function-item">
								<a href="message.php" class="simple-function-link">
									<i class="fa fa-bullhorn simple-function-icon"></i>
									<div class="simple-function-label">站内消息</div>
									<span id="message_count" class="simple-notification-badge"></span>
								</a>
							</div>
						</div>
						
						<div class="row">
							<div class="col-xs-4 simple-function-item">
								<a href="<?php echo $userrow['power']>0?'./shop.php?chadan=1':'../?chadan=1';?>" class="simple-function-link">
									<i class="fa fa-search simple-function-icon"></i>
									<div class="simple-function-label">自助查单</div>
								</a>
							</div>
							<div class="col-xs-4 simple-function-item">
								<a href="./workorder.php" class="simple-function-link">
									<i class="fa fa-check-square-o simple-function-icon"></i>
									<div class="simple-function-label">我的工单</div>
									<span id="work_count" class="simple-notification-badge"></span>
								</a>
							</div>
							<div class="col-xs-4 simple-function-item">
								<a href="record.php" class="simple-function-link">
									<i class="fa fa-hashtag simple-function-icon"></i>
									<div class="simple-function-label">收支明细</div>
								</a>
							</div>
						</div>
						
						<?php if($userrow['power']>0){?>
						<div class="row">
							<div class="col-xs-4 simple-function-item">
								<a href="shoplist.php" class="simple-function-link">
									<i class="fa fa-list-alt simple-function-icon"></i>
									<div class="simple-function-label">商品管理</div>
								</a>
							</div>
							<div class="col-xs-4 simple-function-item">
								<a href="list.php" class="simple-function-link">
									<i class="fa fa-list simple-function-icon"></i>
									<div class="simple-function-label">订单记录</div>
								</a>
							</div>
							<div class="col-xs-4 simple-function-item">
								<?php if($userrow['power']==2){?>
								<a href="sitelist.php" class="simple-function-link">
									<i class="fa fa-sitemap simple-function-icon"></i>
									<div class="simple-function-label">分站管理</div>
								</a>
								<?php }else{?>
								<a href="login.php?logout" class="simple-function-link simple-danger-link">
									<i class="fa fa-sign-out simple-function-icon"></i>
									<div class="simple-function-label">安全退出</div>
								</a>
								<?php }?>
							</div>
						</div>
						<?php }
						// 添加域名管理行
						?>
						
						<div class="row">
							<div class="col-xs-4 simple-function-item">
								<a href="cdomain.php" class="simple-function-link">
									<i class="fa fa-sign-out simple-function-icon"></i>
									<div class="simple-function-label">域名更换</div>
								</a>
							</div>
							<div class="col-xs-4 simple-function-item">
								<a href="ndomain.php" class="simple-function-link">
									<i class="fa fa-sign-out simple-function-icon"></i>
									<div class="simple-function-label">域名增加</div>
								</a>
							</div>
							<div class="col-xs-4 simple-function-item">
								<a href="usetmoban.php?mod=site2" class="simple-function-link">
									<i class="fa fa-home simple-function-icon"></i>
									<div class="simple-function-label">模板设置</div>
								</a>
							</div>
						</div>
						
						<!-- 添加管理功能行 -->
						<div class="row">
							<div class="col-xs-4 simple-function-item">
								<a href="../sup" class="simple-function-link">
									<i class="fa fa-check-square simple-function-icon"></i>
									<div class="simple-function-label">供货管理</div>
								</a>
							</div>
							<div class="col-xs-4 simple-function-item">
								<a href="../toollogs.php" class="simple-function-link">
									<i class="fa fa-list simple-function-icon"></i>
									<div class="simple-function-label">上架日志</div>
								</a>
							</div>
							<div class="col-xs-4 simple-function-item">
								<a href="uset.php?mod=skimg" class="simple-function-link">
									<i class="fa fa-check-square simple-function-icon"></i>
									<div class="simple-function-label">提现设置</div>
								</a>
							</div>
						</div>
					</div>
					
					<!-- 客服链接 -->
					<div class="simple-service-section">
						<a href="https://qqwxfh.github.io/?jOTdN" class="btn btn-default btn-block simple-service-btn" style="background-color: #ffffff; border-color: #dee2e6; color: #495057;">
							<i class="fa fa-check-square"></i>
							<br><b>【QQ微信失联点这里】站长专属客服</b>
						</a>
					</div>
				</div>
				
				<!-- 右侧：站点信息 -->
				<div class="col-lg-6 col-md-12 col-sm-12 simple-column">
					<h4 class="simple-title text-center"><b>我的站点信息</b></h4>
					<div class="simple-site-section">
					<?php if($userrow['power']>0){?>
						<div class="simple-site-item">
							<div class="simple-site-label">通知提醒</div>
							<div class="simple-site-value">
								你当前有<span class="notification-count" id="tiaosu">0</span>条信息未阅读
								<a href="./message.php" class="btn btn-xs btn-primary simple-site-action">立即查看</a>
							</div>
						</div>
						
						<div class="simple-site-item">
							<div class="simple-site-label">我的域名①</div>
							<div class="simple-site-value">
								<a href="http://<?php echo $userrow['domain']?>" target="_blank" rel="noreferrer" class="simple-domain-link"><?php echo $userrow['domain']?></a>
							</div>
						</div>
						
						<?php if($userrow['domain2']){?>
						<div class="simple-site-item">
							<div class="simple-site-label">我的域名②</div>
							<div class="simple-site-value">
								<a href="http://<?php echo $userrow['domain2']?>" target="_blank" rel="noreferrer" class="simple-domain-link"><?php echo $userrow['domain2']?></a>
							</div>
						</div>
						<?php }?>
						
						<?php if($conf['fanghong_api']){?>
						<div class="simple-site-item">
							<div class="simple-site-label">防红链接①</div>
							<div class="simple-site-value">
								<a href="javascript:;" id="copy-btn" data-clipboard-text="" class="simple-fanghong-link">Loading...</a>
								<div class="simple-site-actions">
									<button class="btn btn-default btn-xs simple-site-action" id="recreate_url">重新生成</button>
									<a href="javascript:void(0);" onclick="layer.alert('防红链接：该链接可以在QQ直接打开的您的网站，方便推广！<br />Tips：点击短网址即可复制哦~<br />推荐建议使用防红链接！如果更换防红链接，之前的也是能打开的',{icon: 3,title: '小提示',skin: 'layui-layer-molv layui-layer-wxd'});" class="btn btn-info btn-xs simple-site-action">说明</a>
								</div>
							</div>
						</div>
						
						<?php if($userrow['domain2']){?>
						<div class="simple-site-item">
							<div class="simple-site-label">防红链接②</div>
							<div class="simple-site-value">
								<a href="javascript:;" id="copy-btn2" data-clipboard-text="" class="simple-fanghong-link">Loading...</a>
							</div>
						</div>
						<?php }?>
						
						<div class="simple-site-item simple-warning-item">
							<div class="simple-site-label">注意事项</div>
							<div class="simple-site-value simple-warning-text">
								为了保护你站点域名不被QQ/微信拦截，推荐使用防红链接！点击防红域名自动复制
							</div>
						</div>
						<?php }?>
						
						<div class="simple-site-item">
							<div class="simple-site-label">网站名称</div>
							<div class="simple-site-value">
								<span class="site-name"><?php echo $userrow['sitename']?></span>
								<a href="uset.php?mod=site" class="btn btn-xs btn-info simple-site-action">立即更换</a>
							</div>
						</div>
						
						<div class="simple-site-item">
							<div class="simple-site-label">代理类型</div>
							<div class="simple-site-value">
								<span class="agent-type"><?php echo ($userrow['power']==2?'<font color=red>专业版</font>':'<font color=red>普及版</font>')?></span>
								<?php if($conf['fenzhan_upgrade']>0 && $userrow['power']==1){?>
								<a href="upsite.php" class="btn btn-danger btn-xs simple-site-action">升级站点</a>
								<?php }else{?>
								<a href="./sitelist.php" class="btn btn-danger btn-xs simple-site-action">下级管理</a>
								<?php }?>
							</div>
						</div>
						
						<?php if($conf['fenzhan_expiry']>0){?>
						<div class="simple-site-item">
							<div class="simple-site-label">到期时间</div>
							<div class="simple-site-value">
								<span class="expiry-time"><?php echo $userrow['endtime']?></span>
								<a href="renew.php" class="btn btn-xs btn-primary simple-site-action">立即续期</a>
							</div>
						</div>
						<?php }?>
						
						<div class="simple-site-item">
							<div class="simple-site-label">当前状态</div>
							<div class="simple-site-value">
								<span class="site-status"><?php echo ($conf['fenzhan_expiry']>0 && $userrow['endtime']<$date?'<font color="red">已到期</font>':'<font color="green">正常运行</font>');?></span>
							</div>
						</div>
					<?php }else{?>
						<div class="simple-site-item">
							<div class="simple-site-value simple-no-site">
								你还未开通分站
								<a href="regsite.php" class="btn btn-primary btn-sm simple-site-action">点此开通分站</a>
							</div>
						</div>
					<?php }?>
					</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
	
	<!-- 简约风格样式 -->
	<style>
	/* 主面板样式 */
	.simple-panel {
		border-radius: 4px;
		border: 1px solid #eaeaea;
		background: #ffffff;
		margin-bottom: 20px;
	}
	
	.simple-panel-header {
		background: #f5f5f5;
		color: #333;
		padding: 10px 15px;
		border-bottom: 1px solid #eaeaea;
	}
	
	.simple-panel-body {
		padding: 15px;
	}
	
	/* 列样式 */
	.simple-column {
		padding: 0 15px;
	}
	
	/* 标题样式 */
	.simple-title {
		margin-bottom: 20px;
		color: #333;
		font-size: 16px;
		padding-bottom: 8px;
		border-bottom: 1px solid #eaeaea;
		display: inline-block;
	}
	
	/* 用户资料部分 */
	.simple-profile-section {
		background: #fafafa;
		padding: 12px;
		border-radius: 4px;
		border: 1px solid #eaeaea;
	}
	
	.simple-avatar {
		border: 2px solid #ddd;
	}
	
	.simple-balance {
		color: #333;
		margin-bottom: 5px;
	}
	
	.balance-amount {
		color: #28a745;
		font-size: 18px;
	}
	
	.simple-action-btn {
		margin: 0 3px;
	}
	
	/* 信息卡片样式 */
	.simple-info-cards {
		margin-bottom: 20px;
	}
	
	.simple-info-card {
		background: #fafafa;
		padding: 12px;
		border-radius: 4px;
		border: 1px solid #eaeaea;
		text-align: center;
		margin-bottom: 10px;
	}
	
	.simple-info-label {
		color: #666;
		font-size: 13px;
		margin-bottom: 3px;
	}
	
	.simple-info-value {
		color: #333;
		font-size: 16px;
		font-weight: bold;
	}
	
	/* 功能按钮网格 */
	.simple-function-grid {
		margin-bottom: 20px;
	}
	
	.simple-function-item {
		margin-bottom: 10px;
	}
	
	.simple-function-link {
		display: block;
		background: #fafafa;
		padding: 12px 8px;
		border-radius: 4px;
		text-align: center;
		border: 1px solid #eaeaea;
		color: #333;
		position: relative;
		text-decoration: none;
	}
	
	.simple-function-link:hover {
		background: #f0f0f0;
		color: #333;
		text-decoration: none;
		border-color: #ddd;
	}
	
	.simple-function-link.simple-danger-link:hover {
		background: #f8d7da;
		border-color: #f5c6cb;
	}
	
	.simple-function-icon {
		font-size: 20px;
		margin-bottom: 6px;
	}
	
	.simple-function-label {
		font-size: 13px;
		font-weight: bold;
	}
	
	/* 通知徽章 */
	.simple-notification-badge {
		position: absolute;
		top: 3px;
		right: 8px;
		background: red;
		color: white;
		border-radius: 50%;
		width: 16px;
		height: 16px;
		font-size: 11px;
		line-height: 16px;
		text-align: center;
		font-weight: bold;
	}
	
	/* 客服链接 */
	.simple-service-section {
		margin-top: 15px;
	}
	
	.simple-service-btn {
		border-radius: 4px;
		background: #28a745;
		border: 1px solid #28a745;
		padding: 10px;
	}
	
	.simple-service-btn:hover {
		background: #218838;
		border-color: #1e7e34;
	}
	
	/* 站点信息部分 */
	.simple-site-section {
		background: #fafafa;
		border-radius: 4px;
		padding: 15px;
		border: 1px solid #eaeaea;
	}
	
	.simple-site-item {
		margin-bottom: 15px;
		padding-bottom: 10px;
		border-bottom: 1px solid #eaeaea;
	}
	
	.simple-site-item:last-child {
		margin-bottom: 0;
		padding-bottom: 0;
		border-bottom: none;
	}
	
	.simple-site-label {
		color: #666;
		font-size: 13px;
		margin-bottom: 6px;
		font-weight: bold;
	}
	
	.simple-site-value {
		color: #333;
		font-size: 14px;
		display: flex;
		justify-content: space-between;
		align-items: center;
		flex-wrap: wrap;
	}
	
	.simple-site-action {
		margin-left: 8px;
	}
	
	/* 域名链接 */
	.simple-domain-link {
		color: #007bff;
		text-decoration: none;
		word-break: break-all;
	}
	
	.simple-domain-link:hover {
		color: #0056b3;
		text-decoration: underline;
	}
	
	/* 警告文本 */
	.simple-warning-item {
		background: #fff3cd;
		padding: 12px;
		border-radius: 4px;
		border: 1px solid #ffeeba;
	}
	
	.simple-warning-text {
		color: #856404;
	}
	
	/* 无站点消息 */
	.simple-no-site {
		text-align: center;
		padding: 20px 15px;
		color: #666;
	}
	
	/* 响应式调整 */
	@media (max-width: 768px) {
		.simple-panel-body {
			padding: 10px;
		}
		
		.simple-column {
			margin-bottom: 15px;
		}
		
		.simple-function-item {
			margin-bottom: 8px;
		}
		
		.simple-site-value {
			flex-direction: column;
			align-items: flex-start;
		}
		
		.simple-site-action {
			margin-left: 0;
			margin-top: 8px;
		}
		
		.simple-info-card {
			margin-bottom: 10px;
		}
	}
	</style>

<?php if($islogin2==1){?>
	<div class="col-lg-12 col-md-12 col-sm-12">
		<div class="panel panel-default">
		<div class="panel-heading font-bold">
			<h3 class="panel-title"><i class="fa fa-volume-up"></i>&nbsp;&nbsp;<b>站点公告</b></h3>
		</div>
		<div class="panel-body">
			<marquee>
				<b>最亲爱的站长祝愿：祝各位站长幸福安康，快乐美满，好事成双，生意兴隆，如果是卡密问题或者软件跑路购买的卡密都会退款或者重新给你换一款，低价提卡，诚信邀代理，欢迎新老站长回归加盟</b>
			</marquee>
		</div>
    </div>			
			<?php echo $conf['gg_panel']?>
		

</p ><div class="panel-group text-center" id="accordion">
    <div class="panel panel-default">
        <div class="panel-heading">
           
        </div>
        <div class="panel-collapse collapse" id="collapseTwo" aria-expanded="false" style="height: 0px;">
            <div class="panel-body">
               
               
            </span></div>
		</div>
	</div>
<?php }?>
</div>
</div>
</div>
<script src="<?php echo $cdnpublic?>layer/2.3/layer.js"></script>
<script src="<?php echo $cdnpublic?>clipboard.js/1.7.1/clipboard.min.js"></script>
<script src="<?php echo $cdnpublic?>toastr.js/latest/toastr.min.js"></script>
<script>
$(document).ready(function(){
var clipboard = new Clipboard('#copy-btn');
clipboard.on('success', function (e) {
	layer.msg('复制成功！', {icon: 1});
});
clipboard.on('error', function (e) {
	layer.msg('复制失败，请长按链接后手动复制', {icon: 2});
});
var clipboard2 = new Clipboard('#copy-btn2');
clipboard2.on('success', function (e) {
	layer.msg('复制成功！', {icon: 1});
});
clipboard2.on('error', function (e) {
	layer.msg('复制失败，请长按链接后手动复制', {icon: 2});
});

$("#recreate_url").click(function(){
	var self = $(this);
	if (self.attr("data-lock") === "true") return;
	else self.attr("data-lock", "true");
	var ii = layer.load(1, {shade: [0.1, '#fff']});
	$.get("ajax.php?act=create_url&force=1", function(data) {
		layer.close(ii);
		if(data.code == 0){
			layer.msg('生成链接成功');
			$("#copy-btn").html(data.url);
			$("#copy-btn").attr('data-clipboard-text',data.url);
			if($("#copy-btn2").length>0){
				$("#copy-btn2").html(data.url2);
				$("#copy-btn2").attr('data-clipboard-text',data.url2);
			}
		}else{
			layer.alert(data.msg);
		}
		self.attr("data-lock", "false");
	}, 'json');
});
if(window.location.hash=='#chongzhi'){
	$("#userjs").modal('show');
}
	$.ajax({
		type : "GET",
		url : "ajax.php?act=msg",
		dataType : 'json',
		async: true,
		success : function(data) {
			if(data.code==0){
				if(data.count>0){
					$("#tiaosu").text(data.count);
					$("#message_count").addClass('span_position');
					toastr.info('<a href="message.php">您有<b>'+data.count+'</b>条新消息，请注意查收！</a>', '消息提醒');
				}
				if(data.count2>0){
					$("#work_count").addClass('span_position');
					toastr.warning('<a href="workorder.php">您有<b>'+data.count2+'</b>个工单已被管理员回复！</a>', '工单提醒');
				}
				$("#income_today").html(data.income_today+'元');
			}
		}
	});
	$.ajax({
		type : "GET",
		url : "ajax.php?act=create_url",
		dataType : 'json',
		async: true,
		success : function(data) {
			if(data.code == 0){
				$("#copy-btn").html(data.url);
				$("#copy-btn").attr('data-clipboard-text',data.url);
				if($("#copy-btn2").length>0){
					$("#copy-btn2").html(data.url2);
					$("#copy-btn2").attr('data-clipboard-text',data.url2);
				}
			}else{
				$("#copy-btn").html(data.msg);
			}
		}
	});
});

<?php include_once SYSTEM_ROOT.'sakura.php'; loadSakuraEffect(); ?>
</script>