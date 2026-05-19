<?php
/*
 本代码由 岁岁 @qqfaka 创建
 创建时间 2025-11-30
 技术支持 QQ:1410505990
 模板版本 v1.0
*/
if(!defined('IN_CRONLITE'))exit();
$chdsn_cn_zuocew = $conf['chdsn_cn_zuocew']?$conf['chdsn_cn_zuocew']:'http://aifeili.com.cn/bg.png';

// 模板配置 - 显示/隐藏控制
$show_marquee = isset($conf['show_marquee']) ? $conf['show_marquee'] : '1';
$show_warning_div = isset($conf['show_warning_div']) ? $conf['show_warning_div'] : '1';
$show_guide_link = isset($conf['show_guide_link']) ? $conf['show_guide_link'] : '1';
$show_order_warning = isset($conf['show_order_warning']) ? $conf['show_order_warning'] : '1';
$show_favorite_div = isset($conf['show_favorite_div']) ? $conf['show_favorite_div'] : '1';
$show_article_list = isset($conf['show_article_list']) ? $conf['show_article_list'] : '1';
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
		<script src="https://lib.baomitu.com/jquery/3.5.1/jquery.min.js"></script>
		<script src="https://lib.baomitu.com/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
		<script src="<?php echo $cdnpublic?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<script src="<?php echo $cdnpublic?>modernizr/2.8.3/modernizr.min.js"></script>
		<script src="<?php echo $cdnpublic?>layer/2.3/layer.js"></script>
		<script>
		var isModal = <?php echo empty($conf['modal']) ? 'false' : 'true'; ?> ;
		var modalShowType = <?php echo isset($conf['modal_show_type']) ? intval($conf['modal_show_type']) : 0; ?>;
		var homepage = true;
		var hashsalt = <?php echo $addsalt_js ?> ;
		</script>
		<!-- ============================================ -->
		<!-- 🔥 课程查询强制修复 - 纯原生 JavaScript -->
		<!-- ============================================ -->
		<script>
		console.log('🔥 课程查询强制修复加载...');

		// 1. 定义真正的查询函数
		function doQuery() {
			console.log('🚀 执行查询...');

			// 纯原生获取值
			var input1 = document.getElementById('inputvalue');
			var input2 = document.getElementById('inputvalue2');
			var input3 = document.getElementById('inputvalue3');

			var user = input1 ? input1.value || '' : '';
			var pwd = input2 ? input2.value || '' : '';
			var school = input3 ? input3.value || '' : '';

			user = user.trim();
			pwd = pwd.trim();
			school = school.trim();

			console.log('获取的值:', {user, pwd, school});

			if (!user || !pwd || !school) {
				var msg = '请完整填写信息！';
				if (!user) msg += ' 登录账号';
				if (!pwd) msg += ' 登录密码';
				if (!school) msg += ' 学校名字';
				if (typeof layer !== 'undefined') {
					layer.alert(msg, {icon: 2, title: '提示'});
				} else {
					alert(msg);
				}
				return;
			}

			var url = 'cx.php?user=' + encodeURIComponent(user) + '&pwd=' + encodeURIComponent(pwd) + '&school=' + encodeURIComponent(school);
			console.log('跳转URL:', url);

			window.open(url, '_blank');
		}

		// 2. 立即定义 dzb_ck
		window.dzb_ck = doQuery;

		// 3. 持续启用输入框 + 绑定点击事件
		function fixAndBind() {
			// 启用输入框
			['inputvalue', 'inputvalue2', 'inputvalue3', 'inputvalue4'].forEach(function(id) {
				var el = document.getElementById(id);
				if (el && el.disabled) {
					el.disabled = false;
					el.removeAttribute('disabled');
				}
			});

			// 找到查询按钮并强绑定
			var btn = document.getElementById('dzb_ck');
			if (btn) {
				if (btn.disabled) {
					btn.disabled = false;
					btn.removeAttribute('disabled');
				}
				btn.style.opacity = '1';
				btn.style.cursor = 'pointer';

				// 终极防护：覆盖 onclick 属性并直接绑定事件
				btn.onclick = function(e) {
					e.preventDefault();
					e.stopPropagation();
					console.log('🔥 按钮被点击，执行查询');
					doQuery();
					return false;
				};

				// 同时也用 addEventListener
				if (!btn._queryBound) {
					btn.addEventListener('click', function(e) {
						e.preventDefault();
						e.stopPropagation();
						console.log('🔥 addEventListener 触发');
						doQuery();
						return false;
					});
					btn._queryBound = true;
				}
			}

			// 持续覆盖 dzb_ck 函数，防止被加密代码覆盖
			window.dzb_ck = doQuery;
		}

		// 页面加载完成后立即启动
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', function() {
				console.log('✅ 启用输入框...');
				setInterval(fixAndBind, 100); // 更频繁检查
			});
		} else {
			console.log('✅ 启用输入框...');
			setInterval(fixAndBind, 100); // 更频繁检查
		}

		console.log('✅ 强制修复完成！');
		</script>
		<script src="<?php echo $cdnserver ?>assets/js/main.js?ver=<?php echo VERSION ?>"></script>
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
	.panel-primary {border-color: #ffffff;}
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
</style>
 <body>
	<!--弹出公告-->
	<div class="modal fade" align="left" id="myModal" tabindex="-1" role="dialog"
	aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">
						<span aria-hidden="true">
							×
						</span>
						<span class="sr-only">
							Close
						</span>
					</button>
					<h4 class="modal-title" id="myModalLabel">
						<?php echo $conf['sitename']?>
					</h4>
				</div>
				<div class="modal-body">
					<?php echo $conf['modal']?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">
						知道啦
					</button>
				</div>
			</div>
		</div>
	</div>
	<!--弹出公告-->
	<!--公告-->
	<div class="modal fade" align="left" id="mustsee" tabindex="-1" role="dialog"
	aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">
						<span aria-hidden="true">
							×
						</span>
						<span class="sr-only">
							Close
						</span>
					</button>
					<h4 class="modal-title" id="myModalLabel">
						公告
					</h4>
				</div>
				<div class="modal-body">
					<?php echo $conf['anounce']?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">
						关闭
					</button>
				</div>
			</div>
		</div>
	</div>
	<!--公告-->
	<!--平台公告Modal-->
	<div class="modal fade" id="anounce" tabindex="-1" role="dialog" aria-labelledby="anounceLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="anounceLabel">平台公告</h4>
				</div>
				<div class="modal-body">
					<?php echo $conf['anounce']?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
				</div>
			</div>
		</div>
	</div>
	<!--平台公告Modal结束-->
	<!--客服Modal-->
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
							<span>本站所有项目均源自互联网收集</span>
						</div>
						<div style="display: flex; align-items: flex-start;">
							<i class="fa fa-exclamation-triangle fa-fw" style="color: #8B0000; min-width: 20px; margin-top: 2px;"></i>
							<span>下单前已明确标注封号无售后说明</span>
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
							<span>确认下载地址可正常访问</span>
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
						处理项目无效果问题，处理下载地址失效问题，负责树立平台担保责任
					</div>
				</div>
			</div>
		</div>

		<!-- 提示信息 -->
		<div style="background: #FFF0F0; border: 1px solid #D8A0A0; border-radius: 8px; padding: 15px; margin-bottom: 20px; color: #8B0000; font-weight: 500;">
			<i class="fa fa-exclamation-circle" style="margin-right: 8px;"></i>
			站长没有办法处理？点击下方联系平台人工客服解决
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
						平台无引流人工客服
					</div>
					<div style="color: #8B0000; margin-bottom: 10px;">
						<i class="fa fa-clock-o" style="margin-right: 5px;"></i>
						<b>在线时间：早10：00 - 晚22：00</b>
					</div>
					<a href="/template/XHY-01/content.html" target="_blank" style="color: #8B0000; text-decoration: none;">
						<i class="fa fa-commenting"></i> 点击可查看自助解决教程
					</a>
				</div>


			</div>
		</div>
	</div>

	<!-- 底部 -->
	<div style="border-top: 1px solid #D8A0A0; padding: 15px 30px; background: #FFF0F0; text-align: right;">
		<button type="button" onclick="closeModal()" style="background: linear-gradient(45deg, #8B0000, #B22222); color: #fff; border: none; border-radius: 20px; padding: 8px 25px; transition: all 0.3s ease;">
			<i class="fa fa-check-circle"></i> 知道啦
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

	// 修改a标签的点击事件
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
			<div class="block-content block-content-full text-center bg-image" style="background-image: url('http://aifeili.com.cn/bg.png');background-size: 100% 100%;">
				<div>
					<div>
						<img class="img-avatar img-avatar80 img-avatar-thumb animated zoomInDown"
						src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq']?>&spec=100">
					</div>
				</div>
			</div>
			<div class="panel-body text-center">
				<h3 style="margin: 10px 0;">
					<a href="javascript:void(alert('<?php echo $conf['sitename']?>，建议收藏链接地址到浏览器书签防止丢失哦！'));">
						<b>
							<font color="#8B0000"><?php echo $conf['sitename']?></font>
						</b>
					</a>
				</h3>
				<div style="margin-bottom: 15px;">
					<span style="font-weight:bold">
						<font color="#FF8000">低</font>
						<font color="#EC6D13">价</font>
						<font color="#D95A26">源</font>
						<font color="#C64739">头</font>
						<font color="#A0215F"> - </font>
						<font color="#8D0E72">项</font>
						<font color="#5400AB">目</font>
						<font color="#4100BE">最</font>
						<font color="#2E00D1">全</font>
						<font color="#1B00E4"> - </font>
						<font color="#1B00E4">代</font>
						<font color="#2E00D1">理</font>
						<font color="#4100BE">首</font>
						<font color="#5400AB">选</font>
						<font color="#8D0E72"> - </font>
						<font color="#A0215F">结</font>
						<font color="#C64739">算</font>
						<font color="#D95A26">最</font>
						<font color="#EC6D13">快</font>
					</span>
				</div>
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

						<!-- 供货商入驻 -->
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
								<span style="font-weight:600">今日新上架目录</span>
							</a>
						</div>
						<div class="btn-group">
							<a class="btn nav-btn" href="#lxkf" data-toggle="modal" target="_blank">
								<i class="fa fa-comment"></i>
								<span style="font-weight:600">点我咨询100%处理</span>
							</a>
						</div>
					</div>
				</div>
				<!--主按钮组结束-->
				<!--logo下面按钮结束-->


<!-TAB标签-->

<!-TAB标签-->

				<!--查单说明开始-->
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
								<font color="red">
									请在右侧的输入框内输入您下单时，在第一个输入框内填写的信息
								</font>
							</li>
							<li class="list-group-item">
								例如您购买的是QQ名片赞，输入下单的QQ账号即可查询订单
							</li>
							<li class="list-group-item">
								例如您购买的是邮箱类商品，需要输入您的邮箱号，输入QQ号是查询不到的
							</li>
							<li class="list-group-item">
								例如您购买的是快手商品，需要输入作品链接里“userid=”后面的数字，输入快手号是一般是查询不到的
							</li>
							<li class="list-group-item">
								例如您购买的是全民K歌商品，需要输入歌曲链接里“shareuid=”后面的，&amp;前面的一串英文数字，输入歌曲链接是查询不到的
							</li>
							<li class="list-group-item">
								<font color="red">
									如果不清楚下单账号是什么，可以不填写，直接点击查询，则会根据浏览器缓存查询
								</font>
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
				<div class="block animated bounceInDown btn-rounded" style="border:1px solid #b3cde3; background: url(https://s3.ax1x.com/2021/01/02/sSy9rq.png);margin-top:0px;font-size:15px;padding:5px;border-radius:15px;background-color: white;">
					<ul class="nav nav-tabs btn btn-block animated zoomInLeft btn-rounded" style="background-color: #FFE6E6;" data-toggle="tabs">
					<li class="active" style="width: 20%;" align="center">
						<a href="#shop" data-toggle="tab">
							<i class="fa fa-shopping-bag fa-fw">
							</i>
							下单
						</a>
					</li>
					<li style="width: 20%;" align="center">
						<a href="#search" data-toggle="tab" id="tab-query">
							<i class="fa fa-search">
						</i>
							查单
						</a>
					</li>
					<li style="width: 20%;" align="center">
						<a href="#ktfz" data-toggle="tab">
							<i class="fa fa-coffee fa-fw">
						</i>
							赚钱
						</a>
					</li>
					<li style="width: 20%;" align="center">
						<a href="#gift" data-toggle="tab">
							<i class="fa fa-gift fa-fw">
						</i>
							抽奖
						</a>
					</li>
					<li style="width: 20%;" align="center" class="hide">
						<a href="#cardbuy" data-toggle="tab">
							<i class="fa fa-th">
						</i>
							卡密
						</a>
					</li>
					<li style="width: 20%;" align="center">
						<a href="#more" data-toggle="tab">
							<i class="fa fa-folder-open">
						</i>
							更多
						</a>
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
						<div style="background: linear-gradient(to right, #ffffff, #ffcccc); border-radius: 8px; padding: 10px 20px; margin-bottom: 15px; box-shadow: 0 2px 6px rgba(139,0,0,0.1);">
							<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:15px;">
								<!-- 标题部分 -->
								<h4 style="color:#8B0000; margin:0;
								          display:flex; align-items:center; gap:8px;">
									<i class="fa fa-newspaper-o" style="color:#8B0000;"></i>
									<span style="text-shadow:0 1px 2px rgba(139,0,0,0.1)">注文释意</span>
								</h4>

								<!-- 操作按钮组 -->
								<div style="display:flex; gap:10px; flex-wrap:wrap;">
									<a href="javascript:void(alert('可能是总站或供货商库存不足了，等待后台补卡或联系客服解决即可！'));">
										<span style="display:inline-block;
										          padding:6px 15px;
										          background:#FFE6E6;
										          border:1px solid #D8A0A0;
										          color:#8B0000;
										          border-radius:20px;
										          transition:all 0.3s ease;
										          font-size:13px;
										          box-shadow:1px 1px 4px rgba(139,0,0,0.1);">
											<i class="fa fa-clock-o"></i> 待处理订单
										</span>
									</a>

									<a href="javascript:void(alert('显示已退款是退款到你余额(可提现)，如果没有注册则不到账，先注册账号再联系客服！'));">
										<span style="display:inline-block;
										          padding:6px 15px;
										          background:#FFE6E6;
										          border:1px solid #FFB3B3;
										          color:#C64739;
										          border-radius:20px;
										          transition:all 0.3s ease;
										          font-size:13px;
										          box-shadow:1px 1px 4px rgba(198,71,57,0.1);">
											<i class="fa fa-undo"></i> 已退款订单
										</span>
									</a>

									<a href="javascript:void(alert('如果付款了找不到订单请在下边手动查单，如果还是没有带上付款截图联系客服！'));">
										<span style="display:inline-block;
										          padding:6px 15px;
										          background:#FFF3E6;
										          border:1px solid #FFD9B3;
										          color:#D95A26;
										          border-radius:20px;
										          transition:all 0.3s ease;
										          font-size:13px;
										          box-shadow:1px 1px 4px rgba(217,90,38,0.1);">
											<i class="fa fa-search-minus"></i> 查不到订单
										</span>
									</a>
								</div>
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
									<a href="#lxkf" target="_blank" data-toggle="modal" style="display:inline-block; border:2px solid #FFCCCC; border-radius:50%; padding:3px;">
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
										<small style="display: block; color: #8B0000;">点击头像有教程</small>
									</span>
								</div>
									</div>
								</div>
							</div>
						</div>

						<!-- 查询订单教程 -->
						<div style="background: #FFF0F0;
						          border: 2px solid #FFCCCC;
					          border-radius: 12px;
					          padding: 15px;
					          margin-bottom: 15px;
					          box-shadow: 0 2px 6px rgba(139,0,0,0.1);">
							<h5 style="color: #8B0000; margin: 0 0 10px 0;">【查询订单教程】</h5>
							<ul style="margin: 0; padding-left: 20px; color: #8B0000; line-height: 1.8;">
								<li>方法1：根据浏览器缓存直接点击立即查询，会自动弹出订单</li>
								<li>方法2：输入下单时候填写的联系方式，如QQ号/邮箱/手机号</li>
								<li>方法3：支付记录详细截图联系客服协助查询，高峰期需排队</li>
							</ul>
						</div>

						<!-- 查询表单 -->
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-btn">
									<select class="form-control" id="searchtype" style="padding: 6px 4px;width:90px">
										<option value="0">下单账号</option>
										<option value="1">订单号</option>
									</select>
								</div>
								<input type="text" name="qq" id="qq3" value="" class="form-control" placeholder="输入下单信息" onkeydown="if(event.keyCode==13){submit_query.click()}" required>
								<span class="input-group-btn">
									<a tabindex="0" class="btn btn-default" role="button" data-container="body" data-toggle="popover" data-trigger="focus" data-placement="top" title="" data-content="您下单时，在第一个输入框内填写的信息。如果您不记得下单账号是什么，可以不填写，直接点击查询，则会根据浏览器缓存查询！" data-original-title="下单信息是什么？">
										<i class="fa fa-exclamation-circle"></i>
									</a>
								</span>
							</div>
						</div>

						<!-- 立即查询按钮 -->
						<input type="submit" id="submit_query" class="btn btn-danger btn-block btn-rounded" style="background: linear-gradient(to right, #FFCCCB, #8B0000); color: #fff; border: none; margin-bottom: 15px;" value="立即查询">

						<!-- 结果区域 -->
						<div id="result2" class="form-group" style="display:none;">
							<center>
								<small>
									<font color="#ff0000">
										手机用户可以左右滑动
									</font>
								</small>
							</center>
							<div class="table-responsive">
								<table class="table table-vcenter table-condensed table-striped">
									<thead>
										<tr>
											<th class="hidden-xs">下单账号</th>
											<th>商品名称</th>
											<th>数量</th>
											<th class="hidden-xs">购买时间</th>
											<th>状态</th>
											<th>操作</th>
										</tr>
									</thead>
									<tbody id="list"></tbody>
								</table>
							</div>
						</div>
					</div>
					<!--查询订单-->
						<!--开通分站-->
				<div class="tab-pane" id="ktfz">
					<div style="padding: 20px;">
						<table class="table table-borderless table-pricing">
							<tbody>
								<tr class="active">
									<td style="padding: 0;">
										<div style="width: 100%; height: 8em; display: flex; flex-direction: column; justify-content: center; align-items: center; color: white; margin: auto; background: linear-gradient(135deg, #8B0000 0%, #CD5C5C 100%); border-radius: 10px; box-shadow: 0 4px 12px rgba(139, 0, 0, 0.3);">
											<h3 style="width:100%;font-size: 1.6em; text-align: center; margin: 0 0 0.5em 0;">
												<i class="fa fa-user-o fa-fw"></i><strong>普及版</strong> / <i class="fa fa-user-circle-o fa-fw"></i><strong>专业版</strong>
											</h3>
											<span style="width: 100%;text-align: center;font-size: 1.1em;display: block;">
												0元 / 0元
											</span>
										</div>
									</td>
								</tr>

								<tr>
									<td style="padding: 15px 10px;">
										<div style="display: flex; align-items: center; color: #8B0000;">
											<i class="fa fa-check-circle" style="color: #8B0000; margin-right: 10px;"></i>
											<span>学生/上班族/创业/休闲挣￥必备工具</span>
										</div>
									</td>
								</tr>

								<tr>
									<td style="padding: 15px 10px;">
										<div style="display: flex; align-items: center; color: #8B0000;">
											<i class="fa fa-check-circle" style="color: #8B0000; margin-right: 10px;"></i>
											<span>0手续费提现，每日固定时间打款</span>
										</div>
									</td>
								</tr>

								<tr>
									<td style="padding: 15px 10px;">
										<div style="display: flex; align-items: center; color: #8B0000;">
											<i class="fa fa-check-circle" style="color: #8B0000; margin-right: 10px;"></i>
											<span>余额提成满1元提现</span>
										</div>
									</td>
								</tr>

								<tr>
									<td style="padding: 15px 10px;">
										<div style="display: flex; align-items: center; color: #8B0000;">
											<i class="fa fa-star" style="color: #8B0000; margin-right: 10px;"></i>
											<span><strong>网站轻轻松松推广日挣上千￥不是梦</strong></span>
										</div>
									</td>
								</tr>

								<tr class="active">
									<td style="padding: 20px 10px; text-align: center;">
										<div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
											<a href="#userjs" data-toggle="modal" class="btn btn-effect-ripple" style="background: #FFF0F0; color: #8B0000; border: none; border-radius: 20px; padding: 8px 25px; font-weight: 600; box-shadow: 0 2px 5px rgba(139,0,0,0.1); transition: all 0.3s ease;">
												<i class="fa fa-align-justify"></i> 版本介绍
											</a>

											<a href="user/regsite.php" target="_blank" class="btn btn-effect-ripple" style="background: linear-gradient(135deg, #8B0000 0%, #CD5C5C 100%); color: white; border: none; border-radius: 20px; padding: 8px 25px; font-weight: 600; box-shadow: 0 2px 8px rgba(139,0,0,0.3); transition: all 0.3s ease;">
												<i class="fa fa-arrow-right"></i> 马上开通
											</a>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<!--开通分站-->
						<!--抽奖-->
						<div class="tab-pane fade fade-up" id="gift">
							<div class="panel-body text-center">
								<div id="roll">
									点击下方按钮开始抽奖
								</div>
								<hr>
								<p>
									<a class="btn btn-info" id="start" style="display:block; background-color: #FFCCCC;">
										开始抽奖
									</a>
									<a class="btn btn-danger" id="stop" style="display:none;">
										停止
									</a>
								</p>
								<div id="result">
								</div>
								<br/>
								<div class="giftlist" style="display:none;">
									<strong>
										最近中奖记录
									</strong>
									<ul id="pst_1">
									</ul>
								</div>
							</div>
						</div>
						<!--抽奖-->
						<!--卡密下单-->
						<div class="tab-pane fade fade-up" id="cardbuy">
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-addon">
										输入卡密
									</div>
									<input type="text" name="km" id="km" value="" class="form-control" onkeydown="if(event.keyCode==13){submit_checkkm.click()}"
									required/>
								</div>
							</div>
							<input type="submit" id="submit_checkkm" class="btn btn-primary btn-block"
							value="检查卡密">
							<div id="km_show_frame" style="display:none;">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">
											商品名称
										</div>
										<input type="text" name="name" id="km_name" value="" class="form-control"
										disabled/>
									</div>
								</div>
								<div id="km_inputsname">
								</div>
								<div id="km_alert_frame" class="alert alert-success animation-pullUp"
								style="display:none;">
								</div>
								<input type="submit" id="submit_card" class="btn btn-primary btn-block"
								value="立即购买">
								<div id="result1" class="form-group text-center" style="display:none;">
								</div>
							</div>
							<br />
						</div>
						<!--卡密下单-->
						<!--更多-->
						<div class="tab-pane fade fade-right" id="more">
							<div class="col-xs-6 col-sm-4 col-lg-4<?php if(empty($conf['appurl'])){?> hide<?php }?>">
								<a class="block block-link-hover2 text-center" href="<?php echo $conf['appurl'] ?>"
								target="_blank">
									<div class="block-content block-content-full bg-success">
										<i class="fa fa-cloud-download fa-3x text-white">
										</i>
										<div class="font-w600 text-white-op push-15-t">
											APP下载
										</div>
									</div>
								</a>
							</div>
							<div class="col-xs-6 col-sm-4 col-lg-4<?php if(empty($conf['daiguaurl'])){?> hide<?php }?>">
								<a class="block block-link-hover2 text-center" href="./?mod=daigua">
									<div class="block-content block-content-full bg-primary">
										<i class="fa fa-circle-o fa-3x text-white">
										</i>
										<div class="font-w600 text-white-op push-15-t">
											QQ代挂
										</div>
									</div>
								</a>
							</div>
							<div class="col-xs-6 col-sm-4 col-lg-4<?php if(empty($conf['invite_tid'])){?> hide<?php }?>">
								<a class="block block-link-hover2 text-center" href="./?mod=invite" target="_blank">
									<div class="block-content block-content-full bg-warning">
										<i class="fa fa-paper-plane-o fa-3x text-white">
										</i>
										<div class="font-w600 text-white-op push-15-t">
											免费领赞
										</div>
									</div>
								</a>
							</div>
							<div class="col-xs-6 col-sm-4 col-lg-4">
								<a class="block block-link-hover2 text-center" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $conf['kfqq']?>&site=qq&menu=yes">
									<div class="block-content block-content-full bg-amethyst">
										<i class="fa fa-credit-card fa-3x text-white">
										</i>
										<div class="font-w600 text-white-op push-15-t">
											售后客服

                                        </div>
									</div>
								</a>
							</div>
							<div class="col-xs-6 col-sm-4 col-lg-4">
								<a class="block block-link-hover2 text-center" href="/user/findpwd.php">
									<div class="block-content block-content-full bg-success">
										<i class="fa fa-comments fa-3x text-white">
										</i>
										<div class="font-w600 text-white-op push-15-t">
											找回密码
										</div>
									</div>
								</a>
							</div>
							<div class="col-xs-6 col-sm-4 col-lg-4">
								<a class="block block-link-hover2 text-center" href="./user" target="_blank">
									<div class="block-content block-content-full bg-city">
										<i class="fa fa-certificate fa-3x text-white">
										</i>
										<div class="font-w600 text-white-op push-15-t">
											分站登录
										</div>
									</div>
								</a>
							</div>
						</div>
					</div>
				</div>
				<!--版本介绍-->
				<div class="modal fade" id="userjs" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog modal-dialog-popin">
						<div class="modal-content">
							<div class="block block-themed block-transparent remove-margin-b">
								<div class="block-header bg-primary-dark">
									<ul class="block-options">
										<li>
											<button data-dismiss="modal" type="button">
												<i class="si si-close">
												</i>
											</button>
										</li>
									</ul>
									<h4 class="block-title">
										版本介绍
									</h4>
								</div>
								<div class="modal-body">
									<div class="table-responsive">
										<table class="table table-borderless table-vcenter">
											<thead>
												<tr>
													<th style="width: 100px;">
														功能
													</th>
													<th class="text-center" style="width: 20px;">
														普及版/专业版
													</th>
												</tr>
											</thead>
											<tbody>
												<tr class="active">
													<td>
														专属代刷平台
													</td>
													<td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-success">
															<i class="fa fa-check">
															</i>
														</span>
														<span class="btn btn-effect-ripple btn-xs btn-success">
															<i class="fa fa-check">
															</i>
														</span>
													</td>
												</tr>
												<tr class="">
													<td>
														三种在线支付接口
													</td>
													<td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-success">
															<i class="fa fa-check">
															</i>
														</span>
														<span class="btn btn-effect-ripple btn-xs btn-success">
															<i class="fa fa-check">
															</i>
														</span>
													</td>
												</tr>
												<tr class="success">
													<td>
														专属网站域名
													</td>
													<td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-success">
															<i class="fa fa-check">
															</i>
														</span>
														<span class="btn btn-effect-ripple btn-xs btn-success">
															<i class="fa fa-check">
															</i>
														</span>
													</td>
												</tr>
												<tr class="">
													<td>
														赚取用户提成
													</td>
													<td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-success">
															<i class="fa fa-check">
															</i>
														</span>
														<span class="btn btn-effect-ripple btn-xs btn-success">
															<i class="fa fa-check">
															</i>
														</span>
													</td>
												</tr>
												<tr class="info">
													<td>
														赚取下级分站提成
													</td>
													<td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-danger">
															<i class="fa fa-close">
															</i>
														</span>
														<span class="btn btn-effect-ripple btn-xs btn-success">
															<i class="fa fa-check">
															</i>
														</span>
													</td>
												</tr>
												<tr class="">
													<td>
														设置商品价格
													</td>
													<td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-success">
															<i class="fa fa-check">
															</i>
														</span>
														<span class="btn btn-effect-ripple btn-xs btn-success">
															<i class="fa fa-check">
															</i>
														</span>
													</td>
												</tr>
												<tr class="warning">
													<td>
														设置下级分站商品价格
													</td>
													<td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-danger">
															<i class="fa fa-close">
															</i>
														</span>
														<span class="btn btn-effect-ripple btn-xs btn-success">
															<i class="fa fa-check">
															</i>
														</span>
													</td>
												</tr>
												<tr class="">
													<td>
														搭建下级分站
													</td>
													<td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-danger">
															<i class="fa fa-close">
															</i>
														</span>
														<span class="btn btn-effect-ripple btn-xs btn-success">
															<i class="fa fa-check">
															</i>
														</span>
													</td>
												</tr>
												<tr class="danger">
													<td>
														赠送专属精致APP
													</td>
													<td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-danger">
															<i class="fa fa-close">
															</i>
														</span>
														<span class="btn btn-effect-ripple btn-xs btn-success">
															<i class="fa fa-check">
															</i>
														</span>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
									<center style="color: #b2b2b2;">
										<small>
											<em>
												* 自己的能力决定着你的收入！
											</em>
										</small>
									</center>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">
									关闭
								</button>
							</div>
						</div>
					</div>
				</div>
				<!--版本介绍-->
				<!--关于我们弹窗-->
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
											<font size="3">
												站长ＱＱ：
												<a href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $conf['kfqq']?>&site=qq&menu=yes"
												target="_blank">
													<?php echo $conf['kfqq']?>
												</a>
											</font>
										</strong>
										<br />
										<strong>
											<font size="2">
												本站域名：<?php echo $_SERVER['HTTP_HOST']; ?>
											</font>
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
													由于本站采用全自动订单处理，有几率出现漏单，部分单子处理时间可能会稍长一点，不过都会完成，最终解释权归本站所有。超过24小时没处理请联系客服！
												</div>
											</div>
											<div class="panel panel-trans pad-top">
												<a href="#demo-acc-faq2" class="text-semibold text-lg text-main collapsed"
												data-toggle="collapse" data-parent="#demo-acc-faq" aria-expanded="false">
													ＱＱ空间业务类下单方法讲解
												</a>
												<div id="demo-acc-faq2" class="mar-ver collapse" aria-expanded="false">
													1.下单前：空间必须是所有人可访问,必须自带1~4条原创说说!
													<br>
													2.代刷期间，禁止关闭访问权限，或者删除说说，删除说说的一律由自行负责，不给予补偿。
												</div>
											</div>
											<div class="panel panel-trans pad-top">
												<a href="#demo-acc-faq3" class="text-semibold text-lg text-main collapsed"
												data-toggle="collapse" data-parent="#demo-acc-faq" aria-expanded="false">
													空间说说赞相关下单方法讲解
												</a>
												<div id="demo-acc-faq3" class="mar-ver collapse" aria-expanded="false">
													1.下单前：空间必须是所有人可访问,必须自带1条原创说说!转发的说说不能刷！
													<br>
													2.在“QQ号码”栏目输入QQ号码，点击下面的获取说说ID并选择你需要刷的说说的ID，下单即可。
													<br>
													3.代刷期间，禁止关闭访问权限，或者删除说说，删除说说的一律由自行负责，不给予补偿。
												</div>
											</div>
											<div class="panel panel-trans pad-top">
												<a href="#demo-acc-faq4" class="text-semibold text-lg text-main collapsed"
												data-toggle="collapse" data-parent="#demo-acc-faq" aria-expanded="false">
													全民Ｋ歌业务类下单方法讲解
												</a>
												<div id="demo-acc-faq4" class="mar-ver collapse" aria-expanded="false">
													1.打开你的全名k歌
													<br>
													2.复制你全名k歌里面的需要刷的歌曲链接
													<br>
													3.例如：你歌曲链接是：
													<font color="#ff0000">
														https://kg.qq.com/node/play?s=
														<font color="green">
															881Zbk8aCfIwA8U3
														</font>
														&amp;g_f=personal
													</font>
													<br>
													4.然后把s=后面的
													<font color="green">
														881Zbk8aCfIwA8U3
													</font>
													链接填入到歌曲ID里面，然后提交购买。
												</div>
											</div>
											<div class="panel panel-trans pad-top">
												<a href="#demo-acc-faq5" class="text-semibold text-lg text-main collapsed"
												data-toggle="collapse" data-parent="#demo-acc-faq" aria-expanded="false">
													快手业务类代刷下单方法讲解
												</a>
												<div id="demo-acc-faq5" class="mar-ver collapse" aria-expanded="false">
													1.需要填写用户ID和作品ID，比如
													<font color="#ff0000">
														http://www.kuaishou.com/i/photo/lwx?userId=
														<font color="green">
															294200023
														</font>
														&amp;photoId=
														<font color="green">
															1071823418
														</font>
													</font>
													(分享作品就可以看到“复制链接”了)
													<br>
													2.用户ID就是
													<font color="green">
														294200023
													</font>
													作品ID就是
													<font color="green">
														1071823418
													</font>
													，然后在分别把用户ID和作品ID填上，请勿把两个选项填反了，不给予补单！
												</div>
											</div>
											<div class="panel panel-trans pad-top">
												<a href="#demo-acc-faq6" class="text-semibold text-lg text-main collapsed"
												data-toggle="collapse" data-parent="#demo-acc-faq" aria-expanded="false">
													永久ＱＱ会员/钻下单方法讲解
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
			if($show_article_list == '1'){
			$limit = intval($conf['articlenum']) > 0 ? intval($conf['articlenum']) : 5;
			$rs=$DB->query("SELECT id,title FROM " . DBQZ . "article WHERE active=1 ORDER BY top DESC,id DESC LIMIT {$limit}");
			$msgrow=array();
			while($res = $rs->fetch()){
				$msgrow[]=$res;
			}
			$class_arr = ['danger','warning','primary','success','info'];
			$i=0;
			if(empty($msgrow)){
				$msgrow[] = array(
					'id' => 1,
					'title' => '平台购前必看教程 - 注意事项 - 新手必读 - 协议条约'
				);
			}
			?>
				<!--文章列表-->
				<div class="block block-themed" style="border-radius: 8px; border: 1px solid #D8A0A0;">
					<div class="block-title" style="background: #FFE6E6;
							border-radius: 8px 8px 0 0;
							padding: 12px;
							border-bottom: 2px solid #D8A0A0;">
						<h4 style="color: #8B0000;
							  margin: 0;
							  font-weight: 600;
							  letter-spacing: 1px;">
							<i class="fa fa-newspaper-o" style="margin-right: 8px;"></i>
							文章列表
						</h4>
					</div>
					<div style="padding: 15px;">
						<?php foreach($msgrow as $row){
				echo '<a target="_blank" class="list-group-item" style="display: flex;
				  align-items: center;
				  padding: 12px;
				  margin-bottom: 10px;
				  background: #FFF8F8;
				  border: 1px solid #D8A0A0;
				  border-radius: 6px;
				  color: #8B0000;
				  text-decoration: none;
				  transition: all 0.3s ease;" href="'.article_url($row['id']).'">
					<span style="background: #CD5C5C;
						color: #fff;
						min-width: 28px;
						padding: 4px;
						border-radius: 4px;
						text-align: center;
						margin-right: 12px;">
							'.($i+1).'
					</span>
					'.$row['title'].'
				</a>';
				$i++;
			}?>
					</div>
				</div>
				<!--文章列表-->
				<?php } ?>

				<!--底部导航-->
				<center>
					<?php if($show_favorite_div == '1'){ ?>
					<div class="block panel-body btn btn-block animated bounceInUp btn-rounded" style="border:1px solid #b3cde3; background: url(https://s3.ax1x.com/2021/01/02/sSy9rq.png);margin-top:2px;font-size:15px;padding:2px;border-radius:10px;background-color: white;">
						<div class="block-content text-center border-t">
							<a href="javascript:void(0);" onclick="AddFavorite('货源总站',location.href)">
								<b style="text-shadow: LightSteelBlue 1px 0px 0px;">
									<i class="fa fa-heart text-danger animation-pulse"></i>
									<font color=#CB0034>本</font>
									<font color=#BE0041>站</font>
									<font color=#B1004E>网</font>
									<font color=#A4005B>址</font>
									<font color=#970068>：<?php echo $_SERVER['HTTP_HOST'];?></font>
									<font color=#2F00D0></font>
									<font color=#CB0034>&nbsp;</font>
									<font color=#CB0034>建</font>
									<font color=#BE0041>议</font>
									<font color=#B1004E>收</font>
									<font color=#A4005B>藏</font>
								</b>
							</a>
						</div>
					</div>
					<?php } ?>
					<br/><?php echo $conf['footer']?>
				</center>
	<script src="<?php echo $cdnpublic?>jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
<!-- 移除无效的音乐播放器脚本 -->
	<!--底部导航-->
		<!-- 收藏代码开始-->
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
         alert("手机用户：点击底部 “≡” 添加书签/收藏网址!\n\n电脑用户：请您按 Ctrl+D 手动收藏本网址! ");
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
	<script src="<?php echo $cdnserver ?>assets/appui/js/app.js?v=<?php echo time(); ?>"></script>
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
					$('#myModal').modal('show');
				} else if (modalShowType == 1) {
					// 只弹一次
					var modalShown = localStorage.getItem('modal_shown');
					if (!modalShown) {
						$('#myModal').modal('show');
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
		// 移除无效的计时脚本
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
$("#submit_buy").attr({'class':'btn btn-danger btn-block btn-rounded','style':'background: linear-gradient(to right, #FFCCCB, #8B0000); color: #fff; border: none;'});
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

				// 先检查$_GET变量的类型，确保正确处理
				if (typeof window.$_GET === 'function') {
					// 保存原始的$_GET函数
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
							// 关键修改：将buyok设置为0，禁用showOrder调用
							get['buyok'] = 0;
							return get;
						} else {
							return {};
						}
					};
				}

				// 临时设置querymode，禁用查询弹窗显示
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
</body>
</html>