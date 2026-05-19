<?php
/*
 岁岁云商城 XHY-01 模板
 维护：岁岁 @qqfaka
*/
if(!defined('IN_CRONLITE'))exit();
$chdsn_cn_zuocew = $conf['chdsn_cn_zuocew']?$conf['chdsn_cn_zuocew']:'/template/XHY-01/bg-fallback.jpg';
$q8XhyFenzhanPricing = q8_get_fenzhan_price_context($conf, !empty($is_fenzhan), isset($siterow) ? $siterow : array());
$q8XhyFenzhanNormalPriceText = q8_format_currency_amount($q8XhyFenzhanPricing['normal_price']);
$q8XhyFenzhanProfessionalPriceText = q8_format_currency_amount($q8XhyFenzhanPricing['professional_price']);
$q8XhyWithdrawMinText = q8_format_currency_amount(isset($conf['tixian_min']) ? $conf['tixian_min'] : 0);
?>
<!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
    	<title><?php echo $conf['sitename'] ?> - <?php echo $conf['title'] ?></title>
    	<meta name="keywords" content="<?php echo $conf['keywords'] ?>">
    	<meta name="description" content="<?php echo $conf['description'] ?>">
		<?php if(!empty($conf['favicon'])) { ?>
		<link rel="icon" href="<?php echo $conf['favicon'] ?>" type="image/x-icon" />
		<link rel="shortcut icon" href="<?php echo $conf['favicon'] ?>" type="image/x-icon" />
		<?php } else { ?>
		<link rel="icon" href="assets/img/favicon/favicon.ico" type="image/x-icon" />
		<link rel="shortcut icon" href="assets/img/favicon/favicon.ico" type="image/x-icon" />
		<?php } ?>
		<link href="<?php echo $cdnpublic?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
    	<link href="<?php echo $cdnpublic?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    	<link rel="stylesheet" href="<?php echo $cdnserver?>assets/simple/css/oneui.css">
		<link rel="stylesheet" href="<?php echo $cdnserver?>assets/css/common.css?ver=<?php echo VERSION ?>">
		<script src="/assets/vendor/jquery/3.5.1/jquery.min.js?v=suisuivendor1"></script>
		<script src="/assets/vendor/jquery-cookie/1.4.1/jquery.cookie.min.js?v=suisuivendor1"></script>
		<script src="<?php echo $cdnpublic?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<script src="<?php echo $cdnpublic?>modernizr/2.8.3/modernizr.min.js"></script>
		<script src="/assets/Agod/layer.js?v=suisuilocal1"></script>
		<script>
		var isModal = <?php echo empty($conf['anounce']) ? 'false' : 'true'; ?> ;
		var modalShowType = <?php echo isset($conf['modal_show_type']) ? intval($conf['modal_show_type']) : 0; ?>;
		var homepage = true;
		var hashsalt = <?php echo $addsalt_js ?> ;
		</script>
		<script src="<?php echo $cdnserver ?>assets/js/main.js?ver=<?php echo VERSION ?>-suisuiorder02"></script>
		<!--[if lt IE 9]>
	    <script src="<?php echo $cdnpublic?>html5shiv/3.7.3/html5shiv.min.js"></script>
	    <script src="<?php echo $cdnpublic?>respond.js/1.4.2/respond.min.js"></script>
	    <![endif]-->
    </head>
