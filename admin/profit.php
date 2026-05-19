<?php
include "../includes/common.php";
$title = '利润统计';
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission('record', 1);
include "./head.php";

$today     = date('Y-m-d');
$todayS    = $today . ' 00:00:00';
$tomorrowS = date('Y-m-d', strtotime('+1 day')) . ' 00:00:00';

$range       = isset($_GET['range']) ? $_GET['range'] : 'today';
$customStart = isset($_GET['start']) ? preg_replace('/[^0-9\-]/', '', $_GET['start']) : $today;
$customEnd   = isset($_GET['end'])   ? preg_replace('/[^0-9\-]/', '', $_GET['end'])   : $today;

switch ($range) {
    case 'yesterday':
        $yest  = date('Y-m-d', strtotime('-1 day'));
        $start = $yest . ' 00:00:00'; $end = $todayS;
        $label = '昨日'; break;
    case 'month':
        $start = date('Y-m-01') . ' 00:00:00'; $end = $tomorrowS;
        $label = '本月'; break;
    case 'lastmonth':
        $start = date('Y-m-01', strtotime('-1 month')) . ' 00:00:00';
        $end   = date('Y-m-01') . ' 00:00:00';
        $label = '上月'; break;
    case 'custom':
        $start = $customStart . ' 00:00:00'; $end = $customEnd . ' 23:59:59';
        $label = htmlspecialchars($customStart, ENT_QUOTES, 'UTF-8') . ' ~ ' . htmlspecialchars($customEnd, ENT_QUOTES, 'UTF-8');
        break;
    default:
        $range = 'today'; $start = $todayS; $end = $tomorrowS;
        $label = '今日';
}

// 订单利润按分站
$rows = $DB->getAll(
    "SELECT o.zid, s.sitename,
            COUNT(*) AS order_count,
            ROUND(SUM(o.money),2) AS revenue,
            ROUND(SUM(o.cost),2)  AS cost,
            ROUND(SUM(o.money - o.cost),2) AS profit
     FROM pre_orders o
     LEFT JOIN pre_site s ON o.zid = s.zid
     WHERE o.status IN (1,2)
       AND o.addtime >= '$start'
       AND o.addtime <  '$end'
     GROUP BY o.zid
     ORDER BY profit DESC"
);

// 全站订单汇总
$total = $DB->getRow(
    "SELECT COUNT(*) AS order_count,
            ROUND(COALESCE(SUM(money),0),2) AS revenue,
            ROUND(COALESCE(SUM(cost),0),2)  AS cost,
            ROUND(COALESCE(SUM(money - cost),0),2) AS profit
     FROM pre_orders
     WHERE status IN (1,2) AND addtime >= '$start' AND addtime < '$end'"
);

// 退款统计
$refund = $DB->getRow(
    "SELECT COUNT(*) AS cnt, ROUND(COALESCE(SUM(money),0),2) AS money
     FROM pre_orders WHERE status = 4 AND addtime >= '$start' AND addtime < '$end'"
);

// 提成型分站：用 pre_points（提成+赠送+退款）作为实际利润来源
$actualRows = $DB->getAll(
    "SELECT zid, ROUND(SUM(point),2) AS actual_profit
     FROM pre_points
     WHERE action IN ('提成', '赠送', '退款')
       AND addtime >= '$start'
       AND addtime <  '$end'
     GROUP BY zid"
);
$actualMap = array();
foreach ($actualRows as $ar) {
    $actualMap[intval($ar['zid'])] = floatval($ar['actual_profit']);
}

// 有提成记录的 zid 集合（判断是否为提成型）
$tichengZids = array();
$tichengRows = $DB->getAll(
    "SELECT DISTINCT zid FROM pre_points
     WHERE action = '提成'
       AND addtime >= '$start'
       AND addtime <  '$end'"
);
foreach ($tichengRows as $tr) {
    $tichengZids[] = intval($tr['zid']);
}

// 赠送/返利 按分站（仅用于列显示）
$giftRows = $DB->getAll(
    "SELECT zid, ROUND(SUM(point),2) AS gift_total
     FROM pre_points
     WHERE (action = '赠送' OR action = 'recharge_rebate')
       AND point > 0
       AND addtime >= '$start'
       AND addtime <  '$end'
     GROUP BY zid"
);
$giftMap = array();
foreach ($giftRows as $g) {
    $giftMap[intval($g['zid'])] = floatval($g['gift_total']);
}

// 退款扣回 按分站（仅用于列显示）
$refundDeductRows = $DB->getAll(
    "SELECT zid, ROUND(ABS(SUM(point)),2) AS refund_deduct
     FROM pre_points
     WHERE action = '退款'
       AND point < 0
       AND addtime >= '$start'
       AND addtime <  '$end'
     GROUP BY zid"
);
$refundDeductMap = array();
foreach ($refundDeductRows as $rd) {
    $refundDeductMap[intval($rd['zid'])] = floatval($rd['refund_deduct']);
}

