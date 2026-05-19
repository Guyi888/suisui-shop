<?php
$is_defend=true;
require '../includes/common.php';
if($islogin2==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");

if(!$conf['qiandao_reward']){
	showmsg('当前站点未开启签到功能',3);
}
$_SESSION['isqiandao']=$userrow['zid'];

$day = date("Y-m-d");
$lastday = date("Y-m-d",strtotime("-1 day"));
if ($row = $DB->getRow("SELECT * FROM pre_qiandao WHERE zid='{$userrow['zid']}' AND date='$day' ORDER BY id DESC LIMIT 1")) {
	$isqiandao = true;
	$continue = $row['continue'];
}else{
	if ($row = $DB->getRow("SELECT * FROM pre_qiandao WHERE zid='{$userrow['zid']}' AND date='$lastday' ORDER BY id DESC LIMIT 1")) {
		$continue = $row['continue'];
	}else{
		$continue = 0;
	}
	$isqiandao = false;
}

$rs=$DB->query("SELECT * FROM pre_qiandao ORDER BY id DESC LIMIT 10");
$qqrow=array();
$qdrow=array();
while($res = $rs->fetch()){
	if(count($qqrow)<5){
		$qqrow[]=$res['qq'];
	}
	$qdrow[]=$res;
}

// Virtual sign-in feed
if($conf['qiandao_virtual']==1){
    $today = date("Ymd");
    $bucket = floor(time() / 600);
    $seed = crc32($conf['sitename'].'|'.$today.'|'.$bucket.'|qiandao');
    mt_srand($seed);

    $virtual_data = array();
    $reward_pool = array('0.01','0.01','0.02','0.02','0.02','0.05','0.05','0.10');
    $base_day = strtotime(date("Y-m-d 00:00:00"));
    $seconds_today = time() - $base_day;
    $used_zid = array();
    $used_time = array();
    $count = 14;

    if($seconds_today < 8 * 3600){
        $latest_clock = max(600, $seconds_today - mt_rand(180, 600));
        $dense_windows = array(
            array(600, 1800),
            array(1800, 3600),
            array(3600, 7200),
            array(7200, max(9000, $latest_clock))
        );
    }else{
        $latest_clock = max(8 * 3600 + 300, $seconds_today - mt_rand(120, 480));
        $dense_windows = array(
            array(120, 900),
            array(900, 1800),
            array(1800, 2700),
            array(2700, 4200),
            array(4200, 7200),
            array(7200, 10800)
        );
    }

    for($i=0; $i<$count; $i++){
        do{
            $virtual_zid = mt_rand(200000, 206523);
        }while(isset($used_zid[$virtual_zid]));
        $used_zid[$virtual_zid] = true;

        if($i < 8){
            $window = $dense_windows[min($i, count($dense_windows)-1)];
            $offset = mt_rand($window[0], min($window[1], max($window[0], $latest_clock - 60)));
        }elseif($i < 11){
            $offset = mt_rand(7200, min(14400, max(7500, $latest_clock - 60)));
        }else{
            $offset = mt_rand(14400, min(28800, max(14700, $latest_clock - 60)));
        }

        $virtual_clock = max(600, $latest_clock - $offset);
        $virtual_clock = (int)(floor($virtual_clock / 60) * 60);
        while(isset($used_time[$virtual_clock])){
            $virtual_clock = max(600, $virtual_clock - 60);
        }
        $used_time[$virtual_clock] = true;

        $virtual_time = date("Y-m-d H:i:s", $base_day + $virtual_clock);
        $virtual_reward = $reward_pool[mt_rand(0, count($reward_pool)-1)];
        $virtual_continue = mt_rand(1, 12);

        $virtual_data[] = array(
            'zid' => $virtual_zid,
            'qq' => 10000 + (($virtual_zid * 37 + 97) % 900000000),
            'time' => $virtual_time,
            'reward' => $virtual_reward,
            'continue' => $virtual_continue
        );
    }

    $all_data = array_merge($qdrow, $virtual_data);
    usort($all_data, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });
    $qdrow = array_slice($all_data, 0, 10);

    $qqrow = array();
    foreach($qdrow as $row){
        if(count($qqrow) >= 5) break;
        if(!empty($row['qq']) && is_numeric($row['qq'])){
            $qqrow[] = $row['qq'];
        }elseif(!empty($row['zid']) && is_numeric($row['zid'])){
            $qqrow[] = 10000 + (($row['zid'] * 37 + 97) % 900000000);
        }
    }

    mt_srand();
}

