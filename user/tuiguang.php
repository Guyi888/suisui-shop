<?php
$is_defend=true;
require '../includes/common.php';
if($islogin2==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");

$title = '推广赚钱';
include 'head.php';

?>
<div class="wrapper">
	<div class="col-sm-12">
<?php
if($userrow['power']==0){
	showmsg('你没有权限使用此功能！',3);
}
if(!$userrow['domain'])showmsg('当前分站还未绑定域名',3);
$scriptpath = str_replace('\\','/',$_SERVER['SCRIPT_NAME']);
$scriptpath = substr($scriptpath, 0, strrpos($scriptpath, '/'));
$scriptpath = substr($scriptpath, 0, strrpos($scriptpath, '/'));
$url = 'http://'.$userrow['domain'].$scriptpath.'/';
if($conf['fanghong_api']>0){
	$turl = fanghongdwz($url);
	if($turl == $url){
		showmsg('防红地址生成失败，请联系站长更换接口',3);
	}elseif(strpos($turl,'/')===false){
		showmsg('防红地址生成失败:'.$turl,3);
	}
}else{
	$turl = $url;
}
?>
			<div class="panel panel-default">
			<div class="panel-heading"><h3 class="panel-title"><b>推广赚钱</b></h3></div>
				<div class="panel-body">
                    <p>① 将以下图片保存至本地或者复制文字广告，在QQ好友、QQ群、QQ空间、微信好友、微信朋友圈、贴吧、论坛等地方发表！</p>
                    <p>② 用户扫描下面任一一张二维码或访问任一文字广告内连接均可进入您的网站，下单均可获得提成哦~</p>
				</div>
			</div>
		</div>
		<div class="col-sm-12">
			<div class="panel panel-default">
			<div class="panel-heading">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#pic" data-toggle="tab"><i class="fa fa-image"></i> 图片广告</a></li>
				<li><a href="#text" data-toggle="tab"><i class="fa fa-file-text"></i> 文字广告</a></li>
			</ul>
			<a href="javascript:void(0);" onclick="TgTips()" class="btn btn-primary btn-sm pull-right" style="top:7px;right:28px;position: absolute!important;">投稿</a>
			</div>
			<div class="panel-body">
				<div id="myTabContent" class="tab-content">
					<div class="tab-pane fade in active" id="pic">
						<div class="row">
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <span style="font-weight:bold">专属推广图片①</span>
                                        <a href="javascript:void(0);" class="btn btn-success btn-xs pull-right" onclick="CunTips()">保存图片</a>
                                    </div>
                                    <div class="panel-body">
                                        <img class="img-rounded img-thumbnail" src="./timg/timg.php?id=1&url=<?php echo $turl?>" alt="推广图1">
                                    </div>
                                </div>
                            </div>
							<div class="col-12 col-md-6 col-lg-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <span style="font-weight:bold">专属推广图片②</span>
                                        <a href="javascript:void(0);" class="btn btn-success btn-xs pull-right" onclick="CunTips()">保存图片</a>
                                    </div>
                                    <div class="panel-body">
                                        <img class="img-rounded img-thumbnail" src="./timg/timg.php?id=2&url=<?php echo $turl?>" alt="推广图1">
                                    </div>
                                </div>
                            </div>
							<div class="col-12 col-md-6 col-lg-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <span style="font-weight:bold">专属推广图片③</span>
                                        <a href="javascript:void(0);" class="btn btn-success btn-xs pull-right" onclick="CunTips()">保存图片</a>
                                    </div>
                                    <div class="panel-body">
                                        <img class="img-rounded img-thumbnail" src="./timg/timg.php?id=3&url=<?php echo $turl?>" alt="推广图1">
                                    </div>
                                </div>
                            </div>
							<div class="col-12 col-md-6 col-lg-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <span style="font-weight:bold">专属推广图片④</span>
                                        <a href="javascript:void(0);" class="btn btn-success btn-xs pull-right" onclick="CunTips()">保存图片</a>
                                    </div>
                                    <div class="panel-body">
                                        <img class="img-rounded img-thumbnail" src="./timg/timg.php?id=4&url=<?php echo $turl?>" alt="推广图1">
                                    </div>
                                </div>
                            </div>
							<div class="col-12 col-md-6 col-lg-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <span style="font-weight:bold">专属推广图片⑤</span>
                                        <a href="javascript:void(0);" class="btn btn-success btn-xs pull-right" onclick="CunTips()">保存图片</a>
                                    </div>
                                    <div class="panel-body">
                                        <img class="img-rounded img-thumbnail" src="./timg/timg.php?id=5&url=<?php echo $turl?>" alt="推广图1">
                                    </div>
                                </div>
                            </div>
							<div class="col-12 col-md-6 col-lg-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <span style="font-weight:bold">专属推广图片⑥</span>
                                        <a href="javascript:void(0);" class="btn btn-success btn-xs pull-right" onclick="CunTips()">保存图片</a>
                                    </div>
                                    <div class="panel-body">
                                        <img class="img-rounded img-thumbnail" src="./timg/timg.php?id=6&url=<?php echo $turl?>" alt="推广图1">
                                    </div>
                                </div>
                            </div>
						</div>
					</div>
					<div class="tab-pane fade in" id="text">
						<div class="col-12 col-md-6 col-lg-4">
							<div class="panel panel-default">
                                <div class="panel-heading">
                                    <span style="font-weight:bold">专属文字广告①</span>
                                    <a href="javascript:void(0);" id="copy-btn" class="btn btn-success btn-xs pull-right" data-clipboard-target="#wen-a">复制广告</a>
                                </div>
                                <div class="panel-body">
                                    <p id="wen-a">
                                        岁岁云商城支持数字商品、自助下单、自动发货、订单查询和分站推广。欢迎收藏备用，常用业务可直接搜索下单。<br><br>自助下单地址：<?php echo $turl?>
                                    </p>
                                </div>
                            </div>

                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <span style="font-weight:bold">专属文字广告②</span>
                                    <a href="javascript:void(0);" id="copy-btn" class="btn btn-success btn-xs pull-right" data-clipboard-target="#wen-b">复制广告</a>
                                </div>
                                <div class="panel-body">
                                    <p id="wen-b">
                                        需要一个稳定的自助下单入口？岁岁云商城提供商品展示、在线支付、订单查询、自动发货和售后工单能力，适合长期运营。<br><br>自助下单地址：<?php echo $turl?>
                                    </p>
                                </div>
                            </div>
						</div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <span style="font-weight:bold">专属文字广告③</span>
                                    <a href="javascript:void(0);" id="copy-btn" class="btn btn-success btn-xs pull-right" data-clipboard-target="#wen-c">复制广告</a>
                                </div>
                                <div class="panel-body">
                                    <p id="wen-c">
                                        下单前请仔细阅读商品说明，支付后可在订单查询页面查看进度；遇到问题请联系在线客服并提供订单号。<br><br>自助下单地址：<?php echo $turl?>
                                    </p>
                                </div>
                            </div>

                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <span style="font-weight:bold">专属文字广告④</span>
                                    <a href="javascript:void(0);" id="copy-btn" class="btn btn-success btn-xs pull-right" data-clipboard-target="#wen-d">复制广告</a>
                                </div>
                                <div class="panel-body">
                                    <p id="wen-d">
                                        收藏岁岁云商城，常用商品快速下单，订单状态自助查询，售后问题可通过在线客服处理。<br><br>自助下单地址：<?php echo $turl?>
                                    </p>
                                </div>
                            </div>
                        </div>
						<div class="col-12 col-md-6 col-lg-4">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <span style="font-weight:bold">专属文字广告⑤</span>
                                    <a href="javascript:void(0);" id="copy-btn" class="btn btn-success btn-xs pull-right" data-clipboard-target="#wen-e">复制广告</a>
                                </div>
                                <div class="panel-body">
                                    <p id="wen-e">
                                        岁岁云商城自助服务平台，支持商品购买、卡密发货、订单查询和分站推广，适合个人和团队长期使用。<br><br>自助下单地址：<?php echo $turl?>
                                    </p>
                                </div>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <span style="font-weight:bold">专属文字广告⑥</span>
                                    <a href="javascript:void(0);" id="copy-btn" class="btn btn-success btn-xs pull-right" data-clipboard-target="#wen-f">复制广告</a>
                                </div>
                                <div class="panel-body">
                                    <p id="wen-f" class="text-center">
                                        岁岁云商城<br><?php echo $turl?><br>【自助下单】【在线支付】<br>【自动发货】【订单查询】<br>【售后工单】【分站推广】<br>建议收藏网站，方便下次访问。
                                    </p>
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
<script src="<?php echo $cdnpublic?>clipboard.js/1.7.1/clipboard.min.js"></script>
<script>
function CunTips() {
	layer.alert('保存方法：<br><b>手机</b>：长按图片即可将图片保存至本地！(需要在浏览器才能保存哦)<br><b>电脑</b>：鼠标指针放在图片上并点击右键»图片另存为，即可保存！', {
		icon: 6,
		title: '小提示',
		skin: 'layui-layer-molv layui-layer-wxd'
	})
}
function TgTips() {
	layer.alert('若您有更好的图文广告模板，文字广告语，均可联系客服进行投稿哦~<br>期待下一个投稿的您~！', {
		icon: 6,
		title: '小提示',
		skin: 'layui-layer-molv layui-layer-wxd'
	})
}
$(document).ready(function(){
	var clipboard = new Clipboard('#copy-btn');
        clipboard.on('success', function(e) {
            layer.msg('复制成功！',{time: 1000, icon: 1});
        });
        clipboard.on('error', function(e) {
            layer.msg('复制失败！建议更换其他最新版浏览器！',{time: 2000, icon: 2});
        });
})
</script>
</body>
</html>