<?php
include "../includes/common.php";
include_once __DIR__ . '/finance_helpers.php';
$title = "收支明细";
include "./head.php";
if ($islogin != 1) {
	exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
function q8_record_income_actions()
{
	return array_merge(q8_admin_finance_income_actions(), array('退款'));
}

function q8_record_expense_actions()
{
	return q8_admin_finance_expense_actions();
}

function q8_record_action_sql($actions)
{
	$safe = array();
	foreach ($actions as $action) {
		$safe[] = "'" . addslashes($action) . "'";
	}
	return implode(',', $safe);
}

function q8_record_sum_direction($where, $direction, $start = null, $end = null)
{
	global $DB;
	if ($direction === 'expense') {
		$actionWhere = "(action IN (" . q8_record_action_sql(q8_record_expense_actions()) . ") OR (action='退款' AND point<0))";
		$expr = 'ABS(point)';
	} else {
		$incomeActions = array_diff(q8_record_income_actions(), array('退款'));
		$actionWhere = "(action IN (" . q8_record_action_sql($incomeActions) . ") OR (action='退款' AND point>=0))";
		$expr = 'point';
	}
	$sql = "SELECT COALESCE(SUM({$expr}),0) FROM pre_points WHERE {$where} AND {$actionWhere}";
	if ($start) $sql .= " AND addtime>='" . addslashes($start) . "'";
	if ($end) $sql .= " AND addtime<='" . addslashes($end) . "'";
	return floatval($DB->getColumn($sql));
}

function q8_record_is_income($action, $point = 0)
{
	return q8_admin_finance_point_direction($action, $point) === 'income';
}

function q8_record_breakdown($where, $start = null, $end = null)
{
	global $DB;
	$sql = "SELECT action,
		CASE WHEN action IN (" . q8_record_action_sql(q8_record_expense_actions()) . ") OR (action='退款' AND point<0) THEN 'expense' ELSE 'income' END AS direction,
		COUNT(*) AS cnt,
		ROUND(COALESCE(SUM(ABS(point)),0),2) AS total
		FROM pre_points WHERE {$where}";
	if ($start) $sql .= " AND addtime>='" . addslashes($start) . "'";
	if ($end) $sql .= " AND addtime<='" . addslashes($end) . "'";
	$sql .= " GROUP BY action, direction ORDER BY direction ASC, total DESC";
	return $DB->getAll($sql);
}

function q8_record_money($value)
{
	return number_format(floatval($value), 2);
}

function q8_record_point_sum($where, $positiveOnly = false, $absolute = false)
{
	global $DB;
	$expr = $absolute ? 'ABS(point)' : 'point';
	if ($positiveOnly) $where .= " AND point>0";
	return floatval($DB->getColumn("SELECT ROUND(COALESCE(SUM({$expr}),0),2) FROM pre_points WHERE {$where}"));
}

function q8_record_range_stats($start, $end, $zid = 0)
{
	global $DB;
	$zid = intval($zid);
	$zidWhere = $zid > 0 ? " AND zid={$zid}" : "";
	$orderWhere = q8_admin_finance_datetime_where('addtime', $start, $end) . " AND status IN (0,1,2){$zidWhere}";
	$pointWhere = q8_admin_finance_datetime_where('addtime', $start, $end) . $zidWhere;
	$payTime = q8_admin_finance_paid_time_expr();
	$payWhere = q8_admin_finance_datetime_where($payTime, $start, $end) . " AND status=1{$zidWhere}";
	$welfareActions = q8_admin_finance_action_sql(q8_admin_finance_welfare_actions());

	$order = $DB->getRow("SELECT COUNT(*) AS order_count, ROUND(COALESCE(SUM(money),0),2) AS revenue, ROUND(COALESCE(SUM(cost),0),2) AS cost FROM pre_orders WHERE {$orderWhere}");
	$revenue = floatval($order['revenue']);
	$cost = floatval($order['cost']);
	$commission = q8_record_point_sum("{$pointWhere} AND action='提成'", true);
	$welfare = q8_record_point_sum("{$pointWhere} AND action IN ({$welfareActions})", true);
	$recharge = q8_record_point_sum("{$pointWhere} AND action='充值'", true);
	$manualAdd = q8_record_point_sum("{$pointWhere} AND action='加款'", true);
	$refundToBalance = q8_record_point_sum("{$pointWhere} AND action='退款' AND point>0");
	$commissionRefund = q8_record_point_sum("{$pointWhere} AND action='退款' AND point<0", false, true);
	$consume = q8_record_point_sum("{$pointWhere} AND action='消费'", true);
	$deduct = q8_record_point_sum("{$pointWhere} AND action='扣除'", true);
	$withdraw = q8_record_point_sum("{$pointWhere} AND action='提现'", true);
	$refundOrders = $DB->getRow("SELECT COUNT(*) AS cnt, ROUND(COALESCE(SUM(money),0),2) AS money FROM pre_orders WHERE status=4 AND " . q8_admin_finance_datetime_where('addtime', $start, $end) . $zidWhere);
	$paidProduct = floatval($DB->getColumn("SELECT ROUND(COALESCE(SUM(CAST(money AS DECIMAL(12,2))),0),2) FROM pre_pay WHERE {$payWhere} AND tid NOT IN (-1,-4)"));
	$paidRecharge = floatval($DB->getColumn("SELECT ROUND(COALESCE(SUM(CAST(money AS DECIMAL(12,2))),0),2) FROM pre_pay WHERE {$payWhere} AND tid=-1"));
	$withdrawPending = floatval($DB->getColumn("SELECT ROUND(COALESCE(SUM(realmoney),0),2) FROM pre_tixian WHERE status=0{$zidWhere}"));

	$grossProfit = round($revenue - $cost, 2);
	$ownerIncome = round($grossProfit - $commission - $welfare, 2);
	$ownerNetIncome = round($ownerIncome - $refundToBalance + $commissionRefund, 2);

	return array(
		'order_count' => intval($order['order_count']),
		'revenue' => $revenue,
		'cost' => $cost,
		'gross_profit' => $grossProfit,
		'owner_income' => $ownerIncome,
		'owner_net_income' => $ownerNetIncome,
		'commission' => $commission,
		'welfare' => $welfare,
		'recharge' => $recharge,
		'manual_add' => $manualAdd,
		'recharge_total' => round($recharge + $manualAdd, 2),
		'refund_to_balance' => $refundToBalance,
		'commission_refund' => $commissionRefund,
		'refund_order_count' => intval($refundOrders['cnt']),
		'refund_order_money' => floatval($refundOrders['money']),
		'consume' => $consume,
		'deduct' => $deduct,
		'withdraw' => $withdraw,
		'paid_product' => $paidProduct,
		'paid_recharge' => $paidRecharge,
		'withdraw_pending' => $withdrawPending
	);
}

function q8_record_query_string($overrides = array())
{
	$params = array_merge($_GET, $overrides);
	foreach ($params as $key => $value) {
		if ($value === '' || $value === null) unset($params[$key]);
	}
	return http_build_query($params);
}

function q8_record_stat_card($detail, $title, $value, $desc, $class = 'default', $active = false)
{
	$activeClass = $active ? ' is-active' : '';
	$url = 'record.php?' . q8_record_query_string(array('detail' => $detail, 'page' => 1)) . '#recordDetails';
	return '<a class="record-stat-card record-stat-card--' . $class . $activeClass . '" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"><span class="record-stat-card__label">' . $title . '</span><span class="record-stat-card__value">&#165;' . q8_record_money($value) . '</span><span class="record-stat-card__desc">' . $desc . '</span></a>';
}

function q8_record_detail_labels()
{
	return array(
		'orders' => '订单明细',
		'paid_product' => '在线商品支付明细',
		'recharge_total' => '充值入账明细',
		'paid_recharge' => '在线充值支付明细',
		'commission' => '分站提成明细',
		'welfare' => '站点福利明细',
		'refund_to_balance' => '退款退回余额明细',
		'commission_refund' => '提成退款扣回明细',
		'refund_orders' => '退单订单明细',
		'consume' => '余额消费明细',
		'deduct' => '后台扣款明细',
		'withdraw' => '提现支出明细',
		'withdraw_pending' => '待提现明细',
		'balance_all' => '余额流水明细'
	);
}

function q8_record_detail_rows($detail, $start, $end, $zid, $kw, $page, $pagesize)
{
	global $DB;
	$zid = intval($zid);
	$safeKw = addslashes($kw);
	$zidWhere = $zid > 0 ? " AND zid={$zid}" : "";
	$pointWhere = q8_admin_finance_datetime_where('addtime', $start, $end) . $zidWhere;
	$orderWhere = q8_admin_finance_datetime_where('addtime', $start, $end) . $zidWhere;
	$payTime = q8_admin_finance_paid_time_expr();
	$payWhere = q8_admin_finance_datetime_where($payTime, $start, $end) . " AND status=1" . $zidWhere;
	$withdrawWhere = ($detail === 'withdraw_pending' ? "status=0" : q8_admin_finance_datetime_where('addtime', $start, $end)) . $zidWhere;
	$offset = max(0, ($page - 1) * $pagesize);
	$sql = '';
	$countSql = '';

	if ($detail === 'orders') {
		$where = "{$orderWhere} AND status IN (0,1,2)";
		if ($kw !== '') $where .= " AND (id='{$safeKw}' OR tradeno LIKE '%{$safeKw}%' OR input LIKE '%{$safeKw}%')";
		$countSql = "SELECT COUNT(*) FROM pre_orders WHERE {$where}";
		$sql = "SELECT addtime AS rtime,zid,'订单' AS dtype,id AS ref_id,money AS amount,cost,(money-cost) AS profit,CONCAT('订单 ',id) AS title,CONCAT('支付单号 ',IFNULL(tradeno,''),' / 下单账号 ',IFNULL(input,'')) AS note,CONCAT('./list.php?id=',id) AS href FROM pre_orders WHERE {$where} ORDER BY addtime DESC";
	} elseif ($detail === 'paid_product' || $detail === 'paid_recharge') {
		$where = $payWhere . ($detail === 'paid_recharge' ? " AND tid=-1" : " AND tid NOT IN (-1,-4)");
		if ($kw !== '') $where .= " AND (trade_no LIKE '%{$safeKw}%' OR input LIKE '%{$safeKw}%' OR name LIKE '%{$safeKw}%' OR userid LIKE '%{$safeKw}%')";
		$countSql = "SELECT COUNT(*) FROM pre_pay WHERE {$where}";
		$sql = "SELECT {$payTime} AS rtime,zid,IF(tid=-1,'充值支付','商品支付') AS dtype,trade_no AS ref_id,CAST(money AS DECIMAL(12,2)) AS amount,COALESCE((SELECT ROUND(SUM(cost),2) FROM pre_orders WHERE tradeno=pre_pay.trade_no),0) AS cost,CAST(money AS DECIMAL(12,2))-COALESCE((SELECT ROUND(SUM(cost),2) FROM pre_orders WHERE tradeno=pre_pay.trade_no),0) AS profit,IFNULL(name,'支付记录') AS title,CONCAT('方式 ',IFNULL(type,''),' / 账号 ',IFNULL(input,'')) AS note,CONCAT('./payorder.php?trade_no=',trade_no) AS href FROM pre_pay WHERE {$where} ORDER BY {$payTime} DESC";
	} elseif ($detail === 'refund_orders') {
		$where = "{$orderWhere} AND status=4";
		if ($kw !== '') $where .= " AND (id='{$safeKw}' OR tradeno LIKE '%{$safeKw}%' OR input LIKE '%{$safeKw}%')";
		$countSql = "SELECT COUNT(*) FROM pre_orders WHERE {$where}";
		$sql = "SELECT addtime AS rtime,zid,'退单订单' AS dtype,id AS ref_id,money AS amount,cost,(money-cost) AS profit,CONCAT('退单 ',id) AS title,CONCAT('支付单号 ',IFNULL(tradeno,''),' / 下单账号 ',IFNULL(input,'')) AS note,CONCAT('./list.php?id=',id) AS href FROM pre_orders WHERE {$where} ORDER BY addtime DESC";
	} elseif ($detail === 'withdraw_pending') {
		if ($kw !== '') $withdrawWhere .= " AND (id='{$safeKw}' OR pay_account LIKE '%{$safeKw}%' OR pay_name LIKE '%{$safeKw}%' OR note LIKE '%{$safeKw}%')";
		$countSql = "SELECT COUNT(*) FROM pre_tixian WHERE {$withdrawWhere}";
		$sql = "SELECT addtime AS rtime,zid,'待提现' AS dtype,id AS ref_id,realmoney AS amount,0 AS cost,0 AS profit,CONCAT('提现申请 ',id) AS title,CONCAT('申请 ',money,' / ',IFNULL(pay_name,''),' ',IFNULL(pay_account,''),' / ',IFNULL(note,'')) AS note,CONCAT('./tixian.php?kw=',id) AS href FROM pre_tixian WHERE {$withdrawWhere} ORDER BY addtime DESC";
	} else {
		$where = $pointWhere;
		$welfareActions = q8_admin_finance_action_sql(q8_admin_finance_welfare_actions());
		if ($detail === 'recharge_total') $where .= " AND action IN ('充值','加款') AND point>0";
		elseif ($detail === 'commission') $where .= " AND action='提成' AND point>0";
		elseif ($detail === 'welfare') $where .= " AND action IN ({$welfareActions}) AND point>0";
		elseif ($detail === 'refund_to_balance') $where .= " AND action='退款' AND point>0";
		elseif ($detail === 'commission_refund') $where .= " AND action='退款' AND point<0";
		elseif ($detail === 'consume') $where .= " AND action='消费'";
		elseif ($detail === 'deduct') $where .= " AND action='扣除'";
		elseif ($detail === 'withdraw') $where .= " AND action='提现'";
		if ($kw !== '') $where .= " AND (id='{$safeKw}' OR orderid='{$safeKw}' OR action LIKE '%{$safeKw}%' OR bz LIKE '%{$safeKw}%')";
		$countSql = "SELECT COUNT(*) FROM pre_points WHERE {$where}";
		$sql = "SELECT addtime AS rtime,zid,action AS dtype,id AS ref_id,ABS(point) AS amount,0 AS cost,point AS profit,CONCAT('余额流水 ',id) AS title,CONCAT(IFNULL(bz,''),' / 关联订单 ',IFNULL(orderid,'')) AS note,IF(orderid IS NULL OR orderid=0,'',CONCAT('./list.php?id=',orderid)) AS href FROM pre_points WHERE {$where} ORDER BY addtime DESC";
	}

	$total = intval($DB->getColumn($countSql));
	$rows = $DB->getAll($sql . " LIMIT {$offset},{$pagesize}");
	return array('total' => $total, 'rows' => $rows);
}

$zid = isset($_GET["zid"]) ? intval($_GET["zid"]) : 0;
$action = isset($_GET["action"]) ? trim($_GET["action"]) : '';
$kw = isset($_GET["kw"]) ? trim($_GET["kw"]) : '';
$start = isset($_GET["start"]) ? trim($_GET["start"]) : '';
$end = isset($_GET["end"]) ? trim($_GET["end"]) : '';
$detail = isset($_GET["detail"]) ? trim($_GET["detail"]) : 'balance_all';
$detailLabels = q8_record_detail_labels();
if (!isset($detailLabels[$detail])) $detail = 'balance_all';

$where = $zid > 0 ? "zid=" . $zid : "1";
if ($action !== '') $where .= " AND action='" . addslashes($action) . "'";
if ($start !== '') $where .= " AND addtime>='" . addslashes($start) . " 00:00:00'";
if ($end !== '') $where .= " AND addtime<='" . addslashes($end) . " 23:59:59'";
if ($kw !== '') $where .= " AND (bz LIKE '%" . addslashes($kw) . "%' OR orderid LIKE '%" . addslashes($kw) . "%')";

$link = '';
foreach (array('zid' => $zid, 'action' => $action, 'kw' => $kw, 'start' => $start, 'end' => $end) as $key => $value) {
	if ($value !== '' && $value !== 0) $link .= '&' . $key . '=' . urlencode($value);
}

$todayStart = date("Y-m-d 00:00:00");
$todayEnd = date("Y-m-d 23:59:59");
$yesterdayStart = date("Y-m-d 00:00:00", strtotime("-1 day"));
$yesterdayEnd = date("Y-m-d 23:59:59", strtotime("-1 day"));
$incomeActions = q8_record_income_actions();
$expenseActions = q8_record_expense_actions();
$rangeStart = $start !== '' ? $start . " 00:00:00" : $todayStart;
$rangeEndExclusive = $end !== '' ? date("Y-m-d 00:00:00", strtotime($end . " +1 day")) : date("Y-m-d 00:00:00", strtotime("+1 day"));
$rangeEndText = date("Y-m-d H:i:s", strtotime($rangeEndExclusive) - 1);
$rangeTitle = ($start !== '' || $end !== '') ? "筛选日期统计" : "今日经营统计";
$rangeStats = q8_record_range_stats($rangeStart, $rangeEndExclusive, $zid);

$pagesize = 30;
$page = isset($_GET["page"]) ? max(1, intval($_GET["page"])) : 1;
$detailResult = q8_record_detail_rows($detail, $rangeStart, $rangeEndExclusive, $zid, $kw, $page, $pagesize);
$detailRows = $detailResult['rows'];
$numrows = intval($detailResult['total']);
$pages = max(1, ceil($numrows / $pagesize));
if ($page > $pages) {
	$page = $pages;
	$detailResult = q8_record_detail_rows($detail, $rangeStart, $rangeEndExclusive, $zid, $kw, $page, $pagesize);
	$detailRows = $detailResult['rows'];
}
$offset = $pagesize * ($page - 1);
?>
<div class="col-md-12 center-block" style="float:none;">
	<div class="block">
		<div class="block-title">
			<h2><?php echo $zid > 0 ? "ZID:<b>" . $zid . "</b> " : "全部分站";?>收支明细</h2>
		</div>
		<form method="get" class="record-filter">
			<input type="hidden" name="detail" value="<?php echo htmlspecialchars($detail, ENT_QUOTES, 'UTF-8');?>">
			<input type="number" name="zid" value="<?php echo $zid > 0 ? $zid : '';?>" class="form-control" placeholder="ZID">
			<input type="date" name="start" value="<?php echo htmlspecialchars($start, ENT_QUOTES, 'UTF-8');?>" class="form-control">
			<input type="date" name="end" value="<?php echo htmlspecialchars($end, ENT_QUOTES, 'UTF-8');?>" class="form-control">
			<input type="text" name="kw" value="<?php echo htmlspecialchars($kw, ENT_QUOTES, 'UTF-8');?>" class="form-control record-filter__keyword" placeholder="&#25628;&#32034;&#35814;&#24773;/&#35746;&#21333;">
			<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> &#31579;&#36873;</button>
			<a href="./record.php" class="btn btn-default">&#37325;&#32622;</a>
		</form>
		<div class="record-summary-title">
			<h3><?php echo $rangeTitle; ?></h3>
			<span><?php echo htmlspecialchars($rangeStart, ENT_QUOTES, 'UTF-8'); ?> 至 <?php echo htmlspecialchars($rangeEndText, ENT_QUOTES, 'UTF-8'); ?><?php echo $zid > 0 ? '，ZID ' . intval($zid) : ''; ?></span>
		</div>
		<div class="record-stat-grid">
			<?php
			echo q8_record_stat_card('orders', '订单流水', $rangeStats['revenue'], '有效订单 ' . intval($rangeStats['order_count']) . ' 笔', 'info', $detail === 'orders');
			echo q8_record_stat_card('orders', '订单成本', $rangeStats['cost'], '点击看订单成本来源', 'muted', $detail === 'orders');
			echo q8_record_stat_card('orders', '订单毛利', $rangeStats['gross_profit'], '订单流水 - 订单成本', 'success', $detail === 'orders');
			echo q8_record_stat_card('orders', '主站收入', $rangeStats['owner_income'], '毛利 - 分站提成 - 福利', 'success', $detail === 'orders');
			echo q8_record_stat_card('orders', '主站净收入', $rangeStats['owner_net_income'], '收入 - 退回余额 + 提成扣回', 'warning', $detail === 'orders');
			echo q8_record_stat_card('paid_product', '在线商品支付', $rangeStats['paid_product'], '不含充值支付', 'info', $detail === 'paid_product');
			echo q8_record_stat_card('recharge_total', '充值入账', $rangeStats['recharge_total'], '在线充值 + 后台加款', 'success', $detail === 'recharge_total');
			echo q8_record_stat_card('paid_recharge', '在线充值支付', $rangeStats['paid_recharge'], '支付表充值金额', 'info', $detail === 'paid_recharge');
			echo q8_record_stat_card('commission', '分站提成', $rangeStats['commission'], '分站/上级获得提成', 'warning', $detail === 'commission');
			echo q8_record_stat_card('welfare', '站点福利', $rangeStats['welfare'], '赠送/奖励/返利等', 'warning', $detail === 'welfare');
			echo q8_record_stat_card('refund_to_balance', '退款退回余额', $rangeStats['refund_to_balance'], '退回用户余额', 'danger', $detail === 'refund_to_balance');
			echo q8_record_stat_card('commission_refund', '提成退款扣回', $rangeStats['commission_refund'], '退款时扣回上级提成', 'success', $detail === 'commission_refund');
			echo q8_record_stat_card('refund_orders', '退单订单金额', $rangeStats['refund_order_money'], '退单 ' . intval($rangeStats['refund_order_count']) . ' 笔', 'danger', $detail === 'refund_orders');
			echo q8_record_stat_card('consume', '余额消费', $rangeStats['consume'], '用户余额支付/消费', 'muted', $detail === 'consume');
			echo q8_record_stat_card('deduct', '后台扣款', $rangeStats['deduct'], '人工扣除余额', 'danger', $detail === 'deduct');
			echo q8_record_stat_card('withdraw', '提现支出', $rangeStats['withdraw'], '余额流水提现记录', 'danger', $detail === 'withdraw');
			echo q8_record_stat_card('withdraw_pending', '待提现金额', $rangeStats['withdraw_pending'], '当前待处理提现', 'warning', $detail === 'withdraw_pending');
			?>
		</div>
		<div class="record-detail-panel" id="recordDetails">
			<div class="record-detail-panel__header">
				<div>
					<h3><?php echo htmlspecialchars($detailLabels[$detail], ENT_QUOTES, 'UTF-8'); ?></h3>
					<p>共 <?php echo intval($numrows); ?> 条，点击上方卡片切换所属明细。</p>
				</div>
				<a class="btn btn-default btn-sm" href="record.php?<?php echo htmlspecialchars(q8_record_query_string(array('detail' => 'balance_all', 'page' => 1)), ENT_QUOTES, 'UTF-8'); ?>#recordDetails">查看余额流水</a>
			</div>
			<div class="table-responsive">
			<table class="table table-striped table-hover">
				<thead>
					<tr><th>时间</th><th>ZID</th><th>类型</th><th>编号</th><th class="text-right">金额</th><th class="text-right">成本</th><th class="text-right">毛利/变动</th><th>标题</th><th>说明</th></tr>
				</thead>
				<tbody>
				<?php
				foreach ($detailRows as $res) {
					$profit = floatval($res['profit']);
					$isNegative = $profit < 0 || in_array($res['dtype'], array('消费', '扣除', '提现', '退单订单'), true);
					$amountClass = $isNegative ? 'text-success' : 'text-danger';
					echo '<tr>';
					echo '<td>' . htmlspecialchars($res['rtime'], ENT_QUOTES, 'UTF-8') . '</td>';
					echo '<td><a href="sitelist.php?zid=' . intval($res['zid']) . '">' . intval($res['zid']) . '</a></td>';
					echo '<td><span class="label label-info">' . htmlspecialchars($res['dtype'], ENT_QUOTES, 'UTF-8') . '</span></td>';
					echo '<td>' . htmlspecialchars($res['ref_id'], ENT_QUOTES, 'UTF-8') . '</td>';
					echo '<td class="text-right"><b class="' . $amountClass . '">&#165;' . q8_record_money($res['amount']) . '</b></td>';
					echo '<td class="text-right text-muted">&#165;' . q8_record_money($res['cost']) . '</td>';
					echo '<td class="text-right ' . ($profit < 0 ? 'text-success' : 'text-danger') . '">&#165;' . q8_record_money($profit) . '</td>';
					$titleText = htmlspecialchars($res['title'], ENT_QUOTES, 'UTF-8');
					echo '<td>' . (!empty($res['href']) ? '<a href="' . htmlspecialchars($res['href'], ENT_QUOTES, 'UTF-8') . '" target="_blank">' . $titleText . '</a>' : $titleText) . '</td>';
					echo '<td>' . htmlspecialchars($res['note'], ENT_QUOTES, 'UTF-8') . '</td>';
					echo '</tr>';
				}
				if ($numrows < 1) echo '<tr><td colspan="9" class="text-center text-muted">&#26242;&#26080;&#25910;&#25903;&#26126;&#32454;</td></tr>';
				?>
				</tbody>
			</table>
			</div>
		</div>
		<ul class="pagination">
		<?php
		if ($page > 1) {
			echo '<li><a href="record.php?' . q8_record_query_string(array('page' => 1)) . '#recordDetails">&#39318;&#39029;</a></li>';
			echo '<li><a href="record.php?' . q8_record_query_string(array('page' => $page - 1)) . '#recordDetails">&laquo;</a></li>';
		} else {
			echo '<li class="disabled"><a>&#39318;&#39029;</a></li><li class="disabled"><a>&laquo;</a></li>';
		}
		$startPage = max(1, $page - 5);
		$endPage = min($pages, $page + 5);
		for ($i = $startPage; $i <= $endPage; $i++) {
			echo $i == $page ? '<li class="active"><a>' . $i . '</a></li>' : '<li><a href="record.php?' . q8_record_query_string(array('page' => $i)) . '#recordDetails">' . $i . '</a></li>';
		}
		if ($page < $pages) {
			echo '<li><a href="record.php?' . q8_record_query_string(array('page' => $page + 1)) . '#recordDetails">&raquo;</a></li>';
			echo '<li><a href="record.php?' . q8_record_query_string(array('page' => $pages)) . '#recordDetails">&#23614;&#39029;</a></li>';
		} else {
			echo '<li class="disabled"><a>&raquo;</a></li><li class="disabled"><a>&#23614;&#39029;</a></li>';
		}
		?>
		</ul>
	</div>
</div>
</body>
</html>