<style type="text/css">
	.form-control {color: #646464;border: 1px solid #f8f8f8;border-radius: 3px;-webkit-box-shadow: none;box-shadow: none;-webkit-transition: all 0.15s ease-out;transition: all 0.15s ease-out;}
	.block{margin-bottom:10px;background-color:#fff;-webkit-box-shadow:0 2px 17px 2px rgb(222,223,241);box-shadow:0 2px 17px 2px rgb(222,223,241);font-weight:400}
	ul.ft-link{margin:0;padding:0}
	ul.ft-link li{border-right:1px solid #E6E7EC;display:inline-block;line-height:30px;margin:8px 0;text-align:center;width:24%}
	ul.ft-link li a{color:#74829c;text-transform:uppercase;font-size:12px}
	ul.ft-link li a:hover,ul.ft-link li.active a{color:#58c9f3}
	ul.ft-link li:last-child{border-right:none}
	ul.ft-link li a i{display:block}
	.well {min-height: 20px;padding: 19px;margin-bottom: 15px;background-color: #f9f9f9;border: 1px solid #e3e3e3;border-radius: 4px;-webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);}
	.input-group-addon {color: #646464;background-color: #f9f9f9;border-color: #f9f9f9;border-radius: 3px;}
	.panel-primary {border-color: #ffffff;}
	::-webkit-scrollbar-thumb {-webkit-box-shadow: inset 1px 1px 0 rgba(0,0,0,.1), inset 0 -1px 0 rgba(0,0,0,.07);background-clip: padding-box;background-color: #1bc74c;min-height: 40px;padding-top: 100px;border-radius: 4px;}
	.block > .nav-tabs > li.active > a, .block > .nav-tabs > li.active > a:hover, .block > .nav-tabs > li.active > a:focus {color: #ffffff !important;background-color: #8B0000;border-color: transparent;border-radius: 15px !important;padding: 10px 15px !important;line-height: 1.5 !important;z-index: 10 !important;overflow: visible !important;}
.block > .nav-tabs > li > a {color: #8B0000 !important;border-radius: 15px !important;padding: 10px 15px !important;line-height: 1.5 !important;z-index: 5 !important;overflow: visible !important;}
.block > .nav-tabs > li:not(.active) > a:hover {background-color: #FFCCCC !important;}
	.btn-info{color:#ffffff;background-color:#4098f2;border-color:#ffffff}
	.btn{font-weight:100;-webkit-transition:all 0.15s ease-out;transition:all 0.15s ease-out}
	.btn-sm,.btn-group-sm > .btn{padding:5px 10px;font-size:12px;line-height:1.5;border-radius:3px}
	.btn-primary{color:#ffffff;background-color:rgb(64,152,242);border-color:rgb(64,152,242)}
	.bg-image {background-color: #ffffff;background-position: center center;background-repeat: no-repeat;-webkit-background-size: cover;background-size: cover;}
.nav-btn {color: #8B0000;background: linear-gradient(to right, #ffffff, #ffcccc);border: 2px solid #8B0000;border-radius: 25px !important;font-weight: 600;-webkit-transition: all 0.15s ease-out;transition: all 0.15s ease-out;margin: 0 2px;padding: 8px 16px;display: block !important;float: none !important;width: 100% !important;text-align: center;}
.nav-btn:hover {background: linear-gradient(to right, #fff8f8, #ffb3b3);border-color: #8B0000;}
/* 修复btn-group-justified导致的圆角问题 */
.btn-group-justified > .btn-group .nav-btn {
    border-radius: 25px !important;
    margin: 0 4px;
}
/* 全站动漫背景 */
html, body {
    min-height: 100%;
}
#anime-bg {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    z-index: -1;
    background-color: #1a1a2e;
    background-image: url('/template/XHY-01/bg-fallback.jpg');
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    transition: opacity 0.8s ease;
}
#anime-bg::after {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.35);
}
/* 内容区域加半透明白底，保证可读性 */
.col-xs-12.col-sm-10.col-md-8.col-lg-4.center-block {
    position: relative;
    z-index: 1;
}
.block {
    background-color: rgba(255,255,255,0.92) !important;
}
/* 换图按钮 */

/* xhy mobile fixed controls */
@media (max-width: 768px) {
    #anime-bg {
        background-position: center top;
    }
#lxkf {
        width: 94% !important;
        max-width: 94% !important;
        max-height: 86vh !important;
        border-radius: 10px !important;
    }
    #modalOverlay {
        z-index: 997 !important;
    }
}
@media (min-width: 769px) {
}
.q8-query-list{display:grid;gap:10px}.q8-query-card{background:#fff;border:1px solid #e4eefc;border-radius:12px;padding:13px 14px;box-shadow:0 8px 20px rgba(31,88,150,.08)}


/* XHY order detail modal */
.layui-layer.q8-order-layer{
    position:fixed!important;
    top:50%!important;
    left:50%!important;
    right:auto!important;
    bottom:auto!important;
    margin:0!important;
    transform:translate(-50%,-50%)!important;
    max-width:94vw!important;
    max-height:86vh!important;
    border-radius:14px!important;
    overflow:hidden!important;
    box-shadow:0 18px 48px rgba(15,35,75,.24)!important;
}
.layui-layer.q8-order-layer .layui-layer-content{
    height:100%!important;
    max-height:86vh!important;
    padding:0!important;
    overflow:hidden!important;
}
.layui-layer.q8-order-layer .layui-layer-setwin{
    display:none!important;
}
.q8-order-modal{
    height:100%;
    max-height:86vh;
    display:flex;
    flex-direction:column;
    background:#f7fbff;
    color:#1f2937;
}
.q8-order-hero{
    flex:0 0 auto;
    position:relative;
    background:linear-gradient(135deg,#1677ff,#20c5c8);
    color:#fff;
    padding:18px 56px 18px 20px;
    display:flex;
    align-items:center;
    gap:14px;
}
.q8-order-scroll{
    flex:1 1 auto;
    min-height:0;
    overflow-y:auto;
    overflow-x:hidden;
    -webkit-overflow-scrolling:touch;
    overscroll-behavior:contain;
    touch-action:pan-y;
}
.q8-order-close{
    position:absolute;
    right:14px;
    top:14px;
    width:32px;
    height:32px;
    border:0;
    border-radius:50%;
    background:rgba(255,255,255,.22);
    color:#fff;
    font-size:22px;
    line-height:32px;
    text-align:center;
    cursor:pointer;
}
.q8-order-close:hover{
    background:rgba(255,255,255,.34);
}
.q8-order-check{
    width:44px;
    height:44px;
    border-radius:50%;
    background:rgba(255,255,255,.22);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    font-weight:700;
}
.q8-order-title{
    font-size:20px;
    font-weight:700;
}
.q8-order-sub{
    font-size:13px;
    opacity:.9;
    margin-top:4px;
}
.q8-order-body{
    padding:16px 18px 18px;
}
.q8-order-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:10px;
}
.q8-order-row{
    background:#fff;
    border:1px solid #e6edf7;
    border-radius:10px;
    padding:12px 14px;
    min-height:64px;
}
.q8-order-row-wide{
    grid-column:1/-1;
}
.q8-order-label{
    font-size:12px;
    color:#6b7280;
    margin-bottom:6px;
}
.q8-order-value{
    font-size:14px;
    color:#111827;
    line-height:1.6;
    word-break:break-word;
}
.q8-order-money{
    color:#ff4d5d!important;
    font-weight:800;
    font-size:18px!important;
}
.q8-copy-btn{
    margin-left:8px;
    padding:4px 10px;
    border-radius:999px;
    background:#eef6ff;
    color:#1677ff;
    border:1px solid #bfdcff;
    font-size:12px;
    cursor:pointer;
}
.q8-status{
    display:inline-flex;
    border-radius:999px;
    padding:4px 10px;
    font-size:12px;
    font-weight:700;
}
.q8-status-done{background:#e8fff3;color:#0b9f55}
.q8-status-doing{background:#fff7e6;color:#b77900}
.q8-status-error,.q8-status-refund{background:#fff0f0;color:#dc2626}
.q8-status-wait{background:#edf4ff;color:#1677ff}
.q8-order-section{
    margin-top:12px;
    background:#fff;
    border:1px solid #e6edf7;
    border-radius:10px;
    overflow:hidden;
}
.q8-order-section-title{
    padding:10px 14px;
    font-weight:700;
    color:#1677ff;
    background:#f0f7ff;
    border-bottom:1px solid #e6edf7;
}
.q8-order-section-body{
    padding:12px 14px;
    line-height:1.7;
    word-break:break-word;
}
.q8-order-actions{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}
.q8-order-action{
    border-radius:999px;
    padding:7px 13px;
    border:1px solid #bfdcff;
    background:#fff;
    color:#1677ff;
    font-size:13px;
}
.q8-order-action-danger{
    background:#ff4d5d;
    color:#fff;
    border-color:#ff4d5d;
}
@media(max-width:640px){
    .layui-layer-shade{
        opacity:.10!important;
    }
    .layui-layer.q8-order-layer{
        width:94vw!important;
        height:90vh!important;
        max-height:90vh!important;
    }
    .layui-layer.q8-order-layer .layui-layer-content{
        height:100%!important;
        max-height:100%!important;
    }
    .q8-order-modal{
        height:100%;
        max-height:90vh;
    }
    .q8-order-hero{
        padding:16px 52px 16px 18px;
    }
    .q8-order-body{
        padding:12px 12px 36px;
    }
    .q8-order-grid{
        grid-template-columns:1fr;
    }
    .q8-order-title{
        font-size:18px;
    }
    .q8-order-section-body{
        font-size:13px;
    }
}


/* XHY more module */
.xhy-more-panel{
  overflow:hidden;
  border:1px solid rgba(255,255,255,.36);
  border-radius:18px;
  background:rgba(255,255,255,.82);
  box-shadow:0 18px 42px rgba(15,73,150,.18);
  backdrop-filter:blur(12px);
  -webkit-backdrop-filter:blur(12px);
  clear:both;
}
.xhy-more-head{
  position:relative;
  overflow:hidden;
  padding:20px 22px;
  color:#fff;
  background:linear-gradient(135deg,#1677ff 0%,#19bfd0 58%,#16b88a 100%);
}
.xhy-more-head:after{
  content:"";
  position:absolute;
  right:-56px;
  top:-70px;
  width:180px;
  height:180px;
  border-radius:50%;
  background:rgba(255,255,255,.16);
  box-shadow:-70px 82px 0 rgba(255,255,255,.08);
}
.xhy-more-title{
  position:relative;
  z-index:1;
  display:flex;
  align-items:center;
  gap:10px;
  margin:0 0 7px;
  color:#fff;
  font-size:22px;
  line-height:1.25;
  font-weight:900;
}
.xhy-more-title i{
  width:36px;
  height:36px;
  border-radius:13px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  background:rgba(255,255,255,.18);
  border:1px solid rgba(255,255,255,.22);
}
.xhy-more-sub{
  position:relative;
  z-index:1;
  margin:0;
  color:rgba(255,255,255,.88);
  font-size:13px;
  line-height:1.65;
}
.xhy-more-grid{
  display:grid;
  grid-template-columns:repeat(3,minmax(0,1fr));
  gap:12px;
  padding:16px;
  float:none!important;
  clear:both;
}
.xhy-more-item{
  display:flex;
  align-items:center;
  gap:12px;
  min-height:78px;
  border:1px solid #dceaff;
  border-radius:15px;
  background:#fff;
  padding:14px;
  color:#12233d!important;
  text-align:left;
  text-decoration:none!important;
  box-shadow:0 10px 22px rgba(22,119,255,.08);
  transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease;
  outline:0!important;
  -webkit-tap-highlight-color:transparent;
}
.xhy-more-item:hover,
.xhy-more-item:focus{
  color:#12233d!important;
  border-color:#8fc3ff;
  box-shadow:0 14px 30px rgba(22,119,255,.15);
  transform:translateY(-1px);
}
.xhy-more-item:active{
  transform:translateY(1px) scale(.99);
}
.xhy-more-icon{
  width:44px;
  height:44px;
  border-radius:15px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  flex:0 0 auto;
  color:#fff;
  background:linear-gradient(135deg,#1677ff,#19c2c4);
  box-shadow:0 10px 22px rgba(22,119,255,.18);
}
.xhy-more-icon i{
  display:block;
  font-size:20px;
  line-height:1;
}
.xhy-more-name{
  color:#12233d;
  font-size:15px;
  line-height:1.35;
  font-weight:900;
}
.xhy-more-desc{
  margin-top:4px;
  color:#6b7a90;
  font-size:12px;
  line-height:1.45;
}
@media (max-width:768px){
  .xhy-more-panel{
    border-radius:16px;
  }
  .xhy-more-head{
    padding:18px 16px;
  }
  .xhy-more-title{
    font-size:20px;
  }
  .xhy-more-grid{
    grid-template-columns:1fr;
    gap:10px;
    padding:14px;
  }
  .xhy-more-item{
    min-height:72px;
    padding:13px;
  }
}
/* XHY gift module */
.xhy-gift-panel{
  overflow:hidden;
  border:1px solid rgba(255,255,255,.36);
  border-radius:18px;
  background:rgba(255,255,255,.78);
  box-shadow:0 18px 42px rgba(15,73,150,.18);
  backdrop-filter:blur(12px);
  -webkit-backdrop-filter:blur(12px);
}
.xhy-gift-hero{
  position:relative;
  overflow:hidden;
  padding:22px 22px 20px;
  color:#fff;
  background:linear-gradient(135deg,#1778f2 0%,#18bfd0 58%,#18c08f 100%);
}
.xhy-gift-hero:after{
  content:"";
  position:absolute;
  right:-54px;
  top:-70px;
  width:190px;
  height:190px;
  border-radius:50%;
  background:rgba(255,255,255,.16);
  box-shadow:-72px 82px 0 rgba(255,255,255,.08);
}
.xhy-gift-kicker{
  position:relative;
  z-index:1;
  display:inline-flex;
  align-items:center;
  gap:7px;
  height:28px;
  padding:0 12px;
  border-radius:999px;
  background:rgba(255,255,255,.18);
  border:1px solid rgba(255,255,255,.24);
  font-size:13px;
  font-weight:800;
}
.xhy-gift-title{
  position:relative;
  z-index:1;
  margin:13px 0 7px;
  color:#fff;
  font-size:25px;
  line-height:1.25;
  font-weight:900;
}
.xhy-gift-sub{
  position:relative;
  z-index:1;
  margin:0;
  color:rgba(255,255,255,.88);
  font-size:13px;
  line-height:1.7;
}
.xhy-gift-body{
  padding:18px;
}
.xhy-gift-stage{
  position:relative;
  border:1px solid #dceaff;
  border-radius:16px;
  background:linear-gradient(180deg,#ffffff 0%,#f4f9ff 100%);
  padding:20px 16px 18px;
  text-align:center;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.8);
}
.xhy-gift-icon{
  width:62px;
  height:62px;
  margin:0 auto 12px;
  border-radius:22px;
  display:flex;
  align-items:center;
  justify-content:center;
  color:#fff;
  background:linear-gradient(135deg,#1976f3,#20c4c7);
  box-shadow:0 14px 28px rgba(22,119,255,.24);
}
.xhy-gift-icon i{
  display:block;
  font-size:28px;
  line-height:1;
}
#gift #roll{
  min-height:34px;
  color:#12233d;
  font-size:18px;
  line-height:1.55;
  font-weight:900;
  word-break:break-word;
}
.xhy-gift-result{
  margin-top:8px;
  color:#1677ff;
  font-size:13px;
  font-weight:700;
  min-height:18px;
  word-break:break-word;
}
.xhy-gift-actions{
  display:grid;
  grid-template-columns:1fr;
  gap:10px;
  margin:14px 0;
}
.xhy-gift-btn{
  width:100%;
  min-height:46px;
  border:0!important;
  border-radius:999px!important;
  padding:0 18px!important;
  color:#fff!important;
  font-size:15px!important;
  line-height:46px!important;
  font-weight:900!important;
  text-align:center!important;
  text-decoration:none!important;
  cursor:pointer;
  outline:0!important;
  -webkit-tap-highlight-color:transparent;
  transition:transform .18s ease,box-shadow .18s ease,filter .18s ease;
}
.xhy-gift-btn i{
  margin-right:7px;
}
.xhy-gift-btn-start{
  background:linear-gradient(135deg,#1677ff,#19c2c4)!important;
  box-shadow:0 14px 28px rgba(22,119,255,.22)!important;
}
.xhy-gift-btn-stop{
  background:linear-gradient(135deg,#ff4d6d,#ff8a35)!important;
  box-shadow:0 14px 28px rgba(255,77,109,.22)!important;
}
.xhy-gift-btn:hover,
.xhy-gift-btn:focus{
  color:#fff!important;
  filter:brightness(1.03);
  box-shadow:0 16px 30px rgba(22,119,255,.28)!important;
}
.xhy-gift-btn:active{
  transform:translateY(1px) scale(.99);
}
.xhy-gift-history{
  border:1px solid #dceaff;
  border-radius:16px;
  background:#fff;
  padding:13px 14px 12px;
  text-align:left;
}
.xhy-gift-history-title{
  display:flex;
  align-items:center;
  gap:8px;
  color:#12233d;
  font-size:14px;
  font-weight:900;
  margin-bottom:9px;
}
.xhy-gift-history-title i{
  color:#f59e0b;
}
.xhy-gift-history ul{
  list-style:none;
  margin:0;
  padding:0;
  max-height:160px;
  overflow:auto;
}
.xhy-gift-history li{
  display:flex;
  align-items:center;
  min-height:34px;
  border-top:1px solid #edf4ff;
  color:#506078;
  font-size:13px;
  line-height:1.55;
}
.xhy-gift-history li:first-child{
  border-top:0;
}
@media (max-width:768px){
  .xhy-gift-panel{
    border-radius:16px;
  }
  .xhy-gift-hero{
    padding:20px 18px 18px;
  }
  .xhy-gift-title{
    font-size:22px;
  }
  .xhy-gift-body{
    padding:14px;
  }
  #gift #roll{
    font-size:16px;
  }
}
/* XHY button interaction tune */
.xhy-earn-btn,
.xhy-site-modal-btn,
#submit_query{
  outline:0!important;
  -webkit-tap-highlight-color:transparent;
}
.xhy-earn-btn:focus,
.xhy-earn-btn:active,
.xhy-site-modal-btn:focus,
.xhy-site-modal-btn:active,
#submit_query:focus,
#submit_query:active{
  outline:0!important;
  box-shadow:none!important;
}
.xhy-earn-btn-info:hover,
.xhy-earn-btn-info:focus{
  background:#e3f1ff!important;
  color:#1677ff!important;
  box-shadow:0 10px 20px rgba(22,119,255,.16)!important;
}
.xhy-earn-btn-info:active,
.xhy-earn-btn-main:active,
.xhy-site-modal-btn:active,
#submit_query:active{
  transform:translateY(1px) scale(.99)!important;
}
.xhy-site-modal-close{
  transition:transform .18s ease,background .18s ease,box-shadow .18s ease;
}
.xhy-site-modal-close:hover,
.xhy-site-modal-close:focus{
  outline:0!important;
  background:rgba(255,255,255,.34);
  transform:rotate(90deg) scale(1.04);
}
.xhy-site-modal-close:active{
  transform:rotate(90deg) scale(.96);
}
#submit_query{
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
  min-height:38px!important;
  padding:0 14px!important;
  color:#fff!important;
  line-height:38px!important;
  text-align:center!important;
}
#submit_query:hover,
#submit_query:focus{
  color:#fff!important;
  filter:brightness(1.03);
  box-shadow:0 12px 24px rgba(22,119,255,.22)!important;
}
/* XHY earn module */
.xhy-earn-panel{
  position:relative;
  overflow:hidden;
  margin:14px 8px 4px;
  border:1px solid rgba(219,234,254,.95);
  border-radius:18px;
  background:rgba(255,255,255,.88);
  box-shadow:0 18px 38px rgba(15,95,215,.18);
}
.xhy-earn-panel:before{
  content:"";
  position:absolute;
  inset:0;
  background:linear-gradient(135deg,rgba(255,255,255,.72),rgba(255,255,255,0) 42%);
  pointer-events:none;
}
.xhy-earn-hero{
  position:relative;
  padding:22px 18px 18px;
  color:#fff;
  background:linear-gradient(135deg,#1677ff 0%,#20c5c8 58%,#19b889 100%);
}
.xhy-earn-kicker{
  display:inline-flex;
  align-items:center;
  gap:7px;
  min-height:28px;
  padding:5px 11px;
  border-radius:999px;
  background:rgba(255,255,255,.18);
  border:1px solid rgba(255,255,255,.28);
  font-size:12px;
  font-weight:800;
}
.xhy-earn-title{
  margin:13px 0 6px;
  font-size:24px;
  line-height:1.25;
  font-weight:900;
  letter-spacing:0;
}
.xhy-earn-sub{
  margin:0;
  color:rgba(255,255,255,.88);
  font-size:13px;
  line-height:1.7;
}
.xhy-earn-stats{
  display:grid;
  grid-template-columns:repeat(3,minmax(0,1fr));
  gap:10px;
  margin-top:16px;
}
.xhy-earn-stat{
  min-height:68px;
  padding:11px 10px;
  border-radius:14px;
  background:rgba(255,255,255,.15);
  border:1px solid rgba(255,255,255,.24);
}
.xhy-earn-stat strong{
  display:block;
  font-size:18px;
  line-height:1.2;
  color:#fff;
}
.xhy-earn-stat span{
  display:block;
  margin-top:5px;
  font-size:12px;
  color:rgba(255,255,255,.82);
}
.xhy-earn-body{
  position:relative;
  padding:14px;
}
.xhy-earn-benefits{
  display:grid;
  grid-template-columns:repeat(2,minmax(0,1fr));
  gap:10px;
}
.xhy-earn-benefit{
  display:flex;
  gap:10px;
  align-items:flex-start;
  min-height:82px;
  padding:13px 12px;
  border-radius:14px;
  background:#fff;
  border:1px solid #e6effb;
  box-shadow:0 8px 18px rgba(31,88,150,.07);
}
.xhy-earn-icon{
  flex:0 0 34px;
  width:34px!important;
  height:34px!important;
  min-width:34px!important;
  min-height:34px!important;
  margin:0!important;
  border-radius:12px;
  display:inline-flex!important;
  align-items:center!important;
  justify-content:center!important;
  color:#fff!important;
  background:linear-gradient(135deg,#1677ff,#20c5c8);
  box-shadow:0 8px 16px rgba(22,119,255,.20);
}
.xhy-earn-benefit strong{
  display:block;
  color:#163b70;
  font-size:14px;
  line-height:1.35;
}
.xhy-earn-benefit > div > span{
  display:block;
  margin-top:5px;
  color:#64748b;
  font-size:12px;
  line-height:1.55;
}
.xhy-earn-highlight{
  margin-top:11px;
  padding:13px 14px;
  border-radius:14px;
  background:linear-gradient(135deg,#fff7ed,#ecfeff);
  border:1px solid #fed7aa;
  color:#9a3412;
  font-weight:800;
  line-height:1.55;
}
.xhy-earn-highlight i{
  color:#f97316;
  margin-right:6px;
}
.xhy-earn-actions{
  display:grid;
  grid-template-columns:1fr 1.35fr;
  gap:10px;
  margin-top:13px;
}
.xhy-earn-btn{
  display:flex!important;
  align-items:center;
  justify-content:center;
  gap:7px;
  min-height:44px;
  border:0!important;
  border-radius:999px!important;
  text-decoration:none!important;
  font-weight:900!important;
  letter-spacing:0;
  transition:transform .18s ease,box-shadow .18s ease,filter .18s ease;
}
.xhy-earn-btn-info{
  color:#1677ff!important;
  background:#eef6ff!important;
  box-shadow:0 8px 18px rgba(22,119,255,.12);
}
.xhy-earn-btn-main{
  color:#fff!important;
  background:linear-gradient(135deg,#1677ff,#20c5c8)!important;
  box-shadow:0 12px 24px rgba(22,119,255,.24);
}
.xhy-earn-btn:hover,.xhy-earn-btn:focus{
  transform:translateY(-1px);
  filter:brightness(1.03);
  text-decoration:none!important;
}
.xhy-earn-btn-main:hover,.xhy-earn-btn-main:focus{
  box-shadow:0 16px 30px rgba(22,119,255,.30);
}

.xhy-earn-icon i{
  display:block!important;
  position:static!important;
  width:auto!important;
  height:auto!important;
  margin:0!important;
  color:#fff!important;
  font-size:16px!important;
  line-height:1!important;
}
.xhy-earn-benefit > div{
  flex:1 1 auto;
  min-width:0;
}
#userjs.xhy-site-modal{
  z-index:1050;
}
.xhy-site-modal .modal-dialog{
  width:min(760px,92vw);
  margin:6vh auto;
}
.xhy-site-modal .modal-content{
  overflow:hidden;
  border:0;
  border-radius:18px;
  background:#f7fbff;
  box-shadow:0 24px 60px rgba(15,35,75,.30);
}
.xhy-site-modal-head{
  position:relative;
  padding:20px 58px 18px 20px;
  color:#fff;
  background:linear-gradient(135deg,#1677ff,#20c5c8);
}
.xhy-site-modal-close{
  position:absolute;
  top:14px;
  right:14px;
  width:34px;
  height:34px;
  border:0;
  border-radius:50%;
  background:rgba(255,255,255,.22);
  color:#fff;
  font-size:22px;
  line-height:34px;
  text-align:center;
  cursor:pointer;
}
.xhy-site-modal-title{
  margin:0;
  font-size:21px;
  font-weight:900;
  letter-spacing:0;
}
.xhy-site-modal-sub{
  margin:6px 0 0;
  color:rgba(255,255,255,.88);
  font-size:13px;
  line-height:1.7;
}
.xhy-site-modal-body{
  max-height:68vh;
  overflow-y:auto;
  overflow-x:hidden;
  -webkit-overflow-scrolling:touch;
  padding:14px;
}
.xhy-version-grid{
  display:grid;
  gap:10px;
}
.xhy-version-row{
  display:grid;
  grid-template-columns:minmax(0,1fr) 88px 88px;
  gap:8px;
  align-items:center;
  padding:12px;
  border:1px solid #e6effb;
  border-radius:14px;
  background:#fff;
  box-shadow:0 8px 18px rgba(31,88,150,.07);
}
.xhy-version-row.xhy-version-head{
  background:linear-gradient(135deg,#edf6ff,#f8fbff);
  color:#163b70;
  font-weight:900;
  box-shadow:none;
}
.xhy-version-name{
  color:#1f2d3d;
  font-weight:800;
  line-height:1.45;
}
.xhy-version-cell{
  display:flex;
  align-items:center;
  justify-content:center;
}
.xhy-version-ok,.xhy-version-no{
  width:28px;
  height:28px;
  border-radius:999px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  color:#fff;
  font-size:13px;
}
.xhy-version-ok{
  background:linear-gradient(135deg,#18b36b,#20c5c8);
}
.xhy-version-no{
  background:linear-gradient(135deg,#ff6b6b,#ff9b7a);
}
.xhy-site-modal-note{
  margin:12px 0 0;
  padding:12px 14px;
  border-radius:14px;
  background:#eef6ff;
  color:#1677ff;
  font-weight:800;
  line-height:1.6;
}
.xhy-site-modal-footer{
  display:flex;
  gap:10px;
  padding:0 14px 14px;
}
.xhy-site-modal-btn{
  flex:1 1 0;
  min-height:42px;
  border:0;
  border-radius:999px;
  font-weight:900;
}
.xhy-site-modal-btn-close{
  background:#eef6ff;
  color:#1677ff;
}
.xhy-site-modal-btn-main{
  background:linear-gradient(135deg,#1677ff,#20c5c8);
  color:#fff;
}
@media(max-width:640px){
  .xhy-site-modal .modal-dialog{
    width:94vw;
    margin:4vh auto;
  }
  .xhy-site-modal-body{
    max-height:72vh;
    padding:12px;
  }
  .xhy-version-row{
    grid-template-columns:minmax(0,1fr) 54px 54px;
    gap:6px;
    padding:10px;
  }
  .xhy-version-row.xhy-version-head{
    font-size:12px;
  }
  .xhy-version-name{
    font-size:13px;
  }
}
@media(max-width:640px){
  .xhy-earn-panel{
    margin:10px 0 4px;
    border-radius:16px;
  }
  .xhy-earn-hero{
    padding:18px 14px 14px;
  }
  .xhy-earn-title{
    font-size:21px;
  }
  .xhy-earn-stats{
    grid-template-columns:1fr;
    gap:8px;
  }
  .xhy-earn-stat{
    min-height:58px;
  }
  .xhy-earn-benefits{
    grid-template-columns:1fr;
  }
  .xhy-earn-benefit{
    min-height:74px;
  }
  .xhy-earn-actions{
    grid-template-columns:1fr;
  }
}

/* XHY polished lists */

.q8-query-card #result2{width:100%!important;display:none}
.q8-query-card #list{width:100%!important;display:grid!important;grid-template-columns:1fr!important;gap:10px!important}
.q8-query-card .q8-order-list-card{width:100%!important;box-sizing:border-box!important}

.q8-query-card #result2 table,.q8-query-card #result2 tbody{display:block;width:100%;background:transparent!important}.q8-query-card #result2 thead{display:none}.q8-query-card #result2 tr{display:block}.q8-query-card #result2 td{display:block;width:100%}.q8-query-card #result2 .table{margin:0;background:transparent!important}
.q8-order-list-row td{padding:0!important;border:0!important;background:transparent!important}.q8-order-list-card{background:#fff;border:1px solid #e3eefc;border-radius:12px;padding:13px 14px;margin-bottom:10px;box-shadow:0 8px 20px rgba(31,88,150,.08);text-align:left}.q8-order-list-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:10px}.q8-order-list-name{font-size:15px;font-weight:800;color:#1f2d3d;line-height:1.45;word-break:break-word}.q8-order-list-meta{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}.q8-order-list-cell{background:#f7fbff;border-radius:9px;padding:8px 10px;color:#506078;font-size:12px;line-height:1.45;min-height:52px}.q8-order-list-cell b{display:block;color:#93a0b3;font-weight:600;margin-bottom:3px}.q8-order-list-btn{display:inline-flex;align-items:center;justify-content:center;gap:5px;border:0;border-radius:999px;background:#1677ff;color:#fff!important;padding:7px 12px;font-size:12px;font-weight:700;text-decoration:none!important;box-shadow:0 6px 14px rgba(22,119,255,.22);cursor:pointer}.q8-order-list-status{display:inline-flex;align-items:center;border-radius:999px;padding:5px 10px;font-size:12px;font-weight:800;white-space:nowrap}.q8-order-list-done{background:#e8fff3;color:#079455}.q8-order-list-doing{background:#fff7e6;color:#b77900}.q8-order-list-error,.q8-order-list-refund{background:#fff0f0;color:#dc2626}.q8-order-list-wait{background:#edf4ff;color:#1677ff}.q8-order-list-errorbox{margin-top:8px;border-radius:9px;background:#fff2f2;color:#d92d20;padding:8px 10px;font-size:12px}.q8-order-list-pager{display:flex;justify-content:space-between;gap:10px}.q8-order-list-pager .btn{border-radius:999px!important;padding:7px 14px!important}
.xhy-article-panel{background:rgba(255,255,255,.96);border:1px solid #dbeafe;border-radius:14px;box-shadow:0 12px 28px rgba(22,119,255,.12);overflow:hidden;margin-bottom:12px;clear:both}.xhy-article-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;background:linear-gradient(135deg,#f0f7ff,#f7fbff);border-bottom:1px solid #e3eefc}.xhy-article-title{display:flex;align-items:center;gap:9px;margin:0;color:#163b70;font-size:18px;font-weight:800}.xhy-article-title i{width:34px;height:34px;border-radius:12px;background:linear-gradient(135deg,#1677ff,#20c5c8);color:#fff;display:inline-flex;align-items:center;justify-content:center}.xhy-article-sub{font-size:12px;color:#7a8aa0}.xhy-article-list{display:grid;gap:9px;padding:13px}.xhy-article-item{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid #e8f0fb;border-radius:11px;padding:11px 12px;color:#24364f!important;text-decoration:none!important;transition:all .18s ease}.xhy-article-item:hover{border-color:#1677ff;box-shadow:0 8px 18px rgba(22,119,255,.12);transform:translateY(-1px)}.xhy-article-num{width:28px;height:28px;border-radius:10px;background:#edf6ff;color:#1677ff;display:inline-flex;align-items:center;justify-content:center;font-weight:800;flex:0 0 auto}.xhy-article-text{flex:1;line-height:1.45;word-break:break-word}.xhy-article-arrow{color:#9aa8ba}
.xhy-fav-card{display:flex;align-items:center;justify-content:space-between;gap:12px;background:linear-gradient(135deg,#0f5fd7,#20c5c8);color:#fff;border-radius:14px;padding:13px 15px;margin:12px 0 10px;box-shadow:0 12px 28px rgba(22,119,255,.22);border:1px solid rgba(255,255,255,.28);clear:both}.xhy-fav-main{display:flex;align-items:center;gap:10px}.xhy-fav-icon{width:38px;height:38px;border-radius:13px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center}.xhy-fav-title{font-weight:800;font-size:15px}.xhy-fav-host{font-size:12px;opacity:.9;margin-top:2px}.xhy-fav-btn{border:0;border-radius:999px;background:#fff;color:#1677ff!important;padding:8px 13px;font-weight:800;text-decoration:none!important;white-space:nowrap}@media(max-width:768px){.q8-order-list-top{display:block}.q8-order-list-btn{margin-top:8px}.q8-order-list-meta{grid-template-columns:1fr}.xhy-article-head{align-items:flex-start;flex-direction:column}.xhy-fav-card{align-items:flex-start;flex-direction:column}.xhy-fav-btn{width:100%;text-align:center}}
.q8-today-recommend{display:none;background:rgba(255,255,255,.97);border:1px solid #dbeafe;border-radius:14px;box-shadow:0 12px 28px rgba(22,119,255,.12);margin:0 0 12px;overflow:hidden;clear:both}.q8-today-recommend__head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:13px 15px;background:linear-gradient(135deg,#f0f7ff,#f8fbff)}.q8-today-recommend__title{display:flex;align-items:center;gap:8px;color:#163b70;font-size:17px;font-weight:800}.q8-today-recommend__title i{width:32px;height:32px;border-radius:11px;background:linear-gradient(135deg,#1677ff,#20c5c8);color:#fff;display:inline-flex;align-items:center;justify-content:center}.q8-today-recommend__toggle{border:0;border-radius:999px;background:#edf6ff;color:#1677ff;font-weight:800;padding:7px 12px;display:inline-flex;align-items:center;justify-content:center;gap:6px;white-space:nowrap;cursor:pointer}.q8-today-recommend__toggle i{transition:transform .18s ease}.q8-today-recommend.is-open .q8-today-recommend__toggle i{transform:rotate(180deg)}.q8-today-recommend__grid{display:none;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;padding:12px;border-top:1px solid #e3eefc}.q8-today-recommend.is-open .q8-today-recommend__grid{display:grid}.q8-today-recommend__item{width:100%;border:1px solid #e7f0fb;background:#fff;border-radius:11px;padding:10px 11px;text-align:left;cursor:pointer;transition:all .18s ease;min-height:76px}.q8-today-recommend__item:hover{border-color:#1677ff;box-shadow:0 8px 18px rgba(22,119,255,.13);transform:translateY(-1px)}.q8-today-recommend__name{color:#24364f;font-size:13px;font-weight:800;line-height:1.42;min-height:36px;word-break:break-word;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}.q8-today-recommend__meta{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:8px;font-size:12px;color:#8a98aa}.q8-today-recommend__price{color:#f04438;font-weight:800}.q8-today-recommend__action{color:#1677ff;font-weight:800;white-space:nowrap}@media(max-width:520px){.q8-today-recommend__head{padding:12px}.q8-today-recommend__grid{grid-template-columns:1fr}.q8-today-recommend__title{font-size:15px}.q8-today-recommend__toggle{padding:7px 10px}}



/* XHY button polish */
.nav-btn{
    background:rgba(255,255,255,.94)!important;
    border:1px solid #dbeafe!important;
    color:#334155!important;
    border-radius:999px!important;
    box-shadow:0 8px 18px rgba(15,95,215,.12)!important;
    font-weight:800!important;
}
.nav-btn i{color:#1677ff!important}
.nav-btn:hover,.nav-btn:focus{
    background:linear-gradient(135deg,#edf6ff,#f7fbff)!important;
    color:#1677ff!important;
    transform:translateY(-1px);
}
.block > .nav-tabs{
    background:rgba(255,255,255,.78)!important;
    border:1px solid #dbeafe!important;
    border-radius:16px!important;
    padding:6px!important;
    box-shadow:0 10px 24px rgba(22,119,255,.14)!important;
}
.block > .nav-tabs > li > a{
    color:#334155!important;
    background:transparent!important;
    border:0!important;
    border-radius:12px!important;
    font-weight:800!important;
    padding:11px 8px!important;
}
.block > .nav-tabs > li > a i{color:#1677ff!important}
.block > .nav-tabs > li.active > a,
.block > .nav-tabs > li.active > a:hover,
.block > .nav-tabs > li.active > a:focus{
    color:#fff!important;
    background:linear-gradient(135deg,#1677ff,#20c5c8)!important;
    border:0!important;
    box-shadow:0 9px 18px rgba(22,119,255,.24)!important;
}
.block > .nav-tabs > li.active > a i{color:#fff!important}
.block > .nav-tabs > li:not(.active) > a:hover{
    color:#1677ff!important;
    background:#f0f7ff!important;
}
#search .btn,
#search span[style*="border-radius:20px"]{
    border-color:#dbeafe!important;
    box-shadow:0 6px 14px rgba(22,119,255,.10)!important;
}
#search span[style*="border-radius:20px"]{
    background:#fff!important;
    color:#1677ff!important;
}
#search span[style*="border-radius:20px"] i{color:#1677ff!important}
@media(max-width:768px){
    .block > .nav-tabs{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px}
    .block > .nav-tabs > li{width:auto!important}
    .block > .nav-tabs > li > a{font-size:13px!important}
    .nav-btn{font-size:13px!important;padding:8px 10px!important}
}



/* XHY nav-tabs fixed polish */
.block > ul.nav.nav-tabs.btn.btn-block {
  display: flex !important;
  align-items: center !important;
  gap: 6px !important;
  width: 100% !important;
  min-height: 52px !important;
  padding: 6px !important;
  margin: 0 0 12px !important;
  background: rgba(255,255,255,.86) !important;
  border: 1px solid rgba(191,219,254,.95) !important;
  border-radius: 18px !important;
  box-shadow: 0 10px 24px rgba(22,119,255,.12) !important;
  text-align: center !important;
}
.block > ul.nav.nav-tabs.btn.btn-block > li {
  float: none !important;
  flex: 1 1 0 !important;
  width: auto !important;
  margin: 0 !important;
  padding: 0 !important;
  display: block !important;
}
.block > ul.nav.nav-tabs.btn.btn-block > li > a {
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 6px !important;
  height: 40px !important;
  min-height: 40px !important;
  padding: 0 8px !important;
  margin: 0 !important;
  border: 0 !important;
  border-radius: 14px !important;
  background: transparent !important;
  color: #2563eb !important;
  font-weight: 700 !important;
  line-height: 1 !important;
  white-space: nowrap !important;
  box-shadow: none !important;
}
.block > ul.nav.nav-tabs.btn.btn-block > li > a:hover,
.block > ul.nav.nav-tabs.btn.btn-block > li > a:focus {
  background: rgba(37,99,235,.08) !important;
  color: #1d4ed8 !important;
  outline: none !important;
}
.block > ul.nav.nav-tabs.btn.btn-block > li.active > a,
.block > ul.nav.nav-tabs.btn.btn-block > li.active > a:hover,
.block > ul.nav.nav-tabs.btn.btn-block > li.active > a:focus {
  height: 40px !important;
  min-height: 40px !important;
  background: linear-gradient(135deg,#1d7ff2,#19c3c6) !important;
  color: #fff !important;
  box-shadow: 0 10px 22px rgba(29,127,242,.24) !important;
}
.btn-group-justified .nav-btn,
.btn-group-justified > a.nav-btn {
  min-height: 42px !important;
  display: table-cell !important;
  vertical-align: middle !important;
  color: #2563eb !important;
  font-weight: 700 !important;
}
.btn-group-justified .nav-btn i {
  margin-right: 5px !important;
}
@media (max-width: 768px) {
  .block > ul.nav.nav-tabs.btn.btn-block {
    display: grid !important;
    grid-template-columns: repeat(3,minmax(0,1fr)) !important;
    min-height: 0 !important;
    gap: 6px !important;
  }
  .block > ul.nav.nav-tabs.btn.btn-block > li,
  .block > ul.nav.nav-tabs.btn.btn-block > li > a {
    width: 100% !important;
  }
  .block > ul.nav.nav-tabs.btn.btn-block > li > a {
    font-size: 13px !important;
  }
}



/* XHY top quick buttons fixed grid */
.btn-group-justified {
  display: grid !important;
  grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
  gap: 8px !important;
  width: 100% !important;
  table-layout: auto !important;
  border-collapse: separate !important;
}
.btn-group-justified + .btn-group-justified {
  grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
  margin-top: 8px !important;
}
.btn-group-justified > .btn,
.btn-group-justified > .btn-group,
.btn-group-justified > a.nav-btn {
  display: flex !important;
  width: 100% !important;
  min-width: 0 !important;
  height: 42px !important;
  min-height: 42px !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 6px !important;
  margin: 0 !important;
  padding: 0 10px !important;
  border-radius: 22px !important;
  white-space: nowrap !important;
  box-sizing: border-box !important;
}
.btn-group-justified > a.nav-btn i {
  margin: 0 !important;
  flex: 0 0 auto !important;
}
@media (max-width: 520px) {
  .btn-group-justified,
  .btn-group-justified + .btn-group-justified {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 7px !important;
  }
  .btn-group-justified > .btn,
  .btn-group-justified > .btn-group,
  .btn-group-justified > a.nav-btn {
    height: 40px !important;
    min-height: 40px !important;
    font-size: 13px !important;
  }
}



/* XHY quick button inner fix */
.widget-content.text-center > .btn-group-justified {
  max-width: 620px !important;
  margin-left: auto !important;
  margin-right: auto !important;
}
.widget-content.text-center > .btn-group-justified > .btn-group {
  display: block !important;
  width: 100% !important;
  min-width: 0 !important;
  padding: 0 !important;
  margin: 0 !important;
}
.widget-content.text-center > .btn-group-justified > .btn-group > a.nav-btn {
  display: flex !important;
  width: 100% !important;
  height: 42px !important;
  min-height: 42px !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 6px !important;
  padding: 0 12px !important;
  margin: 0 !important;
  border-radius: 22px !important;
  background: rgba(255,255,255,.92) !important;
  border: 1px solid rgba(191,219,254,.9) !important;
  color: #2563eb !important;
  box-shadow: 0 8px 18px rgba(15,23,42,.10) !important;
  line-height: 1 !important;
  white-space: nowrap !important;
  transition: transform .18s ease, box-shadow .18s ease, background .18s ease !important;
}
.widget-content.text-center > .btn-group-justified > .btn-group > a.nav-btn:hover,
.widget-content.text-center > .btn-group-justified > .btn-group > a.nav-btn:focus {
  background: #fff !important;
  color: #1d4ed8 !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 12px 24px rgba(37,99,235,.16) !important;
  text-decoration: none !important;
  outline: none !important;
}
.widget-content.text-center > .btn-group-justified > .btn-group > a.nav-btn i {
  margin: 0 !important;
  color: #1677ff !important;
}
.widget-content.text-center > .btn-group-justified > .btn-group > a.nav-btn span {
  display: inline-block !important;
  max-width: 100% !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
}
.block > ul.nav.nav-tabs.btn.btn-block.animated {
  animation-name: fadeInUp !important;
  animation-duration: .45s !important;
}



/* XHY mobile and buy button polish */
#submit_buy,
.q8-submit-buy {
  position: relative !important;
  z-index: 3 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  width: 100% !important;
  min-height: 46px !important;
  padding: 0 16px !important;
  border: 0 !important;
  border-radius: 999px !important;
  background: linear-gradient(135deg,#1677ff,#20c5c8) !important;
  color: #fff !important;
  font-size: 15px !important;
  font-weight: 800 !important;
  letter-spacing: 0 !important;
  box-shadow: 0 12px 24px rgba(22,119,255,.24) !important;
  cursor: pointer !important;
  transition: transform .18s ease, box-shadow .18s ease, filter .18s ease !important;
}
#submit_buy:hover,
.q8-submit-buy:hover,
#submit_buy:focus,
.q8-submit-buy:focus {
  color: #fff !important;
  filter: brightness(1.03) !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 16px 30px rgba(22,119,255,.30) !important;
  outline: none !important;
}
#submit_buy:active,
.q8-submit-buy:active {
  transform: translateY(0) !important;
  box-shadow: 0 8px 18px rgba(22,119,255,.22) !important;
}
.block-content.tab-content {
  overflow: visible !important;
}
.tab-pane.fade-up {
  animation-duration: .38s !important;
}
@media (min-width: 769px) {
  .block.animated.bounceInDown {
    max-width: 640px !important;
    margin-left: auto !important;
    margin-right: auto !important;
  }
  #shop .form-group,
  #shop .input-group,
  #shop select.form-control,
  #shop input.form-control,
  #shop textarea.form-control {
    max-width: 100% !important;
  }
}
@media (max-width: 768px) {
  body {
    overflow-x: hidden !important;
  }
  .widget-content.text-center {
    padding: 0 8px !important;
  }
  .widget-content.text-center > .btn-group-justified {
    max-width: 100% !important;
  }
  .block.animated.bounceInDown {
    margin: 8px 8px 0 !important;
    padding: 5px !important;
    border-radius: 14px !important;
  }
  .block > ul.nav.nav-tabs.btn.btn-block {
    grid-template-columns: repeat(3,minmax(0,1fr)) !important;
  }
  .block > ul.nav.nav-tabs.btn.btn-block > li > a {
    height: 38px !important;
    min-height: 38px !important;
    font-size: 13px !important;
    gap: 4px !important;
  }
  .block-content.tab-content {
    padding: 8px 4px 4px !important;
  }
  #shop .panel,
  #shop .list-group,
  #shop .well,
  #shop table,
  #search .q8-query-card,
  .xhy-article-panel,
  .xhy-fav-card {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
  }
  #shop .input-group,
  #shop .form-control,
  #shop select,
  #shop textarea,
  #shop input {
    max-width: 100% !important;
    box-sizing: border-box !important;
  }
  #submit_buy,
  .q8-submit-buy {
    min-height: 44px !important;
    font-size: 14px !important;
  }
  .xhy-article-list {
    padding: 10px !important;
    gap: 8px !important;
  }
  .xhy-article-item {
    padding: 10px !important;
    border-radius: 10px !important;
  }
  .xhy-article-title {
    font-size: 16px !important;
  }
  .xhy-fav-card {
    margin: 10px 0 !important;
    padding: 12px !important;
  }
}



/* XHY submit buy full CTA */
#submit_buy,
input#submit_buy,
button#submit_buy,
#submit_buy.btn,
#submit_buy.btn-block,
#shop #submit_buy {
  display: block !important;
  width: 100% !important;
  max-width: 100% !important;
  min-width: 0 !important;
  height: 48px !important;
  min-height: 48px !important;
  margin: 12px 0 4px !important;
  padding: 0 18px !important;
  border: 0 !important;
  border-radius: 16px !important;
  background: linear-gradient(135deg,#1677ff 0%,#20c5c8 100%) !important;
  color: #fff !important;
  font-size: 16px !important;
  font-weight: 900 !important;
  line-height: 48px !important;
  text-align: center !important;
  box-sizing: border-box !important;
  box-shadow: 0 14px 28px rgba(22,119,255,.26) !important;
  cursor: pointer !important;
}
#submit_buy:hover,
#submit_buy:focus {
  color: #fff !important;
  filter: brightness(1.04) !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 18px 34px rgba(22,119,255,.32) !important;
  outline: none !important;
}
#submit_buy:active {
  transform: translateY(0) !important;
  box-shadow: 0 9px 20px rgba(22,119,255,.24) !important;
}
@media (max-width: 768px) {
  #submit_buy,
  input#submit_buy,
  button#submit_buy {
    height: 46px !important;
    min-height: 46px !important;
    line-height: 46px !important;
    border-radius: 14px !important;
    font-size: 15px !important;
  }
}



/* XHY mobile alignment fix */
@media (max-width: 768px) {
  .widget-content.text-center > .btn-group-justified {
    display: grid !important;
    width: 100% !important;
    max-width: 100% !important;
    gap: 8px !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
  }
  .widget-content.text-center > .btn-group-justified:first-child {
    grid-template-columns: repeat(3,minmax(0,1fr)) !important;
  }
  .widget-content.text-center > .btn-group-justified + .btn-group-justified {
    grid-template-columns: repeat(2,minmax(0,1fr)) !important;
  }
  .widget-content.text-center > .btn-group-justified > .btn-group > a.nav-btn {
    height: 39px !important;
    min-height: 39px !important;
    padding: 0 6px !important;
    font-size: 13px !important;
    border-radius: 19px !important;
  }
  .block > ul.nav.nav-tabs.btn.btn-block:not(.q8-main-tabs) {
    display: grid !important;
    grid-template-columns: repeat(3,minmax(0,1fr)) !important;
    gap: 4px !important;
    min-height: 46px !important;
    padding: 4px !important;
    margin: 0 0 8px !important;
    overflow: hidden !important;
  }
  .block > ul.nav.nav-tabs.btn.btn-block:not(.q8-main-tabs) > li {
    display: block !important;
    width: 100% !important;
    min-width: 0 !important;
  }
  .block > ul.nav.nav-tabs.btn.btn-block:not(.q8-main-tabs) > li.hide {
    display: none !important;
  }
  .block > ul.nav.nav-tabs.btn.btn-block:not(.q8-main-tabs) > li > a {
    width: 100% !important;
    height: 38px !important;
    min-height: 38px !important;
    padding: 0 3px !important;
    border-radius: 13px !important;
    font-size: 12px !important;
    gap: 3px !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
  }
  .block > ul.nav.nav-tabs.btn.btn-block:not(.q8-main-tabs) > li > a i {
    margin: 0 !important;
    font-size: 12px !important;
  }
  .block-content.tab-content {
    padding-top: 8px !important;
    clear: both !important;
  }
  .block.animated.bounceInDown {
    overflow: visible !important;
  }
}
@media (max-width: 360px) {
  .widget-content.text-center > .btn-group-justified > .btn-group > a.nav-btn {
    font-size: 12px !important;
  }
  .block > ul.nav.nav-tabs.btn.btn-block > li > a {
    font-size: 11px !important;
  }
}
/* XHY mobile detail pass */
@media (max-width: 640px) {
  html, body {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden !important;
  }
  #anime-bg::after {
    background: rgba(0,0,0,.22);
  }
  .col-xs-12.col-sm-10.col-md-8.col-lg-4.center-block {
    padding-left: 6px !important;
    padding-right: 6px !important;
  }
  .col-xs-12.col-sm-10.col-md-8.col-lg-4.center-block > br:first-child {
    display: none !important;
  }
  .block.block-link-hover3 {
    margin: 6px 4px 8px !important;
    border-radius: 14px !important;
    overflow: hidden !important;
  }
  .block-link-hover3 .block-content.bg-image {
    height: 92px !important;
    min-height: 92px !important;
    padding: 12px 0 !important;
    background-size: cover !important;
    background-position: center center !important;
  }
  .block-link-hover3 .img-avatar {
    width: 58px !important;
    height: 58px !important;
  }
  .block-link-hover3 .panel-body {
    padding: 12px 10px 10px !important;
  }
  .block-link-hover3 h3 {
    margin: 4px 0 8px !important;
    font-size: 20px !important;
    line-height: 1.3 !important;
  }
  .block-link-hover3 .panel-body > div {
    margin-bottom: 6px !important;
    line-height: 1.65 !important;
  }
  .widget-content.text-center {
    padding: 0 4px !important;
  }
  .widget-content.text-center > .btn-group-justified {
    gap: 7px !important;
    margin-bottom: 7px !important;
  }
  .widget-content.text-center > .btn-group-justified > .btn-group > a.nav-btn {
    height: 39px !important;
    min-height: 39px !important;
    padding: 0 7px !important;
    border-radius: 999px !important;
    font-size: 13px !important;
  }
  .widget-content.text-center > .btn-group-justified > .btn-group > a.nav-btn span {
    white-space: nowrap !important;
  }
  .block.animated.bounceInDown {
    margin: 8px 4px 0 !important;
    padding: 5px !important;
    border-radius: 16px !important;
    background-size: cover !important;
    background-position: center center !important;
  }
  .block > ul.nav.nav-tabs.btn.btn-block.q8-main-tabs,
  .block > ul.q8-main-tabs {
    display: flex !important;
    flex-wrap: nowrap !important;
    align-items: stretch !important;
    justify-content: stretch !important;
    gap: 4px !important;
    width: 100% !important;
    padding: 4px !important;
    margin: 0 0 8px !important;
    min-height: 46px !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
  }
  .block > ul.nav.nav-tabs.btn.btn-block.q8-main-tabs > li,
  .block > ul.q8-main-tabs > li {
    float: none !important;
    display: block !important;
    flex: 1 1 0 !important;
    width: auto !important;
    min-width: 0 !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 0 !important;
    position: static !important;
    transform: none !important;
  }
  .block > ul.nav.nav-tabs.btn.btn-block.q8-main-tabs > li.hide,
  .block > ul.nav.nav-tabs.btn.btn-block.q8-main-tabs > li.q8-tab-card,
  .block > ul.q8-main-tabs > li.hide,
  .block > ul.q8-main-tabs > li.q8-tab-card {
    display: none !important;
    flex: 0 0 0 !important;
    width: 0 !important;
  }
  .block > ul.nav.nav-tabs.btn.btn-block.q8-main-tabs > li.q8-tab-shop,
  .block > ul.nav.nav-tabs.btn.btn-block.q8-main-tabs > li.q8-tab-search,
  .block > ul.nav.nav-tabs.btn.btn-block.q8-main-tabs > li.q8-tab-profit,
  .block > ul.nav.nav-tabs.btn.btn-block.q8-main-tabs > li.q8-tab-gift,
  .block > ul.nav.nav-tabs.btn.btn-block.q8-main-tabs > li.q8-tab-more {
    display: block !important;
  }
  .block > ul.nav.nav-tabs.btn.btn-block.q8-main-tabs > li > a,
  .block > ul.nav.nav-tabs.btn.btn-block.q8-main-tabs > li.active > a,
  .block > ul.nav.nav-tabs.btn.btn-block.q8-main-tabs > li.active > a:hover,
  .block > ul.nav.nav-tabs.btn.btn-block.q8-main-tabs > li.active > a:focus,
  .block > ul.q8-main-tabs > li > a {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 100% !important;
    height: 38px !important;
    min-height: 38px !important;
    margin: 0 !important;
    padding: 0 2px !important;
    gap: 3px !important;
    border: 0 !important;
    border-radius: 13px !important;
    font-size: 12px !important;
    line-height: 1 !important;
    white-space: nowrap !important;
    box-sizing: border-box !important;
    transform: none !important;
  }
  .block-content.tab-content {
    padding: 8px 4px 4px !important;
  }
  #shop .form-group {
    margin-bottom: 9px !important;
  }
  #shop .input-group-addon {
    min-width: 84px !important;
    border-color: #dbeafe !important;
    background: #f7fbff !important;
    color: #475569 !important;
    font-weight: 800 !important;
  }
  #shop .form-control,
  #shop select.form-control,
  #shop textarea.form-control {
    min-height: 42px !important;
    border-color: #dbeafe !important;
    box-shadow: none !important;
  }
  #search .q8-query-card {
    padding: 13px !important;
    border-radius: 14px !important;
  }
  #search .q8-query-card h5 {
    margin-bottom: 11px !important;
    font-size: 15px !important;
  }
  #search .q8-query-card .input-group {
    display: flex !important;
    width: 100% !important;
    align-items: stretch !important;
  }
  #search .q8-query-card .input-group-btn:first-child {
    flex: 0 0 92px !important;
  }
  #search .q8-query-card #searchtype {
    width: 100% !important;
    height: 42px !important;
    border-color: #dbeafe !important;
    border-radius: 12px 0 0 12px !important;
    background: #f7fbff !important;
    color: #475569 !important;
    font-weight: 700 !important;
  }
  #search .q8-query-card #qq3 {
    flex: 1 1 auto !important;
    min-width: 0 !important;
    height: 42px !important;
    border-color: #dbeafe !important;
    box-shadow: none !important;
  }
  #search .q8-query-card .input-group-btn:last-child {
    flex: 0 0 42px !important;
  }
  #search .q8-query-card .input-group-btn:last-child .btn {
    width: 42px !important;
    height: 42px !important;
    padding: 0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-color: #dbeafe !important;
    border-radius: 0 12px 12px 0 !important;
    background: #f7fbff !important;
    color: #64748b !important;
  }
  #search .q8-query-card #submit_query {
    height: 44px !important;
    border-radius: 14px !important;
    font-size: 15px !important;
  }
  #more {
    margin: 0 -3px !important;
  }
  #more .col-xs-6 {
    padding-left: 5px !important;
    padding-right: 5px !important;
  }
  #more .block {
    margin-bottom: 10px !important;
    border-radius: 12px !important;
    overflow: hidden !important;
  }
  #more .block-content-full {
    min-height: 86px !important;
    padding: 14px 6px !important;
  }
  #more .fa-3x {
    font-size: 24px !important;
  }
  #more .font-w600 {
    margin-top: 8px !important;
    font-size: 13px !important;
  }
  .xhy-article-panel {
    margin: 10px 4px 10px !important;
    border-radius: 14px !important;
  }
  .xhy-article-head {
    padding: 12px !important;
    gap: 5px !important;
  }
  .xhy-article-title {
    font-size: 16px !important;
  }
  .xhy-article-sub {
    font-size: 12px !important;
    line-height: 1.5 !important;
  }
  .xhy-article-item {
    align-items: flex-start !important;
  }
  .xhy-fav-card {
    margin: 10px 4px 12px !important;
  }
}
@media (max-width: 370px) {
  .widget-content.text-center > .btn-group-justified:first-child {
    grid-template-columns: repeat(2,minmax(0,1fr)) !important;
  }
  .block > ul.q8-main-tabs > li > a {
    font-size: 11px !important;
  }
}

/* XHY query help */
.xhy-query-help{background:rgba(255,255,255,.96);border:1px solid #dbeafe;border-radius:14px;padding:13px 14px;margin-bottom:12px;box-shadow:0 10px 24px rgba(22,119,255,.10)}
.xhy-query-help-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px}
.xhy-query-help-title{display:flex;align-items:center;gap:8px;margin:0;color:#163b70;font-size:16px;font-weight:800}
.xhy-query-help-title i{width:30px;height:30px;border-radius:10px;background:linear-gradient(135deg,#1677ff,#20c5c8);color:#fff;display:inline-flex;align-items:center;justify-content:center}
.xhy-query-help-actions{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}
.xhy-query-help-btn{display:flex;align-items:center;justify-content:center;gap:5px;min-height:34px;padding:7px 8px;border:1px solid #dbeafe;border-radius:999px;background:#f7fbff;color:#1677ff!important;font-size:12px;font-weight:800;line-height:1.2;text-decoration:none!important;white-space:nowrap}
.xhy-query-help-btn.refund{color:#d95a26!important;background:#fff8ed;border-color:#ffd9b3}
@media(max-width:640px){.xhy-query-help{padding:12px;margin-bottom:10px}.xhy-query-help-head{display:block;margin-bottom:9px}.xhy-query-help-title{font-size:15px}.xhy-query-help-actions{grid-template-columns:repeat(3,minmax(0,1fr));gap:6px}.xhy-query-help-btn{min-height:32px;padding:6px 4px;font-size:11px}.xhy-query-help-btn i{font-size:11px}}

</style>
 <body>
<div id="anime-bg"></div>
<script>
function setXhyBgFallback(targetUrl){
    var bg = document.getElementById('anime-bg');
    if(!bg) return;
    var img = new Image();
    img.onload = function(){ bg.style.backgroundImage = "url('" + targetUrl + "')"; };
    img.onerror = function(){ bg.style.backgroundImage = "url('/template/XHY-01/bg-fallback.jpg')"; };
    img.src = targetUrl;
}
setTimeout(function(){ setXhyBgFallback('/template/XHY-01/bg-fallback.jpg'); }, 80);
</script>
	<!--弹出公告-->
	<!--Announcement Modal-->
	<style>
		#anounce .modal-dialog {
			margin-top: 7vh;
		}
		#anounce .modal-content {
			border: 0;
			border-radius: 14px;
			overflow: hidden;
			background: #FFF7F7;
			box-shadow: 0 18px 45px rgba(80, 18, 18, 0.28);
		}
		#anounce .xhy-announce-head {
			position: relative;
			padding: 22px 26px;
			color: #fff;
			background: linear-gradient(135deg, #8B0000 0%, #C94747 58%, #E18B8B 100%);
		}
		#anounce .xhy-announce-close {
			position: absolute;
			top: 14px;
			right: 16px;
			width: 34px;
			height: 34px;
			border: 1px solid rgba(255,255,255,0.45);
			border-radius: 50%;
			color: #fff;
			background: rgba(255,255,255,0.14);
			font-size: 22px;
			line-height: 30px;
			text-align: center;
			opacity: 1;
			text-shadow: none;
			transition: background .2s ease, transform .2s ease;
		}
		#anounce .xhy-announce-close:hover {
			background: rgba(255,255,255,0.24);
			transform: rotate(90deg);
		}
		#anounce .xhy-announce-title {
			display: flex;
			align-items: center;
			margin: 0;
			padding-right: 40px;
			font-size: 20px;
			font-weight: 700;
			letter-spacing: 0;
		}
		#anounce .xhy-announce-title i {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 38px;
			height: 38px;
			margin-right: 12px;
			border-radius: 50%;
			color: #8B0000;
			background: rgba(255,255,255,0.92);
			box-shadow: 0 6px 14px rgba(80,18,18,0.18);
		}
		#anounce .xhy-announce-subtitle {
			margin: 8px 0 0 50px;
			color: rgba(255,255,255,0.86);
			font-size: 13px;
		}
		#anounce .xhy-announce-body {
			padding: 22px 24px;
			background: linear-gradient(180deg, #FFF7F7 0%, #FFFFFF 100%);
		}
		#anounce .xhy-announce-content {
			max-height: 55vh;
			overflow-y: auto;
			padding: 18px 20px;
			border: 1px solid #F0C9C9;
			border-radius: 10px;
			color: #6F1515;
			background: #fff;
			line-height: 1.75;
			box-shadow: inset 0 1px 0 rgba(255,255,255,0.8);
			word-break: break-word;
		}
		#anounce .xhy-announce-content p:last-child {
			margin-bottom: 0;
		}
		#anounce .notice-polished {
			margin: -4px;
		}
		#anounce .notice-list {
			display: flex;
			flex-direction: column;
			gap: 10px;
		}
		#anounce .notice-item {
			display: flex;
			align-items: flex-start;
			gap: 12px;
			padding: 13px 14px;
			border: 1px solid #F0C9C9;
			border-radius: 10px;
			background: linear-gradient(180deg, #fff 0%, #FFF9F9 100%);
			box-shadow: 0 4px 12px rgba(139,0,0,0.06);
		}
		#anounce .notice-num {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			flex: 0 0 28px;
			width: 28px;
			height: 28px;
			border-radius: 50%;
			color: #fff;
			font-size: 13px;
			font-weight: 700;
			box-shadow: 0 4px 10px rgba(80,18,18,0.16);
		}
		#anounce .notice-text {
			flex: 1;
			min-width: 0;
			padding-top: 2px;
			color: #681515;
			font-size: 14px;
			line-height: 1.65;
		}
		#anounce .notice-text a {
			color: #8B0000;
			font-weight: 700;
			text-decoration: none;
			border-bottom: 1px dashed rgba(139,0,0,0.35);
		}
		#anounce .notice-red .notice-num { background: linear-gradient(135deg, #C83232, #F06C6C); }
		#anounce .notice-green .notice-num { background: linear-gradient(135deg, #1F8F62, #57C58B); }
		#anounce .notice-blue .notice-num { background: linear-gradient(135deg, #2B74B8, #68A8E8); }
		#anounce .notice-gold .notice-num { background: linear-gradient(135deg, #B7791F, #F0B84A); }
		#anounce .notice-purple .notice-num { background: linear-gradient(135deg, #7551B8, #B187EA); }
		#anounce .notice-actions {
			display: grid;
			grid-template-columns: repeat(3, minmax(0, 1fr));
			gap: 10px;
			margin-top: 14px;
		}
		#anounce .notice-action {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			min-height: 42px;
			padding: 9px 12px;
			border-radius: 999px;
			color: #fff;
			font-weight: 700;
			text-decoration: none;
			box-shadow: 0 6px 14px rgba(80,18,18,0.13);
			transition: transform .2s ease, box-shadow .2s ease;
		}
		#anounce .notice-action:hover {
			color: #fff;
			transform: translateY(-1px);
			box-shadow: 0 9px 18px rgba(80,18,18,0.18);
		}
		#anounce .notice-action i {
			margin-right: 7px;
		}
		#anounce .notice-action.tg { background: linear-gradient(135deg, #2383C4, #49B8F2); }
		#anounce .notice-action.qq { background: linear-gradient(135deg, #C88918, #F0B847); }
		#anounce .notice-action.supply { background: linear-gradient(135deg, #8B0000, #D65A5A); }
		#anounce .xhy-announce-footer {
			display: flex;
			justify-content: flex-end;
			padding: 14px 24px 22px;
			border-top: 0;
			background: #fff;
		}
		#anounce .xhy-announce-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			min-width: 116px;
			height: 40px;
			border: 0;
			border-radius: 999px;
			color: #fff;
			background: linear-gradient(135deg, #8B0000 0%, #C94747 100%);
			font-weight: 600;
			box-shadow: 0 6px 14px rgba(139,0,0,0.18);
			transition: transform .2s ease, box-shadow .2s ease;
		}
		#anounce .xhy-announce-btn:hover {
			color: #fff;
			transform: translateY(-1px);
			box-shadow: 0 9px 18px rgba(139,0,0,0.22);
		}
		#anounce .xhy-announce-btn i {
			margin-right: 7px;
		}
		@media (max-width: 768px) {
			#anounce .modal-dialog {
				width: auto;
				margin: 18px 12px;
			}
			#anounce .xhy-announce-head {
				padding: 18px 18px;
			}
			#anounce .xhy-announce-title {
				font-size: 18px;
			}
			#anounce .xhy-announce-title i {
				width: 34px;
				height: 34px;
				margin-right: 10px;
			}
			#anounce .xhy-announce-subtitle {
				margin-left: 44px;
			}
			#anounce .xhy-announce-body {
				padding: 16px;
			}
			#anounce .xhy-announce-content {
				max-height: 60vh;
				padding: 15px;
			}
			#anounce .xhy-announce-footer {
				padding: 0 16px 18px;
			}
			#anounce .xhy-announce-btn {
				width: 100%;
			}			#anounce .notice-item {
				padding: 12px;
			}
			#anounce .notice-actions {
				grid-template-columns: 1fr;
			}
			#anounce .notice-action {
				width: 100%;
			}
		}
	</style>
					<div class="modal fade" id="anounce" tabindex="-1" role="dialog" aria-labelledby="anounceLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="xhy-announce-head">
					<button type="button" class="xhy-announce-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="xhy-announce-title" id="anounceLabel">
						<i class="fa fa-bullhorn"></i>
						<span>&#24179;&#21488;&#20844;&#21578;</span>
					</h4>
					<div class="xhy-announce-subtitle">&#35831;&#20808;&#26597;&#30475;&#26368;&#26032;&#25552;&#37266;&#65292;&#20877;&#36827;&#34892;&#19979;&#21333;&#21672;&#35810;</div>
				</div>
				<div class="xhy-announce-body">
					<div class="xhy-announce-content">
						<?php echo $conf['anounce']?>
					</div>
				</div>
				<div class="xhy-announce-footer">
					<button type="button" class="xhy-announce-btn" data-dismiss="modal">
						<i class="fa fa-check-circle"></i> &#25105;&#30693;&#36947;&#20102;
					</button>
				</div>
			</div>
		</div>
	</div>
	<!--Announcement Modal End-->
	<!--Customer Service Modal-->
	<div id="lxkf" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000; background: white; border-radius: 15px; overflow: hidden; border: 2px solid #D8A0A0; box-shadow: 0 5px 20px rgba(139,0,0,0.1); max-width: 90%; width: 90%; max-height: 90vh; overflow-y: auto;">
	<style>
		/* 自适应样式 */
		@media (max-width: 768px) {
			#lxkf {
				width: 95% !important;
				border-radius: 10px !important;
			}
			#lxkf .block-content {
				padding: 15px !important;
			}
			#lxkf .modal-header {
				padding: 15px !important;
			}
			#lxkf .modal-footer {
				padding: 10px 15px !important;
			}
			#lxkf .media {
				flex-direction: column;
				text-align: center;
			}
			#lxkf .media .pull-left {
				margin-right: 0 !important;
				margin-bottom: 10px !important;
			}
			#lxkf .media .pull-left img {
				width: 60px !important;
				height: 60px !important;
			}
			#lxkf .xhy-service-actions {
				flex-direction: column;
			}
			#lxkf .xhy-service-btn {
				width: 100%;
			}
		}
		#lxkf .xhy-service-actions {
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
			margin-top: 14px;
		}
		#lxkf .xhy-service-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			flex: 1 1 150px;
			min-height: 40px;
			padding: 9px 16px;
			border-radius: 999px;
			font-size: 14px;
			font-weight: 600;
			text-decoration: none !important;
			transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
		}
		#lxkf .xhy-service-btn i {
			margin-right: 7px;
		}
		#lxkf .xhy-service-btn:hover {
			transform: translateY(-1px);
			box-shadow: 0 6px 16px rgba(139,0,0,0.18);
		}
		#lxkf .xhy-service-btn-online {
			color: #fff !important;
			background: linear-gradient(135deg, #8B0000 0%, #D95B5B 100%);
			box-shadow: 0 4px 12px rgba(139,0,0,0.16);
		}
