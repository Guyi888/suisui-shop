<?php
if (!defined('IN_CRONLITE')) exit();

function q8_admin_relation_escape($text)
{
	return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

function q8_admin_relation_power_text($power)
{
	$power = intval($power);
	if ($power === 2) return '&#19987;&#19994;&#29256;&#20998;&#31449;';
	if ($power === 1) return '&#26222;&#21450;&#29256;&#20998;&#31449;';
	return '&#26222;&#36890;&#29992;&#25143;';
}

function q8_admin_relation_load_index()
{
	global $DB;
	$index = array('sites' => array(), 'children' => array());
	$rs = $DB->query("SELECT zid,upzid,power,user,domain,domain2,sitename,qq,status FROM pre_site ORDER BY zid ASC");
	while ($row = $rs->fetch(PDO::FETCH_ASSOC)) {
		$zid = intval($row['zid']);
		$upzid = intval($row['upzid']);
		$row['zid'] = $zid;
		$row['upzid'] = $upzid;
		$row['power'] = intval($row['power']);
		$index['sites'][$zid] = $row;
		if ($upzid > 0) {
			if (!isset($index['children'][$upzid])) $index['children'][$upzid] = array();
			$index['children'][$upzid][] = $zid;
		}
	}
	return $index;
}

function q8_admin_relation_chain($zid, $index)
{
	$chain = array();
	$seen = array();
	$cur = intval($zid);
	while ($cur > 0 && isset($index['sites'][$cur]) && !isset($seen[$cur])) {
		$seen[$cur] = true;
		array_unshift($chain, $cur);
		$cur = intval($index['sites'][$cur]['upzid']);
	}
	return $chain;
}

function q8_admin_relation_descendants($zid, $index)
{
	$list = array();
	$queue = array();
	foreach (isset($index['children'][$zid]) ? $index['children'][$zid] : array() as $childZid) {
		$queue[] = array($childZid, 1);
	}
	$seen = array($zid => true);
	while ($queue) {
		$item = array_shift($queue);
		$childZid = intval($item[0]);
		$level = intval($item[1]);
		if (isset($seen[$childZid]) || !isset($index['sites'][$childZid])) continue;
		$seen[$childZid] = true;
		$row = $index['sites'][$childZid];
		$row['_level'] = $level;
		$list[] = $row;
		foreach (isset($index['children'][$childZid]) ? $index['children'][$childZid] : array() as $nextZid) {
			$queue[] = array($nextZid, $level + 1);
		}
	}
	return $list;
}

function q8_admin_relation_node_label($row)
{
	$name = trim((string)(isset($row['sitename']) && $row['sitename'] !== '' ? $row['sitename'] : $row['user']));
	$user = trim((string)$row['user']);
	$html = '<span class="admin-relation-node"><b>#' . intval($row['zid']) . '</b> ';
	$html .= q8_admin_relation_escape($name !== '' ? $name : '-');
	if ($user !== '' && $user !== $name) $html .= '<small>' . q8_admin_relation_escape($user) . '</small>';
	$html .= '<em>' . q8_admin_relation_power_text($row['power']) . '</em></span>';
	return $html;
}

function q8_admin_relation_summary_html($row, $index)
{
	$zid = intval($row['zid']);
	$upzid = intval($row['upzid']);
	$chain = q8_admin_relation_chain($zid, $index);
	$descendants = q8_admin_relation_descendants($zid, $index);
	$directIds = isset($index['children'][$zid]) ? $index['children'][$zid] : array();
	$siteCount = 0;
	$userCount = 0;
	foreach ($descendants as $child) {
		if (intval($child['power']) > 0) $siteCount++;
		else $userCount++;
	}
	$parent = $upzid > 0 && isset($index['sites'][$upzid]) ? $index['sites'][$upzid] : null;
	$levelText = count($chain) > 0 ? count($chain) : 1;
	$html = '<div class="admin-relation-summary">';
	$html .= '<span class="admin-relation-badge"><i class="fa fa-sitemap"></i> L' . intval($levelText) . '</span>';
	if ($parent) {
		$html .= '<span class="admin-relation-line">&#19978;&#32423; #' . intval($parent['zid']) . ' ' . q8_admin_relation_escape($parent['user']) . '</span>';
	} elseif ($upzid > 0) {
		$html .= '<span class="admin-relation-line text-danger">&#19978;&#32423;&#19981;&#23384;&#22312; #' . $upzid . '</span>';
	} else {
		$html .= '<span class="admin-relation-line text-muted">&#20027;&#31449;&#30452;&#23646;</span>';
	}
	$html .= '<span class="admin-relation-line">&#30452;&#25509;&#19979;&#32423; ' . count($directIds) . ' / &#20840;&#37096;&#19979;&#32423; ' . count($descendants) . '</span>';
	$html .= '<span class="admin-relation-line">&#20998;&#31449; ' . $siteCount . ' / &#29992;&#25143; ' . $userCount . '</span>';
	$html .= '</div>';
	return $html;
}

function q8_admin_relation_detail_html($zid, $index)
{
	$zid = intval($zid);
	if (!isset($index['sites'][$zid])) {
		return '<div class="admin-relation-empty">&#26410;&#25214;&#21040;&#35813;&#35760;&#24405;&#12290;</div>';
	}
	$row = $index['sites'][$zid];
	$chain = q8_admin_relation_chain($zid, $index);
	$directIds = isset($index['children'][$zid]) ? $index['children'][$zid] : array();
	$descendants = q8_admin_relation_descendants($zid, $index);
	$html = '<div class="admin-relation-detail">';
	$html .= '<div class="admin-relation-current">' . q8_admin_relation_node_label($row) . '</div>';
	$html .= '<h4><i class="fa fa-level-up"></i> &#19978;&#32423;&#38142;&#36335;</h4>';
	if ($chain) {
		$parts = array();
		foreach ($chain as $chainZid) $parts[] = q8_admin_relation_node_label($index['sites'][$chainZid]);
		$html .= '<div class="admin-relation-chain">' . implode('<i class="fa fa-angle-right"></i>', $parts) . '</div>';
	} else {
		$html .= '<div class="admin-relation-empty">&#20027;&#31449;&#30452;&#23646;&#65292;&#26080;&#19978;&#32423;&#38142;&#36335;&#12290;</div>';
	}
	$html .= '<h4><i class="fa fa-level-down"></i> &#30452;&#25509;&#19979;&#32423;</h4>';
	$html .= q8_admin_relation_table_html($directIds, $index, false);
	$html .= '<h4><i class="fa fa-sitemap"></i> &#20840;&#37096;&#19979;&#32423;</h4>';
	$html .= q8_admin_relation_descendant_table_html($descendants);
	$html .= '</div>';
	return $html;
}

function q8_admin_relation_table_html($ids, $index, $showLevel)
{
	$rows = array();
	foreach ($ids as $id) {
		if (!isset($index['sites'][$id])) continue;
		$row = $index['sites'][$id];
		$row['_level'] = 1;
		$rows[] = $row;
	}
	return q8_admin_relation_descendant_table_html($rows, $showLevel);
}

function q8_admin_relation_descendant_table_html($rows, $showLevel = true)
{
	if (!$rows) return '<div class="admin-relation-empty">&#26242;&#26080;&#19979;&#32423;&#35760;&#24405;&#12290;</div>';
	$html = '<div class="table-responsive admin-relation-table-wrap"><table class="table table-striped table-hover admin-relation-table"><thead><tr>';
	if ($showLevel) $html .= '<th>&#23618;&#32423;</th>';
	$html .= '<th>ID</th><th>&#31867;&#22411;</th><th>&#29992;&#25143;</th><th>&#31449;&#28857;/QQ</th><th>&#29366;&#24577;</th></tr></thead><tbody>';
	foreach ($rows as $row) {
		$html .= '<tr>';
		if ($showLevel) $html .= '<td>L' . intval($row['_level']) . '</td>';
		$html .= '<td><b>#' . intval($row['zid']) . '</b></td>';
		$html .= '<td>' . q8_admin_relation_power_text($row['power']) . '</td>';
		$html .= '<td>' . q8_admin_relation_escape($row['user']) . '</td>';
		$html .= '<td>' . q8_admin_relation_escape($row['sitename']) . '<br><small>' . q8_admin_relation_escape($row['qq']) . '</small></td>';
		$html .= '<td>' . (intval($row['status']) === 1 ? '<span class="label label-success">&#24320;&#21551;</span>' : '<span class="label label-warning">&#20851;&#38381;</span>') . '</td>';
		$html .= '</tr>';
	}
	$html .= '</tbody></table></div>';
	return $html;
}
