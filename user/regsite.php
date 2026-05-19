<?php
/**
 * 自助开通分站
**/
$is_defend=true;
include("../includes/common.php");
if($islogin2==1 && $userrow['power']>0){
	@header('Content-Type: text/html; charset=UTF-8');
	exit("<script language='javascript'>alert('您已开通过分站！');window.location.href='./';</script>");
}elseif($conf['fenzhan_buy']==0){
	@header('Content-Type: text/html; charset=UTF-8');
	exit("<script language='javascript'>alert('当前站点未开启自助开通分站功能！');window.location.href='./';</script>");
}

$q8FenzhanPricing = q8_get_fenzhan_price_context($conf, $is_fenzhan, isset($siterow) ? $siterow : array());
$conf['fenzhan_price'] = $q8FenzhanPricing['normal_price'];
$conf['fenzhan_price2'] = $q8FenzhanPricing['professional_price'];
$conf['fenzhan_cost2'] = $q8FenzhanPricing['professional_cost'];
$title='自助开通分站';
include './head2.php';

$addsalt=md5(mt_rand(0,999).time());
$_SESSION['addsalt']=$addsalt;
$x = new \lib\hieroglyphy();
$addsalt_js = $x->hieroglyphyString($addsalt);

$kind = isset($_GET['kind']) ? intval($_GET['kind']) : 1;
if ($kind !== 2) {
	$kind = 1;
}
$q8NormalPriceText = q8_format_currency_amount($conf['fenzhan_price']);
$q8ProfessionalPriceText = q8_format_currency_amount($conf['fenzhan_price2']);

if($is_fenzhan == true && $siterow['power']==2 && !empty($siterow['ktfz_domain'])){
	$domains=explode(',',$siterow['ktfz_domain']);
}else{
	$domains=explode(',',$conf['fenzhan_domain']);
}
$select='';
foreach($domains as $domain){
	$select.='<option value="'.$domain.'">'.$domain.'</option>';
}
if(empty($select))showmsg('请先到后台分站设置，填写可选分站域名',3);
?>
<?php if(false && $background_image){?>
<img src="<?php echo $background_image;?>" alt="Full Background" class="full-bg full-bg-bottom animation-pulseSlow" ondragstart="return false;" oncontextmenu="return false;">
<?php }?>