</style>
	<!-- 标题部分 -->
	<div style="background: linear-gradient(135deg, #CD5C5C 0%, #8B0000 100%); padding: 20px 30px;">
		<button type="button" onclick="closeModal()" style="color: #fff; opacity: 0.9; text-shadow: none; margin-top: -10px; background: none; border: none; font-size: 28px; float: right;">
			<span aria-hidden="true">×</span>
		</button>
		<h4 style="color: #fff; letter-spacing: 2px; font-weight: 600; text-shadow: 0 2px 4px rgba(0,0,0,0.2); margin: 0;">
			<i class="fa fa-headphones fa-fw"></i> 客服与帮助
		</h4>
	</div>

	<!-- 内容区域 -->
	<div style="padding: 25px 30px; background: #FFE6E6; min-height: 200px;">
		<!-- 问题1 -->
		<div style="margin-bottom: 15px; border-radius: 10px; border: 1px solid #D8A0A0; box-shadow: 0 2px 6px rgba(139,0,0,0.1);">
			<div style="background: #FFF0F0; border-bottom: 1px solid #D8A0A0; border-radius: 10px 10px 0 0; padding: 15px 20px;">
				<h4 style="margin: 0;">
					<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" style="color: #8B0000; text-decoration: none; display: block; font-weight: 500;" aria-expanded="true">
						<i class="fa fa-question-circle" style="margin-right: 10px;"></i>
						购买的辅助导致账号封禁？
					</a>
				</h4>
			</div>
			<div id="collapseOne" class="panel-collapse collapse in" style="" aria-expanded="true">
				<div style="background: #FFF; border-radius: 0 0 10px 10px; padding: 20px;">
					<div style="color: #8B0000; line-height: 1.6;">
						<div style="display: flex; align-items: flex-start; margin-bottom: 12px;">
							<i class="fa fa-info-circle fa-fw" style="color: #8B0000; min-width: 20px; margin-top: 2px;"></i>
							<span>本站所有项目均由供货商提供，请按商品说明确认适用范围。</span>
						</div>
						<div style="display: flex; align-items: flex-start;">
							<i class="fa fa-exclamation-triangle fa-fw" style="color: #8B0000; min-width: 20px; margin-top: 2px;"></i>
							<span>下单前请确认商品规则，风险提示类商品请谨慎购买。</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- 问题2 -->
		<div style="margin-bottom: 15px; border-radius: 10px; border: 1px solid #D8A0A0; box-shadow: 0 2px 6px rgba(139,0,0,0.1);">
			<div style="background: #FFF0F0; border-bottom: 1px solid #D8A0A0; border-radius: 10px 10px 0 0; padding: 15px 20px;">
				<h4 style="margin: 0;">
					<a data-toggle="collapse" data-parent="#accordion" href="#collapseThree" class="" style="color: #8B0000; text-decoration: none; display: block; font-weight: 500;" aria-expanded="true">
						<i class="fa fa-question-circle" style="margin-right: 10px;"></i>
						买了的项目不会使用？
					</a>
				</h4>
			</div>
			<div id="collapseThree" class="panel-collapse collapse in" style="" aria-expanded="true">
				<div style="background: #FFF; border-radius: 0 0 10px 10px; padding: 20px;">
					<div style="color: #8B0000; line-height: 1.6;">
						<div style="display: flex; align-items: flex-start; margin-bottom: 12px;">
							<i class="fa fa-info-circle fa-fw" style="color: #8B0000; min-width: 20px; margin-top: 2px;"></i>
							<span>确认下载地址可正常访问。</span>
						</div>
						<div style="display: flex; align-items: flex-start;">
							<i class="fa fa-exclamation-triangle fa-fw" style="color: #8B0000; min-width: 20px; margin-top: 2px;"></i>
							<span>仔细阅读商品教程文档</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- 问题3 -->
		<div style="margin-bottom: 15px; border-radius: 10px; border: 1px solid #D8A0A0; box-shadow: 0 2px 6px rgba(139,0,0,0.1);">
			<div style="background: #FFF0F0; border-bottom: 1px solid #D8A0A0; border-radius: 10px 10px 0 0; padding: 15px 20px;">
				<h4 style="margin: 0;">
					<a data-toggle="collapse" data-parent="#accordion" href="#collapseFourth" class="" style="color: #8B0000; text-decoration: none; display: block; font-weight: 500;" aria-expanded="true">
						<i class="fa fa-question-circle" style="margin-right: 10px;"></i>
						人工客服的售后范围？
					</a>
				</h4>
			</div>
			<div id="collapseFourth" class="panel-collapse collapse in" style="" aria-expanded="true">
				<div style="background: #FFF; border-radius: 0 0 10px 10px; padding: 20px;">
					<div style="color: #8B0000; line-height: 1.6;">
						<i class="fa fa-check-circle" style="color: #8B0000; margin-right: 8px;"></i>
						处理项目无效果、下载地址失效等售后问题，并协助核对订单处理进度。
					</div>
				</div>
			</div>
		</div>

		<!-- 提示信息 -->
		<div style="background: #FFF0F0; border: 1px solid #D8A0A0; border-radius: 8px; padding: 15px; margin-bottom: 20px; color: #8B0000; font-weight: 500;">
			<i class="fa fa-exclamation-circle" style="margin-right: 8px;"></i>
			如遇到站长无法处理的问题，请点击下方联系平台人工客服解决。
		</div>

		<!-- 客服联系区域 -->
		<div style="background: #FFF0F0; border: 1px solid #D8A0A0; border-radius: 10px; padding: 20px; box-shadow: 0 2px 6px rgba(139,0,0,0.1);">
			<div style="display: flex; align-items: center;">
				<!-- 客服头像 -->
				<div style="margin-right: 15px;">
					<img src="/template/XHY-01/gif_lb.jpg" alt="客服头像" class="img-circle" style="width: 70px; height: 70px; border: 2px solid #D8A0A0;">
				</div>

				<!-- 客服信息 -->
				<div style="flex: 1;">
					<div style="color: #8B0000; font-size: 18px; font-weight: 600; margin-bottom: 5px;">
						岁岁云商城人工客服
					</div>
					<div style="color: #8B0000; margin-bottom: 10px;">
						<i class="fa fa-clock-o" style="margin-right: 5px;"></i>
						<b>在线时间：10:00 - 22:00</b>
					</div>
					<a href="/template/XHY-01/content.html" target="_blank" style="color: #8B0000; text-decoration: none;">
						<i class="fa fa-commenting"></i> 点击查看自助教程
					</a>
					<div class="xhy-service-actions">
						<a class="xhy-service-btn xhy-service-btn-online" href="javascript:void(0);" onclick="return openXhyOnlineChat(event);">
							<i class="fa fa-comments"></i> &#22312;&#32447;&#23458;&#26381;
						</a>
					</div>
				</div>


			</div>
		</div>
	</div>

	<!-- 底部 -->
	<div style="border-top: 1px solid #D8A0A0; padding: 15px 30px; background: #FFF0F0; text-align: right;">
		<button type="button" onclick="closeModal()" style="background: linear-gradient(45deg, #8B0000, #B22222); color: #fff; border: none; border-radius: 20px; padding: 8px 25px; transition: all 0.3s ease;">
			<i class="fa fa-check-circle"></i> 知道了
		</button>
	</div>
