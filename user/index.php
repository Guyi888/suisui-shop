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
$q8_can_manage_child_sites = isset($q8_can_manage_child_sites) ? $q8_can_manage_child_sites : (function_exists('q8_site_can_create_child_site') ? q8_site_can_create_child_site($userrow) : ($userrow['power']==2));
q8_site_admin_notice_ensure_fields();
$q8_admin_notice_context = $userrow['power'] > 0 ? q8_site_admin_notice_context($userrow, $conf) : array('html' => '');
$q8_user_uid = intval($userrow['zid']);
$q8_today_start = date('Y-m-d 00:00:00');
$q8_today_end = date('Y-m-d 23:59:59');
$q8_order_today = intval($DB->getColumn("SELECT COUNT(*) FROM pre_orders WHERE userid='{$q8_user_uid}' AND addtime>='{$q8_today_start}' AND addtime<='{$q8_today_end}'"));
$q8_order_total = intval($DB->getColumn("SELECT COUNT(*) FROM pre_orders WHERE userid='{$q8_user_uid}'"));
$q8_work_pending = intval($DB->getColumn("SELECT COUNT(*) FROM pre_workorder WHERE zid='{$q8_user_uid}' AND status IN (0,1)"));
$q8_work_reply = intval($DB->getColumn("SELECT COUNT(*) FROM pre_workorder WHERE zid='{$q8_user_uid}' AND status=1"));
if($userrow['power']==2){
	$q8_msg_type = '0,2,4';
}elseif($userrow['power']==1){
	$q8_msg_type = '0,2,3';
}else{
	$q8_msg_type = '0,1';
}
$q8_msgread = trim($userrow['msgread'], ',');
if(empty($q8_msgread)) $q8_msgread = '0';
$q8_msg_unread = intval($DB->getColumn("SELECT COUNT(*) FROM pre_message WHERE id NOT IN ($q8_msgread) AND type IN ($q8_msg_type) AND active=1"));
$q8_recent_orders = $DB->getAll("SELECT A.id,A.tid,A.input,A.status,A.addtime,A.money,B.name FROM pre_orders A LEFT JOIN pre_tools B ON A.tid=B.tid WHERE A.userid='{$q8_user_uid}' ORDER BY A.id DESC LIMIT 5");
$q8_site_today_orders = 0;
$q8_site_today_profit = 0;
$q8_site_week_orders = 0;
$q8_site_week_profit = 0;
$q8_site_today_visits = 0;
$q8_site_today_ips = 0;
$q8_site_week_visits = 0;
$q8_site_week_ips = 0;
$q8_site_visit_chart_days = array();
$q8_site_visit_chart_max = 1;
$q8_site_visit_ready = false;
if($userrow['power']>0){
	$q8_week_start = date('Y-m-d 00:00:00', strtotime('-6 day'));
	$q8_site_today_orders = intval($DB->getColumn("SELECT COUNT(*) FROM pre_orders WHERE zid='{$q8_user_uid}' AND addtime>='{$q8_today_start}' AND addtime<='{$q8_today_end}'"));
	$q8_site_today_profit = round(floatval($DB->getColumn("SELECT IFNULL(SUM(money-cost),0) FROM pre_orders WHERE zid='{$q8_user_uid}' AND addtime>='{$q8_today_start}' AND addtime<='{$q8_today_end}'")),2);
	$q8_site_week_orders = intval($DB->getColumn("SELECT COUNT(*) FROM pre_orders WHERE zid='{$q8_user_uid}' AND addtime>='{$q8_week_start}' AND addtime<='{$q8_today_end}'"));
	$q8_site_week_profit = round(floatval($DB->getColumn("SELECT IFNULL(SUM(money-cost),0) FROM pre_orders WHERE zid='{$q8_user_uid}' AND addtime>='{$q8_week_start}' AND addtime<='{$q8_today_end}'")),2);
	$q8_site_visit_dashboard = q8_get_site_visit_dashboard($q8_user_uid);
	$q8_site_today_visits = intval($q8_site_visit_dashboard['today_visits']);
	$q8_site_today_ips = intval($q8_site_visit_dashboard['today_ips']);
	$q8_site_week_visits = intval($q8_site_visit_dashboard['week_visits']);
	$q8_site_week_ips = intval($q8_site_visit_dashboard['week_ips']);
	$q8_site_visit_chart_days = is_array($q8_site_visit_dashboard['chart_days']) ? $q8_site_visit_dashboard['chart_days'] : array();
	$q8_site_visit_chart_max = max(1, intval($q8_site_visit_dashboard['chart_max']));
	$q8_site_visit_ready = !empty($q8_site_visit_dashboard['ready']);
}
$q8_site_chart_days = array();
$q8_site_chart_max = 1;
if($userrow['power']>0){
	for($i=6;$i>=0;$i--){
		$q8_day = date('Y-m-d', strtotime('-'.$i.' day'));
		$q8_day_start = $q8_day.' 00:00:00';
		$q8_day_end = $q8_day.' 23:59:59';
		$q8_day_orders = intval($DB->getColumn("SELECT COUNT(*) FROM pre_orders WHERE zid='{$q8_user_uid}' AND addtime>='{$q8_day_start}' AND addtime<='{$q8_day_end}'"));
		$q8_day_profit = floatval($DB->getColumn("SELECT IFNULL(SUM(money-cost),0) FROM pre_orders WHERE zid='{$q8_user_uid}' AND addtime>='{$q8_day_start}' AND addtime<='{$q8_day_end}'"));
		if($q8_day_orders>$q8_site_chart_max) $q8_site_chart_max = $q8_day_orders;
		$q8_site_chart_days[] = array('date'=>$q8_day, 'label'=>date('m/d', strtotime($q8_day)), 'orders'=>$q8_day_orders, 'profit'=>round($q8_day_profit,2));
	}
}
function q8_user_order_status_badge($status){
	$status = intval($status);
	if($status==1) return '<span class="q8-status q8-status-done">&#24050;&#23436;&#25104;</span>';
	if($status==2) return '<span class="q8-status q8-status-doing">&#22788;&#29702;&#20013;</span>';
	if($status==3) return '<span class="q8-status q8-status-bad">&#24322;&#24120;</span>';
	if($status==4) return '<span class="q8-status q8-status-refund">&#24050;&#36864;&#27454;</span>';
	return '<span class="q8-status q8-status-wait">&#24453;&#22788;&#29702;</span>';
}
?>
<link rel="stylesheet" href="../assets/vendor/toastr.js/2.1.4/toastr.min.css?v=q8vendor1">
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

        /* q8 site-info button polish */
        .simple-site-value {
            gap: 8px !important;
        }
        .simple-site-action,
        .simple-site-action:link,
        .simple-site-action:visited {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex: 0 0 auto !important;
            min-height: 26px !important;
            padding: 4px 10px !important;
            margin-left: 0 !important;
            margin-top: 0 !important;
            border: 0 !important;
            border-radius: 999px !important;
            line-height: 1 !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            white-space: nowrap !important;
            text-decoration: none !important;
            box-shadow: 0 6px 14px rgba(22,119,255,.12);
        }
        .simple-site-action:hover,
        .simple-site-action:focus,
        .simple-site-action:active {
            color: #fff !important;
            text-decoration: none !important;
            outline: none !important;
        }
        .simple-no-site {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 10px !important;
            flex-wrap: wrap !important;
        }
        @media (max-width: 768px) {
            .simple-site-action {
                margin-top: 8px !important;
            }
        }

