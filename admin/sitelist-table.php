<?php
include "../includes/common.php";
if ($islogin != 1) {
	exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission("site", 1);
include __DIR__ . "/includes/site_relation.php";

$orderby = "zid desc";
$params = array();
$allowed_orderby = array('zid desc', 'zid asc', 'rmb asc', 'rmb desc');

if (isset($_GET["zid"])) {
	$zid = intval($_GET["zid"]);
	$power = $DB->getRow("SELECT power FROM pre_site WHERE zid=:zid limit 1", array(':zid' => $zid));
	if ($power && $power["power"] == 0) {
		exit("<script language='javascript'>top.location.href='./userlist.php?zid=" . $zid . "';</script>");
	}
	$sql = " zid=:zid and power>0";
	$params[':zid'] = $zid;
	$link = "&zid=" . $zid;
} elseif (isset($_GET["power"])) {
	$power = intval($_GET["power"]);
	$sql = " power=:power";
	$params[':power'] = $power;
	$link = "&power=" . $power;
} elseif (isset($_GET["mod"])) {
	$params = array();
	if ($_GET["mod"] == "mj") {
		$sql = " iprice!=''";
		$link = "&mod=mj";
	} elseif ($_GET["mod"] == "1") {
		$sql = " power>0";
		$link = "&mod=1";
	} else {
		$sql = " power>0";
		$link = "&mod=" . urlencode($_GET["mod"]);
	}
} elseif (isset($_GET["sort"])) {
	$sql = " power>0";
	if ($_GET["sort"] == "0") {
		$orderby = "rmb asc";
	} elseif ($_GET["sort"] == "1") {
		$orderby = "rmb desc";
	}
	$link = "&sort=" . urlencode($_GET["sort"]);
} elseif (isset($_GET["kw"])) {
	$kw = trim((string)$_GET["kw"]);
	$sql = " (user=:kw or zid=:kw_zid or domain=:kw or domain2=:kw or qq=:kw) and power>0";
	$params[':kw'] = $kw;
	$params[':kw_zid'] = intval($kw);
	$link = "&kw=" . urlencode($kw);
} else {
	$sql = " power>0";
	$link = "";
}

if (!in_array($orderby, $allowed_orderby, true)) {
	$orderby = "zid desc";
}

$numrows = intval($DB->getColumn("SELECT count(*) from pre_site where" . $sql, $params));
$relationIndex = q8_admin_relation_load_index();
?>
<div class="table-responsive">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>ZID</th>
				<th>&#31867;&#22411;</th>
				<th>&#29992;&#25143;&#21517;</th>
				<th>&#20174;&#23646;&#20851;&#31995;</th>
				<th>&#31449;&#28857;&#21517;&#31216;/&#31449;&#38271;QQ</th>
				<th>&#20313;&#39069;</th>
				<th>&#24320;&#36890;/&#21040;&#26399;&#26102;&#38388;</th>
				<th>&#32465;&#23450;&#22495;&#21517;</th>
				<th>&#29366;&#24577;</th>
				<th>&#25805;&#20316;</th>
			</tr>
		</thead>
		<tbody>
<?php
$pagesize = 30;
$pages = max(1, ceil($numrows / $pagesize));
$page = isset($_GET["page"]) ? intval($_GET["page"]) : 1;
if ($page < 1) $page = 1;
if ($page > $pages) $page = $pages;
$offset = $pagesize * ($page - 1);

$rs = $DB->query("SELECT * FROM pre_site WHERE" . $sql . " order by " . $orderby . " limit " . $offset . "," . $pagesize, $params);
while ($res = $rs->fetch()) {
	$zid = intval($res["zid"]);
	echo '<tr>';
	echo '<td><b>' . $zid . '</b></td>';
	echo '<td><span onclick="setSuper(' . $zid . ')" title="&#20462;&#25913;&#31449;&#28857;&#31867;&#22411;" class="btn btn-default btn-xs">' . ($res["power"] == 2 ? '<font color=red>&#19987;&#19994;&#29256;</font>' : '<font color=blue>&#26222;&#21450;&#29256;</font>') . '</span></td>';
	echo '<td>' . q8_admin_relation_escape($res["user"]) . '</td>';
	echo '<td>' . q8_admin_relation_summary_html($res, $relationIndex) . '<button type="button" class="btn btn-default btn-xs admin-relation-btn" onclick="showSiteRelation(' . $zid . ')"><i class="fa fa-sitemap"></i> &#26597;&#30475;&#20174;&#23646;</button></td>';
	echo '<td>' . q8_admin_relation_escape($res["sitename"]) . '<br/>' . q8_admin_relation_escape($res["qq"]) . '</td>';
	echo '<td><a href="javascript:showRecharge(' . $zid . ')" title="&#28857;&#20987;&#20805;&#20540;">' . q8_admin_relation_escape($res["rmb"]) . '</a></td>';
	echo '<td>' . q8_admin_relation_escape($res["addtime"]) . '<br/><a href="javascript:setEndtime(' . $zid . ')" title="&#28857;&#20987;&#32493;&#26399;">' . q8_admin_relation_escape($res["endtime"]) . '</a></td>';
	echo '<td><a href="http://' . q8_admin_relation_escape($res["domain"]) . '" target="_blank" rel="noreferrer">' . q8_admin_relation_escape($res["domain"]) . '</a><br/><a href="http://' . q8_admin_relation_escape($res["domain2"]) . '" target="_blank" rel="noreferrer">' . q8_admin_relation_escape($res["domain2"]) . '</a></td>';
	echo '<td>' . ($res["status"] == 1 ? '<span class="btn btn-xs btn-success" onclick="setActive(' . $zid . ',0)">&#24320;&#21551;</span>' : '<span class="btn btn-xs btn-warning" onclick="setActive(' . $zid . ',1)">&#20851;&#38381;</span>') . '</td>';
	echo '<td><a href="./sitelist.php?my=edit&zid=' . $zid . '" class="btn btn-info btn-xs">&#32534;&#36753;</a>&nbsp;<a href="./list.php?zid=' . $zid . '" class="btn btn-warning btn-xs">&#35746;&#21333;</a>&nbsp;<a href="./record.php?zid=' . $zid . '" class="btn btn-success btn-xs">&#26126;&#32454;</a>&nbsp;<a href="javascript:delSite(' . $zid . ')" class="btn btn-xs btn-danger">&#21024;&#38500;</a>&nbsp;<a href="./sso.php?zid=' . $zid . '" class="btn btn-default btn-xs" target="_blank">&#30331;&#24405;</a></td>';
	echo '</tr>';
}
if ($numrows < 1) echo '<tr><td colspan="10" class="text-center text-muted">&#26242;&#26080;&#20998;&#31449;</td></tr>';
?>
		</tbody>
	</table>
</div>
<ul class="pagination">
<?php
$first = 1;
$prev = $page - 1;
$next = $page + 1;
$last = $pages;
if ($page > 1) {
	echo "<li><a href=\"javascript:void(0)\" onclick=\"listTable('page=" . $first . $link . "')\">&#39318;&#39029;</a></li>";
	echo "<li><a href=\"javascript:void(0)\" onclick=\"listTable('page=" . $prev . $link . "')\">&laquo;</a></li>";
} else {
	echo '<li class="disabled"><a>&#39318;&#39029;</a></li>';
	echo '<li class="disabled"><a>&laquo;</a></li>';
}
$start = $page - 10 > 1 ? $page - 10 : 1;
$end = $page + 10 < $pages ? $page + 10 : $pages;
for ($i = $start; $i < $page; $i++) {
	echo "<li><a href=\"javascript:void(0)\" onclick=\"listTable('page=" . $i . $link . "')\">" . $i . "</a></li>";
}
echo '<li class="disabled"><a>' . $page . '</a></li>';
for ($i = $page + 1; $i <= $end; $i++) {
	echo "<li><a href=\"javascript:void(0)\" onclick=\"listTable('page=" . $i . $link . "')\">" . $i . "</a></li>";
}
if ($page < $pages) {
	echo "<li><a href=\"javascript:void(0)\" onclick=\"listTable('page=" . $next . $link . "')\">&raquo;</a></li>";
	echo "<li><a href=\"javascript:void(0)\" onclick=\"listTable('page=" . $last . $link . "')\">&#23614;&#39029;</a></li>";
} else {
	echo '<li class="disabled"><a>&raquo;</a></li>';
	echo '<li class="disabled"><a>&#23614;&#39029;</a></li>';
}
?>
</ul>