// 赠送/返利支出（主站成本口径，用于显示额外支出明细）
$totalGift = $DB->getColumn(
    "SELECT ROUND(COALESCE(SUM(point),0),2)
     FROM pre_points
     WHERE (action = '赠送' OR action = 'recharge_rebate')
       AND point > 0
       AND addtime >= '$start'
       AND addtime <  '$end'"
);
$totalGift = floatval($totalGift);

// 全站退款扣回合计（用于显示明细）
$totalRefundDeduct = $DB->getColumn(
    "SELECT ROUND(ABS(COALESCE(SUM(point),0)),2)
     FROM pre_points
     WHERE action = '退款'
       AND point < 0
       AND addtime >= '$start'
       AND addtime <  '$end'"
);
$totalRefundDeduct = floatval($totalRefundDeduct);

// 全站实际利润合计：提成型用points，非提成型用orders
// 先算提成型zid的points合计
$tichengZidList = empty($tichengZids) ? '(0)' : '(' . implode(',', $tichengZids) . ')';
$totalActualPoints = $DB->getColumn(
    "SELECT ROUND(COALESCE(SUM(point),0),2)
     FROM pre_points
     WHERE action IN ('提成', '赠送', '退款')
       AND zid IN $tichengZidList
       AND addtime >= '$start'
       AND addtime <  '$end'"
);
$totalActualPoints = floatval($totalActualPoints);
// 非提成型zid的orders合计
$totalActualOrders = $DB->getColumn(
    "SELECT ROUND(COALESCE(SUM(money-cost),0),2)
     FROM pre_orders
     WHERE status IN (1,2)
       AND zid NOT IN $tichengZidList
       AND addtime >= '$start'
       AND addtime <  '$end'"
);
$totalActualOrders = floatval($totalActualOrders);
$totalActualProfit = round($totalActualPoints + $totalActualOrders, 2);
$profitRate        = ($total['revenue'] > 0) ? round($total['profit'] / $total['revenue'] * 100, 1) : 0;
$actualProfitRate  = ($total['revenue'] > 0) ? round($totalActualProfit / $total['revenue'] * 100, 1) : 0;
?>
<div class="col-md-12 center-block" style="float:none;">
<div class="block">
<div class="block-title clearfix">
  <h2><i class="fa fa-bar-chart"></i> 利润统计 &mdash; <?php echo $label; ?></h2>
</div>

<form method="GET" action="profit.php" style="margin-bottom:16px;">
  <div class="btn-group" style="margin-right:8px;">
    <a href="profit.php?range=today"     class="btn btn-sm <?php echo $range==='today'     ?'btn-primary':'btn-default';?>">今日</a>
    <a href="profit.php?range=yesterday" class="btn btn-sm <?php echo $range==='yesterday' ?'btn-primary':'btn-default';?>">昨日</a>
    <a href="profit.php?range=month"     class="btn btn-sm <?php echo $range==='month'     ?'btn-primary':'btn-default';?>">本月</a>
    <a href="profit.php?range=lastmonth" class="btn btn-sm <?php echo $range==='lastmonth' ?'btn-primary':'btn-default';?>">上月</a>
  </div>
  <input type="hidden" name="range" value="custom">
  <input type="date" name="start" class="form-control input-sm" style="display:inline-block;width:140px;"
         value="<?php echo htmlspecialchars($customStart,ENT_QUOTES,'UTF-8');?>">
  <span style="margin:0 4px;">&ndash;</span>
  <input type="date" name="end" class="form-control input-sm" style="display:inline-block;width:140px;"
         value="<?php echo htmlspecialchars($customEnd,ENT_QUOTES,'UTF-8');?>">
  <button type="submit" class="btn btn-sm btn-info" style="margin-left:6px;">
    <i class="fa fa-search"></i> 查询
  </button>
</form>

<section class="admin-ops-stats admin-profit-stats" style="margin-bottom:20px;">
  <article class="admin-ops-stat admin-ui-stat">
    <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--primary"><i class="fa fa-money"></i></span>
    <div><span>总流水</span><strong>&#165;<?php echo number_format($total['revenue'],2);?></strong></div>
  </article>
  <article class="admin-ops-stat admin-ui-stat">
    <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--warning"><i class="fa fa-arrow-down"></i></span>
    <div><span>总成本</span><strong>&#165;<?php echo number_format($total['cost'],2);?></strong></div>
  </article>
  <article class="admin-ops-stat admin-ui-stat">
    <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--success"><i class="fa fa-line-chart"></i></span>
    <div><span>毛利润</span><strong>&#165;<?php echo number_format($total['profit'],2);?></strong><small><?php echo $profitRate;?>%</small></div>
  </article>
  <article class="admin-ops-stat admin-ui-stat">
    <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--accent"><i class="fa fa-check-circle"></i></span>
    <div><span>实际利润</span><strong>&#165;<?php echo number_format($totalActualProfit,2);?></strong><small><?php echo $actualProfitRate;?>% / 扣赠送 &#165;<?php echo number_format($totalGift,2);?> / 退款扣回 &#165;<?php echo number_format($totalRefundDeduct,2);?></small></div>
  </article>