</style>
<div class="wrapper">
<div class="col-sm-12">
<?php
if($userrow['rmb']>4){
if(strlen($userrow['pwd'])<6 || is_numeric($userrow['pwd']) && strlen($userrow['pwd'])<=10 || $userrow['pwd']===$userrow['qq']){
echo '<div class="alert alert-danger"><span class="btn-sm btn-danger">重要</span>&nbsp;你的密码过于简单，请不要使用较短的纯数字或自己的QQ号当做密码，以免造成资金损失！ <a href="usetmoban.php?mod=user">点此修改密码</a></div>';
}elseif($userrow['user']===$userrow['pwd']){
echo '<div class="alert alert-danger"><span class="btn-sm btn-danger">重要</span>&nbsp;你的用户名与密码相同，极易被黑客破解，请及时修改密码 <a href="usetmoban.php?mod=user">点此修改密码</a></div>';
}
}
?>
</div>
	<div class="col-sm-12">
		<div class="q8-user-dashboard">
			<div class="q8-hero-card">
				<div class="q8-hero-main">
					<img src="<?php echo $faceimg ?>" alt="Avatar" class="q8-hero-avatar">
					<div>
						<div class="q8-hero-kicker">&#27426;&#36814;&#22238;&#26469;</div>
						<h3><?php echo $nickname?></h3>
						<p>UID <?php echo $userrow['zid']?> &middot; &#24403;&#21069;&#20313;&#39069; <b><?php echo $userrow['rmb']?></b> &#20803;</p>
					</div>
				</div>
				<div class="q8-hero-actions">
					<a href="/user/shop.php" class="q8-hero-btn q8-hero-btn-primary"><i class="fa fa-shopping-cart"></i> &#31435;&#21363;&#19979;&#21333;</a>
					<a href="./workorder.php?my=add" class="q8-hero-btn"><i class="fa fa-ticket"></i> &#25552;&#20132;&#24037;&#21333;</a>
					<?php if($conf['qiandao_reward']){?>
					<a href="./qiandao.php" class="q8-hero-btn q8-hero-qiandao"><i class="fa fa-check-square-o"></i> &#27599;&#26085;&#31614;&#21040;</a>
					<?php }?>
					<a href="./recharge.php" class="q8-hero-btn"><i class="fa fa-credit-card"></i> &#20805;&#20540;&#20313;&#39069;</a>
				</div>
			</div>
			<div class="q8-stat-grid">
				<div class="q8-stat-card">
					<i class="fa fa-calendar-check-o"></i>
					<span>&#20170;&#26085;&#35746;&#21333;</span>
					<strong><?php echo $q8_order_today?></strong>
				</div>
				<div class="q8-stat-card">
					<i class="fa fa-list-alt"></i>
					<span>&#32047;&#35745;&#35746;&#21333;</span>
					<strong><?php echo $q8_order_total?></strong>
				</div>
				<div class="q8-stat-card">
					<i class="fa fa-comments-o"></i>
					<span>&#24453;&#22788;&#29702;&#24037;&#21333;</span>
					<strong><?php echo $q8_work_pending?></strong>
				</div>
				<div class="q8-stat-card">
					<i class="fa fa-bell-o"></i>
					<span>&#26410;&#35835;&#28040;&#24687;</span>
					<strong><?php echo $q8_msg_unread?></strong>
				</div>
			</div>
			<div class="q8-dashboard-row">
				<div class="q8-recent-card">
					<div class="q8-card-title">
						<span><i class="fa fa-clock-o"></i> &#26368;&#36817;&#35746;&#21333;</span>
						<a href="./list.php">&#26597;&#30475;&#20840;&#37096;</a>
					</div>
					<?php if($q8_recent_orders){?>
					<div class="q8-order-list">
						<?php foreach($q8_recent_orders as $q8_order){?>
						<a class="q8-order-item" href="./list.php?id=<?php echo intval($q8_order['id'])?>">
							<div class="q8-order-info">
								<strong>#<?php echo intval($q8_order['id'])?> <?php echo $q8_order['name'] ? htmlspecialchars($q8_order['name']) : '&#21830;&#21697;&#35746;&#21333;'?></strong>
								<small><?php echo htmlspecialchars($q8_order['addtime'])?> &middot; <?php echo htmlspecialchars($q8_order['input'])?></small>
							</div>
							<div class="q8-order-meta">
								<?php echo q8_user_order_status_badge($q8_order['status'])?>
								<em><?php echo $q8_order['money']?>&#20803;</em>
							</div>
						</a>
						<?php }?>
					</div>
					<?php }else{?>
					<div class="q8-empty-state">
						<i class="fa fa-shopping-bag"></i>
						<p>&#36824;&#27809;&#26377;&#35746;&#21333;&#35760;&#24405;&#65292;&#21435;&#19979;&#19968;&#21333;&#35797;&#35797;&#21543;</p>
					</div>
					<?php }?>
				</div>
				<div class="q8-help-card">
					<div class="q8-card-title"><span><i class="fa fa-life-ring"></i> &#24120;&#29992;&#26381;&#21153;</span></div>
					<div class="q8-help-grid">
						<a href="./workorder.php?my=add"><i class="fa fa-plus-circle"></i><span>&#25552;&#20132;&#24037;&#21333;</span></a>
						<a href="./workorder2.php"><i class="fa fa-cloud"></i><span>&#32593;&#30424;&#25237;&#35785;</span></a>
						<a href="./record.php"><i class="fa fa-exchange"></i><span>&#25910;&#25903;&#26126;&#32454;</span></a>
						<a href="./message.php"><i class="fa fa-envelope-o"></i><span>&#31449;&#20869;&#28040;&#24687;</span></a>
					</div>
					<div class="q8-service-note">
						<i class="fa fa-info-circle"></i>
						<span>&#21806;&#21518;&#38382;&#39064;&#20248;&#20808;&#25552;&#20132;&#24037;&#21333;&#65292;&#26041;&#20415;&#24102;&#19978;&#35746;&#21333;&#20449;&#24687;&#24182;&#21152;&#24555;&#22788;&#29702;&#12290;</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-12 col-md-12 col-sm-12">
	<div class="panel panel-default simple-panel">
		<div class="panel-heading font-bold simple-panel-header">
			<h3 class="panel-title text-center"><i class="fa fa-th-large"></i>&nbsp;&nbsp;<b>&#24555;&#25463;&#21151;&#33021;&#19982;&#31449;&#28857;&#31649;&#29702;</b></h3>
		</div>
		<div class="panel-body simple-panel-body">
			<div class="row q8-management-layout">
				<div class="col-lg-6 col-md-12 col-sm-12 simple-column q8-cleaned-quick-panel">
					<h4 class="simple-title text-center"><b>&#24120;&#29992;&#24555;&#25463;&#20837;&#21475;</b></h4>
					<div class="simple-function-grid">
						<div class="row">
							<div class="col-xs-4 simple-function-item">
								<a href="<?php echo $userrow['power']>0?'./shop.php?chadan=1':'../?chadan=1';?>" class="simple-function-link">
									<i class="fa fa-search simple-function-icon"></i>
									<div class="simple-function-label">&#33258;&#21161;&#26597;&#21333;</div>
								</a>
							</div>
							<div class="col-xs-4 simple-function-item">
								<a href="./workorder.php" class="simple-function-link">
									<i class="fa fa-check-square-o simple-function-icon"></i>
									<div class="simple-function-label">&#25105;&#30340;&#24037;&#21333;</div>
									<span id="work_count" class="simple-notification-badge<?php echo $q8_work_reply>0?' is-visible':'';?>"><?php echo $q8_work_reply>0?$q8_work_reply:'';?></span>
								</a>
							</div>
							<div class="col-xs-4 simple-function-item">
								<a href="record.php" class="simple-function-link">
									<i class="fa fa-hashtag simple-function-icon"></i>
									<div class="simple-function-label">&#25910;&#25903;&#26126;&#32454;</div>
								</a>
							</div>
						</div>
						<?php if($userrow['power']>0){?>
						<div class="row">
							<div class="col-xs-4 simple-function-item">
								<a href="shoplist.php" class="simple-function-link">
									<i class="fa fa-list-alt simple-function-icon"></i>
									<div class="simple-function-label">&#21830;&#21697;&#31649;&#29702;</div>
								</a>
							</div>
							<div class="col-xs-4 simple-function-item">
								<a href="list.php" class="simple-function-link">
									<i class="fa fa-list simple-function-icon"></i>
									<div class="simple-function-label">&#35746;&#21333;&#35760;&#24405;</div>
								</a>
							</div>
							<div class="col-xs-4 simple-function-item">
								<?php if($q8_can_manage_child_sites){?>
								<a href="sitelist.php" class="simple-function-link">
									<i class="fa fa-sitemap simple-function-icon"></i>
									<div class="simple-function-label">&#20998;&#31449;&#31649;&#29702;</div>
								</a>
								<?php }else{?>
								<a href="login.php?logout" class="simple-function-link simple-danger-link">
									<i class="fa fa-sign-out simple-function-icon"></i>
									<div class="simple-function-label">&#23433;&#20840;&#36864;&#20986;</div>
								</a>
								<?php }?>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-4 simple-function-item">
								<a href="cdomain.php" class="simple-function-link">
									<i class="fa fa-exchange simple-function-icon"></i>
									<div class="simple-function-label">&#22495;&#21517;&#26356;&#25442;</div>
								</a>
							</div>
							<div class="col-xs-4 simple-function-item">
								<a href="ndomain.php" class="simple-function-link">
									<i class="fa fa-plus-circle simple-function-icon"></i>
									<div class="simple-function-label">&#22495;&#21517;&#22686;&#21152;</div>
								</a>
							</div>
							<div class="col-xs-4 simple-function-item">
								<a href="usetmoban.php?mod=site2" class="simple-function-link">
									<i class="fa fa-home simple-function-icon"></i>
									<div class="simple-function-label">&#27169;&#26495;&#35774;&#32622;</div>
								</a>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-4 simple-function-item">
								<a href="../sup" class="simple-function-link">
									<i class="fa fa-cloud-upload simple-function-icon"></i>
									<div class="simple-function-label">&#20379;&#36135;&#31649;&#29702;</div>
								</a>
							</div>
							<div class="col-xs-4 simple-function-item">
								<a href="../toollogs.php" class="simple-function-link">
									<i class="fa fa-list simple-function-icon"></i>
									<div class="simple-function-label">&#19978;&#26550;&#26085;&#24535;</div>
								</a>
							</div>
							<div class="col-xs-4 simple-function-item">
								<a href="usetmoban.php?mod=skimg" class="simple-function-link">
									<i class="fa fa-credit-card simple-function-icon"></i>
									<div class="simple-function-label">&#25552;&#29616;&#35774;&#32622;</div>
								</a>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-4 simple-function-item">
								<a href="sitetask.php" class="simple-function-link">
									<i class="fa fa-tasks simple-function-icon"></i>
									<div class="simple-function-label">&#20998;&#31449;&#20219;&#21153;</div>
								</a>
							</div>
						</div>
						<?php }?>
					</div>
					<?php if($userrow['power']>0 && !empty($q8_admin_notice_context['html'])){?>
					<div class="q8-site-notice-card">
						<div class="q8-site-notice-title">
							<i class="fa fa-bullhorn"></i>
							<span>&#31449;&#28857;&#20844;&#21578;</span>
						</div>
						<div class="q8-site-notice-body">
							<?php echo $q8_admin_notice_context['html']?>
						</div>
					</div>
					<?php }?>
				</div>

				<div class="col-lg-6 col-md-12 col-sm-12 simple-column q8-site-right-panel">
					<h4 class="simple-title text-center"><b>&#25105;&#30340;&#31449;&#28857;&#20449;&#24687;</b></h4>
					<div class="simple-site-section">
					<?php if($userrow['power']>0){?>
						<div class="simple-site-item">
							<div class="simple-site-label">&#36890;&#30693;&#25552;&#37266;</div>
							<div class="simple-site-value"><span>&#20320;&#24403;&#21069;&#26377;<span id="message_count" class="simple-inline-badge<?php if($q8_msg_unread>0) echo ' is-visible';?>"><?php if($q8_msg_unread>0) echo $q8_msg_unread;?></span>&nbsp;&nbsp;&#26465;&#20449;&#24687;&#26410;&#38405;&#35835;</span><a href="./message.php" class="btn btn-xs btn-primary simple-site-action">&#31435;&#21363;&#26597;&#30475;</a></div>
						</div>
						<div class="simple-site-item"><div class="simple-site-label">&#25105;&#30340;&#22495;&#21517;&#9312;</div><div class="simple-site-value"><a href="http://<?php echo $userrow['domain']?>" target="_blank" rel="noreferrer" class="simple-domain-link"><?php echo $userrow['domain']?></a></div></div>
						<?php if($userrow['domain2']){?><div class="simple-site-item"><div class="simple-site-label">&#25105;&#30340;&#22495;&#21517;&#9313;</div><div class="simple-site-value"><a href="http://<?php echo $userrow['domain2']?>" target="_blank" rel="noreferrer" class="simple-domain-link"><?php echo $userrow['domain2']?></a></div></div><?php }?>
					<div class="simple-site-item"><div class="simple-site-label">&#32593;&#31449;&#21517;&#31216;</div><div class="simple-site-value"><span class="site-name"><?php echo $userrow['sitename']?></span><a href="usetmoban.php?mod=site" class="btn btn-xs btn-info simple-site-action">&#31435;&#21363;&#26356;&#25442;</a></div></div>
						<div class="simple-site-item"><div class="simple-site-label">&#20195;&#29702;&#31867;&#22411;</div><div class="simple-site-value"><span class="agent-type"><?php echo ($userrow['power']==2?'<font color=red>&#19987;&#19994;&#29256;</font>':'<font color=red>&#26222;&#21450;&#29256;</font>')?></span><?php if($conf['fenzhan_upgrade']>0 && $userrow['power']==1){?><a href="upsite.php" class="btn btn-danger btn-xs simple-site-action">&#21319;&#32423;&#31449;&#28857;</a><?php }elseif($q8_can_manage_child_sites){?><a href="./sitelist.php" class="btn btn-danger btn-xs simple-site-action">&#19979;&#32423;&#31649;&#29702;</a><?php }?></div></div>
						<?php if($conf['fenzhan_expiry']>0){?><div class="simple-site-item"><div class="simple-site-label">&#21040;&#26399;&#26102;&#38388;</div><div class="simple-site-value"><span class="expiry-time"><?php echo $userrow['endtime']?></span><a href="renew.php" class="btn btn-xs btn-primary simple-site-action">&#31435;&#21363;&#32493;&#26399;</a></div></div><?php }?>
						<div class="simple-site-item"><div class="simple-site-label">&#24403;&#21069;&#29366;&#24577;</div><div class="simple-site-value"><span class="site-status"><?php echo ($conf['fenzhan_expiry']>0 && $userrow['endtime']<$date?'<font color="red">&#24050;&#21040;&#26399;</font>':'<font color="green">&#27491;&#24120;&#36816;&#34892;</font>');?></span></div></div>
					<?php }else{?>
						<div class="simple-site-item"><div class="simple-site-value simple-no-site">&#20320;&#36824;&#26410;&#24320;&#36890;&#20998;&#31449;<a href="regsite.php" class="btn btn-primary btn-sm simple-site-action">&#28857;&#27492;&#24320;&#36890;&#20998;&#31449;</a></div></div>
					<?php }?>
					</div>
					<?php if($userrow['power']>0){?>
					<div class="q8-site-summary-card">
						<div class="q8-site-summary-head">
							<div>
								<strong>&#20998;&#31449;&#36816;&#33829;&#25688;&#35201;</strong>
								<span>&#20170;&#26085;&#19982;&#36817; 7 &#22825;&#25968;&#25454;</span>
							</div>
							<a href="./visitlogs.php" class="q8-site-summary-link">&#35775;&#38382;&#26126;&#32454;</a>
						</div>
						<div class="q8-site-summary-grid">
							<div class="q8-site-summary-item">
								<span>&#20170;&#26085;&#35746;&#21333;</span>
								<strong><?php echo $q8_site_today_orders?></strong>
							</div>
							<div class="q8-site-summary-item">
								<span>&#20170;&#26085;&#25910;&#30410;</span>
								<strong><?php echo $q8_site_today_profit?></strong>
							</div>
							<div class="q8-site-summary-item">
								<span>7 &#22825;&#35746;&#21333;</span>
								<strong><?php echo $q8_site_week_orders?></strong>
							</div>
							<div class="q8-site-summary-item">
								<span>7 &#22825;&#25910;&#30410;</span>
								<strong><?php echo $q8_site_week_profit?></strong>
							</div>
							<div class="q8-site-summary-item">
								<span>&#20170;&#26085;&#35775;&#38382;&#37327;</span>
								<strong><?php echo $q8_site_today_visits?></strong>
							</div>
							<div class="q8-site-summary-item">
								<span>&#20170;&#26085;&#29420;&#31435; IP</span>
								<strong><?php echo $q8_site_today_ips?></strong>
							</div>
							<div class="q8-site-summary-item">
								<span>7 &#22825;&#35775;&#38382;&#37327;</span>
								<strong><?php echo $q8_site_week_visits?></strong>
							</div>
							<div class="q8-site-summary-item">
								<span>7 &#22825;&#29420;&#31435; IP</span>
								<strong><?php echo $q8_site_week_ips?></strong>
							</div>
						</div>
						<div class="q8-site-summary-actions">
							<a href="./list.php"><i class="fa fa-list"></i>&#35746;&#21333;</a>
							<a href="./shoplist.php"><i class="fa fa-shopping-bag"></i>&#21830;&#21697;</a>
							<a href="./price.php"><i class="fa fa-sliders"></i>&#21152;&#20215;&#27169;&#26495;</a>
							<a href="./userlist.php"><i class="fa fa-users"></i>&#29992;&#25143;</a>
							<a href="./record.php"><i class="fa fa-money"></i>&#26126;&#32454;</a>
						</div>
					</div>
					<?php }?>

					<?php if($userrow['power']>0){?>
					<div class="q8-site-chart-card">
						<div class="q8-site-chart-head"><div><strong>&#36817; 7 &#22825;&#20998;&#31449;&#21160;&#24577;</strong><span>&#35746;&#21333;&#25968;&#19982;&#20272;&#31639;&#25910;&#30410;</span></div><a href="./list.php">&#35746;&#21333;&#35760;&#24405;</a></div>
						<div class="q8-site-chart-bars">
							<?php foreach($q8_site_chart_days as $q8_day_item){ $q8_bar_h = max(8, round(($q8_day_item['orders'] / max(1,$q8_site_chart_max)) * 76)); ?>
							<div class="q8-site-chart-day" title="<?php echo $q8_day_item['date']?> <?php echo $q8_day_item['orders']?> &#26465;&#35746;&#21333;"><div class="q8-site-chart-value"><?php echo $q8_day_item['orders']?></div><div class="q8-site-chart-bar-wrap"><div class="q8-site-chart-bar" style="height:<?php echo $q8_bar_h?>px"></div></div><div class="q8-site-chart-label"><?php echo $q8_day_item['label']?></div><div class="q8-site-chart-profit">+<?php echo $q8_day_item['profit']?></div></div>
							<?php }?>
						</div>
					</div>
					<div class="q8-site-chart-card q8-site-visit-chart-card">
						<div class="q8-site-chart-head"><div><strong>&#36817; 7 &#22825;&#35775;&#38382;&#36235;&#21183;</strong><span>&#35775;&#38382;&#37327;&#19982;&#29420;&#31435; IP</span></div><a href="./visitlogs.php">&#35775;&#38382;&#26126;&#32454;</a></div>
						<div class="q8-site-chart-bars">
							<?php foreach($q8_site_visit_chart_days as $q8_visit_day){ $q8_visit_bar_h = max(8, round(($q8_visit_day['visits'] / max(1,$q8_site_visit_chart_max)) * 76)); ?>
							<div class="q8-site-chart-day" title="<?php echo $q8_visit_day['date']?> <?php echo $q8_visit_day['visits']?> &#27425;&#35775;&#38382;">
								<div class="q8-site-chart-value"><?php echo $q8_visit_day['visits']?></div>
								<div class="q8-site-chart-bar-wrap"><div class="q8-site-chart-bar q8-site-chart-bar-visit" style="height:<?php echo $q8_visit_bar_h?>px"></div></div>
								<div class="q8-site-chart-label"><?php echo $q8_visit_day['label']?></div>
								<div class="q8-site-chart-profit"><?php echo $q8_visit_day['ips']?> IP</div>
							</div>
							<?php }?>
						</div>
						<?php if($q8_site_visit_ready){?>
						<div class="q8-site-chart-note">&#25353;&#20998;&#31449;&#29420;&#31435;&#32479;&#35745;&#35775;&#38382;&#35760;&#24405;&#65292;&#20174;&#26412;&#27425;&#21551;&#29992;&#21518;&#24320;&#22987;&#32047;&#31215;&#12290;</div>
						<?php }?>
					</div>
					<?php }?>
				</div>
			</div>
		</div>
	</div>
</div>
	<style>
	/* 主面板样式 */
	/* User dashboard v1 */
	.q8-user-dashboard { margin-bottom: 18px; }
	.q8-hero-card {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 18px;
		padding: 20px;
		border-radius: 10px;
		background: linear-gradient(135deg, #0f62d9 0%, #38a3ff 100%);
		color: #fff;
		box-shadow: 0 10px 28px rgba(22,119,255,.22);
	}
	.q8-hero-main { display: flex; align-items: center; min-width: 0; }
	.q8-hero-avatar { width: 66px; height: 66px; border-radius: 50%; border: 3px solid rgba(255,255,255,.65); margin-right: 14px; background: #fff; object-fit: cover; }
	.q8-hero-kicker { font-size: 13px; opacity: .86; }
	.q8-hero-card h3 { margin: 3px 0 4px; color: #fff; font-size: 22px; font-weight: 800; text-shadow: 0 2px 8px rgba(0,0,0,.18); }
	.q8-hero-card p { margin: 0; opacity: .92; }
	.q8-hero-actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; }
	.q8-hero-btn {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-height: 38px;
		padding: 8px 14px;
		border-radius: 999px;
		color: #0f62d9;
		background: rgba(255,255,255,.95);
		text-decoration: none !important;
		font-weight: 700;
		box-shadow: 0 5px 15px rgba(0,0,0,.12);
	}
	.q8-hero-btn i { margin-right: 6px; }
	.q8-hero-btn:hover { color: #0958d9; background: #fff; }
	.q8-hero-btn-primary { color: #fff; background: #0b3d91; }
	.q8-hero-btn-primary:hover { color: #fff; background: #082f70; }
	.q8-hero-qiandao { color: #8a4b00; background: #fff7e6; }
	.q8-hero-qiandao:hover { color: #6f3b00; background: #fff2cc; }
	.q8-stat-grid { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 12px; margin: 14px 0; }
	.q8-stat-card {
		display: flex;
		align-items: center;
		gap: 12px;
		padding: 15px;
		border-radius: 10px;
		background: #fff;
		border: 1px solid #e8f1ff;
		box-shadow: 0 5px 16px rgba(22,119,255,.08);
	}
	.q8-stat-card i { width: 38px; height: 38px; line-height: 38px; border-radius: 10px; text-align: center; color: #1677ff; background: #eaf4ff; font-size: 18px; }
	.q8-stat-card span { display: block; color: #667085; font-size: 13px; }
	.q8-stat-card strong { display: block; color: #1f2937; font-size: 22px; line-height: 1.1; }
	.q8-dashboard-row { display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(260px, .9fr); gap: 14px; }
	.q8-recent-card, .q8-help-card { background: #fff; border: 1px solid #e8f1ff; border-radius: 10px; padding: 16px; box-shadow: 0 5px 16px rgba(22,119,255,.08); }
	.q8-card-title { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; color: #1f2937; font-weight: 700; }
	.q8-card-title i { color: #1677ff; margin-right: 6px; }
	.q8-card-title a { color: #1677ff; font-size: 13px; text-decoration: none; }
	.q8-order-list { display: flex; flex-direction: column; gap: 9px; }
	.q8-order-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 11px 12px; border: 1px solid #edf4ff; border-radius: 8px; color: inherit; text-decoration: none !important; background: #fbfdff; }
	.q8-order-item:hover { background: #f4f9ff; border-color: #d5e9ff; }
	.q8-order-info { min-width: 0; }
	.q8-order-info strong { display: block; color: #1f2937; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
	.q8-order-info small { display: block; max-width: 100%; color: #667085; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
	.q8-order-meta { flex: 0 0 auto; text-align: right; }
	.q8-order-meta em { display: block; margin-top: 4px; color: #667085; font-style: normal; }
	.q8-status { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 12px; font-weight: 700; }
	.q8-status-wait { color: #9a5b00; background: #fff5d6; }
	.q8-status-doing { color: #0f62d9; background: #eaf4ff; }
	.q8-status-done { color: #027a48; background: #dcfae6; }
	.q8-status-bad { color: #b42318; background: #fee4e2; }
	.q8-status-refund { color: #475467; background: #f2f4f7; }
	.q8-help-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 10px; }
	.q8-help-grid a { display: flex; align-items: center; gap: 8px; min-height: 44px; padding: 10px; border-radius: 8px; background: #f5faff; color: #175cd3; text-decoration: none !important; font-weight: 700; }
	.q8-help-grid a:hover { background: #eaf4ff; }
	.q8-service-note { display: flex; gap: 8px; margin-top: 12px; padding: 11px 12px; border-radius: 8px; color: #5f3b00; background: #fff7e6; line-height: 1.6; }
	.q8-service-note i { margin-top: 3px; }
	.q8-user-announcement {
		display: flex;
		flex-direction: column;
		gap: 10px;
	}
	.q8-ann-row {
		display: flex;
		gap: 10px;
		padding: 11px 12px;
		border: 1px solid #e5efff;
		border-radius: 10px;
		background: #fff;
		box-shadow: 0 3px 10px rgba(22,119,255,.05);
	}
	.q8-ann-icon {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		flex: 0 0 30px;
		width: 30px;
		height: 30px;
		border-radius: 9px;
		color: #fff;
		background: linear-gradient(135deg, #1677ff, #38a3ff);
	}
	.q8-ann-row strong {
		display: block;
		margin-bottom: 3px;
		color: #1f2937;
		font-size: 13px;
	}
	.q8-ann-row p {
		margin: 0 0 4px;
		color: #475467;
		line-height: 1.65;
	}
	.q8-ann-row p:last-child {
		margin-bottom: 0;
	}
	.q8-ann-row a {
		color: #175cd3;
		font-weight: 700;
		text-decoration: none;
	}
	.q8-ann-service .q8-ann-icon { background: linear-gradient(135deg, #1677ff, #38a3ff); }
	.q8-ann-money .q8-ann-icon { background: linear-gradient(135deg, #079455, #32d583); }
	.q8-ann-contact .q8-ann-icon { background: linear-gradient(135deg, #2b74b8, #68a8e8); }
	.q8-ann-site .q8-ann-icon { background: linear-gradient(135deg, #7a5af8, #b692f6); }
	.q8-ann-price .q8-ann-icon { background: linear-gradient(135deg, #dc6803, #fdb022); }
	@media (max-width: 640px) {
		.q8-ann-row {
			padding: 10px;
		}
	}	.q8-empty-state { padding: 24px 12px; text-align: center; color: #667085; background: #fbfdff; border-radius: 8px; }
	.q8-empty-state i { display: block; margin-bottom: 8px; color: #98a2b3; font-size: 28px; }
	@media (max-width: 992px) {
		.q8-hero-card { align-items: flex-start; flex-direction: column; }
		.q8-hero-actions { width: 100%; justify-content: flex-start; }
		.q8-stat-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
		.q8-dashboard-row { grid-template-columns: 1fr; }
	}
	@media (max-width: 640px) {
		.q8-hero-card { padding: 16px; }
		.q8-hero-actions { display: grid; grid-template-columns: 1fr; }
		.q8-hero-btn { width: 100%; }
		.q8-stat-grid { grid-template-columns: 1fr; }
		.q8-order-item { align-items: flex-start; flex-direction: column; }
		.q8-order-meta { text-align: left; }
		.q8-help-grid { grid-template-columns: 1fr; }
	}

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
			display: none;
			position: absolute;
			top: 6px;
			right: 12px;
			min-width: 18px;
			height: 18px;
			padding: 0 5px;
			align-items: center;
			justify-content: center;
			background: #ef4444;
			color: white;
			border-radius: 999px;
			font-size: 11px;
			line-height: 18px;
			text-align: center;
			font-weight: 900;
			box-shadow: 0 6px 14px rgba(239,68,68,.25);
		}
		.simple-notification-badge.is-visible {
			display: inline-flex;
		}
		.simple-inline-badge {
			display:none;
			min-width:18px;
			height:18px;
			padding:0 5px;
			margin-left:6px;
			align-items:center;
			justify-content:center;
			border-radius:999px;
			background:#ef4444;
			color:#fff;
			font-size:11px;
			font-weight:900;
			line-height:18px;
		}
		.simple-inline-badge.is-visible {display:inline-flex;}

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
	.q8-site-summary-card {
		margin-top: 14px;
		padding: 15px;
		border-radius: 10px;
		border: 1px solid #e8f1ff;
		background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
		box-shadow: 0 5px 16px rgba(22,119,255,.08);
	}
	.q8-site-summary-head {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 10px;
		margin-bottom: 12px;
	}
	.q8-site-summary-head strong {
		color: #1f2937;
		font-size: 14px;
	}
	.q8-site-summary-head span {
		color: #667085;
		font-size: 12px;
	}
	.q8-site-summary-link,
	.q8-site-summary-link:link,
	.q8-site-summary-link:visited {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-height: 28px;
		padding: 0 12px;
		border-radius: 999px;
		background: linear-gradient(135deg,#1677ff,#22c4c8);
		color: #fff;
		font-size: 12px;
		font-weight: 800;
		text-decoration: none;
		box-shadow: 0 8px 18px rgba(22,119,255,.16);
	}
	.q8-site-summary-link:hover,
	.q8-site-summary-link:focus {
		color: #fff;
		text-decoration: none;
		filter: brightness(1.03);
	}
	.q8-site-summary-grid {
		display: grid;
		grid-template-columns: repeat(4, minmax(0,1fr));
		gap: 8px;
	}
	.q8-site-summary-item {
		padding: 10px 8px;
		border-radius: 9px;
		background: #f5faff;
		text-align: center;
	}
	.q8-site-summary-item span {
		display: block;
		color: #667085;
		font-size: 12px;
	}
	.q8-site-summary-item strong {
		display: block;
		margin-top: 5px;
		color: #175cd3;
		font-size: 18px;
		line-height: 1.1;
		word-break: break-all;
	}
	.q8-site-summary-actions {
		display: grid;
		grid-template-columns: repeat(4, minmax(0,1fr));
		gap: 8px;
		margin-top: 10px;
	}
	.q8-site-summary-actions a {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 5px;
		min-height: 34px;
		border-radius: 8px;
		color: #175cd3;
		background: #eef6ff;
		font-size: 12px;
		font-weight: 700;
		text-decoration: none;
	}
	.q8-site-summary-actions a:hover {
		background: #e1f0ff;
	}
	@media (max-width: 640px) {
		.q8-site-summary-grid,
		.q8-site-summary-actions {
			grid-template-columns: repeat(2, minmax(0,1fr));
		}
	}	.q8-site-chart-card {
		margin-top: 14px;
		padding: 15px;
		border-radius: 10px;
		border: 1px solid #e8f1ff;
		background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
		box-shadow: 0 5px 16px rgba(22,119,255,.08);
	}
	.q8-site-chart-head {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 10px;
		margin-bottom: 12px;
	}
	.q8-site-chart-head strong {
		display: block;
		color: #1f2937;
		font-size: 14px;
	}
	.q8-site-chart-head span {
		display: block;
		margin-top: 2px;
		color: #667085;
		font-size: 12px;
	}
	.q8-site-chart-head a {
		color: #1677ff;
		font-size: 12px;
		font-weight: 700;
		text-decoration: none;
	}
	.q8-site-chart-bars {
		display: grid;
		grid-template-columns: repeat(7, minmax(0,1fr));
		gap: 8px;
		align-items: end;
	}
	.q8-site-chart-day {
		text-align: center;
		min-width: 0;
	}
	.q8-site-chart-value {
		color: #175cd3;
		font-size: 12px;
		font-weight: 800;
		height: 18px;
	}
	.q8-site-chart-bar-wrap {
		display: flex;
		align-items: flex-end;
		justify-content: center;
		height: 82px;
		border-radius: 8px;
		background: #eef6ff;
		overflow: hidden;
	}
	.q8-site-chart-bar {
		width: 58%;
		min-height: 8px;
		border-radius: 8px 8px 0 0;
		background: linear-gradient(180deg, #38a3ff 0%, #1677ff 100%);
		box-shadow: 0 4px 10px rgba(22,119,255,.22);
	}
	.q8-site-chart-bar-visit {
		background: linear-gradient(180deg, #5eead4 0%, #14b8a6 100%);
		box-shadow: 0 4px 10px rgba(20,184,166,.22);
	}
	.q8-site-chart-label {
		margin-top: 7px;
		color: #667085;
		font-size: 11px;
		white-space: nowrap;
	}
	.q8-site-chart-profit {
		margin-top: 2px;
		color: #079455;
		font-size: 11px;
		font-weight: 700;
		white-space: nowrap;
	}
	.q8-site-chart-note {
		margin-top: 10px;
		padding: 10px 12px;
		border-radius: 8px;
		background: #e9fffb;
		color: #0f4c5c;
		font-size: 12px;
		line-height: 1.6;
	}
	@media (max-width: 640px) {
		.q8-site-summary-head {
			flex-direction: column;
			align-items: flex-start;
		}
		.q8-site-chart-bars {
			gap: 5px;
		}
		.q8-site-chart-profit {
			display: none;
		}
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
	<div class="col-lg-12 col-md-12 col-sm-12 q8-bottom-marquee">
		<div class="panel panel-default">
			<div class="panel-heading font-bold">
				<h3 class="panel-title"><i class="fa fa-volume-up"></i>&nbsp;&nbsp;<b>&#28201;&#39336;&#25552;&#37266;</b></h3>
			</div>
			<div class="panel-body">
				<marquee>
					<b>&#26368;&#20146;&#29233;&#30340;&#31449;&#38271;&#31069;&#24895;&#65306;&#31069;&#21508;&#20301;&#31449;&#38271;&#24184;&#31119;&#23433;&#24247;&#65292;&#24555;&#20048;&#32654;&#28385;&#65292;&#22909;&#20107;&#25104;&#21452;&#65292;&#29983;&#24847;&#20852;&#38534;&#65292;&#22914;&#26524;&#26159;&#21345;&#23494;&#38382;&#39064;&#25110;&#32773;&#36719;&#20214;&#36305;&#36335;&#36141;&#20080;&#30340;&#21345;&#23494;&#37117;&#20250;&#36864;&#27454;&#25110;&#32773;&#37325;&#26032;&#32473;&#20320;&#25442;&#19968;&#27454;&#65292;&#20302;&#20215;&#25552;&#21345;&#65292;&#35802;&#20449;&#36992;&#20195;&#29702;&#65292;&#27426;&#36814;&#26032;&#32769;&#31449;&#38271;&#22238;&#24402;&#21152;&#30431;</b>
				</marquee>
			</div>
		</div>
	</div>


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
<script src="../assets/Agod/layer.js?v=q8vendor1"></script>
<script src="../assets/vendor/clipboard.js/1.7.1/clipboard.min.js?v=q8vendor1"></script>
<script src="../assets/vendor/toastr.js/2.1.4/toastr.min.js?v=q8vendor1"></script>
<script>
function openUserOnlineChat(e) {
	if (e) e.preventDefault();
	var tryOpenChat = function() {
		if (window.$chatwoot && typeof window.$chatwoot.toggle === 'function') {
			window.$chatwoot.toggle('open');
			return true;
		}
		var chatButton = document.querySelector('.woot-widget-bubble, .woot--bubble-holder, .woot-widget-holder');
		if (chatButton) {
			chatButton.click();
			return true;
		}
		return false;
	};
	if (!tryOpenChat()) {
		setTimeout(function() {
			if (!tryOpenChat()) {
				alert("\u5728\u7ebf\u5ba2\u670d\u6b63\u5728\u52a0\u8f7d\uff0c\u8bf7\u7a0d\u540e\u518d\u8bd5");
			}
		}, 1200);
	}
	return false;
}
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
					$("#message_count").text(data.count).addClass('is-visible');
					toastr.info('<a href="message.php">您有<b>'+data.count+'</b>条新消息，请注意查收！</a>', '消息提醒');
				}
				if(data.count2>0){
						$("#work_count").text(data.count2).addClass('is-visible');
					toastr.warning('<a href="workorder.php">您有<b>'+data.count2+'</b>个工单已被管理员回复！</a>', '工单提醒');
					}else{
						$("#work_count").text('').removeClass('is-visible');
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
</script>
<?php include './foot.php';?>
<?php include_once SYSTEM_ROOT.'sakura.php'; loadSakuraEffect(); ?>
</body>
</html>