<style>
html,body{min-height:100%;}
body{background:#eef5ff url("../template/XHY-01/bg-fallback.jpg") center center/cover fixed no-repeat!important;}
body:before{content:"";position:fixed;inset:0;z-index:-1;background:rgba(255,255,255,.18);backdrop-filter:blur(4px);}
body>.full-bg{display:none!important;}
.q8-regsite-page{float:none!important;width:min(960px,calc(100% - 28px));min-height:100vh;margin:0 auto;padding:28px 0!important;display:flex;align-items:center;}
.q8-regsite-card{width:100%;overflow:hidden;border:1px solid rgba(73,132,230,.18);border-radius:14px;background:#fff;box-shadow:0 18px 45px rgba(31,78,142,.16);}
.q8-regsite-card .widget-content{position:relative;min-height:122px;padding:22px 24px 20px!important;background-image:linear-gradient(135deg,#1d7cf2 0%,#36b6ff 52%,#24c7a8 100%)!important;background-size:cover!important;}
.q8-regsite-card .widget-content:after{content:"";position:absolute;right:-80px;top:-90px;width:230px;height:230px;border:38px solid rgba(255,255,255,.16);border-radius:50%;}
.q8-regsite-card .widget-content img{position:relative;z-index:1;width:66px;height:66px;object-fit:cover;border:3px solid rgba(255,255,255,.8);background:#fff;box-shadow:0 10px 24px rgba(0,0,0,.16);}
.q8-regsite-card .widget-content p{display:none;}
.q8-regsite-card .block{margin:0;padding:22px 26px 26px;background:#fff;}
.q8-regsite-card .block-title{display:grid!important;grid-template-columns:1fr 1fr;align-items:stretch!important;gap:12px;min-height:auto;margin:0 0 18px!important;padding:0 0 18px!important;border-bottom:1px solid #e8eef8;}
.q8-regsite-card .block-title h2,.q8-regsite-card .block-options{width:100%;margin:0!important;}
.q8-regsite-card .block-title h2,.q8-regsite-card .block-options .btn{display:flex;align-items:center;justify-content:center;min-height:42px;border:1px solid #cfe0f5!important;border-radius:999px;background:#f8fbff!important;color:#214b78!important;box-shadow:0 6px 18px rgba(39,97,169,.06);line-height:1.2!important;text-align:center;}
.q8-regsite-card .block-title h2{gap:6px;font-size:15px!important;font-weight:800;}
.q8-regsite-card .block-title h2 b{font-weight:800;}
.q8-regsite-card .block-title h2 .fa{color:#1979e8!important;}
.q8-regsite-card .block-options{position:static;float:none!important;}
.q8-regsite-card .block-options .btn{width:100%;padding:0 16px;font-weight:700;}
.q8-regsite-card .block-options .btn:hover,.q8-regsite-card .block-options .btn:focus,.q8-regsite-card .block-options .btn:active{border-color:#b8d3f4!important;background:#edf6ff!important;color:#1769c7!important;}
.q8-regsite-card .row.text-center{display:flex;gap:12px;margin:0 0 20px;}
.q8-regsite-card .row.text-center>div{width:50%;padding:0;}
.q8-regsite-card .row.text-center .btn{display:flex;align-items:center;justify-content:center;min-height:42px;border:1px solid #cfe0f5!important;border-radius:10px;background:#edf6ff!important;color:#1769c7!important;font-weight:800;line-height:1.2!important;box-shadow:inset 0 0 0 1px rgba(29,124,242,.12);}
.q8-regsite-card .row.text-center .btn:hover,.q8-regsite-card .row.text-center .btn:focus,.q8-regsite-card .row.text-center .btn:active{color:#0f5dab!important;background:#e5f1ff!important;box-shadow:inset 0 0 0 1px rgba(29,124,242,.22)!important;}
.q8-regsite-card form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 16px;align-items:start;}
.q8-regsite-card .form-group{margin:0;}
.q8-regsite-card .q8-wide-field{grid-column:1/-1;}
.q8-regsite-card .form-group small{display:block;margin-top:7px;color:#6d7e97!important;line-height:1.55;}
.q8-regsite-card .input-group{display:table!important;width:100%!important;table-layout:fixed;overflow:hidden;border:1px solid #dfe8f5;border-radius:11px;background:#f9fbff;border-collapse:separate;box-shadow:0 8px 20px rgba(38,91,159,.05);}
.q8-regsite-card .input-group .input-group{display:table!important;width:100%!important;table-layout:auto;border:0;border-radius:0;box-shadow:none;background:transparent;}
.q8-regsite-card .input-group-addon{display:table-cell!important;float:none!important;width:94px!important;min-width:94px!important;height:44px;padding:0 10px;border:0;border-right:1px solid #e6edf7;border-radius:11px 0 0 11px;background:#f1f6fd;color:#123b63!important;font-size:14px;font-weight:800;line-height:1.25!important;vertical-align:middle!important;white-space:nowrap;}
.q8-regsite-card .form-control{display:table-cell!important;float:none!important;width:100%!important;height:44px!important;padding:10px 12px;border:0;border-radius:0 11px 11px 0;background:transparent;color:#1c2e43!important;font-size:14px;line-height:24px!important;vertical-align:middle!important;box-shadow:none;}
.q8-regsite-card .form-control::placeholder{color:#9aa9bc;}
.q8-regsite-card select.form-control{cursor:pointer;}
.q8-regsite-card [name="domain"]{display:block!important;width:100%!important;margin-top:10px!important;border:1px solid #dfe8f5!important;border-radius:11px!important;background:#f9fbff!important;}
.q8-regsite-card .input-group-btn{display:table-cell!important;width:64px;vertical-align:middle;}
.q8-regsite-card .input-group-btn .btn{display:flex;align-items:center;justify-content:center;width:64px;height:44px;padding:0;border:0;border-left:1px solid #e6edf7;border-radius:0 11px 11px 0;background:#edf6ff!important;color:#1769c7!important;font-weight:800;line-height:1.2!important;}
.q8-regsite-card #submit_buy{grid-column:1/-1;height:48px;margin-top:2px!important;border:0;border-radius:12px;background:linear-gradient(135deg,#ff4f67,#ff8a34);color:#fff;font-size:16px;font-weight:900;box-shadow:0 14px 24px rgba(255,100,73,.28);}
.q8-regsite-card hr{grid-column:1/-1;width:100%;margin:2px 0 0;border-color:#edf2f8;}
.q8-regsite-card form>.q8-regsite-actions{grid-column:1/-1!important;display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:14px!important;align-items:center!important;margin:0!important;}
.q8-regsite-card .q8-action-link,.q8-regsite-card .q8-action-link:link,.q8-regsite-card .q8-action-link:visited{float:none!important;display:flex!important;align-items:center!important;justify-content:center!important;gap:6px!important;width:100%!important;min-width:0!important;height:40px!important;margin:0!important;padding:0 14px!important;border:0!important;border-radius:999px!important;color:#fff!important;font-size:14px!important;font-weight:800!important;line-height:1!important;text-align:center!important;white-space:nowrap!important;text-decoration:none!important;box-shadow:0 10px 22px rgba(39,97,169,.12)!important;}
.q8-regsite-card .q8-action-findpwd,.q8-regsite-card .q8-action-findpwd:hover,.q8-regsite-card .q8-action-findpwd:focus,.q8-regsite-card .q8-action-findpwd:active{background:#55b2dc!important;color:#fff!important;}
.q8-regsite-card .q8-action-login,.q8-regsite-card .q8-action-login:hover,.q8-regsite-card .q8-action-login:focus,.q8-regsite-card .q8-action-login:active{background:#55c8d8!important;color:#fff!important;}
.q8-regsite-card .q8-action-ico{display:inline-flex!important;align-items:center!important;justify-content:center!important;width:14px!important;min-width:14px!important;height:14px!important;font-family:Arial,sans-serif!important;font-size:12px!important;font-weight:900!important;line-height:1!important;}
.q8-regsite-card .modal-dialog{width:min(760px,calc(100% - 24px));margin:34px auto;}
.q8-regsite-card .modal-content{overflow:hidden;border:0;border-radius:14px;box-shadow:0 24px 70px rgba(24,63,118,.26);}
.q8-regsite-card .modal-header{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border:0;background:linear-gradient(135deg,#1d7cf2,#24c7a8);color:#fff;}
.q8-regsite-card .modal-header .close{position:relative;width:28px;height:28px;display:flex;align-items:center;justify-content:center;margin:0;border-radius:50%;background:rgba(255,255,255,.18);opacity:1;color:transparent;text-shadow:none;font-size:0;line-height:1;outline:none!important;}
.q8-regsite-card .modal-header .close:before,.q8-regsite-card .modal-header .close:after{content:"";position:absolute;width:13px;height:2px;border-radius:2px;background:#fff;}
.q8-regsite-card .modal-header .close:before{transform:rotate(45deg);}
.q8-regsite-card .modal-header .close:after{transform:rotate(-45deg);}
.q8-regsite-card .modal-header .close:hover{background:rgba(239,68,68,.95);}
.q8-regsite-card .modal-title{color:#fff;font-weight:900;}
.q8-regsite-card .modal-body{padding:20px;background:#f6f9fe;}
.q8-regsite-card .modal-footer{border-top:1px solid #eaf1fb;background:#f7fbff;text-align:center;}
.q8-regsite-card .modal-footer .btn{min-width:118px;height:34px;display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:999px;background:linear-gradient(135deg,#1d7cf2,#24c7a8)!important;color:#fff!important;font-weight:900;line-height:1;box-shadow:0 10px 22px rgba(29,124,242,.18);}
.q8-regsite-card .modal-footer .btn:hover,.q8-regsite-card .modal-footer .btn:focus{color:#fff!important;filter:brightness(1.03);}
.q8-modal-grid,.q8-version-wrap{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;}
.q8-modal-card,.q8-version-card{border:1px solid #dfebfb;border-radius:12px;background:#fff;box-shadow:0 10px 26px rgba(31,78,142,.08);}
.q8-modal-card{padding:16px;}
.q8-modal-card h5{margin:0 0 8px;color:#14233b;font-size:15px;font-weight:900;}
.q8-modal-card p{margin:0;color:#5d7089;line-height:1.72;}
.q8-version-card{overflow:hidden;border-radius:14px;}
.q8-version-card.featured{border-color:rgba(255,138,52,.36);}
.q8-version-head{padding:16px;background:#eef6ff;}
.q8-version-card.featured .q8-version-head{background:linear-gradient(135deg,#fff4e9,#fff9f0);}
.q8-version-head strong{display:block;color:#14233b;font-size:18px;font-weight:900;}
.q8-version-head span{display:block;margin-top:5px;color:#63758f;}
.q8-feature-list{list-style:none;margin:0;padding:12px 16px 16px;}
.q8-feature-list li{display:flex;align-items:center;gap:8px;min-height:30px;color:#364b63;border-bottom:1px dashed #e6edf7;}
.q8-feature-list li:last-child{border-bottom:0;}
.q8-feature-list .fa-check{color:#16a766;}
.q8-feature-list .fa-close{color:#df4d53;}
.q8-modal-tip{margin-top:14px;padding:12px 14px;border:1px solid #dceafa;border-radius:12px;background:#fff;color:#526780;line-height:1.65;}
@media(max-width:767px){
  .q8-regsite-page{align-items:flex-start;min-height:auto;width:calc(100% - 20px);padding:12px 0 22px!important;}
  .q8-regsite-card{border-radius:12px;}
  .q8-regsite-card .widget-content{min-height:104px;padding:18px 16px!important;}
  .q8-regsite-card .widget-content img{width:58px;height:58px;}
  .q8-regsite-card .block{padding:16px 14px 20px;}
  .q8-regsite-card .block-title{grid-template-columns:1fr!important;}
  .q8-regsite-card .block-title h2,.q8-regsite-card .block-options .btn{min-height:40px;font-size:14px!important;}
  .q8-regsite-card .row.text-center{flex-direction:column;gap:10px;}
  .q8-regsite-card .row.text-center>div{width:100%;}
  .q8-regsite-card form{grid-template-columns:1fr;}
  .q8-regsite-card .input-group-addon{width:82px!important;min-width:82px!important;font-size:13px;}
  .q8-modal-grid,.q8-version-wrap{grid-template-columns:1fr;}
  .q8-regsite-card .modal-dialog{width:calc(100% - 18px);margin:18px auto;}
  .q8-regsite-card .modal-body{padding:14px;}
}
@media(max-width:520px){.q8-regsite-card form>.q8-regsite-actions{grid-template-columns:1fr!important;gap:10px!important;}}
</style>

<div class="col-xs-12 center-block q8-regsite-page">
  <br />
    <div class="widget q8-regsite-card">
    <div class="widget-content themed-background-flat text-center"  style="background-image: url(<?php echo $cdnserver?>assets/simple/img/head3.jpg);background-size: 100% 100%;" >
<img class="img-circle" src="../assets/simple/img/head3.jpg" alt="Site" height="60" width="60" />
<p></p>
    </div>

    <div class="block">
        <div class="block-title">
            <div class="block-options pull-right">
            <a href="../" class="btn btn-effect-ripple btn-default toggle-bordered enable-tooltip">返回首页</a>
            </div>
            <h2><i class="fa fa-user-plus"></i>&nbsp;&nbsp;<b>自助开通分站</b></h2>
        </div>
				<div class="row text-center">
                    <div class="col-xs-6">
                    <a class="btn btn-block btn-info" href="#about" data-toggle="modal">分站详细介绍</a>
                    </div>
                    <div class="col-xs-6">
                    <a class="btn btn-block btn-info" href="#userjs" data-toggle="modal">分站版本介绍</a>
                    </div>
                </div>
				<br>
                <form>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">
                                分站版本
                            </div>
                            <select name="kind" class="form-control"><option value="1" <?php if($kind===1){?>selected<?php }?>>普及版(<?php echo $q8NormalPriceText?>元)</option><option value="2" <?php if($kind===2){?>selected<?php }?>>专业版(<?php echo $q8ProfessionalPriceText?>元)</option></select>
                        </div>
						<?php if($conf['fenzhan_regalert']){?><small style="color:red"><i class="fa fa-info-circle"></i>&nbsp;专业版可以无限免费搭建下级网站并且别人在你下级网站下单你还有提成赚，专业版的商品比普通版更便宜，利润更多！</small><?php }?>
                    </div>
					<div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">
                                二级域名
                            </div>
							<div class="input-group" style="width: 100%;">
                            <input type="text" onkeyup="value=value.replace(/[^\w\.\/]/ig,'')" name="qz" class="form-control" required data-parsley-length="[2,8]" placeholder="输入你想要的二级前缀">
							<?php if($conf['fenzhan_regrand']){?><span class="input-group-btn">
                                <button class="btn btn-default" onclick="$('[name=\'qz\']').val(Math.random().toString(36).substr(6))" type="button">随机</button>
                            </span><?php }?>
							</div>
                            <select name="domain" class="form-control"><?php echo $select?></select>
                        </div>
						<?php if($conf['fenzhan_regalert']){?><small style="color:red"><i class="fa fa-info-circle"></i>&nbsp;可用字母，数字建议为2-5字，不能有标点符号（尽量简短,便于推广宣传）！</small><?php }?>
                    </div>
					<?php if(!$islogin2){?>
					<div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">
                                管理账号
                            </div>
                            <input type="text" name="user" class="form-control" required placeholder="输入要注册的用户名">
                        </div>
						<?php if($conf['fenzhan_regalert']){?><small style="color:red"><i class="fa fa-info-circle"></i>&nbsp;建议填写您的QQ号，方便记住！</small><?php }?>
                    </div>
					<div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">
                                管理密码
                            </div>
                            <input type="text" name="pwd" class="form-control" required placeholder="输入管理员密码">
                        </div>
						<?php if($conf['fenzhan_regalert']){?><small style="color:red"><i class="fa fa-info-circle"></i>&nbsp;可以用字母或数字，密码不能低于6位！</small><?php }?>
                    </div>
					<div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">
                                绑定ＱＱ
                            </div>
                            <input type="number" name="qq" class="form-control" required
                                   data-parsley-length="[5,10]"
                                   placeholder="输入你的QQ号" value="">
                        </div>
						<?php if($conf['fenzhan_regalert']){?><small style="color:red"><i class="fa fa-info-circle"></i>&nbsp;输入您的QQ号，方便联系和找回密码！</small><?php }?>
                    </div>
					<?php }?>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">
                                网站名称
                            </div>
                            <input type="text" name="name" class="form-control" required
                                   data-parsley-length="[2,10]"
                                   placeholder="输入网站名称">
                        </div>
						<?php if($conf['fenzhan_regalert']){?><small style="color:red"><i class="fa fa-info-circle"></i>&nbsp;例如：XX业务网，XX百货商城，自定义你想要的名字！</small><?php }?>
                    </div>
                    <input type="button" id="submit_buy" value="点此立即拥有分站" class="btn btn-danger btn-block">
					<hr>
					<div class="form-group q8-regsite-actions">
                            <a href="findpwd.php" class="btn btn-info btn-rounded q8-action-link q8-action-findpwd"><span class="q8-action-ico">#</span><span>&#25214;&#22238;&#23494;&#30721;</span></a>
                            <a href="login.php" class="btn btn-primary btn-rounded q8-action-link q8-action-login"><span class="q8-action-ico">+</span><span>&#36820;&#22238;&#30331;&#24405;</span></a>
                        </div>
                </form>
        </div>
	</div>

<!--分站介绍开始-->

<div class="modal fade" align="left" id="userjs" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel"><i class="fa fa-diamond"></i>&nbsp;分站版本介绍</h4>
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
            <div class="q8-version-wrap">
                <div class="q8-version-card">
                    <div class="q8-version-head">
                        <strong>普及版</strong>
                        <span>适合自己推广和接单，基础功能齐全。</span>
                    </div>
                    <ul class="q8-feature-list">
                        <li><i class="fa fa-check"></i>专属商城平台</li>
                        <li><i class="fa fa-check"></i>专属网站域名</li>
                        <li><i class="fa fa-check"></i>赚取用户提成</li>
                        <li><i class="fa fa-check"></i>设置商品价格</li>
                        <li><i class="fa fa-close"></i>下级分站提成</li>
                        <li><i class="fa fa-close"></i>搭建下级分站</li>
                    </ul>
                </div>
                <div class="q8-version-card featured">
                    <div class="q8-version-head">
                        <strong>专业版</strong>
                        <span>适合长期运营，支持下级分站和更多利润空间。</span>
                    </div>
                    <ul class="q8-feature-list">
                        <li><i class="fa fa-check"></i>专属商城平台</li>
                        <li><i class="fa fa-check"></i>专属网站域名</li>
                        <li><i class="fa fa-check"></i>赚取用户提成</li>
                        <li><i class="fa fa-check"></i>设置商品价格</li>
                        <li><i class="fa fa-check"></i>赚取下级分站提成</li>
                        <li><i class="fa fa-check"></i>搭建下级分站</li>
                    </ul>
                </div>
            </div>
            <div class="q8-modal-tip"><i class="fa fa-lightbulb-o"></i>&nbsp;想长期做推广或发展下级站长，建议优先选择专业版。</div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        </div>
    </div>
  </div>
</div>
<!--分站介绍结束-->


<div class="modal fade" align="left" id="about" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel"><i class="fa fa-info-circle"></i>&nbsp;分站详细介绍</h4>
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
            <div class="q8-modal-grid">
                <div class="q8-modal-card">
                    <h5><i class="fa fa-line-chart"></i>&nbsp;分站如何获取收益？</h5>
                    <p>把你的分站域名发给用户下单，订单付款成功后，你的账户会增加对应差价收益。</p>
                </div>
                <div class="q8-modal-card">
                    <h5><i class="fa fa-credit-card"></i>&nbsp;赚到的钱在哪里？</h5>
                    <p>分站后台有消费明细和余额信息，余额可用于平台消费，满 <?php echo $conf['tixian_min']; ?> 元可申请提现。</p>
                </div>
                <div class="q8-modal-card">
                    <h5><i class="fa fa-cubes"></i>&nbsp;需要自己供货吗？</h5>
                    <p>商品由主站提供，下单后由平台处理。如果网站没有想要的商品，可联系客服添加。</p>
                </div>
                <div class="q8-modal-card">
                    <h5><i class="fa fa-tags"></i>&nbsp;可以修改售价吗？</h5>
                    <p>分站可以按自己的运营节奏设置商品销售价格，用户成交后获取价差利润。</p>
                </div>
                <div class="q8-modal-card">
                    <h5><i class="fa fa-shield"></i>&nbsp;为什么选择我们？</h5>
                    <p>平台商品稳定，售后响应快，并持续优化分站后台和用户体验。</p>
                </div>
                <div class="q8-modal-card">
                    <h5><i class="fa fa-rocket"></i>&nbsp;适合谁开通？</h5>
                    <p>适合有社群、流量或长期推广需求的站长，开通后可直接使用后台管理。</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        </div>
    </div>
  </div>
</div>

<script src="../assets/vendor/jquery/1.12.4/jquery.min.js?v=q8vendor1"></script>
<script src="../assets/vendor/twitter-bootstrap/3.3.7/js/bootstrap.min.js?v=q8vendor1"></script>
<script src="../assets/Agod/layer.js"></script>
<script>
var hashsalt=<?php echo $addsalt_js?>;
</script>
<script src="../assets/js/regsite.js?ver=<?php echo VERSION ?>-q8ui2"></script>
</body>
</html>
