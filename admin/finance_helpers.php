<?php
if (!defined('IN_CRONLITE')) exit();

function q8_admin_finance_quote($value)
{
	global $DB;
	return method_exists($DB, 'quote') ? $DB->quote($value) : ("'" . addslashes($value) . "'");
}

function q8_admin_finance_action_sql($actions)
{
	$list = array();
	foreach ($actions as $action) {
		$list[] = q8_admin_finance_quote($action);
	}
	return implode(',', $list);
}

function q8_admin_finance_welfare_actions()
{
	return array('奖励', '赠送', '返利', 'recharge_rebate');
}

function q8_admin_finance_income_actions()
{
	return array('提成', '奖励', '赠送', '退回', '充值', '加款', '返利', 'recharge_rebate');
}

function q8_admin_finance_expense_actions()
{
	return array('消费', '扣除', '提现', '退款扣回');
}

function q8_admin_finance_datetime_where($column, $start, $end)
{
	return $column . ">=" . q8_admin_finance_quote($start) . " AND " . $column . "<" . q8_admin_finance_quote($end);
}

function q8_admin_finance_point_date_where($start, $end)
{
	return q8_admin_finance_datetime_where('addtime', $start, $end);
}

function q8_admin_finance_paid_time_expr()
{
	return "COALESCE(endtime, addtime)";
}

function q8_admin_finance_sum_point($where, $positiveOnly = false, $absolute = false)
{
	global $DB;
	$expr = $absolute ? 'ABS(point)' : 'point';
	if ($positiveOnly) {
		$where .= " AND point>0";
	}
	return floatval($DB->getColumn("SELECT ROUND(COALESCE(SUM({$expr}),0),2) FROM pre_points WHERE {$where}"));
}

function q8_admin_finance_order_where($start, $end)
{
	return q8_admin_finance_datetime_where('addtime', $start, $end) . " AND status IN (0,1,2)";
}

function q8_admin_finance_range_stats($start, $end)
{
	global $DB;
	$orderWhere = q8_admin_finance_order_where($start, $end);
	$pointWhere = q8_admin_finance_point_date_where($start, $end);
	$welfareActions = q8_admin_finance_action_sql(q8_admin_finance_welfare_actions());
	$paidTime = q8_admin_finance_paid_time_expr();
	$paidWhere = q8_admin_finance_datetime_where($paidTime, $start, $end) . " AND status=1";

	$order = $DB->getRow(
		"SELECT COUNT(*) AS order_count,
			ROUND(COALESCE(SUM(money),0),2) AS revenue,
			ROUND(COALESCE(SUM(cost),0),2) AS cost
		 FROM pre_orders WHERE {$orderWhere}"
	);
	$revenue = floatval($order['revenue']);
	$cost = floatval($order['cost']);
	$commission = q8_admin_finance_sum_point("{$pointWhere} AND action='提成' AND (status IS NULL OR status<>4)", true);
	$welfare = q8_admin_finance_sum_point("{$pointWhere} AND action IN ({$welfareActions})", true);
	$recharge = q8_admin_finance_sum_point("{$pointWhere} AND action='充值'", true);
	$manualAdd = q8_admin_finance_sum_point("{$pointWhere} AND action='加款'", true);
	$refundToBalance = q8_admin_finance_sum_point("{$pointWhere} AND action='退款' AND point>0", false);
	$commissionRefund = q8_admin_finance_sum_point("{$pointWhere} AND action='退款' AND point<0", false, true);
	$consume = q8_admin_finance_sum_point("{$pointWhere} AND action='消费'", true);
	$deduct = q8_admin_finance_sum_point("{$pointWhere} AND action='扣除'", true);
	$withdraw = q8_admin_finance_sum_point("{$pointWhere} AND action='提现'", true);
	$refundOrders = $DB->getRow(
		"SELECT COUNT(*) AS cnt, ROUND(COALESCE(SUM(money),0),2) AS money
		 FROM pre_orders WHERE status=4 AND " . q8_admin_finance_datetime_where('addtime', $start, $end)
	);
	$paidProduct = floatval($DB->getColumn("SELECT ROUND(COALESCE(SUM(CAST(money AS DECIMAL(12,2))),0),2) FROM pre_pay WHERE {$paidWhere} AND tid NOT IN (-1,-4)"));
	$paidRecharge = floatval($DB->getColumn("SELECT ROUND(COALESCE(SUM(CAST(money AS DECIMAL(12,2))),0),2) FROM pre_pay WHERE {$paidWhere} AND tid=-1"));
	$balanceTotal = floatval($DB->getColumn("SELECT ROUND(COALESCE(SUM(rmb),0),2) FROM pre_site"));
	$balanceWithdrawable = floatval($DB->getColumn("SELECT ROUND(COALESCE(SUM(rmbtc),0),2) FROM pre_site"));
	$withdrawPending = floatval($DB->getColumn("SELECT ROUND(COALESCE(SUM(realmoney),0),2) FROM pre_tixian WHERE status=0"));

	$grossProfit = round($revenue - $cost, 2);
	$ownerIncome = round($revenue - $cost - $commission - $welfare, 2);
	$ownerNetIncome = round($ownerIncome - $refundToBalance + $commissionRefund, 2);

	return array(
		'order_count' => intval($order['order_count']),
		'revenue' => $revenue,
		'cost' => $cost,
		'gross_profit' => $grossProfit,
		'commission' => $commission,
		'welfare' => $welfare,
		'owner_income' => $ownerIncome,
		'owner_net_income' => $ownerNetIncome,
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
		'balance_total' => $balanceTotal,
		'balance_withdrawable' => $balanceWithdrawable,
		'balance_locked' => round($balanceTotal - $balanceWithdrawable, 2),
		'withdraw_pending' => $withdrawPending
	);
}

function q8_admin_finance_point_direction($action, $point)
{
	$point = floatval($point);
	if ($action === '退款' && $point < 0) return 'expense';
	if (in_array($action, q8_admin_finance_expense_actions(), true)) return 'expense';
	return 'income';
}

function q8_admin_finance_format_money($value)
{
	return number_format(floatval($value), 2);
}