</section>
<div style="margin-bottom:4px;color:#888;font-size:12px;">
  <i class="fa fa-info-circle"></i>
  完成订单数：<?php echo intval($total['order_count']);?> 笔 &nbsp;|&nbsp;
  退款：<?php echo intval($refund['cnt']);?> 笔 共 &#165;<?php echo number_format($refund['money'],2);?>
</div>

<div class="table-responsive" style="margin-top:12px;">
<table class="table table-bordered table-striped table-hover" style="font-size:14px;">
  <thead style="background:#f5f5f5;">
    <tr>
      <th style="width:80px;">分站 ZID</th>
      <th>站名</th>
      <th style="text-align:right;">订单数</th>
      <th style="text-align:right;">流水</th>
      <th style="text-align:right;">成本</th>
      <th style="text-align:right;">毛利润</th>
      <th style="text-align:right;">赠送/返利</th>
      <th style="text-align:right;">退款扣回</th>
      <th style="text-align:right;">实际利润</th>
      <th style="text-align:right;">利润率</th>
    </tr>
  </thead>
  <tbody>
<?php
if (empty($rows)) {
    echo '<tr><td colspan="9" style="text-align:center;color:#999;padding:30px;">暂无数据</td></tr>';
} else {
    foreach ($rows as $r) {
        $zid        = intval($r['zid']);
        $name       = $r['sitename']
            ? htmlspecialchars($r['sitename'], ENT_QUOTES, 'UTF-8')
            : ($zid === 1 ? '<b>主站</b>' : '<span style="color:#999;">未命名</span>');
        $gift         = isset($giftMap[$zid]) ? $giftMap[$zid] : 0;
        $refundDeduct = isset($refundDeductMap[$zid]) ? $refundDeductMap[$zid] : 0;
        // 提成型用points，非提成型用orders
        if (in_array($zid, $tichengZids)) {
            $actual = isset($actualMap[$zid]) ? $actualMap[$zid] : 0;
        } else {
            $actual = floatval($r['profit']);
        }
        $rate          = $r['revenue'] > 0 ? round($r['profit'] / $r['revenue'] * 100, 1) : 0;
        $profitColor   = floatval($r['profit']) >= 0 ? '#27ae60' : '#e74c3c';
        $actualColor   = $actual >= 0 ? '#e67e22' : '#e74c3c';
        echo '<tr>';
        echo '<td><a href="sitelist.php?zid=' . $zid . '" target="_blank">' . $zid . '</a></td>';
        echo '<td>' . $name . '</td>';
        echo '<td style="text-align:right;">' . intval($r['order_count']) . '</td>';
        echo '<td style="text-align:right;">&#165;' . number_format($r['revenue'], 2) . '</td>';
        echo '<td style="text-align:right;color:#e74c3c;">&#165;' . number_format($r['cost'], 2) . '</td>';
        echo '<td style="text-align:right;font-weight:700;color:' . $profitColor . ';">&#165;' . number_format($r['profit'], 2) . '</td>';
        echo '<td style="text-align:right;color:#e67e22;">&#165;' . number_format($gift, 2) . '</td>';
        echo '<td style="text-align:right;color:#c0392b;">&#165;' . number_format($refundDeduct, 2) . '</td>';
        echo '<td style="text-align:right;font-weight:700;color:' . $actualColor . ';">&#165;' . number_format($actual, 2) . '</td>';
        echo '<td style="text-align:right;">' . $rate . '%</td>';
        echo '</tr>';
    }
}
?>
  </tbody>
  <tfoot style="background:#e8f4e8;font-weight:700;">
    <tr>
      <td colspan="2">合计</td>
      <td style="text-align:right;"><?php echo intval($total['order_count']);?></td>
      <td style="text-align:right;">&#165;<?php echo number_format($total['revenue'],2);?></td>
      <td style="text-align:right;color:#e74c3c;">&#165;<?php echo number_format($total['cost'],2);?></td>
      <td style="text-align:right;color:#27ae60;font-size:15px;">&#165;<?php echo number_format($total['profit'],2);?></td>
      <td style="text-align:right;color:#e67e22;">&#165;<?php echo number_format($totalGift,2);?></td>
      <td style="text-align:right;color:#c0392b;">&#165;<?php echo number_format($totalRefundDeduct,2);?></td>
      <td style="text-align:right;color:#e67e22;font-size:15px;">&#165;<?php echo number_format($totalActualProfit,2);?></td>
      <td style="text-align:right;"><?php echo $profitRate;?>%</td>
    </tr>
  </tfoot>
</table>
</div>
<p style="color:#888;font-size:12px;margin-top:8px;">
  <i class="fa fa-info-circle"></i>
  毛利润 = 完成订单收入 - 成本（订单表口径）；实际利润 = 提成 + 赠送 - 退款扣回（流水表口径，与分站余额变动完全一致）。
</p>
</div>
</div>
<?php include './foot.php'; ?>
</body>
</html>
