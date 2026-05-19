<?php
/*
TG：@qqfaka
岁岁 @qqfaka
*/
/**
 * 自助下单系统
 **/
include("../includes/common.php");
$title = '彩虹自助下单系统管理中心';
include './head.php';
if ($islogin == 1) {
    // 记录访问统计
    $today = date('Y-m-d');
    $ip = x_real_ip();

    // 创建访问统计表（如果不存在）
    $DB->query("CREATE TABLE IF NOT EXISTS shua_visit_statistics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        date DATE NOT NULL UNIQUE,
        visits INT NOT NULL DEFAULT 0,
        ip_count INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // 创建IP记录表（如果不存在）
    $DB->query("CREATE TABLE IF NOT EXISTS shua_visit_ips (
        id INT AUTO_INCREMENT PRIMARY KEY,
        date DATE NOT NULL,
        ip VARCHAR(45) NOT NULL,
        url VARCHAR(255) DEFAULT '-',
        user_agent TEXT,
        region VARCHAR(100) DEFAULT '未知地区',
        visits INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_ip_date (date, ip)
    )");

    // 检查今日是否已有记录
    $today_record = $DB->getRow("SELECT * FROM shua_visit_statistics WHERE date = :date", array(':date' => $today));

    if ($today_record) {
        // 更新访问次数
        $DB->query("UPDATE shua_visit_statistics SET visits = visits + 1 WHERE date = :date", array(':date' => $today));
    } else {
        // 创建今日记录
        $DB->query("INSERT INTO shua_visit_statistics (date, visits, ip_count) VALUES (:date, 1, 0)", array(':date' => $today));
    }

    // 获取当前URL和User Agent
    $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '-';
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '-';

    // 检查当前IP今日是否已有记录
    $ip_record = $DB->getRow("SELECT * FROM shua_visit_ips WHERE date = :date AND ip = :ip", array(':date' => $today, ':ip' => $ip));

    if ($ip_record) {
        // 更新IP访问次数和最新URL
        $DB->query("UPDATE shua_visit_ips SET visits = visits + 1, url = :url, user_agent = :user_agent WHERE date = :date AND ip = :ip", array(':date' => $today, ':ip' => $ip, ':url' => $url, ':user_agent' => $user_agent));
    } else {
        // 创建新IP记录
        $DB->query("INSERT INTO shua_visit_ips (date, ip, url, user_agent) VALUES (:date, :ip, :url, :user_agent)", array(':date' => $today, ':ip' => $ip, ':url' => $url, ':user_agent' => $user_agent));
        // 更新今日IP数
        $DB->query("UPDATE shua_visit_statistics SET ip_count = ip_count + 1 WHERE date = :date", array(':date' => $today));
    }
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
?>
<?php
$mysqlversion = $DB->getColumn("select VERSION()");
$sec_msg = sec_check();
?>
<style>
	/* 企业风格样式 */
	/* 移除所有渐变和新拟态效果 */

	/* 统计卡片样式 - 企业风格 */
	.stat-card {
		display: flex;
		align-items: center;
		padding: 15px;
		border-radius: 4px;
		background: #ffffff;
		border: 1px solid #e0e0e0;
		margin-bottom: 15px;
		width: 100%; /* 确保卡片占满父容器 */
	}
	.stat-icon {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 45px;
		height: 45px;
		border-radius: 4px;
		margin-right: 12px;
		font-size: 18px;
		flex-shrink: 0;
	}
	.stat-content h3 {
		margin: 0;
		font-size: 16px;
		font-weight: 600;
		color: #2c3e50;
	}
	.stat-content p {
		margin: 0;
		font-size: 13px;
		color: #7f8c8d;
	}

	/* 卡片样式 - 企业风格 */
	.neumorphic-card {
		background: #ffffff;
		border-radius: 4px;
		border: 1px solid #e0e0e0;
		padding: 20px;
		margin-bottom: 20px;
	}
	.neumorphic-inset {
		background: #f8f9fa;
		border-radius: 4px;
		border: 1px solid #e0e0e0;
		padding: 15px;
	}

	/* 企业配色方案 */
	.gradient-primary {
		background: #1890ff;
		color: white;
	}
	.gradient-success {
		background: #52c41a;
		color: white;
	}
	.gradient-warning {
		background: #faad14;
		color: white;
	}
	.gradient-danger {
		background: #f5222d;
		color: white;
	}
	.gradient-info {
		background: #13c2c2;
		color: white;
	}
	.gradient-amber {
		background: #fa8c16;
		color: white;
	}
	.gradient-blue {
		background: #40a9ff;
		color: white;
	}

	/* 文本颜色类 - 企业风格 */
	.text-gradient-primary {
		color: #1890ff;
	}
	.text-gradient-success {
		color: #52c41a;
	}
	.text-gradient-warning {
		color: #faad14;
	}
	.text-gradient-danger {
		color: #f5222d;
	}
	.text-gradient-info {
		color: #13c2c2;
	}

	/* 响应式优化 */
	@media (max-width: 767px) {
		/* 手机端样式调整 */
		.stat-card {
			padding: 12px;
			min-width: auto;
		}
		.stat-icon {
			width: 40px;
			height: 40px;
			font-size: 16px;
			margin-right: 10px;
		}
		.stat-content h3 {
			font-size: 14px;
		}
		.stat-content p {
			font-size: 12px;
		}
	}

	@media (max-width: 480px) {
		/* 超小屏幕样式进一步优化 */
		.stat-card {
			flex-direction: column;
			justify-content: center;
			text-align: center;
			padding: 10px;
		}
		.stat-icon {
			margin-right: 0;
			margin-bottom: 8px;
			width: 35px;
			height: 35px;
			font-size: 14px;
		}
		.stat-content h3 {
			font-size: 13px;
		}
		.stat-content p {
			font-size: 11px;
		}
	}

	/* 图表容器样式 */
	.chart-container {
		position: relative;
		height: 300px;
		width: 100%;
		overflow: hidden;
	}

	/* 确保canvas元素不会超出容器 */
	.chart-container canvas {
		max-width: 100% !important;
		height: auto !important;
	}

	/* 自定义滚动条 */
	::-webkit-scrollbar {
		width: 8px;
		height: 8px;
	}
	::-webkit-scrollbar-track {
		background: #f1f1f1;
		border-radius: 4px;
	}
	::-webkit-scrollbar-thumb {
		background: #c1c1c1;
		border-radius: 4px;
	}
	::-webkit-scrollbar-thumb:hover {
		background: #a8a8a8;
	}

	/* 企业风格网格布局 */
	.grid {
		display: grid;
		grid-gap: 15px;
	}
	.grid-cols-2 {
		grid-template-columns: repeat(2, 1fr);
	}

	/* 移除所有过渡效果 */
	* {
		transition: none !important;
	}
</style>

<!-- 顶部数据统计卡片区域 -->
<div class="row">
	<div class="col-xs-6 col-sm-6 col-md-4 col-lg-3">
		<div class="stat-card" style="cursor: pointer;" onclick="window.location.href='./list.php'">
			<div class="stat-icon gradient-primary">
				<i class="fa fa-list-ol"></i>
			</div>
			<div class="stat-content">
				<h3 class="text-gradient-primary"><span id="count1"></span></h3>
				<p>订单总数</p>
			</div>
		</div>
	</div>

	<div class="col-xs-6 col-sm-6 col-md-4 col-lg-3">
		<div class="stat-card" style="cursor: pointer;" onclick="window.location.href='./list.php?type=0'">
			<div class="stat-icon gradient-warning">
				<i class="fa fa-hourglass-half"></i>
			</div>
			<div class="stat-content">
				<h3 class="text-gradient-warning"><span id="count3"></span></h3>
				<p>待处理订单</p>
			</div>
		</div>
	</div>

	<div class="col-xs-6 col-sm-6 col-md-4 col-lg-3">
		<div class="stat-card" style="cursor: pointer;" onclick="window.location.href='./list.php'">
			<div class="stat-icon gradient-success">
				<i class="fa fa-calendar-check-o"></i>
			</div>
			<div class="stat-content">
				<h3 class="text-gradient-success">+ <span id="count4"></span></h3>
				<p>今日订单数</p>
			</div>
		</div>
	</div>

	<div class="col-xs-6 col-sm-6 col-md-4 col-lg-3">
		<div class="stat-card" style="cursor: pointer;" onclick="window.location.href='./list.php'">
			<div class="stat-icon gradient-info">
				<i class="fa fa-line-chart"></i>
			</div>
			<div class="stat-content">
				<h3 class="text-gradient-info">￥<span id="count5"></span></h3>
				<p>今日交易额</p>
			</div>
		</div>
	</div>

	<div class="col-xs-6 col-sm-6 col-md-4 col-lg-3">
		<div class="stat-card" style="cursor: pointer;" onclick="window.location.href='./record.php'">
			<div class="stat-icon gradient-amber">
				<i class="fa fa-money"></i>
			</div>
			<div class="stat-content">
				<h3 class="text-gradient-warning">￥<span id="count15"></span></h3>
				<p>今日收益</p>
			</div>
		</div>
	</div>

	<div class="col-xs-6 col-sm-6 col-md-4 col-lg-3">
		<div class="stat-card" style="cursor: pointer;" onclick="window.location.href='./record.php'">
			<div class="stat-icon gradient-info">
				<i class="fa fa-history"></i>
			</div>
			<div class="stat-content">
				<h3 class="text-gradient-info">￥<span id="count16"></span></h3>
				<p>昨日收益</p>
			</div>
		</div>
	</div>
	<div class="col-xs-6 col-sm-6 col-md-4 col-lg-3">
		<div class="stat-card">
			<div class="stat-icon gradient-info">
				<i class="fa fa-eye"></i>
			</div>
			<div class="stat-content">
				<h3 class="text-gradient-info"><span id="visit_today">0</span></h3>
				<p>今日访问量</p>
			</div>
		</div>
	</div>

	<div class="col-xs-6 col-sm-6 col-md-4 col-lg-3">
		<div class="stat-card">
			<div class="stat-icon gradient-primary">
				<i class="fa fa-users"></i>
			</div>
			<div class="stat-content">
				<h3 class="text-gradient-primary"><span id="ip_today">0</span></h3>
				<p>今日独立IP</p>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<!-- 主要内容区域 -->
	<div class="col-sm-8">
		<!-- 一周交易与订单统计图表 -->
		<div class="neumorphic-card">
			<h3 class="text-center mb-4 text-gradient-primary"><i class="fa fa-bar-chart"></i> 一周交易数据分析</h3>
			<div class="neumorphic-inset mb-4">
				<div id="chart-classic-dash" class="chart-container">
				</div>
			</div>
			<div class="row text-center">
				<div class="col-xs-4 push-inner-top-bottom border-right">
					<h4 class="text-gradient-primary"><i class="fa fa-qq push-bit"></i>&nbsp;QQ钱包交易额<br>
						<strong>￥<span id="count12"></span></strong>
					</h4>
				</div>
				<div class="col-xs-4 push-inner-top-bottom">
					<h4 class="text-gradient-success"><i class="fa fa-weixin push-bit"></i>&nbsp;微信交易额<br>
						<strong>￥<span id="count13"></span></strong>
					</h4>
				</div>
				<div class="col-xs-4 push-inner-top-bottom border-left">
					<h4 class="text-gradient-warning"><i class="fa fa-credit-card push-bit"></i>&nbsp;支付宝交易额<br>
						<strong>￥<span id="count14"></span></strong>
					</h4>
				</div>
			</div>
		</div>

		<!-- 站点维护记录 -->
		<div class="row">
			<div class="col-sm-12">
				<div class="neumorphic-card">
					<h3 class="text-center mb-4 text-gradient-primary"><i class="fa fa-list-alt"></i> 更新记录</h3>
					<div class="neumorphic-inset">
						<ul class="list-group">
							<li class="list-group-item bg-transparent border-0"><p>当前基线：6V6 1.29 修复版，已合并磁盘显示、移动位置、菜单等最新修复。</p></li>
							<li class="list-group-item bg-transparent border-0"><p>维护标识：岁岁 @qqfaka。后续变更会同步写入程序内更新日志与仓库 CHANGELOG。</p></li>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<!-- 右侧信息区域 -->
	<div class="col-sm-4">
		<!-- 分站统计 -->
		<div class="neumorphic-card">
			<h3 class="text-center mb-4 text-gradient-primary"><i class="fa fa-sitemap"></i> 分站统计</h3>
			<div class="row">
				<div class="col-xs-6">
					<div class="neumorphic-inset text-center">
						<i class="fa fa-users text-gradient-primary" style="font-size: 24px; margin-bottom: 10px;"></i>
						<h4 class="text-gradient-primary"><span id="count6"></span></h4>
						<p style="font-size: 12px; color: #7f8c8d; margin: 0;">分站/用户总数</p>
					</div>
				</div>
				<div class="col-xs-6">
					<div class="neumorphic-inset text-center">
						<i class="fa fa-plus-circle text-gradient-success" style="font-size: 24px; margin-bottom: 10px;"></i>
						<h4 class="text-gradient-success"><span id="count7"></span></h4>
						<p style="font-size: 12px; color: #7f8c8d; margin: 0;">今日新开分站</p>
					</div>
				</div>
				<div class="col-xs-6">
					<div class="neumorphic-inset text-center">
						<i class="fa fa-percent text-gradient-warning" style="font-size: 24px; margin-bottom: 10px;"></i>
						<h4 class="text-gradient-warning">￥<span id="count8"></span></h4>
						<p style="font-size: 12px; color: #7f8c8d; margin: 0;">今日分站提成</p>
					</div>
				</div>
				<div class="col-xs-6">
					<div class="neumorphic-inset text-center">
						<i class="fa fa-exchange text-gradient-danger" style="font-size: 24px; margin-bottom: 10px;"></i>
						<h4><a id="count11" href="tixian.php" class="text-gradient-danger">￥<span id="count11_val"></span></a></h4>
						<p style="font-size: 12px; color: #7f8c8d; margin: 0;">待处理提现</p>
					</div>
				</div>
			</div>
		</div>

		<!-- 系统信息 -->
		<div class="neumorphic-card">
			<h3 class="text-center mb-4 text-gradient-primary"><i class="fa fa-server"></i> 系统信息</h3>
			<div class="neumorphic-inset">
				<ul class="list-group">
					<li class="list-group-item bg-transparent border-0"><i class="fa fa-code text-gradient-info"></i> <b>PHP 版本：</b><?php echo phpversion() ?></li>
					<li class="list-group-item bg-transparent border-0"><i class="fa fa-database text-gradient-success"></i> <b>MySQL 版本：</b><?php echo $mysqlversion ?></li>
					<li class="list-group-item bg-transparent border-0"><i class="fa fa-globe text-gradient-warning"></i> <b>服务器软件：</b><?php echo $_SERVER['SERVER_SOFTWARE'] ?></li>
					<li class="list-group-item bg-transparent border-0"><i class="fa fa-clock-o text-gradient-primary"></i> <b>服务器时间：</b><?php echo $date ?></li>
				</ul>
			</div>
		</div>

		<!-- 安全中心 -->
		<div class="neumorphic-card">
			<h3 class="text-center mb-4 text-gradient-primary"><i class="fa fa-shield"></i> 安全中心</h3>
			<div class="neumorphic-inset">
				<ul class="list-group">
					<li class="list-group-item bg-transparent border-0" id="updatemsg">正在获取中...</li>
					<?php
					foreach ($sec_msg as $row) {
						echo $row;
					}
					if (count($sec_msg) == 0) echo '<li class="list-group-item bg-transparent border-0"><span class="btn-sm btn-success">正常</span>&nbsp;<font color="#006400">暂未发现网站安全问题</li></font>';
					?>
				</ul>
			</div>
		</div>

		<!-- 待处理工单 -->
		<div class="neumorphic-card">
			<h3 class="text-center mb-4 text-gradient-primary"><i class="fa fa-comments"></i> 工单提醒</h3>
			<div class="neumorphic-inset text-center">
				<i class="fa fa-bell-o text-gradient-primary" style="font-size: 24px; margin-bottom: 10px;"></i>
				<p>待处理工单数量：<a id="count17" href="workorder.php" class="text-gradient-primary">0</a> 个</p>
			</div>
		</div>
	</div>
</div>
<script>
	$(document).ready(function() {

		$('#title').html('正在加载数据中...');

		$.ajax({
			type: "POST",
			url: "index.php",
			data:{'SF_Action':'check'},
			dataType: 'json',
			success: function(data) {
			    if(data.code == 0){
                    // code为1代表需要更新，0则不需要
                    if(data.data.code == 1){
                        $("#updatemsg").html('<b><font color="#FF0000">通知:发现新版本 V'+data.data.data.edition+'</font></b> <a href="update.php">点击更新</a>');
                    }else{
                        $("#updatemsg").html('<font color="#DC143C">您当前已是最新版本</font>');
                    }
                }else{
                    $("#updatemsg").html(data.msg);
                }
			},
		    error :function(data){
			    $("#updatemsg").html('链接服务器成功！');
			}
		});
		$.ajax({
			type: "GET",
			url: "ajax.php?act=getcount",
			dataType: 'json',
			async: true,
			success: function(data) {
				$('#title').html('后台管理首页');
				$('#yxts').html(data.yxts);
				$('#count1').html(data.count1);
				$('#count2').html(data.count2);
				$('#count3').html(data.count3);
				$('#count4').html(data.count4);
				$('#count5').html(data.count5);
				$('#count6').html(data.count6);
				$('#count7').html(data.count7);
				$('#count8').html(data.count8);
				$('#count9').html(data.count9);
				$('#count10').html(data.count10);
				$('#count11').attr('href', 'tixian.php');
				$('#count11_val').html(data.count11);
				$('#count12').html(data.count12);
				$('#count13').html(data.count13);
				$('#count14').html(data.count14);
				$('#count15').html(data.count15);
				$('#count16').html(data.count16);
				$('#count17').html(data.count17);

				// 显示访问统计数据
				if(data.visit_today !== undefined) $('#visit_today').text(data.visit_today);
				if(data.ip_today !== undefined) $('#ip_today').text(data.ip_today);



				// 绘制图表
				var t = $("#chart-classic-dash");

				// 图表配置
				var chartOptions = {
					colors: ['#5BC0DE', '#5CB85C'],
					shadowSize: 0,
					legend: {
						show: !0,
						position: "nw",
						backgroundOpacity: 0
					},
					grid: {
						borderWidth: 0,
						hoverable: !0,
						clickable: !0
					},
					yaxis: {
						show: !1,
						ticks: 3
					},
					xaxis: {
						ticks: data.chart.date,
						tickFormatter: function(val, axis) {
							return data.chart.date[val];
						}
					}
				};

				// 图表数据
				var chartData = [{
					label: "订单量",
					data: data.chart.orders,
					lines: {
						show: !0,
						fill: !0,
						fillColor: {
							colors: [{
								opacity: .6
							}, {
								opacity: .6
							}]
						}
					},
					points: {
						show: !0,
						radius: 5
					}
				}, {
					label: "交易量",
					data: data.chart.money,
					lines: {
						show: !0,
						fill: !0,
						fillColor: {
							colors: [{
								opacity: .6
							}, {
								opacity: .6
							}]
						}
					},
					points: {
						show: !0,
						radius: 5
					}
				}];

				// 初始化图表
				var plot = $.plot(t, chartData, chartOptions);

				// 添加窗口大小改变时的重绘逻辑
				$(window).resize(function() {
					// 清除之前的图表
					t.empty();
					// 重绘图表，使用新的容器尺寸
					plot = $.plot(t, chartData, chartOptions);
				});

		// 如果有访问数据，绘制访问统计图表
		if(data.visit_chart) {
			// 创建访问统计图表区域
			var visitChartHtml = `
			<div class="row">
				<div class="col-sm-12">
					<div class="neumorphic-card">
						<div class="d-flex justify-content-between align-items-center mb-4">
							<h3 class="text-gradient-primary mb-0"><i class="fa fa-area-chart"></i> 一周访问统计</h3>
							<button class="btn btn-primary btn-sm" id="viewVisitDetails"><i class="fa fa-eye"></i> 详细查看</button>
						</div>
						<div class="neumorphic-inset mb-4">
							<div id="visit-chart" class="chart-container"></div>
						</div>
					</div>
				</div>
			</div>`;

			// 添加访问详情模态框
			var visitModalHtml = `
			<div class="modal fade" id="visitDetailModal" tabindex="-1" role="dialog" aria-labelledby="visitDetailModalLabel">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title text-gradient-primary" id="visitDetailModalLabel"><i class="fa fa-list-alt"></i> 访问详情</h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
						<div class="modal-body">
							<div class="table-responsive">
								<table class="table table-hover">
									<thead>
								<tr>
									<th>IP地址</th>
									<th>访问URL</th>
									<th>地区</th>
									<th>访问时间</th>
									<th>访问次数</th>
									<th>浏览器信息</th>
								</tr>
							</thead>
									<tbody id="visitDetailTable"></tbody>
								</table>
							</div>
							<div id="visitDetailEmpty" class="text-center py-10" style="display: none;">
								<i class="fa fa-info-circle text-5xl text-gradient-primary mb-4"></i>
								<p class="text-gray-600">暂无访问记录</p>
							</div>
							<div id="visitDetailLoading" class="text-center py-10">
								<i class="fa fa-spinner fa-spin text-3xl text-gradient-primary"></i>
								<p class="mt-2 text-gray-600">正在加载访问记录...</p>
							</div>
							<div class="modal-footer">
								<nav class="pagination-container">
									<ul class="pagination" id="visitDetailPagination"></ul>
								</nav>
							</div>
						</div>
					</div>
				</div>
			</div>`;

			// 将模态框添加到页面
			$('body').append(visitModalHtml);

			// 插入到原图表之后
			$("#chart-classic-dash").closest(".neumorphic-card").after(visitChartHtml);

			// 绘制访问统计图表
				var v = $("#visit-chart");

				// 访问统计图表配置
				var visitChartOptions = {
					colors: ['#3b82f6', '#4cc9f0'], // 蓝色主题配色
					shadowSize: 0,
					legend: {
						show: !0,
						position: "nw",
						backgroundOpacity: 0
					},
					grid: {
						borderWidth: 0,
						hoverable: !0,
						clickable: !0
					},
					yaxis: {
						show: !1,
						ticks: 3
					},
					xaxis: {
						ticks: data.visit_chart.date,
						tickFormatter: function(val, axis) {
							return data.visit_chart.date[val];
						}
					}
				};

				// 访问统计图表数据
				var visitChartData = [{
					label: "访问量",
					data: data.visit_chart.visits,
					lines: {
						show: !0,
						fill: !0,
						fillColor: {
							colors: [{
								opacity: .6
							}, {
								opacity: .6
							}]
						}
					},
					points: {
						show: !0,
						radius: 5
					}
				}, {
					label: "独立IP",
					data: data.visit_chart.ips,
					lines: {
						show: !0,
						fill: !0,
						fillColor: {
							colors: [{
								opacity: .6
							}, {
								opacity: .6
							}]
						}
					},
					points: {
						show: !0,
						radius: 5
					}
				}];

				// 初始化访问统计图表
				var visitPlot = $.plot(v, visitChartData, visitChartOptions);

				// 添加窗口大小改变时的重绘逻辑
				$(window).resize(function() {
					// 清除之前的图表
					v.empty();
					// 重绘图表，使用新的容器尺寸
					visitPlot = $.plot(v, visitChartData, visitChartOptions);
				});

			var visitPreviousPoint = null;
			v.bind("plothover", function(event, pos, item) {
				if (item) {
					if (visitPreviousPoint != item.dataIndex) {
						visitPreviousPoint = item.dataIndex;
						$("#tooltip").remove();
						var x = item.datapoint[0].toFixed(2),
							y = item.datapoint[1].toFixed(2);
						showTooltip(item.pageX, item.pageY,
							item.series.label + "：" + y);
					}
				} else {
					$("#tooltip").remove();
					visitPreviousPoint = null;
				}
			});
		}

				var previousPoint = null;
				t.bind("plothover", function(event, pos, item) {
					$("#x").text(pos.x.toFixed(2));
					$("#y").text(pos.y.toFixed(2));
					if (item) {
						if (previousPoint != item.dataIndex) {
							previousPoint = item.dataIndex;
							$("#tooltip").remove();
							var x = item.datapoint[0].toFixed(2),
								y = item.datapoint[1].toFixed(2);
							showTooltip(item.pageX, item.pageY,
								item.series.label + "：" + (item.seriesIndex == 1 ? "￥" + y : y) + (item.seriesIndex == 0 ? " 订单" : " 张"));
						}
					} else {
						$("#tooltip").remove();
						previousPoint = null;
					}
				});

				function showTooltip(x, y, contents) {
				$("<div id='tooltip'>" + contents + "</div>").css({
					position: "absolute",
					display: "none",
					top: y - 30,
					left: x + 5,
					border: "1px solid #fdd",
					padding: "2px",
					"background-color": "#fee",
					opacity: 0.80
				}).appendTo("body").fadeIn(200);
			}

			// 访问详情功能
			let currentPage = 1;
			const pageSize = 20;

			// 详细查看按钮点击事件
			$(document).on('click', '#viewVisitDetails', function() {
				currentPage = 1;
				loadVisitDetails(currentPage);
				$('#visitDetailModal').modal('show');
			});

			// 加载访问详情数据
			function loadVisitDetails(page) {
				$('#visitDetailLoading').show();
				$('#visitDetailTable').empty();
				$('#visitDetailEmpty').hide();
				$('#visitDetailPagination').empty();

				$.ajax({
					type: 'GET',
					url: 'ajax.php?act=get_visit_details',
					data: {
						page: page,
						pageSize: pageSize
					},
					dataType: 'json',
					success: function(data) {
						$('#visitDetailLoading').hide();

						if (data.code === 0) {
							// 显示访问记录
							if (data.visits && data.visits.length > 0) {
								$.each(data.visits, function(index, visit) {
									const row = `
										<tr>
											<td>${visit.ip}</td>
											<td>${visit.url}</td>
											<td>${visit.region}</td>
											<td>${visit.visit_time}</td>
											<td>${visit.visits}</td>
											<td class="text-break" style="max-width: 200px;">${visit.user_agent}</td>
										</tr>
									`;
									$('#visitDetailTable').append(row);
								});

								// 生成分页
								generatePagination(data.total, data.page, data.pageSize);
							} else {
								$('#visitDetailEmpty').show();
							}
						} else if (data.code === 1) {
							// 表不存在
							$('#visitDetailEmpty').show();
							$('#visitDetailEmpty').html(`
								<i class="fa fa-info-circle text-5xl text-gradient-primary mb-4"></i>
								<p class="text-gray-600">${data.msg}</p>
							`);
						} else {
							// 其他错误
							alert('加载失败：' + data.msg);
						}
					},
					error: function() {
						$('#visitDetailLoading').hide();
						alert('网络错误，无法加载访问记录');
					}
				});
			}

			// 生成分页控件
			function generatePagination(total, current, pageSize) {
				const totalPages = Math.ceil(total / pageSize);
				const maxVisible = 5; // 最多显示的页码数
				let startPage = Math.max(1, current - Math.floor(maxVisible / 2));
				let endPage = Math.min(totalPages, startPage + maxVisible - 1);

				if (endPage - startPage + 1 < maxVisible) {
					startPage = Math.max(1, endPage - maxVisible + 1);
				}

				// 首页
				if (startPage > 1) {
					$('#visitDetailPagination').append(`<li><a href="#" data-page="1">首页</a></li>`);
					$('#visitDetailPagination').append(`<li class="disabled"><a href="#">...</a></li>`);
				}

				// 页码
				for (let i = startPage; i <= endPage; i++) {
					const activeClass = i === current ? 'active' : '';
					$('#visitDetailPagination').append(`<li class="${activeClass}"><a href="#" data-page="${i}">${i}</a></li>`);
				}

				// 末页
				if (endPage < totalPages) {
					$('#visitDetailPagination').append(`<li class="disabled"><a href="#">...</a></li>`);
					$('#visitDetailPagination').append(`<li><a href="#" data-page="${totalPages}">末页</a></li>`);
				}

				// 分页点击事件
				$('#visitDetailPagination a[data-page]').click(function(e) {
					e.preventDefault();
					const page = parseInt($(this).data('page'));
					if (page !== currentPage) {
						currentPage = page;
						loadVisitDetails(currentPage);
					}
				});
			}

			// 模态框关闭时重置状态
			$('#visitDetailModal').on('hidden.bs.modal', function() {
				currentPage = 1;
			})

		}
		});
	})
</script>