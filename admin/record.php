<?php
include "../includes/common.php";
$title = "&#25910;&#25903;&#26126;&#32454;";
include "./head.php";
if ($islogin != 1) {
	exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
function q8_record_income_actions()
{
	return array('提成', '奖励', '赠送', '退款', '退回', '充值', '加款', '返利', 'recharge_rebate');
}

function q8_record_expense_actions()
{
	return array('消费', '扣除', '提现', '退款扣回');
}

function q8_record_action_sql($actions)
{
	$safe = array();
	foreach ($actions as $action) {
		$safe[] = "'" . addslashes($action) . "'";
	}
	return implode(',', $safe);
}

function q8_record_sum($where, $actions, $start = null, $end = null)
{
	global $DB;
	$sql = "SELECT COALESCE(SUM(point),0) FROM pre_points WHERE {$where} AND action IN (" . q8_record_action_sql($actions) . ")";
	if ($start) $sql .= " AND addtime>='" . addslashes($start) . "'";
	if ($end) $sql .= " AND addtime<='" . addslashes($end) . "'";
	return floatval($DB->getColumn($sql));
}

function q8_record_is_income($action)
{
	return in_array($action, q8_record_income_actions(), true);
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
	'today_income' => q8_record_sum($where, $incomeActions, $todayStart, $todayEnd),
	'today_expense' => q8_record_sum($where, $expenseActions, $todayStart, $todayEnd),
	'yesterday_income' => q8_record_sum($where, $incomeActions, $yesterdayStart, $yesterdayEnd),
	'yesterday_expense' => q8_record_sum($where, $expenseActions, $yesterdayStart, $yesterdayEnd),
	'all_income' => q8_record_sum($where, $incomeActions),
	'all_expense' => q8_record_sum($where, $expenseActions),
);
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
			<h2><?php echo $zid > 0 ? "ZID:<b>" . $zid . "</b> " : "&#20840;&#37096;&#20998;&#31449;";?>&#25910;&#25903;&#26126;&#32454;</h2>
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
			<table class="table table-striped table-hover">
				<thead>
					<tr><th>ID</th><th>ZID</th><th>&#29992;&#25143;</th><th>&#31867;&#22411;</th><th>&#26041;&#21521;</th><th>&#37329;&#39069;</th><th>&#35814;&#24773;</th><th>&#35746;&#21333;</th><th>&#26102;&#38388;</th><?php if($statusColumn){ ?><th>&#29366;&#24577;</th><?php } ?></tr>
				</thead>
				<tbody>
				<?php
				$rs = $DB->query("SELECT p.*,s.user,s.power FROM pre_points p LEFT JOIN pre_site s ON p.zid=s.zid WHERE {$where} ORDER BY p.id DESC LIMIT {$offset},{$pagesize}");
				while ($res = $rs->fetch()) {
					$isIncome = q8_record_is_income($res['action']);
					$orderid = function_exists('q8_resolve_point_record_orderid') ? q8_resolve_point_record_orderid($res) : (isset($res['orderid']) ? $res['orderid'] : '');
					echo '<tr>';
					echo '<td><b>' . intval($res['id']) . '</b></td>';
					echo '<td><a href="sitelist.php?zid=' . intval($res['zid']) . '">' . intval($res['zid']) . '</a></td>';
					echo '<td>' . htmlspecialchars($res['user'] ? $res['user'] : '-', ENT_QUOTES, 'UTF-8') . (intval($res['power']) > 0 ? ' <span class="label label-info">&#20998;&#31449;</span>' : '') . '</td>';
					echo '<td>' . htmlspecialchars($res['action'], ENT_QUOTES, 'UTF-8') . '</td>';
					echo '<td>' . ($isIncome ? '<span class="label label-danger">&#25910;&#20837;</span>' : '<span class="label label-success">&#25903;&#20986;</span>') . '</td>';
					echo '<td><b class="' . ($isIncome ? 'text-danger' : 'text-success') . '">' . ($isIncome ? '+' : '-') . round(floatval($res['point']), 2) . '</b></td>';
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
