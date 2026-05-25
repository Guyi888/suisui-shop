<?php

include "../includes/common.php";
if ($islogin == 1) {
} else {
	exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission("site", 1);
$zid = intval($_GET["zid"]);
$price_obj = new \lib\Price($zid);
$pricedOnly = isset($_GET["priced"]) && intval($_GET["priced"]) == 1;
$pagesize = isset($_GET["num"]) ? intval($_GET["num"]) : 30;
$page = isset($_GET["page"]) ? intval($_GET["page"]) : 1;
$offset = $pagesize * ($page - 1);
if (isset($_GET["kw"])) {
	$kw = trim(daddslashes($_GET["kw"]));
	$sql = " name LIKE '%" . $kw . "%'";
	$link = "&kw=" . $kw;
} elseif (isset($_GET["cid"])) {
	$rs = $DB->query("SELECT * FROM pre_class WHERE active=1 order by sort asc");
	$select = "<option value=\"0\">未分类</option>";
	$shua_class[0] = "未分类";
	while ($res = $rs->fetch()) {
		$shua_class[$res["cid"]] = $res["name"];
		$select .= "<option value=\"" . $res["cid"] . "\">" . $res["name"] . "</option>";
	}
	$cid = intval($_GET["cid"]);
	$sql = " cid='" . $cid . "'";
	$link = "&cid=" . $cid;
} else {
	$sql = " 1";
	$link = "";
}
$listRows = array();
if ($pricedOnly) {
	$toolRows = $DB->getAll("SELECT * FROM pre_tools WHERE" . $sql . " order by sort asc");
	foreach ($toolRows as $toolRow) {
		$price_obj->setToolInfo($toolRow["tid"], $toolRow);
		$iprice = $price_obj->getTooliPrice($toolRow["tid"]);
		if ($iprice <= 0) continue;
		$toolRow["_iprice"] = $iprice;
		$listRows[] = $toolRow;
	}
	$numrows = count($listRows);
} else {
	$numrows = intval($DB->getColumn("SELECT count(*) from pre_tools WHERE" . $sql));
	$listRows = $DB->getAll("SELECT * FROM pre_tools WHERE" . $sql . " order by sort asc limit " . $offset . "," . $pagesize);
}
if (isset($_GET["kw"])) {
	$con = "包含 <b>" . htmlspecialchars($kw, ENT_QUOTES, 'UTF-8') . "</b> 的共有 <b>" . $numrows . "</b> 个商品";
} elseif (isset($_GET["cid"])) {
	$con = "分类 <a href=\"../?cid=" . $cid . "\" target=\"_blank\">" . htmlspecialchars($shua_class[$cid], ENT_QUOTES, 'UTF-8') . "</a> 共有 <b>" . $numrows . "</b> 个商品";
} else {
	$con = "系统共有 <b>" . $numrows . "</b> 个商品";
}
if ($pricedOnly) {
	$con .= " <span class=\"label label-primary\">仅看已设密价</span>";
	$link .= "&priced=1";
}
?>	  <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>商品名称</th><th>当前分站成本价</th><th>自定义密价</th></tr></thead>
          <tbody>
<?php
$pages = max(1, ceil($numrows / $pagesize));
$pageRows = $pricedOnly ? array_slice($listRows, $offset, $pagesize) : $listRows;
foreach ($pageRows as $res) {
	$price_obj->setToolInfo($res["tid"], $res);
	if ($price_obj->getPower() == 2) {
		$price = $price_obj->getManageSelfCostPrice($res["tid"]);
	} else {
		$price = $price_obj->getToolCost($res["tid"]);
	}
	$iprice = isset($res["_iprice"]) ? $res["_iprice"] : $price_obj->getTooliPrice($res["tid"]);
	echo "<tr><td>" . htmlspecialchars($res["name"], ENT_QUOTES, 'UTF-8') . "</td><td>" . $price . " 元</td><td><a title=\"设置密价\" href=\"javascript:setPrice(" . $res["tid"] . ",'" . $iprice . "')\">" . ($iprice > 0 ? "<font color=\"blue\">" . $iprice . " 元</font>" : "<font color=\"green\">点击设置</font>") . "</a></td></tr>\r\n";
}
if ($numrows < 1) echo '<tr><td colspan="3" class="text-center text-muted">暂无符合条件的商品</td></tr>';
?>          </tbody>
        </table>
</div>
<ul class="pagination"><?php
$first = 1;
$prev = $page - 1;
$next = $page + 1;
$last = $pages;
if ($page > 1) {
	echo "<li><a href=\"javascript:void(0)\" onclick=\"listTable('page=" . $first . $link . "')\">首页</a></li>";
	echo "<li><a href=\"javascript:void(0)\" onclick=\"listTable('page=" . $prev . $link . "')\">&laquo;</a></li>";
} else {
	echo "<li class=\"disabled\"><a>首页</a></li>";
	echo "<li class=\"disabled\"><a>&laquo;</a></li>";
}
$start = $page - 10 > 1 ? $page - 10 : 1;
$end = $page + 10 < $pages ? $page + 10 : $pages;
for ($i = $start; $i < $page; $i++) {
	echo "<li><a href=\"javascript:void(0)\" onclick=\"listTable('page=" . $i . $link . "')\">" . $i . "</a></li>";
}
echo "<li class=\"disabled\"><a>" . $page . "</a></li>";
for ($i = $page + 1; $i <= $end; $i++) {
	echo "<li><a href=\"javascript:void(0)\" onclick=\"listTable('page=" . $i . $link . "')\">" . $i . "</a></li>";
}
if ($page < $pages) {
	echo "<li><a href=\"javascript:void(0)\" onclick=\"listTable('page=" . $next . $link . "')\">&raquo;</a></li>";
	echo "<li><a href=\"javascript:void(0)\" onclick=\"listTable('page=" . $last . $link . "')\">尾页</a></li>";
} else {
	echo "<li class=\"disabled\"><a>&raquo;</a></li>";
	echo "<li class=\"disabled\"><a>尾页</a></li>";
}
?></ul><script>
$("#blocktitle").html('<?php echo $con;?>');
</script>