$title = '每日签到';
$stats_seed = crc32($conf['sitename'].'|'.date('Ymd').'|qiandao-summary');
mt_srand($stats_seed);
$base_day = strtotime(date("Y-m-d 00:00:00"));
$hour_now = (int)date('G');
$start_hour = 8;
$active_hours = 16;
$elapsed = max(0, min($active_hours, ($hour_now + (date('i') / 60)) - $start_hour));
$progress = $elapsed / $active_hours;
$progress = max(0.03, min(0.98, $progress));

$summary_yesterday = 920 + mt_rand(-80, 120);
$curve = pow($progress, 0.88);
$summary_today = (int)round($summary_yesterday * $curve) + mt_rand(-18, 24);
if ($hour_now < 8) {
    $summary_today = mt_rand(8, 40);
}
$summary_today = max(12, min($summary_today, $summary_yesterday - 20));
mt_srand();

$summary_today_text = (string)$summary_today;
$summary_yesterday_text = (string)$summary_yesterday;
$summary_total_text = '9999+';

$month_start = date('Y-m-01');
$month_end = date('Y-m-t');
$month_days = (int)date('t');
$month_first_weekday = (int)date('N', strtotime($month_start));
$month_today_day = (int)date('j');
$month_signed = array();
$month_query = $DB->query("SELECT date FROM pre_qiandao WHERE zid='{$userrow['zid']}' AND date>='{$month_start}' AND date<='{$month_end}' ORDER BY date ASC");
while($month_row = $month_query->fetch()){
    $month_signed[$month_row['date']] = true;
}
$month_signed_count = count($month_signed);
$month_remaining = max(0, $month_days - $month_today_day);
$month_keep = max(0, $month_days - $month_signed_count);
include 'head.php';
?>

<!-- 引入淡蓝色主题 -->
<link rel="stylesheet" href="./public/css/blue_theme.css">