</div>

<!-- 遮罩层 -->
<div id="modalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999;"></div>

<!-- JavaScript -->
<script>
	// 显示弹窗
	function showModal() {
		document.getElementById('lxkf').style.display = 'block';
		document.getElementById('modalOverlay').style.display = 'block';
	}

	// 关闭弹窗
	function closeModal() {
		document.getElementById('lxkf').style.display = 'none';
		document.getElementById('modalOverlay').style.display = 'none';
	}

	// 点击遮罩层关闭弹窗
	document.getElementById('modalOverlay').onclick = closeModal;

	function openXhyOnlineChat(e) {
		if (e) e.preventDefault();
		closeModal();
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
			window.open("<?php echo site_contact_url($conf['kfqq']); ?>", "_blank");
		}
		return false;
	}

	// 修改客服链接的点击事件
	document.addEventListener('DOMContentLoaded', function() {
		var links = document.querySelectorAll('a[href="#lxkf"]');
		links.forEach(function(link) {
			link.removeAttribute('data-toggle');
			link.onclick = function(e) {
				e.preventDefault();
				showModal();
			};
		});
	});
</script>
	<div class="col-xs-12 col-sm-10 col-md-8 col-lg-4 center-block" style="float: none;">
		<br/>
		<!--顶部导航-->
		<div class="block block-link-hover3" href="javascript:void(0)">
			<div class="block-content block-content-full text-center bg-image" style="background-image: url('/template/XHY-01/bg-fallback.jpg');background-size: 100% 100%;">
				<div>
					<div>
						<img class="img-avatar img-avatar80 img-avatar-thumb animated zoomInDown"
						src="/assets/simple/img/head3.jpg">
					</div>
				</div>
			</div>
			<div class="panel-body text-center">
				<h3 style="margin: 10px 0;">
					<a href="javascript:void(alert('<?php echo $conf['sitename']?>，建议收藏本站，避免丢失访问地址。'));"><b><span style="color:#8B0000"><?php echo $conf['sitename']?></span></b></a>
				</h3>
				<div class="xhy-site-slogan" style="margin-bottom:15px;font-weight:700;color:#0f5fd7;line-height:1.7;">正品服务 · 稳定处理 · 售后无忧</div>
			</div>
		</div>
		<aside id="php_text-8" class="widget php_text wow fadelnUp" data-wow-delay="3.0s">
			<div class="textwidget widget-text">
				</table>
				</a>
				<!--主按钮组-->
				<div class="widget-content text-center">
					<div class="btn-group btn-group-justified" style="margin-bottom: 6px;">
						<!-- 平台公告 -->
