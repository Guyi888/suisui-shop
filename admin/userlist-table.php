<?php
include "../includes/common.php";
if ($islogin != 1) {
	exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission("site", 1);

$params = array();
$orderby = "zid desc";
$allowedOrderby = array('zid desc', 'zid asc', 'rmb asc', 'rmb desc');
$where = "1";
$link = "";

if (isset($_GET["zid"])) {
	$where = "zid=:zid";
	$params[':zid'] = intval($_GET["zid"]);
	$link = "&zid=" . intval($_GET["zid"]);
} elseif (isset($_GET["kw"])) {
	$kw = trim($_GET["kw"]);
	$where = "(user=:kw OR qq=:kw OR zid=:zid)";
	$params[':kw'] = $kw;
	$params[':zid'] = intval($kw);
	$link = "&kw=" . urlencode($kw);
} elseif (isset($_GET["power"])) {
	$where = "power=:power";
	$params[':power'] = intval($_GET["power"]);
	$link = "&power=" . intval($_GET["power"]);
} elseif (isset($_GET["sort"])) {
	if ($_GET["sort"] == "0") $orderby = "rmb asc";
	if ($_GET["sort"] == "1") $orderby = "rmb desc";
	$link = "&sort=" . intval($_GET["sort"]);
}
if (!in_array($orderby, $allowedOrderby, true)) $orderby = "zid desc";

$numrows = intval($DB->getColumn("SELECT COUNT(*) FROM pre_site WHERE {$where}", $params));
$pagesize = 30;
$pages = max(1, ceil($numrows / $pagesize));
$page = isset($_GET["page"]) ? max(1, intval($_GET["page"])) : 1;
$offset = $pagesize * ($page - 1);

function q8_userlist_power_label($power)
{
	$power = intval($power);
	if ($power === 2) return '<span class="label label-danger">&#19987;&#19994;&#29256;&#20998;&#31449;</span>';
	if ($power === 1) return '<span class="label label-info">&#26222;&#21450;&#29256;&#20998;&#31449;</span>';
	return '<span class="label label-default">&#26222;&#36890;&#29992;&#25143;</span>';
}
?>
<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>UID</th>
				<th>&#31867;&#22411;</th>
				<th>&#29992;&#25143;&#21517;</th>
				<th>QQ</th>
				<th>&#27880;&#20876;IP</th>
				<th>&#20313;&#39069;</th>
				<th>&#26102;&#38388;</th>
				<th>&#29366;&#24577;</th>
				<th class="text-right">&#25805;&#20316;</th>
			</tr>
		</thead>
		<tbody>
<?php
$rs = $DB->query("SELECT * FROM pre_site WHERE {$where} ORDER BY {$orderby} LIMIT {$offset},{$pagesize}", $params);
while ($res = $rs->fetch()) {
	$zid = intval($res["zid"]);
	$power = intval($res["power"]);
	$ip = trim((string)$res["reg_ip"]);
	$ipHtml = $ip !== '' ? '<a href="javascript:showBanIP(\'' . htmlspecialchars($ip, ENT_QUOTES, 'UTF-8') . '\',' . $zid . ')" style="cursor:pointer;color:#d9534f;">' . htmlspecialchars($ip, ENT_QUOTES, 'UTF-8') . '</a>' : '<span class="text-muted">-</span>';
	$editUrl = $power > 0 ? './sitelist.php?my=edit&zid=' . $zid : './userlist.php?my=edit&zid=' . $zid;
	$orderUrl = $power > 0 ? './list.php?zid=' . $zid : './list.php?uid=' . $zid;
	echo '<tr>';
	echo '<td><b>' . $zid . '</b></td>';
	echo '<td>' . q8_userlist_power_label($power) . '</td>';
	echo '<td>' . htmlspecialchars($res["user"], ENT_QUOTES, 'UTF-8') . '</td>';
	echo '<td>' . htmlspecialchars($res["qq"], ENT_QUOTES, 'UTF-8') . '</td>';
	echo '<td>' . $ipHtml . '</td>';
	echo '<td><a href="javascript:showRecharge(' . $zid . ')" title="&#28857;&#20987;&#35843;&#25972;&#20313;&#39069;">' . htmlspecialchars($res["rmb"], ENT_QUOTES, 'UTF-8') . '</a></td>';
	echo '<td>' . htmlspecialchars($res["addtime"], ENT_QUOTES, 'UTF-8') . '<br><a href="javascript:setEndtime(' . $zid . ')" title="&#28857;&#20987;&#32493;&#26399;">' . htmlspecialchars($res["endtime"], ENT_QUOTES, 'UTF-8') . '</a></td>';
	echo '<td>' . ($res["status"] == 1 ? '<span class="btn btn-xs btn-success" onclick="setActive(' . $zid . ',0)">&#24320;&#21551;</span>' : '<span class="btn btn-xs btn-warning" onclick="setActive(' . $zid . ',1)">&#20851;&#38381;</span>') . '</td>';
	echo '<td class="text-right admin-userlist-actions">';
	if ($power === 0) echo '<a href="./sitelist.php?my=add2&zid=' . $zid . '" class="btn btn-default btn-xs">&#24320;&#20998;&#31449;</a>&nbsp;';
	echo '<a href="' . $editUrl . '" class="btn btn-info btn-xs">&#32534;&#36753;</a>&nbsp;';
	echo '<a href="' . $orderUrl . '" class="btn btn-warning btn-xs">&#35746;&#21333;</a>&nbsp;';
	echo '<a href="./record.php?zid=' . $zid . '" class="btn btn-success btn-xs">&#26126;&#32454;</a>&nbsp;';
	echo '<a href="javascript:delUser(' . $zid . ')" class="btn btn-xs btn-danger">&#21024;&#38500;</a>&nbsp;';
	echo '<a href="./sso.php?zid=' . $zid . '" class="btn btn-default btn-xs" target="_blank">&#30331;&#24405;</a>';
	echo '</td></tr>';
}
if ($numrows < 1) echo '<tr><td colspan="9" class="text-center text-muted">&#26242;&#26080;&#29992;&#25143;</td></tr>';
?>
		</tbody>
	</table>
</div>
<ul class="pagination">
<?php
if ($page > 1) {
	echo "<li><a href=\"javascript:void(0)\" onclick=\"listTable('page=1" . $link . "')\">&#39318;&#39029;</a></li>";
	echo "<li><a href=\"javascript:void(0)\" onclick=\"listTable('page=" . ($page - 1) . $link . "')\">&laquo;</a></li>";
} else {
	echo '<li class="disabled"><a>&#39318;&#39029;</a></li><li class="disabled"><a>&laquo;</a></li>';
}
$startPage = max(1, $page - 5);
$endPage = min($pages, $page + 5);
for ($i = $startPage; $i <= $endPage; $i++) {
	echo $i == $page ? '<li class="active"><a>' . $i . '</a></li>' : "<li><a href=\"javascript:void(0)\" onclick=\"listTable('page=" . $i . $link . "')\">" . $i . "</a></li>";
}
if ($page < $pages) {
	echo "<li><a href=\"javascript:void(0)\" onclick=\"listTable('page=" . ($page + 1) . $link . "')\">&raquo;</a></li>";
	echo "<li><a href=\"javascript:void(0)\" onclick=\"listTable('page=" . $pages . $link . "')\">&#23614;&#39029;</a></li>";
} else {
	echo '<li class="disabled"><a>&raquo;</a></li><li class="disabled"><a>&#23614;&#39029;</a></li>';
}
?>
</ul>
