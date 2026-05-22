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

$zid = isset($_GET["zid"]) ? intval($_GET["zid"]) : 0;
$action = isset($_GET["action"]) ? trim($_GET["action"]) : '';
$kw = isset($_GET["kw"]) ? trim($_GET["kw"]) : '';
$start = isset($_GET["start"]) ? trim($_GET["start"]) : '';
$end = isset($_GET["end"]) ? trim($_GET["end"]) : '';

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

$summary = array(
	'today_income' => q8_record_sum_direction($where, 'income', $todayStart, $todayEnd),
	'today_expense' => q8_record_sum_direction($where, 'expense', $todayStart, $todayEnd),
	'yesterday_income' => q8_record_sum_direction($where, 'income', $yesterdayStart, $yesterdayEnd),
	'yesterday_expense' => q8_record_sum_direction($where, 'expense', $yesterdayStart, $yesterdayEnd),
	'all_income' => q8_record_sum_direction($where, 'income'),
	'all_expense' => q8_record_sum_direction($where, 'expense'),
);
$breakdownStart = $start !== '' ? $start . " 00:00:00" : null;
$breakdownEnd = $end !== '' ? $end . " 23:59:59" : null;
$breakdown = q8_record_breakdown($where, $breakdownStart, $breakdownEnd);
$numrows = intval($DB->getColumn("SELECT COUNT(*) FROM pre_points WHERE {$where}"));
$pagesize = 30;
$pages = max(1, ceil($numrows / $pagesize));
$page = isset($_GET["page"]) ? max(1, intval($_GET["page"])) : 1;
$offset = $pagesize * ($page - 1);
$statusColumn = $DB->getColumn("SHOW COLUMNS FROM pre_points LIKE 'status'");
?>
<div class="col-md-12 center-block" style="float:none;">
	<div class="block">
		<div class="block-title">
			<h2><?php echo $zid > 0 ? "ZID:<b>" . $zid . "</b> " : "全部分站";?>收支明细</h2>
		</div>
		<form method="get" class="form-inline" style="margin-bottom:15px;">
			<input type="number" name="zid" value="<?php echo $zid > 0 ? $zid : '';?>" class="form-control" placeholder="ZID">
			<select name="action" class="form-control">
				<option value="">&#20840;&#37096;&#31867;&#22411;</option>
				<?php foreach(array_merge($incomeActions, $expenseActions) as $item){ ?>
				<option value="<?php echo htmlspecialchars($item, ENT_QUOTES, 'UTF-8');?>" <?php echo $action === $item ? 'selected' : '';?>><?php echo htmlspecialchars($item, ENT_QUOTES, 'UTF-8');?></option>
				<?php } ?>
			</select>
			<input type="date" name="start" value="<?php echo htmlspecialchars($start, ENT_QUOTES, 'UTF-8');?>" class="form-control">
			<input type="date" name="end" value="<?php echo htmlspecialchars($end, ENT_QUOTES, 'UTF-8');?>" class="form-control">
			<input type="text" name="kw" value="<?php echo htmlspecialchars($kw, ENT_QUOTES, 'UTF-8');?>" class="form-control" placeholder="&#25628;&#32034;&#35814;&#24773;/&#35746;&#21333;">
			<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> &#31579;&#36873;</button>
			<a href="./record.php" class="btn btn-default">&#37325;&#32622;</a>
		</form>
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<td class="text-center"><b>&#20170;&#26085;&#25910;&#20837;</b><br><span class="text-danger"><?php echo round($summary['today_income'], 2);?></span></td>
						<td class="text-center"><b>&#20170;&#26085;&#25903;&#20986;</b><br><span class="text-success"><?php echo round($summary['today_expense'], 2);?></span></td>
						<td class="text-center"><b>&#26152;&#26085;&#25910;&#20837;</b><br><span class="text-danger"><?php echo round($summary['yesterday_income'], 2);?></span></td>
						<td class="text-center"><b>&#26152;&#26085;&#25903;&#20986;</b><br><span class="text-success"><?php echo round($summary['yesterday_expense'], 2);?></span></td>
						<td class="text-center"><b>&#24635;&#25910;&#20837;</b><br><span class="text-danger"><?php echo round($summary['all_income'], 2);?></span></td>
						<td class="text-center"><b>&#24635;&#25903;&#20986;</b><br><span class="text-success"><?php echo round($summary['all_expense'], 2);?></span></td>
					</tr>
				</tbody>
			</table>
			<?php if (!empty($breakdown)) { ?>
			<table class="table table-bordered table-hover">
				<thead>
					<tr>
						<th>&#31867;&#22411;</th>
						<th>&#26041;&#21521;</th>
						<th class="text-right">&#31508;&#25968;</th>
						<th class="text-right">&#37329;&#39069;</th>
						<th>&#35828;&#26126;</th>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach ($breakdown as $item) {
					$isIncome = $item['direction'] === 'income';
					$actionName = htmlspecialchars($item['action'], ENT_QUOTES, 'UTF-8');
					$desc = '';
					if ($item['action'] === '充值') $desc = '&#29992;&#25143;&#22312;&#32447;&#20805;&#20540;&#25110;&#21152;&#27454;&#21345;&#20805;&#20540;';
					elseif ($item['action'] === '加款') $desc = '&#21518;&#21488;&#25163;&#21160;&#21152;&#27454;';
					elseif ($item['action'] === '提成') $desc = '&#20998;&#31449;&#25110;&#19978;&#32423;&#33719;&#24471;&#30340;&#25552;&#25104;';
					elseif ($item['action'] === '赠送' || $item['action'] === '奖励' || $item['action'] === '返利' || $item['action'] === 'recharge_rebate') $desc = '&#31614;&#21040;&#12289;&#20805;&#20540;&#36820;&#21033;&#12289;&#20219;&#21153;&#12289;&#24320;&#31449;&#36192;&#36865;&#31561;&#31119;&#21033;';
					elseif ($item['action'] === '退款' && !$isIncome) $desc = '&#36864;&#27454;&#26102;&#25187;&#22238;&#19978;&#32423;&#25552;&#25104;';
					elseif ($item['action'] === '退款') $desc = '&#35746;&#21333;&#36864;&#27454;&#36864;&#22238;&#29992;&#25143;&#20313;&#39069;';
					elseif ($item['action'] === '消费') $desc = '&#20313;&#39069;&#28040;&#36153;';
					elseif ($item['action'] === '扣除') $desc = '&#21518;&#21488;&#25163;&#21160;&#25187;&#27454;';
					elseif ($item['action'] === '提现') $desc = '&#31449;&#28857;&#20313;&#39069;&#25552;&#29616;';
					echo '<tr>';
					echo '<td>' . $actionName . '</td>';
					echo '<td>' . ($isIncome ? '<span class="label label-danger">&#25910;&#20837;</span>' : '<span class="label label-success">&#25903;&#20986;</span>') . '</td>';
					echo '<td class="text-right">' . intval($item['cnt']) . '</td>';
					echo '<td class="text-right"><b class="' . ($isIncome ? 'text-danger' : 'text-success') . '">&#165;' . number_format($item['total'], 2) . '</b></td>';
					echo '<td>' . $desc . '</td>';
					echo '</tr>';
				}
				?>
				</tbody>
			</table>
			<?php } ?>
			<table class="table table-striped table-hover">
				<thead>
					<tr><th>ID</th><th>ZID</th><th>&#29992;&#25143;</th><th>&#31867;&#22411;</th><th>&#26041;&#21521;</th><th>&#37329;&#39069;</th><th>&#35814;&#24773;</th><th>&#35746;&#21333;</th><th>&#26102;&#38388;</th><?php if($statusColumn){ ?><th>&#29366;&#24577;</th><?php } ?></tr>
				</thead>
				<tbody>
				<?php
				$rs = $DB->query("SELECT p.*,s.user,s.power FROM pre_points p LEFT JOIN pre_site s ON p.zid=s.zid WHERE {$where} ORDER BY p.id DESC LIMIT {$offset},{$pagesize}");
				while ($res = $rs->fetch()) {
					$isIncome = q8_record_is_income($res['action'], $res['point']);
					$orderid = function_exists('q8_resolve_point_record_orderid') ? q8_resolve_point_record_orderid($res) : (isset($res['orderid']) ? $res['orderid'] : '');
					$amount = abs(floatval($res['point']));
					echo '<tr>';
					echo '<td><b>' . intval($res['id']) . '</b></td>';
					echo '<td><a href="sitelist.php?zid=' . intval($res['zid']) . '">' . intval($res['zid']) . '</a></td>';
					echo '<td>' . htmlspecialchars($res['user'] ? $res['user'] : '-', ENT_QUOTES, 'UTF-8') . (intval($res['power']) > 0 ? ' <span class="label label-info">&#20998;&#31449;</span>' : '') . '</td>';
					echo '<td>' . htmlspecialchars($res['action'], ENT_QUOTES, 'UTF-8') . '</td>';
					echo '<td>' . ($isIncome ? '<span class="label label-danger">&#25910;&#20837;</span>' : '<span class="label label-success">&#25903;&#20986;</span>') . '</td>';
					echo '<td><b class="' . ($isIncome ? 'text-danger' : 'text-success') . '">' . ($isIncome ? '+' : '-') . round($amount, 2) . '</b></td>';
					echo '<td>' . htmlspecialchars($res['bz'], ENT_QUOTES, 'UTF-8') . '</td>';
					echo '<td>' . ($orderid ? '<a href="./list.php?id=' . urlencode($orderid) . '" target="_blank">' . htmlspecialchars($orderid, ENT_QUOTES, 'UTF-8') . '</a>' : '-') . '</td>';
					echo '<td>' . htmlspecialchars($res['addtime'], ENT_QUOTES, 'UTF-8') . '</td>';
					if($statusColumn) echo '<td>' . htmlspecialchars($res['status'], ENT_QUOTES, 'UTF-8') . '</td>';
					echo '</tr>';
				}
				if ($numrows < 1) echo '<tr><td colspan="10" class="text-center text-muted">&#26242;&#26080;&#25910;&#25903;&#26126;&#32454;</td></tr>';
				?>
				</tbody>
			</table>
		</div>
		<ul class="pagination">
		<?php
		if ($page > 1) {
			echo '<li><a href="record.php?page=1' . $link . '">&#39318;&#39029;</a></li>';
			echo '<li><a href="record.php?page=' . ($page - 1) . $link . '">&laquo;</a></li>';
		} else {
			echo '<li class="disabled"><a>&#39318;&#39029;</a></li><li class="disabled"><a>&laquo;</a></li>';
		}
		$startPage = max(1, $page - 5);
		$endPage = min($pages, $page + 5);
		for ($i = $startPage; $i <= $endPage; $i++) {
			echo $i == $page ? '<li class="active"><a>' . $i . '</a></li>' : '<li><a href="record.php?page=' . $i . $link . '">' . $i . '</a></li>';
		}
		if ($page < $pages) {
			echo '<li><a href="record.php?page=' . ($page + 1) . $link . '">&raquo;</a></li>';
			echo '<li><a href="record.php?page=' . $pages . $link . '">&#23614;&#39029;</a></li>';
		} else {
			echo '<li class="disabled"><a>&raquo;</a></li><li class="disabled"><a>&#23614;&#39029;</a></li>';
		}
		?>
		</ul>
	</div>
</div>
</body>
</html>