<div class="btn-group">
								<a class="btn nav-btn" href="javascript:void(0);" onclick="$('#anounce').modal('show');">
									<i class="fa fa-bullhorn"></i>
									<span style="font-weight:600">平台公告</span>
								</a>
							</div>

						<!-- 供货商入口 -->
						<div class="btn-group">
							<a href="./sup" target="_blank" class="btn nav-btn">
								<i class="fa fa-shopping-cart"></i>
								<span style="font-weight:600">供货上架</span>
							</a>
						</div>

						<!-- 登录/注册 -->
						<div class="btn-group">
							<a class="btn nav-btn" href="./user/login.php" target="_blank">
								<i class="fa fa-users"></i>
								<span>登录后台</span>
							</a>
						</div>
					</div>

					<!-- 客服链接 -->
					<div class="btn-group btn-group-justified" style="margin: 6px 0;">
						<div class="btn-group">
							<a class="btn nav-btn" href="/toollogs.php" target="_blank">
								<i class="fa fa-comment"></i>
								<span style="font-weight:600">今日新上架项目</span>
							</a>
						</div>
						<div class="btn-group">
							<a class="btn nav-btn" href="#lxkf" data-toggle="modal">
								<i class="fa fa-comment"></i>
								<span style="font-weight:600">点我咨询100%处理</span>
							</a>
						</div>
					</div>
				</div>
				<!--主按钮组结束-->
				<!--logo下面按钮结束-->


