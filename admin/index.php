<?php
/**
 * 岁岁云商城管理中心
 * 维护：岁岁 @qqfaka
 */
include("../includes/common.php");
$title = '岁岁云商城管理中心';
include './head.php';
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

$mysqlversion = $DB->getColumn("select VERSION()");
$dashboardAssetVersion = isset($adminAssetVersion) ? $adminAssetVersion : ((defined('VERSION') ? VERSION : '1.0.0') . '.20260426admin37');
$todayDate = date('Y-m-d');
?>
<div class="col-xs-12 admin-dashboard-page">
    <div class="admin-stats-grid">
        <a class="admin-stat-card admin-stat-card--primary" href="./list.php" aria-label="查看订单总数" title="查看全部订单">
            <div class="admin-stat-card__icon">
                <i class="fa fa-list-ol"></i>
            </div>
            <div class="admin-stat-card__content">
                <p class="admin-stat-card__value"><span id="count1">--</span></p>
                <p class="admin-stat-card__label">订单总数</p>
            </div>
        </a>

        <a class="admin-stat-card admin-stat-card--success" href="./list.php?type=1" aria-label="查看已完成订单" title="查看已完成订单">
            <div class="admin-stat-card__icon">
                <i class="fa fa-check-circle"></i>
            </div>
            <div class="admin-stat-card__content">
                <p class="admin-stat-card__value"><span id="count2">--</span></p>
                <p class="admin-stat-card__label">已完成订单</p>
            </div>
        </a>

        <a class="admin-stat-card admin-stat-card--warning" href="./list.php?type=0" aria-label="查看待处理订单" title="查看待处理订单">
            <div class="admin-stat-card__icon">
                <i class="fa fa-hourglass-half"></i>
            </div>
            <div class="admin-stat-card__content">
                <p class="admin-stat-card__value"><span id="count3">--</span></p>
                <p class="admin-stat-card__label">待处理订单</p>
            </div>
        </a>

        <a class="admin-stat-card admin-stat-card--info" href="./list.php?starttime=<?php echo $todayDate; ?>&amp;endtime=<?php echo $todayDate; ?>" aria-label="查看今日订单" title="查看今日订单">
            <div class="admin-stat-card__icon">
                <i class="fa fa-calendar-check-o"></i>
            </div>
            <div class="admin-stat-card__content">
                <p class="admin-stat-card__value"><span id="count4">--</span></p>
                <p class="admin-stat-card__label">今日订单数</p>
            </div>
        </a>

        <a class="admin-stat-card admin-stat-card--violet" href="./payorder.php?dstatus=2&amp;starttime=<?php echo $todayDate; ?>&amp;endtime=<?php echo $todayDate; ?>" aria-label="查看今日已支付记录" title="查看今日已支付记录">
            <div class="admin-stat-card__icon">
                <i class="fa fa-line-chart"></i>
            </div>
            <div class="admin-stat-card__content">
                <p class="admin-stat-card__value">￥<span id="count5">--</span></p>
                <p class="admin-stat-card__label">今日交易额</p>
            </div>
        </a>

        <a class="admin-stat-card admin-stat-card--info" href="./userlist.php" aria-label="查看全站用户总余额" title="统计所有用户账户余额">
            <div class="admin-stat-card__icon">
                <i class="fa fa-database"></i>
            </div>
            <div class="admin-stat-card__content">
                <p class="admin-stat-card__value">￥<span id="count21">--</span></p>
                <p class="admin-stat-card__label">全站用户总余额</p>
            </div>
        </a>

        <a class="admin-stat-card admin-stat-card--success" href="./record.php" aria-label="查看今日收益" title="收支明细页顶部展示今日收益">
            <div class="admin-stat-card__icon">
                <i class="fa fa-money"></i>
            </div>
            <div class="admin-stat-card__content">
                <p class="admin-stat-card__value">￥<span id="count15">--</span></p>
                <p class="admin-stat-card__label">今日收益</p>
            </div>
        </a>

        <a class="admin-stat-card admin-stat-card--primary" href="./record.php" aria-label="查看昨日收益" title="收支明细页顶部展示昨日收益">
            <div class="admin-stat-card__icon">
                <i class="fa fa-history"></i>
            </div>
            <div class="admin-stat-card__content">
                <p class="admin-stat-card__value">￥<span id="count16">--</span></p>
                <p class="admin-stat-card__label">昨日收益</p>
            </div>
        </a>

        <a class="admin-stat-card admin-stat-card--info" href="./index.php#visitChartSection" aria-label="查看今日访问量" title="当前访问统计在后台首页展示，没有独立列表页">
            <div class="admin-stat-card__icon">
                <i class="fa fa-eye"></i>
            </div>
            <div class="admin-stat-card__content">
                <p class="admin-stat-card__value"><span id="visit_today">--</span></p>
                <p class="admin-stat-card__label">今日访问量</p>
            </div>
        </a>

        <a class="admin-stat-card admin-stat-card--danger" href="./index.php#visitChartSection" aria-label="查看今日独立 IP" title="当前访问统计在后台首页展示，没有独立列表页">
            <div class="admin-stat-card__icon">
                <i class="fa fa-users"></i>
            </div>
            <div class="admin-stat-card__content">
                <p class="admin-stat-card__value"><span id="ip_today">--</span></p>
                <p class="admin-stat-card__label">今日独立 IP</p>
            </div>
        </a>

        <a class="admin-stat-card admin-stat-card--success" href="./shoplist.php?status=up" aria-label="查看上架商品" title="商品列表当前支持上架状态筛选，不支持今日上架时间筛选">
            <div class="admin-stat-card__icon">
                <i class="fa fa-arrow-circle-up"></i>
            </div>
            <div class="admin-stat-card__content">
                <p class="admin-stat-card__value"><span id="count18">--</span></p>
                <p class="admin-stat-card__label">今日上架商品</p>
            </div>
        </a>

        <a class="admin-stat-card admin-stat-card--warning" href="./shoplist.php?status=down" aria-label="查看下架商品" title="商品列表当前支持下架状态筛选，不支持今日下架时间筛选">
            <div class="admin-stat-card__icon">
                <i class="fa fa-arrow-circle-down"></i>
            </div>
            <div class="admin-stat-card__content">
                <p class="admin-stat-card__value"><span id="count19">--</span></p>
                <p class="admin-stat-card__label">今日下架商品</p>
            </div>
        </a>

        <a class="admin-stat-card admin-stat-card--info" href="./set.php?mod=qiandao" aria-label="查看签到设置" title="当前后台没有独立签到记录列表页，先跳转签到设置">
            <div class="admin-stat-card__icon">
                <i class="fa fa-calendar-plus-o"></i>
            </div>
            <div class="admin-stat-card__content">
                <p class="admin-stat-card__value"><span id="count20">--</span></p>
                <p class="admin-stat-card__label">今日签到人数</p>
            </div>
        </a>
    </div>
    <?php echo q8_render_action('admin_dashboard_after_stat_cards', array('date' => $todayDate)); ?>

    <div class="row">
        <div class="col-lg-8">
            <section class="admin-dashboard-panel">
                <div class="admin-dashboard-panel__header">
                    <div>
                        <h2 class="admin-dashboard-panel__title"><i class="fa fa-area-chart"></i> 一周交易数据分析</h2>
                        <p class="admin-dashboard-panel__subtitle">统一查看最近 7 天的订单与支付走势。</p>
                    </div>
                </div>
                <div class="admin-dashboard-chart-shell">
                    <div id="chart-classic-dash" class="admin-dashboard-chart">
                        <div class="admin-dashboard-loading-state admin-dashboard-chart-state">
                            <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
                            <p>正在加载一周交易数据...</p>
                        </div>
                    </div>
                </div>
                <div class="admin-dashboard-kpi-grid">
                    <div class="admin-dashboard-kpi">
                        <p class="admin-dashboard-kpi__label">QQ 钱包交易额</p>
                        <p class="admin-dashboard-kpi__value">￥<span id="count12">--</span></p>
                    </div>
                    <div class="admin-dashboard-kpi">
                        <p class="admin-dashboard-kpi__label">微信交易额</p>
                        <p class="admin-dashboard-kpi__value">￥<span id="count13">--</span></p>
                    </div>
                    <div class="admin-dashboard-kpi">
                        <p class="admin-dashboard-kpi__label">支付宝交易额</p>
                        <p class="admin-dashboard-kpi__value">￥<span id="count14">--</span></p>
                    </div>
                </div>
            </section>

            <section class="admin-dashboard-panel" id="visitChartSection">
                <div class="admin-dashboard-panel__header">
                    <div>
                        <h2 class="admin-dashboard-panel__title"><i class="fa fa-line-chart"></i> 一周访问统计</h2>
                        <p class="admin-dashboard-panel__subtitle">统计前台页面访问，不包含后台、接口和静态资源请求。</p>
                    </div>
                    <button type="button" class="btn btn-primary" id="viewVisitDetails">
                        <i class="fa fa-list-alt"></i> 详细查看
                    </button>
                </div>
                <div class="admin-dashboard-visit-layout">
                    <div class="admin-dashboard-chart-shell admin-dashboard-chart-shell--visit">
                        <div id="visit-chart" class="admin-dashboard-chart">
                            <div class="admin-dashboard-loading-state admin-dashboard-chart-state">
                                <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
                                <p>正在加载一周访问统计...</p>
                            </div>
                        </div>
                    </div>
                    <div class="admin-dashboard-visit-meta">
                        <div class="admin-dashboard-visit-meta__item">
                            <span class="admin-dashboard-visit-meta__label">统计周期</span>
                            <strong id="visitChartRange">--</strong>
                        </div>
                        <div class="admin-dashboard-visit-meta__item">
                            <span class="admin-dashboard-visit-meta__label">数据来源</span>
                            <strong>前台访问统计</strong>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="admin-dashboard-panel">
                <div class="admin-dashboard-panel__header">
                    <div>
                        <h2 class="admin-dashboard-panel__title"><i class="fa fa-sitemap"></i> 分站统计</h2>
                        <p class="admin-dashboard-panel__subtitle">把分站、提成、提现状态集中到右侧决策区。</p>
                    </div>
                </div>
                <div class="admin-dashboard-mini-grid">
                    <div class="admin-dashboard-mini-card">
                        <div class="admin-dashboard-mini-card__icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <p class="admin-dashboard-mini-card__value"><span id="count6">--</span></p>
                        <p class="admin-dashboard-mini-card__label">分站 / 用户总数</p>
                    </div>
                    <div class="admin-dashboard-mini-card">
                        <div class="admin-dashboard-mini-card__icon">
                            <i class="fa fa-plus-circle"></i>
                        </div>
                        <p class="admin-dashboard-mini-card__value"><span id="count7">--</span></p>
                        <p class="admin-dashboard-mini-card__label">今日新开分站</p>
                    </div>
                    <div class="admin-dashboard-mini-card">
                        <div class="admin-dashboard-mini-card__icon">
                            <i class="fa fa-percent"></i>
                        </div>
                        <p class="admin-dashboard-mini-card__value">￥<span id="count8">--</span></p>
                        <p class="admin-dashboard-mini-card__label">今日分站提成</p>
                    </div>
                    <div class="admin-dashboard-mini-card">
                        <div class="admin-dashboard-mini-card__icon">
                            <i class="fa fa-exchange"></i>
                        </div>
                        <p class="admin-dashboard-mini-card__value"><a id="count11" href="tixian.php" class="admin-dashboard-mini-card__link">￥<span id="count11_val">--</span></a></p>
                        <p class="admin-dashboard-mini-card__label">待处理提现</p>
                    </div>
                </div>
            </section>

            <section class="admin-dashboard-panel">
                <div class="admin-dashboard-panel__header">
                    <div>
                        <h2 class="admin-dashboard-panel__title"><i class="fa fa-server"></i> 系统信息</h2>
                        <p class="admin-dashboard-panel__subtitle">保留后台常用环境信息，方便排障和核对。</p>
                    </div>
                </div>
                <ul class="list-group admin-dashboard-list">
                    <li class="list-group-item"><i class="fa fa-code text-info"></i> <b>PHP 版本：</b><?php echo phpversion() ?></li>
                    <li class="list-group-item"><i class="fa fa-database text-success"></i> <b>MySQL 版本：</b><?php echo $mysqlversion ?></li>
                    <li class="list-group-item"><i class="fa fa-globe text-warning"></i> <b>服务器软件：</b><?php echo $_SERVER['SERVER_SOFTWARE'] ?></li>
                    <li class="list-group-item"><i class="fa fa-clock-o text-primary"></i> <b>服务器时间：</b><span id="serverTime" data-server-time="<?php echo time(); ?>"><?php echo date('Y-m-d H:i:s') ?></span></li>
                </ul>
            </section>

            <section class="admin-dashboard-panel">
                <div class="admin-dashboard-panel__header">
                    <div>
                        <h2 class="admin-dashboard-panel__title"><i class="fa fa-tasks"></i> 运营待办</h2>
                        <p class="admin-dashboard-panel__subtitle">把需要优先处理的订单、工单和提现集中提醒。</p>
                    </div>
                </div>
                <div class="admin-dashboard-action-list">
                    <a class="admin-dashboard-action" href="list.php?status=0">
                        <span><i class="fa fa-hourglass-half"></i> 待处理订单</span>
                        <strong><span id="pendingOrderTodo">--</span> 单</strong>
                    </a>
                    <a class="admin-dashboard-action" href="workorder.php">
                        <span><i class="fa fa-comments"></i> 待处理工单</span>
                        <strong><span id="pendingWorkorderTodo">--</span> 个</strong>
                    </a>
                    <a class="admin-dashboard-action" href="tixian.php">
                        <span><i class="fa fa-credit-card"></i> 待处理提现</span>
                        <strong>￥<span id="pendingWithdrawTodo">--</span></strong>
                    </a>
                    <a class="admin-dashboard-action" href="shoplist.php">
                        <span><i class="fa fa-cubes"></i> 今日上架商品</span>
                        <strong><span id="listedGoodsTodo">--</span> 个</strong>
                    </a>
                </div>
            </section>
        </div>
    </div>
    <?php echo q8_render_action('admin_dashboard_after_content', array('date' => $todayDate)); ?>

    <div class="modal fade admin-dashboard-modal" id="visitDetailModal" tabindex="-1" role="dialog" aria-labelledby="visitDetailModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="visitDetailModalLabel"><i class="fa fa-list-alt"></i> 前台访问详情</h4>
                </div>
                <div class="modal-body">
                    <div class="admin-dashboard-modal-summary">
                        <div>
                            <strong>按最近访问时间排序</strong>
                            <span>登录用户显示账号，游客账号列留空。</span>
                        </div>
                        <span class="admin-dashboard-modal-summary__badge">前台访问</span>
                    </div>
                    <div class="admin-dashboard-table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>最近访问</th>
                                        <th>账号</th>
                                        <th>IP 地址</th>
                                        <th>访问页面</th>
                                        <th>地区</th>
                                        <th>访问次数</th>
                                        <th>浏览器信息</th>
                                    </tr>
                                </thead>
                                <tbody id="visitDetailTable"></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="visitDetailEmpty" class="admin-dashboard-empty-state" hidden>
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                        <p>暂无访问记录</p>
                    </div>

                    <div id="visitDetailLoading" class="admin-dashboard-loading-state">
                        <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
                        <p>正在加载访问记录...</p>
                    </div>
                </div>
                <div class="modal-footer admin-dashboard-pagination">
                    <ul class="pagination" id="visitDetailPagination"></ul>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script src="./assets/js/admin-dashboard.js?v=<?php echo urlencode($dashboardAssetVersion); ?>-balance01"></script>
</body>
</html>