<?php
$url = 'http://'.$userrow['domain'].'/';
if($conf['fanghong_api']>0){
	$turl = fanghongdwz($url);
	if(strpos($turl,'/')===false){
		$turl = $url;
	}
}else{
	$turl = $url;
}
?>
<style id="q8-qiandao-clean">
.q8-qiandao-page{max-width:1100px;margin:0 auto;padding:0 12px 24px}
.q8-qiandao-layout{display:grid;grid-template-columns:minmax(0,1.06fr) minmax(320px,.94fr);gap:18px;align-items:start}
.q8-qiandao-card,.q8-qiandao-side{border:1px solid #dceaff;border-radius:20px;background:rgba(255,255,255,.98);box-shadow:0 16px 34px rgba(22,119,255,.10);overflow:hidden}
.q8-qiandao-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 20px;background:linear-gradient(135deg,#1677ff,#21c4c8);color:#fff}
.q8-qiandao-head h3{margin:0;color:#fff;font-size:22px;font-weight:900;display:flex;align-items:center;gap:10px}
.q8-qiandao-head p{margin:4px 0 0;color:rgba(255,255,255,.84);font-size:13px}
.q8-qiandao-badge{display:inline-flex;align-items:center;justify-content:center;min-width:96px;height:34px;padding:0 14px;border-radius:999px;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.24);font-size:13px;font-weight:800;color:#fff}
.q8-qiandao-hero{position:relative;min-height:252px;background:linear-gradient(180deg,rgba(12,86,184,.14),rgba(12,86,184,.06)),url('../assets/img/qiandao.jpg') center/cover no-repeat}
.q8-qiandao-hero:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(6,18,37,.10) 0%,rgba(6,18,37,.56) 100%)}
.q8-qiandao-weather{position:absolute;top:16px;left:16px;z-index:1;padding:6px 10px;border-radius:14px;background:rgba(255,255,255,.14);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px)}
.q8-qiandao-weather iframe{display:block}
.q8-qiandao-hero-main{position:absolute;left:18px;right:18px;bottom:18px;z-index:1;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.q8-qiandao-hero-stat{padding:16px 16px 14px;border-radius:16px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.24);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);color:#fff}
.q8-qiandao-hero-stat span{display:block;color:rgba(255,255,255,.82);font-size:12px;line-height:1.4}
.q8-qiandao-hero-stat strong{display:block;margin-top:7px;color:#fff;font-size:26px;line-height:1.2;font-weight:900}.q8-qiandao-hero-stat strong .q8-qiandao-reward-amount{font-size:34px;letter-spacing:0;display:inline-block;vertical-align:-1px}
.q8-qiandao-body{padding:18px}
.q8-qiandao-actions{display:grid;grid-template-columns:minmax(0,1fr) 190px;gap:10px;align-items:center}
.q8-qiandao-btn{height:46px;display:inline-flex;align-items:center;justify-content:center;gap:8px;border:0;border-radius:14px;color:#fff!important;font-size:15px;font-weight:900;line-height:1;text-decoration:none!important;box-shadow:0 12px 24px rgba(22,119,255,.18);transition:transform .18s ease,box-shadow .18s ease,filter .18s ease}
.q8-qiandao-primary{background:linear-gradient(135deg,#1677ff,#24b7ff)}
.q8-qiandao-share{background:linear-gradient(135deg,#4b5cff,#6f7eff)}
.q8-qiandao-btn:hover,.q8-qiandao-btn:focus{color:#fff!important;filter:brightness(1.03);transform:translateY(-1px)}
.q8-qiandao-btn:active{transform:translateY(1px) scale(.99)}
.q8-qiandao-tips{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-top:14px}
.q8-qiandao-tip{padding:14px;border:1px solid #e6f0ff;border-radius:16px;background:linear-gradient(180deg,#fff 0%,#f7fbff 100%)}
.q8-qiandao-tip b{display:block;color:#1f2d3d;font-size:14px;font-weight:900;margin-bottom:6px}
.q8-qiandao-tip span{display:block;color:#6b7a90;font-size:12px;line-height:1.7}.q8-qiandao-extra{display:grid;grid-template-columns:1.18fr .82fr;gap:12px;margin-top:14px}.q8-qiandao-extra-card{padding:16px;border:1px solid #e6f0ff;border-radius:18px;background:linear-gradient(180deg,#fff 0%,#f7fbff 100%);box-shadow:0 10px 22px rgba(22,119,255,.05)}.q8-qiandao-extra-card h5{margin:0 0 12px;color:#163b70;font-size:15px;font-weight:900;display:flex;align-items:center;gap:8px}.q8-qiandao-calendar-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}.q8-qiandao-calendar-head span{color:#163b70;font-size:14px;font-weight:900}.q8-qiandao-calendar-head em{font-style:normal;color:#1677ff;font-size:12px;font-weight:800}.q8-qiandao-calendar-week,.q8-qiandao-calendar-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:8px}.q8-qiandao-calendar-week{margin-bottom:8px}.q8-qiandao-calendar-week span{height:28px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:#eef6ff;color:#6b7a90;font-size:12px;font-weight:800}.q8-qiandao-day{position:relative;height:42px;display:flex;align-items:center;justify-content:center;border-radius:12px;border:1px solid #e4eefc;background:#fff;color:#506176;font-size:13px;font-weight:800}.q8-qiandao-day.is-empty{background:transparent;border-color:transparent}.q8-qiandao-day.is-future{color:#a7b5c8;background:#f9fbff}.q8-qiandao-day.is-signed{background:linear-gradient(135deg,#1677ff,#21c4c8);border-color:transparent;color:#fff;box-shadow:0 10px 22px rgba(22,119,255,.16)}.q8-qiandao-day.is-today{box-shadow:0 0 0 2px rgba(22,119,255,.18) inset}.q8-qiandao-day.is-signed:after{content:'\2713';position:absolute;right:5px;top:3px;font-size:10px;font-weight:900;color:#fff}.q8-qiandao-month-stats{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-bottom:12px}.q8-qiandao-month-stat{padding:14px 12px;border-radius:14px;background:#fff;border:1px solid #e4eefc}.q8-qiandao-month-stat b{display:block;color:#1677ff;font-size:22px;font-weight:900;line-height:1.2}.q8-qiandao-month-stat span{display:block;margin-top:6px;color:#6b7a90;font-size:12px}.q8-qiandao-month-note{margin:0;padding:0;list-style:none;display:grid;gap:10px}.q8-qiandao-month-note li{position:relative;padding-left:18px;color:#5f6f86;font-size:12px;line-height:1.75}.q8-qiandao-month-note li:before{content:'';position:absolute;left:0;top:8px;width:8px;height:8px;border-radius:50%;background:linear-gradient(135deg,#1677ff,#21c4c8)}
.q8-qiandao-side-head{padding:18px 20px;border-bottom:1px solid #edf4ff;background:linear-gradient(180deg,#fff 0%,#f8fbff 100%)}
.q8-qiandao-side-head h4{margin:0;color:#163b70;font-size:18px;font-weight:900;display:flex;align-items:center;gap:10px}
.q8-qiandao-side-head p{margin:6px 0 0;color:#7a8aa0;font-size:12px}
.q8-qiandao-avatars{display:flex;flex-wrap:wrap;gap:10px;padding:16px 18px 10px}
.q8-qiandao-avatars img{width:56px;height:56px;border-radius:18px;border:3px solid #fff;box-shadow:0 10px 22px rgba(22,119,255,.16);object-fit:cover}
.q8-qiandao-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;padding:0 18px 16px}
.q8-qiandao-summary-item{padding:13px 10px;border-radius:14px;background:#f7fbff;border:1px solid #e4eefc;text-align:center}
.q8-qiandao-summary-item b{display:block;color:#1677ff;font-size:19px;font-weight:900;line-height:1.2}
.q8-qiandao-summary-item span{display:block;margin-top:5px;color:#6b7a90;font-size:12px}
.q8-qiandao-records{padding:0 18px 18px;display:grid;gap:10px}
.q8-qiandao-record{padding:14px;border-radius:16px;border:1px solid #e7eefb;background:#fff;box-shadow:0 10px 20px rgba(22,119,255,.06)}
.q8-qiandao-record-top{display:flex;align-items:center;justify-content:space-between;gap:10px}
.q8-qiandao-record-id{color:#1f2d3d;font-size:14px;font-weight:900}
.q8-qiandao-record-time{color:#7a8aa0;font-size:12px}
.q8-qiandao-record-bottom{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:10px}
.q8-qiandao-continue{display:inline-flex;align-items:center;justify-content:center;height:28px;padding:0 12px;border-radius:999px;background:#eef6ff;color:#1677ff;font-size:12px;font-weight:800}
.q8-qiandao-reward{color:#10b981;font-size:15px;font-weight:900}
.q8-share-modal .modal-dialog{width:min(92vw,640px);margin:6vh auto}
.q8-share-modal .modal-content{border:1px solid #dceaff;border-radius:20px;overflow:hidden;box-shadow:0 22px 48px rgba(22,119,255,.18)}
.q8-share-head{padding:18px 20px;background:linear-gradient(135deg,#1677ff,#21c4c8);color:#fff;position:relative}
.q8-share-head h4{margin:0;color:#fff;font-size:22px;font-weight:900}
.q8-share-head p{margin:6px 0 0;color:rgba(255,255,255,.86);font-size:13px}
.q8-share-head .close{position:absolute;top:14px;right:16px;color:#fff;opacity:1;text-shadow:none}
.q8-share-head .close:hover,.q8-share-head .close:focus{color:#fff;opacity:1}
.q8-share-body{padding:18px;background:#f7fbff}
.q8-share-box{border:1px solid #dceaff;border-radius:16px;background:#fff;padding:14px}
.q8-share-box label{display:block;margin:0 0 8px;color:#163b70;font-size:14px;font-weight:900}
.q8-share-box textarea{min-height:190px!important;border-radius:12px!important;resize:vertical}
.q8-share-note{margin-top:12px;border:1px solid #dceaff;border-radius:14px;background:#fff;padding:13px 14px;color:#5b6b81;font-size:13px;line-height:1.7}
.q8-share-footer{display:flex;gap:10px;padding:0 18px 18px;background:#f7fbff}
.q8-share-copy{flex:1 1 auto;height:44px;border:0;border-radius:14px;background:linear-gradient(135deg,#1677ff,#24b7ff);color:#fff!important;font-size:15px;font-weight:900;box-shadow:0 12px 24px rgba(22,119,255,.18)}
.q8-share-close{flex:0 0 150px;height:44px;border:0;border-radius:14px;background:#e9f2ff;color:#1677ff;font-size:15px;font-weight:900}
@media(max-width:992px){.q8-qiandao-layout{grid-template-columns:1fr}.q8-qiandao-card,.q8-qiandao-side{max-width:760px;margin:0 auto;width:100%}}
@media(max-width:768px){.q8-qiandao-page{padding:0 8px 18px}.q8-qiandao-head{padding:16px}.q8-qiandao-head h3{font-size:19px}.q8-qiandao-head p{font-size:12px}.q8-qiandao-badge{min-width:84px;height:30px;font-size:12px;padding:0 12px}.q8-qiandao-hero{min-height:220px}.q8-qiandao-weather{left:12px;top:12px;right:12px;padding:5px 8px;overflow:hidden}.q8-qiandao-weather iframe{max-width:100%}.q8-qiandao-hero-main{left:12px;right:12px;bottom:12px;grid-template-columns:1fr}.q8-qiandao-hero-stat strong{font-size:22px}.q8-qiandao-hero-stat strong .q8-qiandao-reward-amount{font-size:28px}.q8-qiandao-body{padding:14px}.q8-qiandao-actions{grid-template-columns:1fr}.q8-qiandao-tips,.q8-qiandao-summary,.q8-qiandao-extra{grid-template-columns:1fr}.q8-qiandao-calendar-week,.q8-qiandao-calendar-grid{gap:6px}.q8-qiandao-day{height:38px;font-size:12px}.q8-qiandao-month-stats{grid-template-columns:1fr 1fr}.q8-share-modal .modal-dialog{width:94vw;margin:3vh auto}.q8-share-footer{flex-direction:column}.q8-share-close,.q8-share-copy{flex:1 1 auto;width:100%}}
</style>

<div class="wrapper">
	<div class="q8-qiandao-page">
		<div class="q8-qiandao-layout">
			<div class="q8-qiandao-card">
				<div class="q8-qiandao-head">
					<div>
						<h3><i class="fa fa-check-square-o"></i> &#27599;&#26085;&#31614;&#21040;</h3>
						<p>&#22362;&#25345;&#31614;&#21040;&#39046;&#22870;&#21169;&#65292;&#25226;&#27599;&#19968;&#22825;&#30340;&#27963;&#36339;&#37117;&#35760;&#24405;&#19979;&#26469;&#12290;</p>
					</div>
					<div class="q8-qiandao-badge"><?php echo $isqiandao==true?'&#24050;&#31614;&#21040;':'&#26410;&#31614;&#21040;';?></div>
				</div>
				<div class="q8-qiandao-hero">
					<div class="q8-qiandao-weather">
						<iframe width="300" scrolling="no" height="60" frameborder="0" allowtransparency="true" src="//i.tianqi.com/index.php?c=code&id=12&icon=1&num=2&site=12"></iframe>
					</div>
					<div class="q8-qiandao-hero-main">
						<div class="q8-qiandao-hero-stat">
							<span>&#32047;&#35745;&#31614;&#21040;&#22870;&#21169;</span>
							<strong><span id="rewardcount" class="q8-qiandao-reward-amount">0.00</span> &#20803;</strong>
						</div>
						<div class="q8-qiandao-hero-stat">
							<span>&#36830;&#32493;&#31614;&#21040;</span>
							<strong><?php echo $continue?> &#22825;</strong>
						</div>
					</div>
				</div>
				<div class="q8-qiandao-body">
					<div class="q8-qiandao-actions">
						<button type="button" class="q8-qiandao-btn q8-qiandao-primary" id="qiandao"><i class="fa fa-check-square"></i><span><?php echo $isqiandao==true?'&#20170;&#22825;&#24050;&#31614;&#21040;':'&#31435;&#21363;&#31614;&#21040;';?></span></button>
						<a href="#fxhy" data-toggle="modal" title="&#28857;&#20987;&#20998;&#20139;&#26412;&#31449;" class="q8-qiandao-btn q8-qiandao-share"><i class="fa fa-share-alt"></i><span>&#20998;&#20139;&#32473;&#22909;&#21451;</span></a>
					</div>
					<div class="q8-qiandao-tips">
						<div class="q8-qiandao-tip">
							<b>&#31614;&#21040;&#25552;&#37266;</b>
							<span>&#27599;&#22825;&#26469;&#19968;&#27425;&#65292;&#22870;&#21169;&#20250;&#33258;&#21160;&#35745;&#20837;&#20320;&#30340;&#20313;&#39069;&#12290;</span>
						</div>
						<div class="q8-qiandao-tip">
							<b>&#36830;&#32493;&#35760;&#24405;</b>
							<span>&#36319;&#19978;&#33410;&#22863;&#23601;&#20250;&#19968;&#30452;&#32047;&#35745;&#65292;&#26029;&#31614;&#21518;&#20250;&#37325;&#26032;&#24320;&#22987;&#12290;</span>
						</div>
						<div class="q8-qiandao-tip">
							<b>&#20998;&#20139;&#31119;&#21033;</b>
							<span>&#25226;&#20320;&#30340;&#20998;&#31449;&#20998;&#20139;&#32473;&#26379;&#21451;&#65292;&#36824;&#26377;&#26426;&#20250;&#24102;&#26469;&#39069;&#22806;&#25910;&#30410;&#12290;</span>
						</div>
					</div>
					<div class="q8-qiandao-extra">
						<div class="q8-qiandao-extra-card">
							<h5><i class="fa fa-calendar"></i> &#26412;&#26376;&#31614;&#21040;&#26085;&#21382;</h5>
							<div class="q8-qiandao-calendar-head">
								<span><?php echo date('Y') . '&#24180;' . date('m') . '&#26376;';?></span>
								<em>&#24050;&#31614; <?php echo $month_signed_count;?> &#22825;</em>
							</div>
							<div class="q8-qiandao-calendar-week">
								<span>&#19968;</span><span>&#20108;</span><span>&#19977;</span><span>&#22235;</span><span>&#20116;</span><span>&#20845;</span><span>&#26085;</span>
							</div>
							<div class="q8-qiandao-calendar-grid">
<?php
for($i=1; $i<$month_first_weekday; $i++){
	echo '<span class="q8-qiandao-day is-empty"></span>';
}
for($d=1; $d<=$month_days; $d++){
	$date_str = date('Y-m-') . str_pad($d, 2, '0', STR_PAD_LEFT);
	$cls = 'q8-qiandao-day';
	if(isset($month_signed[$date_str])) $cls .= ' is-signed';
	if($d == $month_today_day) $cls .= ' is-today';
	if($d > $month_today_day) $cls .= ' is-future';
	echo '<span class="'.$cls.'">'.$d.'</span>';
}
?>
							</div>
						</div>
						<div class="q8-qiandao-extra-card">
							<h5><i class="fa fa-bar-chart"></i> &#26412;&#26376;&#31614;&#21040;&#27010;&#35272;</h5>
							<div class="q8-qiandao-month-stats">
								<div class="q8-qiandao-month-stat">
									<b><?php echo $month_signed_count;?></b>
									<span>&#26412;&#26376;&#24050;&#31614;</span>
								</div>
								<div class="q8-qiandao-month-stat">
									<b><?php echo $continue;?></b>
									<span>&#24403;&#21069;&#36830;&#31614;</span>
								</div>
								<div class="q8-qiandao-month-stat">
									<b><?php echo $month_remaining;?></b>
									<span>&#26412;&#26376;&#21097;&#20313;</span>
								</div>
								<div class="q8-qiandao-month-stat">
									<b><?php echo $month_keep;?></b>
									<span>&#21487;&#34917;&#31614;&#22825;</span>
								</div>
							</div>
							<ul class="q8-qiandao-month-note">
								<li>&#26085;&#21382;&#24050;&#28857;&#20142;&#30340;&#26085;&#26399;&#34920;&#31034;&#24403;&#22825;&#24050;&#25104;&#21151;&#31614;&#21040;&#12290;</li>
								<li>&#20170;&#22825;&#26410;&#28857;&#20142;&#26102;&#65292;&#28857;&#20987;&#39030;&#37096;&#25353;&#38062;&#23601;&#21487;&#23436;&#25104;&#24403;&#26085;&#31614;&#21040;&#12290;</li>
								<li>&#26412;&#26376;&#27010;&#35272;&#21487;&#20197;&#30452;&#25509;&#30475;&#21040;&#20320;&#30340;&#36830;&#31614;&#24805;&#20917;&#21644;&#21097;&#20313;&#22825;&#25968;&#12290;</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="q8-qiandao-side">
				<div class="q8-qiandao-side-head">
					<h4><i class="fa fa-trophy"></i> &#26368;&#26032;&#31614;&#21040;&#27036;</h4>
					<p>&#36825;&#37324;&#23637;&#31034;&#24403;&#21069;&#26368;&#26032;&#30340;&#31614;&#21040;&#35760;&#24405;&#21644;&#27963;&#36291;&#27675;&#22260;&#12290;</p>
				</div>
				<div class="q8-qiandao-avatars">
<?php
foreach($qqrow as $row){
	echo $row?'<img src="https://q4.qlogo.cn/headimg_dl?dst_uin='.$row.'&spec=100" class="img-rounded img-thumbnail">':'<img src="../assets/img/user.png" class="img-rounded img-thumbnail">';
}
?>
				</div>
				<div class="q8-qiandao-summary">
					<div class="q8-qiandao-summary-item"><b id="count1"><?php echo $summary_today_text;?></b><span>&#20170;&#26085;&#31614;&#21040;</span></div>
					<div class="q8-qiandao-summary-item"><b id="count2"><?php echo $summary_yesterday_text;?></b><span>&#26152;&#26085;&#31614;&#21040;</span></div>
					<div class="q8-qiandao-summary-item"><b id="count3"><?php echo $summary_total_text;?></b><span>&#32047;&#35745;&#31614;&#21040;</span></div>
				</div>
				<div class="q8-qiandao-records">
<?php
foreach($qdrow as $row){
	echo '<div class="q8-qiandao-record">
		<div class="q8-qiandao-record-top">
			<div class="q8-qiandao-record-id"><i class="fa fa-user-circle-o"></i> ZID:'.$row['zid'].'</div>
			<div class="q8-qiandao-record-time">'.date("H:i",strtotime($row['time'])).'</div>
		</div>
		<div class="q8-qiandao-record-bottom">
			<div class="q8-qiandao-continue">&#36830;&#32493;'.$row['continue'].'&#22825;</div>
			<div class="q8-qiandao-reward">+'.$row['reward'].' &#20803;</div>
		</div>
	</div>';
}
?>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- share modal start --><!-- share modal start -->
	        <div class="modal fade q8-share-modal" align="left" id="fxhy" tabindex="-1" role="dialog" aria-labelledby="fxhyLabel" aria-hidden="true">
	          <div class="modal-dialog">
	            <div class="modal-content">
	              <div class="q8-share-head">
	                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-times-circle"></i></span><span class="sr-only">Close</span></button>
	                <h4 id="fxhyLabel">&#23558;&#32593;&#31449;&#20998;&#20139;&#32473;&#22909;&#21451;</h4>
	                <p>&#19968;&#38190;&#22797;&#21046;&#20998;&#20139;&#35821;&#65292;&#35753;&#20320;&#30340;&#20998;&#31449;&#25512;&#24191;&#26356;&#30465;&#24515;&#12290;</p>
	              </div>
	              <div class="q8-share-body">
	                <div class="q8-share-box">
	                  <label>&#24191;&#21578;&#35821;</label>
	                  <textarea id="fxggc" class="form-control" rows="5" cols="30" readonly="" unselectable="on">
&#12304;&#20840;&#32593;&#26368;&#20840;&#31185;&#25216;&#32593;&#12305; <?php echo $conf['sitename'] ?>

      &#20840;&#25163;&#28216;/&#31471;&#28216;&#31185;&#25216;&#36741;&#21161;
       &#20195;&#21047;&#19994;&#21153;&#8212;&#28216;&#25103;&#23567;&#21495;
      &#25307;&#25910;&#20195;&#29702;&#8212;&#8212;&#26080;&#38656;&#25104;&#26412;
&#20840;&#32593;&#26368;&#20302;&#20215;&#8212;&#8212;&#20840;&#32593;&#26368;&#40784;&#20840;
&#31561;&#24744;&#26469;&#36186;&#38065;&#12304;&#28857;&#20987;&#12305;&#24555;&#36895;&#24320;&#36890;
&#32593;&#22336;:<?php
echo ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
?>

&#28385;&#21313;&#20803;&#21363;&#21487;&#25552;&#29616;&#8212;&#26377;&#19987;&#23646;&#23458;&#26381;&#21806;&#21518;
&#21345;&#23494;&#38382;&#39064;&#21253;&#36864;&#25442;&#8212;&#19987;&#23646;&#31449;&#28857;&#32676;&#36890;&#30693;
	                  </textarea>
	                </div>
	                <div class="q8-share-note">&#25226;&#20320;&#30340;&#20998;&#31449;&#20998;&#20139;&#32473;&#22909;&#21451;&#65292;&#26377;&#26426;&#20250;&#33719;&#21462; 100 &#20803;&#20313;&#39069;&#25110;&#38543;&#26426;&#21830;&#21697;&#12290;</div>
	              </div>
	              <div class="q8-share-footer">
	                <button data-clipboard-target="#fxggc" class="q8-share-copy fenx">&#28857;&#20987;&#19968;&#38190;&#22797;&#21046;&#20998;&#20139;&#35821;</button>
	                <button type="button" class="q8-share-close" data-dismiss="modal">&#20851;&#38381;</button>
	              </div>
	            </div>
	          </div>
	        </div>
	        <!-- share modal end -->
<?php include './foot.php';?>
<script src="<?php echo $cdnpublic?>clipboard.js/1.7.1/clipboard.min.js"></script>
<script>
var clipboard = new Clipboard('.fenx');
clipboard.on('success', function(e) {
	layer.msg("复制成功,快去分享给朋友让你多一份收入吧！", {icon: 1});
});
clipboard.on('error', function(e) {
     layer.msg("复制失败，请长按链接后手动复制", {icon: 2});
});
$(document).ready(function(){
	$("#qiandao").click(function(){
		$.ajax({
		 type: "get",
		 url: "ajax_user.php?act=qiandao",
		 dataType: "json",
		 success: function(data){
			if(data.code == 0){
				layer.alert(data.msg,{icon:6},function(){
					window.location.reload();
				})
			}else{
				layer.alert(data.msg,{icon:5})
			}
		 },
		 error: function(){
			layer.alert('签到失败，请稍后刷新重试！');
		 }
	   });
	});
			$.ajax({
			type : "GET",
			url : "ajax_user.php?act=qdcount",
			dataType : 'json',
			async: true,
			success : function(data) {
				$('#rewardcount').html(data.rewardcount);
			}
		});
})
</script>
</body>
</html>