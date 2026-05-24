<?php
/**
 * 自助下单
**/
include("../includes/common.php");
$title='自助下单';

// 先进行登录检查
if($islogin2==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");

// 再包含head.php
include './head.php';

$usershop = true;
$q8_chadan = isset($_GET['chadan']) && $_GET['chadan'] == '1';
// 确保siterow变量存在
if(!isset($siterow)) {
    $siterow = array('class' => '');
}
// 确保is_fenzhan变量存在
if(!isset($is_fenzhan)) {
    $is_fenzhan = false;
}
$addsalt=md5(mt_rand(0,999).time());
$_SESSION['addsalt']=$addsalt;
$x = new \lib\hieroglyphy();
$addsalt_js = $x->hieroglyphyString($addsalt);
?>
<link rel="stylesheet" href="./public/css/blue_theme.css?v=q8userclean12">
<style>
img.logo{width: 22px;margin: -2px 5px 0 5px;}
.onclick{cursor: pointer;touch-action: manipulation;}
.border-t{border-top: 1px solid #e9e9e9;}
.border-b{border-bottom: 1px solid #e9e9e9;}
.layui-fixbar{position:fixed;right:15px;bottom:15px;z-index:999999;margin:0;padding:0}
.layui-fixbar li{list-style:none;width:50px;height:50px;line-height:50px;margin-bottom:1px;text-align:center;cursor:pointer;font-size:30px;background-color:#9F9F9F;color:#fff;border-radius:2px;opacity:.95}
.nav-counter{position:absolute;font-size:16px;top:-1px;right:1px;height:20px;width:20px;line-height:20px;padding:0 6px;color:#fff;text-align:center;background:#e23442;border-radius:50%;background-image:-webkit-linear-gradient(top,#e8616c,#dd202f);background-image:-moz-linear-gradient(top,#e8616c,#dd202f);background-image:-o-linear-gradient(top,#e8616c,#dd202f);background-image:linear-gradient(to bottom,#e8616c,#dd202f);-webkit-box-shadow:inset 0 0 1px 1px rgba(255,255,255,.1),0 1px rgba(0,0,0,.12);box-shadow:inset 0 0 1px 1px rgba(255,255,255,.1),0 1px rgba(0,0,0,.12)}
#list td{max-width:280px;text-overflow: ellipsis;overflow: hidden;white-space: nowrap;}
#list2 td{max-width:280px;text-overflow: ellipsis;overflow: hidden;white-space: nowrap;}
#orderItem .orderTitle{word-break:keep-all;}
#orderItem .orderContent{word-break:break-all;}
#orderItem .orderContent img{max-width:100%}
#alert_frame img{max-width:100%}






/* Q8 clean user shop query layout */
.q8-user-query-panel:not(.active){display:none!important}
.q8-user-query-panel.active{display:grid!important;gap:14px}
.q8-user-query-panel .form-group{margin:0}
.q8-user-query-panel .input-group{width:100%;display:flex;border:1px solid #dbe7f7;border-radius:12px;overflow:hidden;background:#fff;box-shadow:0 8px 22px rgba(30,64,175,.06)}
.q8-user-query-panel .input-group-addon{flex:0 0 92px;display:flex;align-items:center;justify-content:center;border:0;font-weight:800;color:#1d4ed8;background:#eef6ff}
.q8-user-query-panel .form-control{height:44px;min-width:0;border:0;box-shadow:none;background:#fff}
.q8-shop-search-btn{width:46px;min-width:46px;padding:0!important;border-left:0!important;color:#1677ff!important;background:#eef6ff!important;display:table-cell!important;text-align:center!important;vertical-align:middle!important;cursor:pointer}.q8-shop-search-btn .fa{font-size:15px;line-height:1}
#submit_query.q8-user-query-btn{height:44px;border:0;border-radius:12px;background:linear-gradient(135deg,#1677ff,#22c4c8);color:#fff;font-weight:900;letter-spacing:0;box-shadow:0 12px 26px rgba(22,119,255,.20);display:flex!important;align-items:center;justify-content:center;width:100%!important;padding:0 16px;text-align:center}
.q8-user-buy-btn{height:44px;border:0;border-radius:12px;background:linear-gradient(135deg,#1677ff,#22c4c8);color:#fff;font-weight:900;letter-spacing:0;box-shadow:0 12px 26px rgba(22,119,255,.20);display:flex;align-items:center;justify-content:center;width:100%;padding:0 16px;text-align:center;line-height:44px;text-decoration:none}
.q8-user-buy-btn:hover,.q8-user-buy-btn:focus{color:#fff;text-decoration:none;filter:brightness(1.03)}
#result2{width:100%;max-width:100%;overflow:hidden}
#result2 center{display:none}
#result2 .table-responsive{width:100%;max-width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;border:0;border-radius:14px;background:#fff;box-shadow:0 10px 24px rgba(15,23,42,.06)}
#result2 table{margin:0}
#result2 .btn,.q8-query-popup .btn{min-width:72px;height:30px;display:inline-flex!important;align-items:center;justify-content:center;border-radius:999px!important;font-weight:800;white-space:nowrap;padding:0 12px!important;line-height:1!important}
.q8-query-popup{padding:14px;background:#f7fbff}
.q8-query-popup .table-responsive{border-radius:14px;background:#fff;box-shadow:0 10px 26px rgba(15,23,42,.08);overflow:auto}
.q8-query-popup table{margin:0}
.q8-query-popup thead th{background:#f1f7ff;color:#1e40af;border-bottom:1px solid #dbeafe!important;font-weight:800;white-space:nowrap}
.q8-query-popup tbody td{vertical-align:middle!important;border-top:1px solid #eef2f7!important}
.q8-query-detail-btn{background:#1677ff!important;border-color:#1677ff!important;color:#fff!important;box-shadow:0 8px 18px rgba(22,119,255,.18)}
.q8-query-mini-btn{height:24px!important;min-width:46px!important;font-size:12px!important}
.q8-query-page-row td{text-align:center!important;background:#f8fbff!important}
.q8-query-page-btn{margin:4px 6px!important}
.q8-refund-status{color:#dc2626;font-weight:800}
.q8-query-error-row td{background:#fff1f2!important;color:#be123c!important;font-weight:700}
#orderItem{margin:0;border-collapse:separate;border-spacing:0 8px;background:#f7fbff;padding:10px}
#orderItem td{border:0!important;background:#fff!important;vertical-align:middle!important}
#orderItem .orderTitle{width:118px;color:#1d4ed8;font-weight:900;white-space:nowrap;background:#eef6ff!important;border-radius:10px 0 0 10px}
#orderItem .orderContent{color:#111827;word-break:break-word;border-radius:0 10px 10px 0}
.q8-order-modal-actions{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.q8-order-modal-actions .btn{min-width:116px;height:34px;display:inline-flex!important;align-items:center;justify-content:center;border-radius:999px!important;font-weight:800;margin:0!important}
.q8-kminfo-box{padding:12px;border:1px dashed #93c5fd;border-radius:10px;background:#f8fbff;color:#111827;line-height:1.8;white-space:pre-wrap;word-break:break-word;margin-bottom:10px}
.q8-copy-kminfo{border:0!important;border-radius:999px!important;height:34px!important;min-width:128px!important;background:linear-gradient(135deg,#1677ff,#22c4c8)!important;font-weight:900!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;line-height:1!important;padding:0 18px!important}
.layui-layer.q8-query-layer,.layui-layer.layui-layer-rim{border:0!important;border-radius:14px!important;overflow:hidden!important;box-shadow:0 24px 60px rgba(15,23,42,.22)!important;background:#fff!important}
.layui-layer.q8-query-layer .layui-layer-title,.layui-layer.layui-layer-rim .layui-layer-title{height:48px!important;line-height:48px!important;border:0!important;background:linear-gradient(135deg,#1677ff,#22c4c8)!important;color:#fff!important;font-weight:900!important;padding-left:18px!important}
.layui-layer-page .layui-layer-content{max-height:calc(100vh - 120px);overflow:auto!important;-webkit-overflow-scrolling:touch;background:#f7fbff}
@media (max-width:768px){
  .wrapper{padding:10px!important}
  .col-sm-12,.col-md-8,.col-lg-6{padding-left:0!important;padding-right:0!important}
  .nav-tabs-alt .modal-body{padding:12px}
  .q8-user-query-panel.active{gap:12px}
  .q8-user-query-panel .input-group-addon{flex:0 0 82px;font-size:13px}
  .q8-user-query-panel .form-control{font-size:13px}
  #submit_query.q8-user-query-btn{height:46px;border-radius:13px;font-size:15px}
  #result2 .table-responsive{overflow:visible;box-shadow:none;background:transparent}
  #result2 table,#result2 thead,#result2 tbody,#result2 tr,#result2 td{display:block;width:100%!important}
  #result2 table{min-width:0!important;background:transparent}
  #result2 thead{display:none!important}
  #result2 tr.q8-query-row{box-sizing:border-box;margin:0 auto 12px!important;padding:12px!important;width:100%!important;max-width:100%!important;border:1px solid #dbeafe;border-radius:14px;background:#fff;box-shadow:0 10px 22px rgba(15,23,42,.06)}
  #result2 tr.q8-query-row td{box-sizing:border-box;display:grid;grid-template-columns:74px minmax(0,1fr);gap:8px;align-items:center;width:100%!important;max-width:100%!important;border:0!important;padding:7px 0!important;white-space:normal!important;word-break:break-word;background:transparent!important}
  #result2 tr.q8-query-row td:before{content:attr(data-label);color:#64748b;font-size:12px;font-weight:800}
  #result2 tr.q8-query-row td.hidden-xs{display:grid!important}
  #result2 tr.q8-query-row td:last-child{grid-template-columns:1fr;padding-top:10px!important}
  #result2 tr.q8-query-row td:last-child:before{display:none}
  #result2 .q8-query-detail-btn{width:100%!important;height:36px!important;font-size:13px!important;margin:0!important}
  #result2 tr.q8-query-error-row{display:block;margin:-6px 0 12px;padding:10px 12px;border-radius:12px;background:#fff1f2;color:#be123c}
  #result2 tr.q8-query-error-row td{border:0!important;background:transparent!important}
  #result2 tr.q8-query-page-row td{display:flex!important;gap:8px;justify-content:center;background:transparent!important;border:0!important}
  #orderItem{padding:8px;border-spacing:0 7px}
  #orderItem tbody,#orderItem tr,#orderItem td{display:block;width:100%!important}
  #orderItem .orderTitle{border-radius:10px 10px 0 0;padding:8px 10px!important}
  #orderItem .orderContent{border-radius:0 0 10px 10px;padding:10px!important}
  .q8-order-modal-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px}
  .q8-order-modal-actions .btn{width:100%!important;min-width:0}
  .layui-layer{max-width:94vw!important}
  .layui-layer-page .layui-layer-content{max-height:calc(100vh - 96px)!important}
}

</style>
<div class="wrapper">
	<div class="col-sm-12 col-md-8 col-lg-6 center-block" style="float: none;">
		<div class="panel panel">
			<div class="panel-heading font-bold">
				自助下单
				<span class="pull-right">
					余额：<?php echo $userrow['rmb']?>元
				</span>
			</div>
			<div class="nav-tabs-alt">
				<ul class="nav nav-tabs nav-justified">
					<li class="<?php echo $q8_chadan?'':'active'; ?>">
							<a href="#onlinebuy" data-toggle="tab">
							在线下单
						</a>
					</li>
					<li class="<?php echo $q8_chadan?'active':''; ?>">
						<a href="#query" data-toggle="tab" id="tab-query">
							查询订单
						</a>
					</li>
				</ul>
				<div class="modal-body">
					<div id="myTabContent" class="tab-content">
						<div class="tab-pane fade <?php echo $q8_chadan?'':'in active'; ?>" id="onlinebuy">
							<?php include 'shop.inc.php'; ?>
						</div>
						<div class="tab-pane fade <?php echo $q8_chadan?'in active':''; ?> q8-user-query-panel" id="query">
							<?php if(!empty(trim($conf['gg_search']))){ ?>
								<ul class="list-group animated bounceIn">
									<li class="list-group-item">
										<?php echo $conf['gg_search']?>
									</li>
								</ul>
								<?php } ?>
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-addon">
										查询内容
									</div>
									<input type="text" name="qq" id="qq3" value="" class="form-control" placeholder="请输入下单账号（留空则根据浏览器缓存查询）"
									required="">
								</div>
							</div>
							<input type="submit" id="submit_query" class="btn btn-primary btn-block q8-user-query-btn"
							value="立即查询">
							<div id="result2" style="display:none;">
							<center><small><font color="#ff0000">手机用户可以左右滑动</font></small></center>
								<div class="table-responsive">
									<table class="table table-striped">
										<thead>
											<tr>
												<th>
													账号
												</th>
												<th>
													名称
												</th>
												<th>
													份数
												</th>
												<th class="hidden-xs">
													时间
												</th>
												<th>
													状态
												</th>
												<th>
													操作
												</th>
											</tr>
										</thead>
										<tbody id="list">
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php include './foot.php';?>
<script src="<?php echo $cdnpublic?>jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
<script src="<?php echo $cdnpublic?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script type="text/javascript">
var isModal=false;
var homepage=false;
var hashsalt=<?php echo $addsalt_js?>;
$(function() {
	$("img.lazy").lazyload({effect: "fadeIn"});
<?php if($conf['shoppingcart']==1){?>
$.ajax({
	type : "GET",
	url : "../ajax.php?act=cart_info",
	dataType : 'json',
	async: true,
	success : function(data) {
		if(data.count != null && data.count>0){
			$('#cart_count').html(data.count);
			$('#alert_cart').show();
		}
	}
});
<?php }?>
});
</script>
<script src="../assets/js/usershop.js?ver=<?php echo VERSION ?>&v=q8todayrecommend5"></script>
</body>
</html>