<!--TAB标签-->

<!--TAB标签-->

				<!-- 查单说明开始 -->
										<div class="modal fade" align="left" id="cxsm" tabindex="-1" role="dialog"
				aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">
									<span aria-hidden="true">
										&times;
									</span>
									<span class="sr-only">
										Close
									</span>
								</button>
								<h4 class="modal-title" id="myModalLabel">
									查询内容是什么？该输入什么？
								</h4>
							</div>
							<li class="list-group-item">
								<span style="color:red">
									请在右侧的输入框内输入您下单时，在第一个输入框内填写的信息
								</span>
							</li>
							<li class="list-group-item">
								例如您购买的是QQ名片赞，输入下单的QQ账号即可查询订单
							</li>
							<li class="list-group-item">
								例如您购买的是邮箱类商品，需要输入您的邮箱号，输入 QQ 号通常查询不到。
							</li>
							<li class="list-group-item">
								例如您购买的是快手商品，需要输入作品链接里“userid=”后面的数字，输入快手号通常查询不到。
							</li>
							<li class="list-group-item">
								例如您购买的是全民 K 歌商品，需要输入歌曲链接里“shareuid=”后面、“&amp;”前面的一串英文数字，直接输入歌曲链接通常查询不到。
							</li>
							<li class="list-group-item">
								<span style="color:red">
									如果不清楚下单账号是什么，可以不填写，直接点击查询，则会根据浏览器缓存查询
								</span>
							</li>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">
									关闭
								</button>
							</div>
						</div>
					</div>
				</div>
				<!--查单说明结束-->
				<div class="block animated bounceInDown btn-rounded" style="border:1px solid #b3cde3; background: url(/template/XHY-01/bg-fallback.jpg);margin-top:0px;font-size:15px;padding:5px;border-radius:15px;background-color: white;">
					<ul class="nav nav-tabs btn btn-block animated fadeInUp btn-rounded q8-main-tabs" style="background-color: #FFE6E6;" data-toggle="tabs">
						<li class="active q8-tab-shop" style="width: 20%;" align="center">
							<a href="#shop" data-toggle="tab"><i class="fa fa-shopping-bag fa-fw"></i>&#19979;&#21333;</a>
						</li>
						<li class="q8-tab-search" style="width: 20%;" align="center">
							<a href="#search" data-toggle="tab" id="tab-query"><i class="fa fa-search"></i>&#26597;&#21333;</a>
						</li>
						<li class="q8-tab-profit" style="width: 20%;" align="center">
							<a href="#ktfz" data-toggle="tab"><i class="fa fa-coffee fa-fw"></i>&#36186;&#38065;</a>
						</li>
						<li class="q8-tab-gift" style="width: 20%;" align="center">
							<a href="#gift" data-toggle="tab"><i class="fa fa-gift fa-fw"></i>&#25277;&#22870;</a>
						</li>
						<li class="q8-tab-more" style="width: 20%;" align="center">
							<a href="#more" data-toggle="tab"><i class="fa fa-folder-open"></i>&#26356;&#22810;</a>
						</li>
					</ul>
				<!-- 添加警告信息和下单步骤 -->

					<!--TAB-->
					<div class="block-content tab-content">
						<!--在线下单-->
						<div class="tab-pane fade fade-up in active" id="shop">
							<?php include TEMPLATE_ROOT.'default/shop.inc.php'; ?>
						</div>
						<!--在线下单-->
						<!--查询订单-->
					<div class="tab-pane fade fade-up" id="search">
						<!-- 注文释意标题和按钮 -->
						<div class="xhy-query-help">
								<div class="xhy-query-help-head">
									<h4 class="xhy-query-help-title"><i class="fa fa-newspaper-o"></i><span>&#27880;&#25991;&#37322;&#20041;</span></h4>
								</div>
								<div class="xhy-query-help-actions">
									<a class="xhy-query-help-btn" href="javascript:void(alert('&#21487;&#33021;&#26159;&#24635;&#31449;&#25110;&#20379;&#36135;&#21830;&#24211;&#23384;&#19981;&#36275;&#20102;&#65292;&#31561;&#24453;&#21518;&#21488;&#34917;&#21345;&#25110;&#32852;&#31995;&#23458;&#26381;&#35299;&#20915;&#21363;&#21487;&#65281;'));"><i class="fa fa-clock-o"></i><span>&#24453;&#22788;&#29702;</span></a>
									<a class="xhy-query-help-btn refund" href="javascript:void(alert('&#26174;&#31034;&#24050;&#36864;&#27454;&#26159;&#36864;&#27454;&#21040;&#20320;&#20313;&#39069;&#65288;&#21487;&#25552;&#29616;&#65289;&#65292;&#22914;&#26524;&#27809;&#26377;&#27880;&#20876;&#21017;&#19981;&#21040;&#36134;&#65292;&#20808;&#27880;&#20876;&#36134;&#21495;&#20877;&#32852;&#31995;&#23458;&#26381;&#65281;'));"><i class="fa fa-undo"></i><span>&#24050;&#36864;&#27454;</span></a>
									<a class="xhy-query-help-btn refund" href="javascript:void(alert('&#22914;&#26524;&#20184;&#27454;&#20102;&#25214;&#19981;&#21040;&#35746;&#21333;&#35831;&#22312;&#19979;&#36793;&#25163;&#21160;&#26597;&#21333;&#65292;&#22914;&#26524;&#36824;&#26159;&#27809;&#26377;&#24102;&#19978;&#20184;&#27454;&#25130;&#22270;&#32852;&#31995;&#23458;&#26381;&#65281;'));"><i class="fa fa-search-minus"></i><span>&#26597;&#19981;&#21040;</span></a>
								</div>
							</div>

							<!-- 客服信息卡片 -->
							<div style="background: white;
						          border: 1px solid #D8A0A0;
					          border-radius: 8px;
					          padding: 15px;
					          margin-bottom: 15px;
					          box-shadow: 0 2px 6px rgba(139,0,0,0.1);">
							<div style="display: flex; align-items: center; gap: 15px;">
								<!-- 客服头像 -->
								<div>
									<a href="#lxkf" data-toggle="modal" style="display:inline-block; border:2px solid #FFCCCC; border-radius:50%; padding:3px;">
										<img src="/template/XHY-01/gif_lb.jpg" alt="客服头像" style="width:80px; height:80px; border-radius:50%;">
									</a>
								</div>

								<!-- 客服信息 -->
								<div style="flex: 1;">
								<h4 style="color: #8B0000; margin: 0 0 8px 0;">
									<i class="fa fa-home" style="margin-right: 8px;"></i>
									有任何问题可在右下角联系客服
								</h4>
									<div style="color: #8B0000; line-height: 1.6;">
										<div style="display: flex; align-items: center;">
									<i class="fa fa-comment" style="margin-right: 8px;"></i>
									<span>
										<span style="font-weight: 600;">人工客服</span>
										<span style="color: #8B0000;"> - 24H在线随时咨询</span>
										<small style="display: block; color: #8B0000;">点击头像查看教程</small>
									</span>
								</div>
									</div>
								</div>
							</div>
						</div>

						<!-- 查询订单教程 -->
						<div class="q8-query-card">
