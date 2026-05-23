<?php
include "../includes/common.php";
$title = "价格系统诊断";
include "./head.php";
if ($islogin != 1) {
	exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission("price", 1);

function q8_price_diag_escape($value)
{
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function q8_price_diag_count($sql, $params = array())
{
	global $DB;
	return intval($DB->getColumn($sql, $params));
}

$hasSyncConfig = $DB->getColumn("SHOW TABLES LIKE 'pre_sync_config'") ? true : false;

$checks = array(
	array(
		'name' => '自动同步商品未绑定已配置模板',
		'count' => $hasSyncConfig ? q8_price_diag_count("SELECT COUNT(*) FROM pre_tools t INNER JOIN pre_sync_config c ON c.shequ_id=t.shequ AND c.status=1 WHERE t.shequ>0 AND c.markup_template>0 AND t.prid<>c.markup_template") : 0,
		'level' => 'danger',
		'desc' => '供货站启用加价模板后，同步商品应保存对应 prid。'
	),
	array(
		'name' => '手动价商品专业成本为空',
		'count' => q8_price_diag_count("SELECT COUNT(*) FROM pre_tools WHERE prid=0 AND active=1 AND price>0 AND (cost2 IS NULL OR cost2<=0)"),
		'level' => 'warning',
		'desc' => '后台展示会兜底，但建议历史数据补齐 cost2。'
	),
	array(
		'name' => '商品绑定不存在的主站模板',
		'count' => q8_price_diag_count("SELECT COUNT(*) FROM pre_tools t LEFT JOIN pre_price p ON p.id=t.prid AND p.zid=0 WHERE t.prid>0 AND p.id IS NULL"),
		'level' => 'danger',
		'desc' => '模板不存在时会回退到基础价，容易造成价格口径不一致。'
	),
	array(
		'name' => '分站默认模板不存在',
		'count' => q8_price_diag_count("SELECT COUNT(*) FROM pre_site s LEFT JOIN pre_price p ON p.id=s.site_prid WHERE s.site_prid>0 AND p.id IS NULL"),
		'level' => 'danger',
		'desc' => '分站默认模板无效时，分站商品会回退到上级成本。'
	),
	array(
		'name' => '分站密价低于专业成本',
		'count' => q8_price_diag_count("SELECT COUNT(*) FROM pre_site_price sp INNER JOIN pre_tools t ON t.tid=sp.tid WHERE sp.cost2>0 AND t.cost2>0 AND sp.cost2<t.cost2"),
		'level' => 'warning',
		'desc' => '下级专业价低于主站专业成本时，需要人工确认是否为历史异常。'
	),
	array(
		'name' => '分站售价低于自用成本',
		'count' => q8_price_diag_count("SELECT COUNT(*) FROM pre_site_price WHERE cost>0 AND price>0 AND price<cost"),
		'level' => 'danger',
		'desc' => '分站销售价低于成本会导致亏损或利润统计异常。'
	)
);

$samples = $hasSyncConfig ? $DB->getAll("SELECT t.tid,t.name,t.price,t.cost,t.cost2,t.prid,t.shequ,c.markup_template FROM pre_tools t INNER JOIN pre_sync_config c ON c.shequ_id=t.shequ AND c.status=1 WHERE t.shequ>0 AND c.markup_template>0 AND t.prid<>c.markup_template ORDER BY t.tid DESC LIMIT 20") : array();
?>
<div class="col-xs-12">
	<div class="block">
		<div class="block-title">
			<h3><i class="fa fa-stethoscope"></i> 价格系统诊断</h3>
		</div>
		<div class="table-responsive">
			<table class="table table-bordered table-striped">
				<thead>
					<tr>
						<th>检查项</th>
						<th>异常数量</th>
						<th>说明</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($checks as $check) { ?>
					<tr>
						<td><?php echo q8_price_diag_escape($check['name']); ?></td>
						<td><span class="label label-<?php echo $check['count'] > 0 ? q8_price_diag_escape($check['level']) : 'success'; ?>"><?php echo intval($check['count']); ?></span></td>
						<td><?php echo q8_price_diag_escape($check['desc']); ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>
	</div>

	<div class="block">
		<div class="block-title">
			<h3><i class="fa fa-list"></i> 同步模板异常样例</h3>
		</div>
		<div class="table-responsive">
			<table class="table table-bordered table-striped">
				<thead>
					<tr>
						<th>ID</th>
						<th>商品</th>
						<th>价格字段</th>
						<th>当前模板</th>
						<th>应绑定模板</th>
					</tr>
				</thead>
				<tbody>
				<?php if (empty($samples)) { ?>
					<tr><td colspan="5" class="text-center text-success">暂无异常样例</td></tr>
				<?php } else { foreach ($samples as $row) { ?>
					<tr>
						<td><?php echo intval($row['tid']); ?></td>
						<td><?php echo q8_price_diag_escape($row['name']); ?></td>
						<td><?php echo q8_price_diag_escape($row['price'] . ' / ' . $row['cost'] . ' / ' . $row['cost2']); ?></td>
						<td><?php echo intval($row['prid']); ?></td>
						<td><?php echo intval($row['markup_template']); ?></td>
					</tr>
				<?php }} ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
</div>
