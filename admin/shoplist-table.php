<?php

include "../includes/common.php";
if ($islogin == 1) {
} else {
	exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission("shop", 1);
if (!function_exists('q8_admin_shop_table_escape')) {
	function q8_admin_shop_table_escape($value)
	{
		return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
	}
}

if (!function_exists('q8_admin_shop_table_attr')) {
	function q8_admin_shop_table_attr($name, $value)
	{
		return ' ' . $name . '="' . q8_admin_shop_table_escape($value) . '"';
	}
}

if (!function_exists('q8_admin_shop_effective_prices')) {
	function q8_admin_shop_effective_prices($row, $priceRules)
	{
		$price = round(floatval($row['price']), 2);
		$cost = round(floatval($row['cost']), 2);
		$cost2 = round(floatval($row['cost2']), 2);
		$prid = intval($row['prid']);

		if ($prid > 0 && isset($priceRules[$prid])) {
			$rule = $priceRules[$prid];
			if (intval($rule['kind']) === 1) {
				$cost2 = round($price + floatval($rule['p_2']), 2);
				$cost = round($price + floatval($rule['p_1']), 2);
				$price = round($price + floatval($rule['p_0']), 2);
			} else {
				$cost2 = round($price * floatval($rule['p_2']), 2);
				$cost = round($price * floatval($rule['p_1']), 2);
				$price = round($price * floatval($rule['p_0']), 2);
			}
		}

		if ($cost <= 0) {
			$cost = $price;
		}
		if ($cost2 <= 0) {
			$cost2 = $cost;
		}

		return array(
			'price' => number_format($price, 2, '.', ''),
			'cost' => number_format($cost, 2, '.', ''),
			'cost2' => number_format($cost2, 2, '.', '')
		);
	}
}

if (!function_exists('q8_admin_shop_stock_meta')) {
	function q8_admin_shop_stock_meta($row, $localStock)
	{
		$remoteStock = $row['stock'];
		$type = intval($row['is_curl']);
		$remoteEditable = $remoteStock === null ? '' : (string)max(0, intval($remoteStock));
		$remoteCount = $remoteStock === null ? null : max(0, intval($remoteStock));

		if ($type === 4) {
			return array(
				'label' => (string)$localStock,
				'edit' => (string)$localStock,
				'note' => '本地卡密',
				'class' => $localStock > 0 ? 'card' : 'low'
			);
		}

		if ($remoteCount === null && $localStock > 0) {
			return array(
				'label' => (string)$localStock,
				'edit' => '',
				'note' => '本地卡密',
				'class' => 'normal'
			);
		}

		if ($remoteCount === null) {
			return array(
				'label' => '无限',
				'edit' => '',
				'note' => '不限库存',
				'class' => 'infinite'
			);
		}

		$totalStock = $localStock + $remoteCount;
		$note = $localStock > 0 ? '本地' . $localStock . ' + 对接' . $remoteCount : '站点库存';

		return array(
			'label' => (string)$totalStock,
			'edit' => $remoteEditable,
			'note' => $note,
			'class' => $totalStock > 0 ? 'normal' : 'low'
		);
	}
}

if (!function_exists('q8_admin_shop_type_meta')) {
	function q8_admin_shop_type_meta($type, $shequ = 0, $tid = 0)
	{
		global $shequurls;
		$type = intval($type);

		if ($type === 1 || $type === 2) {
			$detail = isset($shequurls[$shequ]) ? $shequurls[$shequ] : '对接站点';
			return array('label' => '对接商品', 'short' => '对接', 'detail' => $detail, 'class' => 'docking', 'icon' => 'fa-cloud');
		}

		if ($type === 4) {
			return array('label' => '发卡商品', 'short' => '发卡', 'detail' => '本地卡密库存', 'class' => 'card', 'icon' => 'fa-credit-card');
		}

		return array('label' => '自营商品', 'short' => '自营', 'detail' => '本地商品', 'class' => 'self', 'icon' => 'fa-cube');
	}
}

if (!function_exists('q8_admin_shop_front_url')) {
	function q8_admin_shop_front_url($cid, $tid)
	{
		$scriptpath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
		$scriptpath = substr($scriptpath, 0, strrpos($scriptpath, '/'));
		$scriptpath = substr($scriptpath, 0, strrpos($scriptpath, '/'));
		return (is_https() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $scriptpath . '/?cid=' . intval($cid) . '&tid=' . intval($tid);
	}
}

$shequlist = $DB->getAll("SELECT id,url FROM pre_shequ order by id asc");
$shequurls = array();
foreach ($shequlist as $res) {
	$shequurls[$res["id"]] = $res["url"] . ($res["remark"] ? " (" . $res["remark"] . ")" : null);
}
$classlist = $DB->getAll("SELECT * FROM pre_class WHERE active=1 order by sort asc");
$select = "<option value=\"0\">未分类</option>";
$shua_class[0] = "未分类";
foreach ($classlist as $res) {
	$shua_class[$res["cid"]] = $res["name"];
	$select .= "<option value=\"" . $res["cid"] . "\">" . $res["name"] . "</option>";
}
if ($_SESSION["price_class"]) {
	$price_class = $_SESSION["price_class"];
} else {
	$pricelist = $DB->getAll("SELECT * FROM pre_price order by id asc");
	$price_class[0] = "不加价";
	foreach ($pricelist as $res) {
		$price_class[$res["id"]] = $res["name"];
	}
}
$price_rules_map = array();
$price_rule_rows = $DB->getAll("SELECT id,kind,p_0,p_1,p_2 FROM pre_price order by id asc");
foreach ($price_rule_rows as $price_rule_row) {
	$price_rules_map[intval($price_rule_row['id'])] = $price_rule_row;
}
$pagesize = isset($_GET["num"]) ? intval($_GET["num"]) : 30;
$orderby = "A.tid desc";
if (isset($_GET["kw"])) {
	$kw = trim(daddslashes($_GET["kw"]));
	$sql = " A.name LIKE '%" . $kw . "%'";
	if (is_numeric($kw)) {
		$sql .= " OR A.tid='" . $kw . "'";
	}
	$numrows = $DB->getColumn("SELECT count(*) from pre_tools A where" . $sql);
	$con = "包含 <b>" . $kw . "</b> 的共有 <b>" . $numrows . "</b> 个商品";
	$link = "&kw=" . $kw;
} elseif (isset($_GET["cid"])) {
	$cid = intval($_GET["cid"]);
	$sql = " A.cid='" . $cid . "'";
	$numrows = $DB->getColumn("SELECT count(*) from pre_tools A where" . $sql);
	$con = "分类 <a href=\"../?cid=" . $cid . "\" target=\"_blank\">" . $shua_class[$cid] . "</a> 共有 <b>" . $numrows . "</b> 个商品";
	$link = "&cid=" . $cid;
	$orderby = "A.sort asc";
	if ($pagesize < $numrows) {
		$pagesize = $numrows;
	}
} elseif (isset($_GET["prid"])) {
	$prid = intval($_GET["prid"]);
	$sql = " prid='" . $prid . "'";
	$numrows = $DB->getColumn("SELECT count(*) from pre_tools where" . $sql);
	$con = "加价模板 " . $price_class[$prid] . " 共有 <b>" . $numrows . "</b> 个商品";
	$link = "&prid=" . $prid;
} elseif (isset($_GET["tid"])) {
	$tid = intval($_GET["tid"]);
	$sql = " tid='" . $tid . "'";
	$numrows = $DB->getColumn("SELECT count(*) from pre_tools where" . $sql);
	$con = "商品列表";
	$link = "&tid=" . $tid;
} else {
	$sql = " 1";
	$link = "";
	$type_text = "";
	$status_text = "";

	// 处理类型搜索
	if (isset($_GET["type"])) {
		$type = trim(daddslashes($_GET["type"]));
		if ($type == "1") {
			$sql .= " AND A.is_curl IN (1,2)";
			$type_text = "对接";
		} elseif ($type == "4") {
			$sql .= " AND A.is_curl=4";
			$type_text = "发卡";
		} elseif ($type == "other") {
			$sql .= " AND A.is_curl NOT IN (1,2,4)";
			$type_text = "自营";
		}
		if ($type_text) {
			$link .= "&type=" . $type;
		}
	}

	// 处理状态搜索
	if (isset($_GET["status"])) {
		$status = trim(daddslashes($_GET["status"]));
		if ($status == "1") {
			$sql .= " AND A.active=1";
			$status_text = "显示";
		} elseif ($status == "0") {
			$sql .= " AND A.active=0";
			$status_text = "隐藏";
		} elseif ($status == "up") {
			$sql .= " AND A.close=0";
			$status_text = "上架";
		} elseif ($status == "down") {
			$sql .= " AND A.close=1";
			$status_text = "下架";
		}
		if ($status_text) {
			$link .= "&status=" . $status;
		}
	}

	$numrows = $DB->getColumn("SELECT count(*) from pre_tools A where" . $sql);
	if ($type_text && $status_text) {
		$con = "类型为 <b>" . $type_text . "</b> 且状态为 <b>" . $status_text . "</b> 的共有 <b>" . $numrows . "</b> 个商品";
	} elseif ($type_text) {
		$con = "类型为 <b>" . $type_text . "</b> 的共有 <b>" . $numrows . "</b> 个商品";
	} elseif ($status_text) {
		$con = "状态为 <b>" . $status_text . "</b> 的共有 <b>" . $numrows . "</b> 个商品";
	} else {
		$con = "系统共有 <b>" . $numrows . "</b> 个商品";
	}
}
?>	  <form name="form1" id="shopListForm" class="admin-shop-list-form">
	  <div class="admin-shop-table-wrap">
        <table class="table admin-shop-table" id="shoplist">
          <thead><tr><th class="admin-shop-table__check">选择</th><th>商品</th><th><a href="./shoprank.php">销量</a></th><th>价格</th><th>来源与分类</th><th class="<?php echo isset($_GET["cid"]) ? "" : "hide";?>">排序</th><th>库存</th><th>状态</th><th>操作</th></tr></thead>
          <tbody>
<?php
$pages = ceil($numrows / $pagesize);
$page = isset($_GET["page"]) ? intval($_GET["page"]) : 1;
$offset = $pagesize * ($page - 1);
$rs = $DB->query("SELECT A.*,B.name classname, (SELECT COUNT(*) FROM pre_pay WHERE tid=A.tid AND status=1) as sales FROM pre_tools A LEFT JOIN pre_class B ON A.cid=B.cid WHERE" . $sql . " order by " . $orderby . " limit " . $offset . "," . $pagesize);
while ($res = $rs->fetch()) {
	$localStock = q8_local_faka_stock_count($DB, $res["tid"]);
	$stockMeta = q8_admin_shop_stock_meta($res, $localStock);
	$typeMeta = q8_admin_shop_type_meta($res["is_curl"], $res["shequ"], $res["tid"]);
	$effectivePrices = q8_admin_shop_effective_prices($res, $price_rules_map);
	$classname = $res["classname"] ?: "未分类";
	$priceName = isset($price_class[$res["prid"]]) ? $price_class[$res["prid"]] : "不加价";
	$frontUrl = q8_admin_shop_front_url($res["cid"], $res["tid"]);
	$rowAttrs = array(
		q8_admin_shop_table_attr('data-shop-row', '1'),
		q8_admin_shop_table_attr('data-tid', $res['tid']),
		q8_admin_shop_table_attr('data-cid', $res['cid']),
		q8_admin_shop_table_attr('data-name', $res['name']),
		q8_admin_shop_table_attr('data-price', $res['price']),
		q8_admin_shop_table_attr('data-cost', $effectivePrices['cost']),
		q8_admin_shop_table_attr('data-cost2', $effectivePrices['cost2']),
		q8_admin_shop_table_attr('data-effective-price', $effectivePrices['price']),
		q8_admin_shop_table_attr('data-prid', $res['prid']),
		q8_admin_shop_table_attr('data-price-name', $priceName),
		q8_admin_shop_table_attr('data-stock', $stockMeta['edit']),
		q8_admin_shop_table_attr('data-stock-label', $stockMeta['label']),
		q8_admin_shop_table_attr('data-type', $res['is_curl']),
		q8_admin_shop_table_attr('data-type-label', $typeMeta['label']),
		q8_admin_shop_table_attr('data-type-detail', $typeMeta['detail']),
		q8_admin_shop_table_attr('data-active', $res['active']),
		q8_admin_shop_table_attr('data-close', $res['close']),
		q8_admin_shop_table_attr('data-sales', $res['sales']),
		q8_admin_shop_table_attr('data-addtime', isset($res['addtime']) ? $res['addtime'] : ''),
		q8_admin_shop_table_attr('data-front-url', $frontUrl),
		q8_admin_shop_table_attr('data-class-path', $classname)
	);
	echo "<tr" . implode('', $rowAttrs) . ">";
	echo "<td data-label=\"选择\" class=\"admin-shop-table__check\"><label class=\"admin-shop-check\"><input type=\"checkbox\" name=\"checkbox[]\" value=\"" . intval($res["tid"]) . "\" data-shop-checkbox><span>选择</span></label></td>";
	echo "<td data-label=\"商品\"><div class=\"admin-shop-name\"><button type=\"button\" class=\"admin-shop-name__title\" data-shop-action=\"detail\" data-tid=\"" . intval($res["tid"]) . "\">" . q8_admin_shop_table_escape($res["name"]) . "</button><div class=\"admin-shop-name__meta\"><span>ID " . intval($res["tid"]) . "</span><a href=\"" . q8_admin_shop_table_escape($frontUrl) . "\" target=\"_blank\" rel=\"noopener\">前台预览</a></div><span class=\"admin-shop-type admin-shop-type--" . q8_admin_shop_table_escape($typeMeta["class"]) . "\"><i class=\"fa " . q8_admin_shop_table_escape($typeMeta["icon"]) . "\"></i> " . q8_admin_shop_table_escape($typeMeta["short"]) . "</span></div></td>";
	echo "<td data-label=\"销量\"><div class=\"admin-shop-sales\"><strong>" . intval($res["sales"]) . "</strong><span>已售</span></div></td>";
	if (intval($res["prid"]) > 0) {
		echo "<td data-label=\"价格\"><button type=\"button\" class=\"admin-shop-cell-button\" data-shop-action=\"price\" data-tid=\"" . intval($res["tid"]) . "\"><span class=\"admin-shop-price\"><span class=\"admin-shop-price__template\">" . q8_admin_shop_table_escape($priceName) . "</span><small>成本 " . q8_admin_shop_table_escape($res["price"]) . "</small></span></button></td>";
	} else {
		echo "<td data-label=\"价格\"><button type=\"button\" class=\"admin-shop-cell-button\" data-shop-action=\"price\" data-tid=\"" . intval($res["tid"]) . "\"><span class=\"admin-shop-price admin-shop-price--manual\"><span>零售 <strong>" . q8_admin_shop_table_escape($res["price"]) . "</strong></span><span>普及 <strong>" . q8_admin_shop_table_escape($res["cost"]) . "</strong></span><span>专业 <strong>" . q8_admin_shop_table_escape($res["cost2"]) . "</strong></span></span></button></td>";
	}
	echo "<td data-label=\"来源与分类\"><div class=\"admin-shop-source\"><a class=\"admin-shop-source__category\" href=\"./shoplist.php?cid=" . intval($res["cid"]) . "\">" . q8_admin_shop_table_escape($classname) . "</a><small>" . q8_admin_shop_table_escape($typeMeta["detail"]) . "</small></div></td>";
	echo "<td data-label=\"排序\" class=\"" . (isset($_GET["cid"]) ? "" : "hide") . "\"><div class=\"admin-shop-sort\"><button type=\"button\" class=\"admin-shop-icon-btn\" title=\"移到顶部\" data-shop-action=\"sort\" data-cid=\"" . intval($res["cid"]) . "\" data-tid=\"" . intval($res["tid"]) . "\" data-sort=\"0\"><i class=\"fa fa-long-arrow-up\"></i></button><button type=\"button\" class=\"admin-shop-icon-btn\" title=\"移到上一行\" data-shop-action=\"sort\" data-cid=\"" . intval($res["cid"]) . "\" data-tid=\"" . intval($res["tid"]) . "\" data-sort=\"1\"><i class=\"fa fa-chevron-up\"></i></button><button type=\"button\" class=\"admin-shop-icon-btn\" title=\"移到下一行\" data-shop-action=\"sort\" data-cid=\"" . intval($res["cid"]) . "\" data-tid=\"" . intval($res["tid"]) . "\" data-sort=\"2\"><i class=\"fa fa-chevron-down\"></i></button><button type=\"button\" class=\"admin-shop-icon-btn\" title=\"移到底部\" data-shop-action=\"sort\" data-cid=\"" . intval($res["cid"]) . "\" data-tid=\"" . intval($res["tid"]) . "\" data-sort=\"3\"><i class=\"fa fa-long-arrow-down\"></i></button></div></td>";
	echo "<td data-label=\"库存\"><div class=\"admin-shop-stock-cell\"><button type=\"button\" class=\"admin-shop-stock admin-shop-stock--" . q8_admin_shop_table_escape($stockMeta["class"]) . "\" data-shop-action=\"stock\" data-tid=\"" . intval($res["tid"]) . "\"><strong>" . q8_admin_shop_table_escape($stockMeta["label"]) . "</strong><small>" . q8_admin_shop_table_escape($stockMeta["note"]) . "</small></button><a class=\"admin-shop-add-card\" href=\"./fakakms.php?my=add&amp;tid=" . intval($res["tid"]) . "\"><i class=\"fa fa-plus\"></i><span>加卡</span></a></div></td>";
	echo "<td data-label=\"状态\"><div class=\"admin-shop-statuses\"><button type=\"button\" class=\"admin-shop-status " . ($res["close"] == 1 ? "admin-shop-status--warning" : "admin-shop-status--success") . "\" data-shop-action=\"toggle-close\" data-tid=\"" . intval($res["tid"]) . "\" data-close=\"" . ($res["close"] == 1 ? 0 : 1) . "\"><i class=\"fa " . ($res["close"] == 1 ? "fa-pause" : "fa-play") . "\"></i> " . ($res["close"] == 1 ? "已下架" : "上架中") . "</button><button type=\"button\" class=\"admin-shop-status " . ($res["active"] == 1 ? "admin-shop-status--success" : "admin-shop-status--danger") . "\" data-shop-action=\"toggle-active\" data-tid=\"" . intval($res["tid"]) . "\" data-active=\"" . ($res["active"] == 1 ? 0 : 1) . "\"><i class=\"fa " . ($res["active"] == 1 ? "fa-eye" : "fa-eye-slash") . "\"></i> " . ($res["active"] == 1 ? "显示" : "隐藏") . "</button></div></td>";
	echo "<td data-label=\"操作\"><div class=\"admin-shop-actions\"><a href=\"./shopedit.php?my=edit&amp;tid=" . intval($res["tid"]) . "\" class=\"btn btn-info btn-xs\"><i class=\"fa fa-pencil\"></i> 编辑</a><a href=\"./list.php?tid=" . intval($res["tid"]) . "\" class=\"btn btn-warning btn-xs\"><i class=\"fa fa-list-alt\"></i> 订单</a><button type=\"button\" class=\"btn btn-danger btn-xs\" data-shop-action=\"delete\" data-tid=\"" . intval($res["tid"]) . "\" data-name=\"" . q8_admin_shop_table_escape($res["name"]) . "\"><i class=\"fa fa-trash\"></i> 删除</button></div></td>";
	echo "</tr>\r\n";
}
?>          </tbody>
        </table>
</div>
<div class="admin-shop-bulkbar">
	<div class="admin-shop-bulkbar__left">
		<label class="admin-shop-check"><input type="checkbox" data-shop-check-all><span>全选</span></label>
		<span class="admin-shop-bulkbar__selected" data-shop-selected-count>已选 0 项</span>
		<select id="shopBulkAction" class="form-control">
			<option value="">批量操作</option>
			<option value="10">改加价模板</option>
			<option value="11">改商品库存</option>
			<option value="1">改为显示</option>
			<option value="2">改为隐藏</option>
			<option value="3">改为上架中</option>
			<option value="4">改为已下架</option>
			<option value="5">删除选中</option>
			<option value="6">复制选中</option>
		</select>
		<button type="button" class="btn btn-primary" data-shop-action="bulk"><i class="fa fa-check"></i> 执行</button>
		<select id="shopMoveCategory" class="form-control">
			<option value="">移动到分类</option><?php echo $select;?>
		</select>
		<button type="button" class="btn btn-default" data-shop-action="move"><i class="fa fa-share"></i> 移动</button>
	</div>
	<div class="admin-shop-bulkbar__note"><i class="fa fa-info-circle"></i><span>库存列显示本地卡密与站点库存的合计；发卡商品请进入卡密库存维护。</span></div>
</div>
</form>
<div class="admin-shop-pagination"><ul class="pagination"><?php
$first = 1;
$prev = $page - 1;
$next = $page + 1;
$last = $pages;
if ($page > 1) {
	echo "<li><a href=\"javascript:void(0)\" data-shop-query=\"page=" . $first . $link . "\">首页</a></li>";
	echo "<li><a href=\"javascript:void(0)\" data-shop-query=\"page=" . $prev . $link . "\">&laquo;</a></li>";
} else {
	echo "<li class=\"disabled\"><a>首页</a></li>";
	echo "<li class=\"disabled\"><a>&laquo;</a></li>";
}
$start = $page - 10 > 1 ? $page - 10 : 1;
$end = $page + 10 < $pages ? $page + 10 : $pages;
for ($i = $start; $i < $page; $i++) {
	echo "<li><a href=\"javascript:void(0)\" data-shop-query=\"page=" . $i . $link . "\">" . $i . "</a></li>";
}
echo "<li class=\"active\"><span>" . $page . "</span></li>";
for ($i = $page + 1; $i <= $end; $i++) {
	echo "<li><a href=\"javascript:void(0)\" data-shop-query=\"page=" . $i . $link . "\">" . $i . "</a></li>";
}
if ($page < $pages) {
	echo "<li><a href=\"javascript:void(0)\" data-shop-query=\"page=" . $next . $link . "\">&raquo;</a></li>";
	echo "<li><a href=\"javascript:void(0)\" data-shop-query=\"page=" . $last . $link . "\">尾页</a></li>";
} else {
	echo "<li class=\"disabled\"><a>&raquo;</a></li>";
	echo "<li class=\"disabled\"><a>尾页</a></li>";
}
?></ul></div>