<h5 style="margin:0 0 10px;color:#1677ff;font-weight:800">订单查询</h5>
<div class="input-group" style="margin-bottom:10px"><div class="input-group-btn"><select class="form-control" id="searchtype" style="padding:6px 4px;width:90px"><option value="0">下单账号</option><option value="1">订单号</option></select></div><input type="text" name="qq" id="qq3" value="" class="form-control" placeholder="输入下单信息" onkeydown="if(event.keyCode==13){submit_query.click()}" required><span class="input-group-btn"><a tabindex="0" class="btn btn-default" role="button" data-container="body" data-toggle="popover" data-trigger="focus" data-placement="top" data-content="输入下单时填写的信息；不记得可留空查询浏览器缓存。"><i class="fa fa-info-circle"></i></a></span></div>
<input type="submit" id="submit_query" class="btn btn-primary btn-block btn-rounded" style="background:linear-gradient(135deg,#1677ff,#22c7c9);border:0;font-weight:800" value="立即查询">
<div id="result2" class="q8-query-list" style="display:none;margin-top:12px"><div id="list" class="q8-query-list"></div></div>
</div>
</div>
					<!--查询订单-->
						<!-- 开通分站 -->
				<div class="tab-pane fade fade-up" id="ktfz">
						<section class="xhy-earn-panel">
							<div class="xhy-earn-hero">
								<div class="xhy-earn-kicker"><i class="fa fa-line-chart"></i> <?php echo htmlspecialchars($q8XhyFenzhanNormalPriceText, ENT_QUOTES, 'UTF-8'); ?> &#20803;&#36215;&#24320;&#36890;&#20998;&#31449;</div>
								<h3 class="xhy-earn-title">&#25226;&#24179;&#21488;&#36164;&#28304;&#21464;&#25104;&#20320;&#33258;&#24049;&#30340;&#23567;&#24215;</h3>
								<p class="xhy-earn-sub">&#26080;&#38656;&#22788;&#29702;&#21457;&#36135;&#21644;&#24211;&#23384;&#65292;&#24320;&#22909;&#20998;&#31449;&#23601;&#33021;&#25512;&#24191;&#25509;&#21333;&#65292;&#20313;&#39069;&#25552;&#25104;&#21487;&#25552;&#29616;&#12290;</p>
								<div class="xhy-earn-stats">
									<div class="xhy-earn-stat"><strong>&#26222;&#21450;&#29256;</strong><span><?php echo htmlspecialchars($q8XhyFenzhanNormalPriceText, ENT_QUOTES, 'UTF-8'); ?> &#20803;&#20837;&#38376;</span></div>
									<div class="xhy-earn-stat"><strong>&#19987;&#19994;&#29256;</strong><span><?php echo htmlspecialchars($q8XhyFenzhanProfessionalPriceText, ENT_QUOTES, 'UTF-8'); ?> &#20803;&#21319;&#32423;</span></div>
									<div class="xhy-earn-stat"><strong><?php echo htmlspecialchars($q8XhyWithdrawMinText, ENT_QUOTES, 'UTF-8'); ?> &#20803;</strong><span>&#28385;&#39069;&#21487;&#25552;&#29616;</span></div>
								</div>
							</div>
							<div class="xhy-earn-body">
								<div class="xhy-earn-benefits">
									<div class="xhy-earn-benefit">
										<span class="xhy-earn-icon"><i class="fa fa-rocket"></i></span>
										<div><strong>&#19968;&#38190;&#24320;&#31449;</strong><span>&#33258;&#21161;&#24320;&#36890;&#65292;&#25317;&#26377;&#33258;&#24049;&#30340;&#21830;&#22478;&#38142;&#25509;&#12290;</span></div>
									</div>
									<div class="xhy-earn-benefit">
										<span class="xhy-earn-icon"><i class="fa fa-cubes"></i></span>
										<div><strong>&#24179;&#21488;&#36164;&#28304;&#20849;&#20139;</strong><span>&#19981;&#29992;&#22244;&#36135;&#65292;&#21830;&#21697;&#12289;&#25945;&#31243;&#12289;&#21457;&#36135;&#27969;&#31243;&#30452;&#25509;&#20351;&#29992;&#12290;</span></div>
									</div>
									<div class="xhy-earn-benefit">
										<span class="xhy-earn-icon"><i class="fa fa-money"></i></span>
										<div><strong>&#25552;&#29616;&#31616;&#21333;</strong><span>0 &#25163;&#32493;&#36153;&#65292;&#27599;&#26085;&#22266;&#23450;&#26102;&#38388;&#22788;&#29702;&#25171;&#27454;&#12290;</span></div>
									</div>
									<div class="xhy-earn-benefit">
										<span class="xhy-earn-icon"><i class="fa fa-users"></i></span>
										<div><strong>&#36866;&#21512;&#22810;&#31181;&#20154;&#32676;</strong><span>&#23398;&#29983;&#12289;&#19978;&#29677;&#26063;&#12289;&#21019;&#19994;&#32773;&#65292;&#37117;&#21487;&#20197;&#20316;&#20026;&#21103;&#19994;&#25512;&#24191;&#12290;</span></div>
									</div>
								</div>
								<div class="xhy-earn-highlight"><i class="fa fa-star"></i>&#29992;&#33258;&#24049;&#30340;&#20998;&#31449;&#25512;&#24191;&#25509;&#21333;&#65292;&#25104;&#20132;&#21518;&#33719;&#21462;&#20313;&#39069;&#25552;&#25104;&#65292;&#26085;&#31215;&#26376;&#32047;&#26356;&#31283;&#12290;</div>
								<div class="xhy-earn-actions">
									<a href="#userjs" data-toggle="modal" class="xhy-earn-btn xhy-earn-btn-info">
										<i class="fa fa-list-alt"></i> &#29256;&#26412;&#20171;&#32461;
									</a>
									<a href="user/regsite.php" target="_blank" class="xhy-earn-btn xhy-earn-btn-main">
										<i class="fa fa-arrow-right"></i> &#39532;&#19978;&#24320;&#36890;&#20998;&#31449;
									</a>
								</div>
							</div>
						</section>
					</div>
							<!-- gift module -->
														<div class="tab-pane fade fade-up" id="gift">
								<section class="xhy-gift-panel">
									<div class="xhy-gift-hero">
										<div class="xhy-gift-kicker"><i class="fa fa-gift"></i> &#27599;&#26085;&#22909;&#36816;&#25277;&#22870;</div>
										<h3 class="xhy-gift-title">&#35797;&#35797;&#20170;&#22825;&#30340;&#25163;&#27668;</h3>
										<p class="xhy-gift-sub">&#28857;&#20987;&#24320;&#22987;&#21518;&#20877;&#20572;&#27490;&#65292;&#20013;&#22870;&#32467;&#26524;&#20250;&#31435;&#21363;&#26174;&#31034;&#12290;&#31069;&#20320;&#25277;&#21040;&#22909;&#22870;&#21697;&#12290;</p>
									</div>
									<div class="xhy-gift-body">
										<div class="xhy-gift-stage">
											<div class="xhy-gift-icon"><i class="fa fa-diamond"></i></div>
											<div id="roll">&#28857;&#20987;&#19979;&#26041;&#25353;&#38062;&#24320;&#22987;&#25277;&#22870;</div>
											<div id="result" class="xhy-gift-result"></div>
										</div>
										<div class="xhy-gift-actions">
											<a class="xhy-gift-btn xhy-gift-btn-start" id="start" style="display:block;"><i class="fa fa-play"></i> &#24320;&#22987;&#25277;&#22870;</a>
											<a class="xhy-gift-btn xhy-gift-btn-stop" id="stop" style="display:none;"><i class="fa fa-stop"></i> &#20572;&#27490;&#25277;&#22870;</a>
										</div>
										<div class="giftlist xhy-gift-history" style="display:none;">
											<div class="xhy-gift-history-title"><i class="fa fa-trophy"></i> &#26368;&#36817;&#20013;&#22870;&#35760;&#24405;</div>
											<ul id="pst_1"></ul>
										</div>
									</div>
								</section>
							</div>

							<!-- 更多服务 -->
							<div class="tab-pane fade fade-right" id="more">
								<section class="xhy-more-panel">
									<div class="xhy-more-head">
										<h3 class="xhy-more-title"><i class="fa fa-folder-open"></i> &#26356;&#22810;&#26381;&#21153;</h3>
										<p class="xhy-more-sub">&#24120;&#29992;&#20837;&#21475;&#25918;&#22312;&#36825;&#37324;&#65292;&#19979;&#21333;&#12289;&#21806;&#21518;&#12289;&#20998;&#31449;&#31649;&#29702;&#37117;&#26356;&#22909;&#25214;&#12290;</p>
									</div>
									<div class="xhy-more-grid">
										<?php if(!empty($conf['appurl'])){?>
										<a class="xhy-more-item" href="<?php echo $conf['appurl'] ?>" target="_blank">
											<span class="xhy-more-icon"><i class="fa fa-cloud-download"></i></span>
											<span><span class="xhy-more-name">APP&#19979;&#36733;</span><span class="xhy-more-desc">&#25163;&#26426;&#31471;&#24555;&#36895;&#35775;&#38382;&#21644;&#19979;&#21333;</span></span>
										</a>
										<?php }?>
										<?php if(!empty($conf['daiguaurl'])){?>
										<a class="xhy-more-item" href="./?mod=daigua">
											<span class="xhy-more-icon"><i class="fa fa-circle-o"></i></span>
											<span><span class="xhy-more-name">QQ&#20195;&#25346;</span><span class="xhy-more-desc">&#20195;&#25346;&#26381;&#21153;&#20837;&#21475;</span></span>
										</a>
										<?php }?>
										<?php if(!empty($conf['invite_tid'])){?>
										<a class="xhy-more-item" href="./?mod=invite" target="_blank">
											<span class="xhy-more-icon"><i class="fa fa-paper-plane-o"></i></span>
											<span><span class="xhy-more-name">&#20813;&#36153;&#39046;&#36190;</span><span class="xhy-more-desc">&#27963;&#21160;&#31119;&#21033;&#20837;&#21475;</span></span>
										</a>
										<?php }?>
										<a class="xhy-more-item" href="#lxkf" data-toggle="modal">
											<span class="xhy-more-icon"><i class="fa fa-headphones"></i></span>
											<span><span class="xhy-more-name">&#21806;&#21518;&#23458;&#26381;</span><span class="xhy-more-desc">&#38382;&#39064;&#21672;&#35810;&#21644;&#24037;&#21333;&#22788;&#29702;</span></span>
										</a>
										<a class="xhy-more-item" href="/user/findpwd.php">
											<span class="xhy-more-icon"><i class="fa fa-key"></i></span>
											<span><span class="xhy-more-name">&#25214;&#22238;&#23494;&#30721;</span><span class="xhy-more-desc">&#24536;&#35760;&#23494;&#30721;&#21487;&#20197;&#22312;&#36825;&#37324;&#25214;&#22238;</span></span>
										</a>
										<a class="xhy-more-item" href="./user" target="_blank">
											<span class="xhy-more-icon"><i class="fa fa-user-circle"></i></span>
											<span><span class="xhy-more-name">&#20998;&#31449;&#30331;&#24405;</span><span class="xhy-more-desc">&#31649;&#29702;&#20998;&#31449;&#12289;&#35746;&#21333;&#21644;&#21830;&#21697;</span></span>
										</a>
									</div>
								</section>
							</div>
										</div>
				</div>
				<div class="modal fade xhy-site-modal" id="userjs" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog modal-dialog-popin">
						<div class="modal-content">
							<div class="xhy-site-modal-head">
								<button class="xhy-site-modal-close" data-dismiss="modal" type="button" aria-label="Close">&times;</button>
								<h4 class="xhy-site-modal-title"><i class="fa fa-list-alt"></i> &#29256;&#26412;&#20171;&#32461;</h4>
								<p class="xhy-site-modal-sub">&#26222;&#21450;&#29256;&#21644;&#19987;&#19994;&#29256;&#37117;&#21487;&#24320;&#36890;&#20998;&#31449;&#65292;&#19987;&#19994;&#29256;&#25552;&#20379;&#26356;&#22810;&#31649;&#29702;&#21644;&#25512;&#24191;&#33021;&#21147;&#12290;</p>
							</div>
							<div class="xhy-site-modal-body">
								<div class="xhy-version-grid">
									<div class="xhy-version-row xhy-version-head">
										<div>&#21151;&#33021;</div>
										<div class="xhy-version-cell">&#26222;&#21450;&#29256;</div>
										<div class="xhy-version-cell">&#19987;&#19994;&#29256;</div>
									</div>
									<div class="xhy-version-row"><div class="xhy-version-name">&#19987;&#23646;&#20195;&#21047;&#24179;&#21488;</div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div></div>
									<div class="xhy-version-row"><div class="xhy-version-name">&#19977;&#31181;&#22312;&#32447;&#25903;&#20184;&#25509;&#21475;</div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div></div>
									<div class="xhy-version-row"><div class="xhy-version-name">&#19987;&#23646;&#32593;&#31449;&#22495;&#21517;</div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div></div>
									<div class="xhy-version-row"><div class="xhy-version-name">&#36186;&#21462;&#29992;&#25143;&#25552;&#25104;</div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div></div>
									<div class="xhy-version-row"><div class="xhy-version-name">&#36186;&#21462;&#19979;&#32423;&#20998;&#31449;&#25552;&#25104;</div><div class="xhy-version-cell"><span class="xhy-version-no"><i class="fa fa-times"></i></span></div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div></div>
									<div class="xhy-version-row"><div class="xhy-version-name">&#35774;&#32622;&#21830;&#21697;&#20215;&#26684;</div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div></div>
									<div class="xhy-version-row"><div class="xhy-version-name">&#35774;&#32622;&#19979;&#32423;&#20998;&#31449;&#21830;&#21697;&#20215;&#26684;</div><div class="xhy-version-cell"><span class="xhy-version-no"><i class="fa fa-times"></i></span></div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div></div>
									<div class="xhy-version-row"><div class="xhy-version-name">&#25645;&#24314;&#19979;&#32423;&#20998;&#31449;</div><div class="xhy-version-cell"><span class="xhy-version-no"><i class="fa fa-times"></i></span></div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div></div>
									<div class="xhy-version-row"><div class="xhy-version-name">&#36192;&#36865;&#19987;&#23646;&#31934;&#33268; APP</div><div class="xhy-version-cell"><span class="xhy-version-no"><i class="fa fa-times"></i></span></div><div class="xhy-version-cell"><span class="xhy-version-ok"><i class="fa fa-check"></i></span></div></div>
								</div>
								<div class="xhy-site-modal-note"><i class="fa fa-lightbulb-o"></i> &#33258;&#24049;&#30340;&#25512;&#24191;&#33021;&#21147;&#20915;&#23450;&#25910;&#20837;&#65292;&#20808;&#24320;&#31449;&#65292;&#20877;&#24930;&#24930;&#31215;&#32047;&#23458;&#25143;&#12290;</div>
							</div>
							<div class="xhy-site-modal-footer">
								<button type="button" class="xhy-site-modal-btn xhy-site-modal-btn-close" data-dismiss="modal">&#20851;&#38381;</button>
								<a href="user/regsite.php" target="_blank" class="xhy-site-modal-btn xhy-site-modal-btn-main" style="display:flex;align-items:center;justify-content:center;text-decoration:none;">&#39532;&#19978;&#24320;&#36890;</a>
							</div>
						</div>
					</div>
				</div>
				<!--鐗堟湰浠嬬粛-->
														<div class="modal fade" align="left" id="about" tabindex="-1" role="dialog"
				aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">
									<span aria-hidden="true">
										&times;
									</span>
									<span class="sr-only">
										Close
									</span>
								</button>
								<h4 class="modal-title" id="myModalLabel">
									新手下单帮助
								</h4>
							</div>
							<div class="modal-body">
								<a href="javascript:void(0)" class="widget">
									<center>
										<strong>
											<span style="font-size:larger">
												客服 TG：
												<a href="<?php echo site_contact_url($conf['kfqq'])?>" target="_blank">
													@qqfaka
												</a>
											</span>
										</strong>
										<br />
										<strong>
											<span style="font-size:small">
												本站域名：<?php echo $_SERVER['HTTP_HOST']; ?>
											</span>
										</strong>
									</center>
									<center>
										<div id="demo-acc-faq" class="panel-group accordion">
											<div class="panel panel-trans pad-top">
												<a href="#demo-acc-faq1" class="text-semibold text-lg text-main collapsed"
												data-toggle="collapse" data-parent="#demo-acc-faq" aria-expanded="false">
													下单很久了都没有开始刷呢？
												</a>
												<div id="demo-acc-faq1" class="mar-ver collapse" aria-expanded="false"
												style="height: 0px;">
													本站采用自动订单处理，部分商品会因供货接口或库存波动延迟处理。超过 24 小时未处理请联系客服核查。
												</div>
											</div>
											<div class="panel panel-trans pad-top">
												<a href="#demo-acc-faq2" class="text-semibold text-lg text-main collapsed"
												data-toggle="collapse" data-parent="#demo-acc-faq" aria-expanded="false">
													空间类业务下单方法说明
												</a>
												<div id="demo-acc-faq2" class="mar-ver collapse" aria-expanded="false">
													1.下单前：空间必须允许访问，并按商品说明准备内容。
													<br>
													2.处理期间请勿关闭访问权限或删除内容，否则可能影响订单处理。
												</div>
											</div>
											<div class="panel panel-trans pad-top">
												<a href="#demo-acc-faq3" class="text-semibold text-lg text-main collapsed"
												data-toggle="collapse" data-parent="#demo-acc-faq" aria-expanded="false">
													空间说说赞相关下单方法说明
												</a>
												<div id="demo-acc-faq3" class="mar-ver collapse" aria-expanded="false">
													1.下单前：空间必须允许访问，并按商品说明准备内容。
													<br>
													2.在指定栏目填写下单账号或内容 ID，按页面提示提交即可。
													<br>
													3.处理期间请勿关闭访问权限或删除内容，否则可能影响订单处理。
												</div>
											</div>
											<div class="panel panel-trans pad-top">
												<a href="#demo-acc-faq4" class="text-semibold text-lg text-main collapsed"
												data-toggle="collapse" data-parent="#demo-acc-faq" aria-expanded="false">
													全民 K 歌业务类下单方法说明
												</a>
												<div id="demo-acc-faq4" class="mar-ver collapse" aria-expanded="false">
													1.打开全民 K 歌。
													<br>
													2.复制需要提交的歌曲链接。
													<br>
													3.例如：你歌曲链接是：
													<span style="color:#ff0000">
														https://kg.qq.com/node/play?s=
														<span style="color:green">
															881Zbk8aCfIwA8U3
														</span>
														&amp;g_f=personal
													</span>
													<br>
													4.然后把 s= 后面的
													<span style="color:green">
														881Zbk8aCfIwA8U3
													</span>
													链接填入到歌曲 ID 里面，然后提交购买。
												</div>
											</div>
											<div class="panel panel-trans pad-top">
												<a href="#demo-acc-faq5" class="text-semibold text-lg text-main collapsed"
												data-toggle="collapse" data-parent="#demo-acc-faq" aria-expanded="false">
													快手业务类下单方法说明
												</a>
												<div id="demo-acc-faq5" class="mar-ver collapse" aria-expanded="false">
													1.需要填写用户 ID 和作品 ID，例如：
													<span style="color:#ff0000">
														http://www.kuaishou.com/i/photo/lwx?userId=
														<span style="color:green">
															294200023
														</span>
														&amp;photoId=
														<span style="color:green">
															1071823418
														</span>
													</span>
													(分享作品就可以看到”复制链接”了)
													<br>
													2.用户ID就是
													<span style="color:green">
														294200023
													</span>
													作品ID就是
													<span style="color:green">
														1071823418
													</span>
													，然后在分别把用户ID和作品ID填上，请勿把两个选项填反了，不给予补单！
												</div>
											</div>
											<div class="panel panel-trans pad-top">
												<a href="#demo-acc-faq6" class="text-semibold text-lg text-main collapsed"
												data-toggle="collapse" data-parent="#demo-acc-faq" aria-expanded="false">
													会员类商品下单方法说明
												</a>
												<div id="demo-acc-faq6" class="mar-ver collapse" aria-expanded="false">
													1.下单之前，先确认输的信息是不是正确的!
													<br>
													2.Q会员/钻因为需要人工处理，所以每天不定时开刷，24小时-48小时内到账！
												</div>
											</div>
										</div>
									</center>
								</a>
							</div>
						</div>
					</div>
				</div>



				<?php
			// 确保文章列表总是显示
			$limit = intval($conf['articlenum']) > 0 ? intval($conf['articlenum']) : 5;
			$rs=$DB->query("SELECT id,title FROM " . DBQZ . "article WHERE active=1 ORDER BY top DESC,id DESC LIMIT {$limit}");
			$msgrow=array();
			while($res = $rs->fetch()){
				$msgrow[]=$res;
			}
			$class_arr = ['danger','warning','primary','success','info'];
			$i=0;
			// 如果没有文章，添加一个默认文章
	if(empty($msgrow)){
				$msgrow[] = array(
					'id' => 1,
					'title' => '平台购前必看教程 - 注意事项 - 新手必读 - 协议条约'
				);
			}
			?>
				<!--文章列表-->
				<div class="xhy-article-panel">
					<div class="xhy-article-head">
						<h4 class="xhy-article-title"><i class="fa fa-newspaper-o"></i>&#25991;&#31456;&#21015;&#34920;</h4>
						<div class="xhy-article-sub">&#19979;&#21333;&#21069;&#24314;&#35758;&#20808;&#30475;&#65292;&#35268;&#21017;&#21644;&#21806;&#21518;&#35828;&#26126;&#37117;&#22312;&#36825;&#37324;&#12290;</div>
					</div>
					<div class="xhy-article-list">
						<?php foreach($msgrow as $row){
							$title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
							echo '<a target="_blank" class="xhy-article-item" href="'.article_url($row['id']).'">
								<span class="xhy-article-num">'.($i+1).'</span>
								<span class="xhy-article-text">'.$title.'</span>
								<i class="fa fa-angle-right xhy-article-arrow"></i>
							</a>';
							$i++;
						}?>
					</div>
				</div>
				<div class="xhy-fav-card">
					<div class="xhy-fav-main">
						<div class="xhy-fav-icon"><i class="fa fa-heart"></i></div>
						<div>
							<div class="xhy-fav-title">&#26412;&#31449;&#32593;&#22336;&#65306;<?php echo $_SERVER['HTTP_HOST'];?></div>
							<div class="xhy-fav-host">&#24314;&#35758;&#25910;&#34255;&#65292;&#26041;&#20415;&#19979;&#27425;&#26597;&#35810;&#35746;&#21333;&#21644;&#25552;&#20132;&#21806;&#21518;&#12290;</div>
						</div>
					</div>
					<a class="xhy-fav-btn" href="javascript:void(0);" onclick="AddFavorite('\u8d27\u6e90\u603b\u7ad9',location.href)">
						<i class="fa fa-star"></i> &#31435;&#21363;&#25910;&#34255;
					</a>
				</div>
				<div class="text-center" style="color:#fff;margin-bottom:10px;"><?php echo $conf['footer']?></div>
			</div>
		</div>
	<script src="/assets/vendor/jquery.lazyload/1.9.1/jquery.lazyload.min.js?v=suisuivendor1"></script>
<!-- remove invalid music player script -->
	<script>
    function AddFavorite(title, url) {
  try {
      window.external.addFavorite(url, title);
  }
catch (e) {
     try {
       window.sidebar.addPanel(title, url, "");
    }
     catch (e) {
         alert("手机用户：点击底部菜单添加书签/收藏网址！\n\n电脑用户：请按 Ctrl+D 手动收藏本网址！");
     }
  }
}
</script>
<!-- 收藏代码结束-->

	</div>
	<!--音乐代码-->
	<!--音乐代码-->
	<div id="audio-play" <?php if(empty($conf['musicurl'])){?>style="display:none;"<?php }?>>
	  <div id="audio-btn" class="on" onclick="audio_init.changeClass(this,'media')">
	    <audio loop="loop" src="<?php echo $conf['musicurl']?>" id="media" preload="preload"> </audio>
	  </div>
	</div>
	<script src="<?php echo $cdnserver ?>assets/appui/js/app.js?v=<?php echo VERSION; ?>"></script>
	<script>
	// 确保文档加载完成后初始化tooltip
	$(document).ready(function() {
		// 初始化所有tooltip
		if (typeof $().tooltip !== 'undefined') {
			$('[data-toggle="tooltip"]').tooltip();
		} else {
			console.log('Tooltip functionality not available');
		}
	});
	</script>
	<script type="text/javascript">
		// 弹出公告控制
		$(document).ready(function() {
			if (isModal) {
				// 检查是否需要显示公告
				if (modalShowType == 0) {
					// 每次进网站都弹
					$('#anounce').modal('show');
				} else if (modalShowType == 1) {
					// 只弹一次
					var modalShown = localStorage.getItem('modal_shown');
					if (!modalShown) {
						$('#anounce').modal('show');
						localStorage.setItem('modal_shown', '1');
					}
				}
			}
		});
		$(function() {
   			if (typeof $.fn.lazyload !== 'undefined') {
       			$("img.lazy").lazyload({
           		effect: "fadeIn"
        		});
    		} else {
       			console.log('Lazyload functionality not available');
    		}
		});
		// 移除无效的计时脚本依赖
		var stimeElement = document.createElement('div');
		stimeElement.id = 'stime';
		stimeElement.style.display = 'none';
		document.body.appendChild(stimeElement);
		var ss = 0,
		    mm = 0,
		    hh = 0;

		function TimeGo() {
		    ss++;
		    if (ss >= 60) {
		        mm += 1;
		        ss = 0
		    }
		    if (mm >= 60) {
		        hh += 1;
		        mm = 0
		    }
		    ss_str = (ss < 10 ? "0" + ss : ss);
		    mm_str = (mm < 10 ? "0" + mm : mm);
		    tMsg = "" + hh + "小时" + mm_str + "分" + ss_str + "秒";
		    var stimeElement = document.getElementById("stime");
		    if (stimeElement) {
		        stimeElement.innerHTML = tMsg;
		    }
		    setTimeout("TimeGo()", 1000)
		}
		TimeGo();
$("#submit_buy").attr({'class':'btn btn-primary btn-block btn-rounded q8-submit-buy','style':'background: linear-gradient(135deg,#1677ff,#20c5c8); color: #fff; border: none;'});
	</script>
	<script>
	// 购买成功后自动显示订单详情弹窗
	$(document).ready(function(){
		// 使用纯JavaScript获取URL参数，避免混合PHP
		function getUrlParam(name) {
			var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
			var r = window.location.search.substr(1).match(reg);
			if (r != null) return decodeURIComponent(r[2]);
			return '';
		}

		if(getUrlParam('buyok') == '1'){
			// 直接从URL获取订单ID和skey参数
			var orderid = getUrlParam('orderid');
			var skey = getUrlParam('skey');

			// 如果有订单ID和skey参数，直接显示订单详情
			if(orderid && skey){
				showOrder(orderid, skey);
			}
			// 否则，为了保持兼容性，仍然通过查询获取最新订单
			else {
				var searchtype = getUrlParam('searchtype') || 1;
				var qq = getUrlParam('qq') || '';

				// 先检查 $_GET 变量的类型，确保正确处理
				if (typeof window.$_GET === 'function') {
					// 保存原始 $_GET 函数
					var original_GET = window.$_GET;
					// 创建一个临时的$_GET函数，移除buyok参数
					window.$_GET = function() {
						var url = window.document.location.href.toString();
						var u = url.split("?");
						if(typeof(u[1]) == "string"){
							u = u[1].split("&");
							var get = {};
							for(var i in u){
								var j = u[i].split("=");
								get[j[0]] = j[1];
							}
							// 关键修改：将 buyok 设置为 0，禁用 showOrder 调用
							get['buyok'] = 0;
							return get;
						} else {
							return {};
						}
					};
				}

				// 临时设置 querymode，禁用查询弹窗显示
				var tempQuerymode = window.querymode;
				window.querymode = 'noPopup';

				// 执行订单查询
				queryOrder(searchtype, qq, 1);

				// 恢复原始设置
				setTimeout(function() {
					window.querymode = tempQuerymode;
					if (typeof original_GET !== 'undefined') {
						window.$_GET = original_GET;
					}
				}, 100);
			}
		}
	});
	</script>


<script>
$(function(){
  var $userjsModal = $('#userjs');
  if ($userjsModal.length && !$userjsModal.parent().is('body')) {
    $userjsModal.appendTo(document.body);
  }
});
</script>
<script>
(function() {
	function q8EscapeHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
	function q8BuildRecommendOption(res) {
		return '<option value="'+res.tid+'" cid="'+res.cid+'" price="'+res.price+'" desc="'+escape(res.desc || '')+'" alert="'+escape(res.alert || '')+'" inputname="'+(res.input || '')+'" inputsname="'+(res.inputs || '')+'" multi="'+res.multi+'" isfaka="'+res.isfaka+'" count="'+res.value+'" close="'+res.close+'" prices="'+(res.prices || '')+'" max="'+res.max+'" min="'+res.min+'" stock="'+res.stock+'">'+q8EscapeHtml(res.name)+'</option>';
	}
	function q8SelectRecommendTool(cid, tid, pcid) {
		cid = parseInt(cid, 10) || 0;
		tid = parseInt(tid, 10) || 0;
		pcid = parseInt(pcid, 10) || cid;
		if (!cid || !tid) return;
		if ($('#subcid').length && typeof getPoint === 'function') {
			history.replaceState({}, null, './?cid='+cid+'&tid='+tid);
			$_GET['cid'] = cid;
			$_GET['tid'] = tid;
			$('#cid').val(pcid);
			$('#goodType').hide('normal');
			$('#goodTypeContent,#goodTypeContents').show('normal');
			$.getJSON('./ajax.php?act=getsubclass', {cid: pcid}, function(data) {
				if (data && data.code == 0) {
					$('#subcid').html(data.html || '<option value="0">请选择二级分类</option>');
					$('#subcid').val(cid);
					if ($('#subcid option').length > 1) $('#display_selectsubclass').show(); else $('#display_selectsubclass').hide();
				}
			});
			$.getJSON('./ajax.php?act=gettool', {tid: tid}, function(data) {
				if (data && data.code == 0 && data.data && data.data.length) {
					$('#tid').html(q8BuildRecommendOption(data.data[0])).val(tid);
					getPoint();
				}
			});
			return;
		}
		if (typeof toTool === 'function') {
			toTool(cid, tid);
		}
	}
	function q8LoadTodayRecommend() {
		if (!$('#q8TodayRecommend').length) return;
		$.ajax({
			type: 'GET',
			url: './ajax.php?act=gettodayrecommend&limit=8',
			dataType: 'json',
			success: function(data) {
				if (!data || data.code != 0 || !data.data || !data.data.length) return;
				var html = '';
				$.each(data.data, function(i, res) {
					var cid = parseInt(res.cid, 10) || 0;
					var tid = parseInt(res.tid, 10) || 0;
					var pcid = parseInt(res.pcid, 10) || cid;
					html += '<button type="button" class="q8-today-recommend__item" data-cid="'+cid+'" data-tid="'+tid+'" data-pcid="'+pcid+'">' +
						'<div class="q8-today-recommend__name">'+q8EscapeHtml(res.name)+'</div>' +
						'<div class="q8-today-recommend__meta"><span class="q8-today-recommend__price">&yen;'+q8EscapeHtml(res.price)+'</span><span class="q8-today-recommend__action">去下单</span></div>' +
					'</button>';
				});
				$('#q8TodayRecommendList').html(html);
				var $anchor = $('.custom-btn:visible').first();
				if ($anchor.length) $('#q8TodayRecommend').insertAfter($anchor);
				$('#q8TodayRecommend').removeClass('is-open').show();
				$('#q8TodayRecommend .q8-today-recommend__toggle').attr('aria-expanded', 'false').find('span').text('\u5c55\u5f00\u63a8\u8350');
			}
		});
	}
	$(document).ready(function() {
		q8LoadTodayRecommend();
		$(document).on('click', '.q8-today-recommend__toggle', function() {
			var $box = $(this).closest('.q8-today-recommend');
			var isOpen = !$box.hasClass('is-open');
			$box.toggleClass('is-open', isOpen);
			$(this).attr('aria-expanded', isOpen ? 'true' : 'false').find('span').text(isOpen ? '\u6536\u8d77\u63a8\u8350' : '\u5c55\u5f00\u63a8\u8350');
		});
		$(document).on('click', '.q8-today-recommend__item', function() {
			q8SelectRecommendTool($(this).data('cid'), $(this).data('tid'), $(this).data('pcid'));
		});
	});
})();
</script>

<script>
(function(){
	function q8ToolJumpOption(res){
		return '<option value="'+res.tid+'" cid="'+res.cid+'" price="'+res.price+'" desc="'+escape(res.desc || '')+'" alert="'+escape(res.alert || '')+'" inputname="'+(res.input || '')+'" inputsname="'+(res.inputs || '')+'" multi="'+res.multi+'" isfaka="'+res.isfaka+'" count="'+res.value+'" close="'+res.close+'" prices="'+(res.prices || '')+'" max="'+res.max+'" min="'+res.min+'" stock="'+res.stock+'">'+String(res.name || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;')+'</option>';
	}
	function q8EnforceUrlToolSelect(){
		var tid = parseInt((window.$_GET && window.$_GET.tid) || 0, 10);
		if (!tid || !$('#tid').length) return;
		$.getJSON('./ajax.php?act=gettool', {tid: tid}, function(data){
			if (!data || data.code != 0 || !data.data || !data.data.length) return;
			var res = data.data[0];
			if (res.cid) {
				$('#cid').val(res.cid);
				if (window.$_GET) window.$_GET.cid = String(res.cid);
			}
			if ($('#tid option[value="'+res.tid+'"]').length === 0) {
				$('#tid').append(q8ToolJumpOption(res));
			}
			$('#tid').val(String(res.tid));
			if (typeof getPoint === 'function') getPoint();
			$('#goodType').hide('normal');
			$('#goodTypeContent,#goodTypeContents').show('normal');
		});
	}
	$(window).on('load', function(){
		setTimeout(q8EnforceUrlToolSelect, 600);
		setTimeout(q8EnforceUrlToolSelect, 1800);
	});
})();
</script>

</body>
</html>
