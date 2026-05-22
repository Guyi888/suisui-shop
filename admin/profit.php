<?php
include "../includes/common.php";
include_once __DIR__ . '/finance_helpers.php';
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
     WHERE o.status IN (0,1,2)
       AND o.addtime >= '$start'
       AND o.addtime <  '$end'
     GROUP BY o.zid
     ORDER BY profit DESC"
);

$finance = q8_admin_finance_range_stats($start, $end);
$total = array(
    'order_count' => $finance['order_count'],
    'revenue' => $finance['revenue'],
    'cost' => $finance['cost'],
    'profit' => $finance['gross_profit']
);

$pointWhere = q8_admin_finance_point_date_where($start, $end);
$welfareSql = q8_admin_finance_action_sql(q8_admin_finance_welfare_actions());
$commissionRows = $DB->getAll("SELECT zid, ROUND(COALESCE(SUM(point),0),2) AS total FROM pre_points WHERE {$pointWhere} AND action='提成' AND point>0 AND (status IS NULL OR status<>4) GROUP BY zid");
$welfareRows = $DB->getAll("SELECT zid, action, COUNT(*) AS cnt, ROUND(COALESCE(SUM(point),0),2) AS total FROM pre_points WHERE {$pointWhere} AND action IN ({$welfareSql}) AND point>0 GROUP BY zid, action");
$refundRows = $DB->getAll("SELECT zid, action, COUNT(*) AS cnt, ROUND(COALESCE(SUM(ABS(point)),0),2) AS total, CASE WHEN point<0 THEN 'deduct' ELSE 'refund' END AS direction FROM pre_points WHERE {$pointWhere} AND action='退款' GROUP BY zid, direction");
$rechargeRows = $DB->getAll("SELECT zid, action, COUNT(*) AS cnt, ROUND(COALESCE(SUM(point),0),2) AS total FROM pre_points WHERE {$pointWhere} AND action IN ('充值','加款') AND point>0 GROUP BY zid, action");
$pointBreakdown = $DB->getAll("SELECT action,
        CASE WHEN action IN ('消费','扣除','提现','退款扣回') OR (action='退款' AND point<0) THEN 'expense' ELSE 'income' END AS direction,
        COUNT(*) AS cnt,
        ROUND(COALESCE(SUM(ABS(point)),0),2) AS total
    FROM pre_points
    WHERE {$pointWhere}
    GROUP BY action, direction
    ORDER BY direction ASC, total DESC");

$commissionMap = array();
$welfareMap = array();
$refundMap = array();
$refundDeductMap = array();
$rechargeMap = array();
foreach ($commissionRows as $item) $commissionMap[intval($item['zid'])] = floatval($item['total']);
foreach ($welfareRows as $item) {
    $zid = intval($item['zid']);
    if (!isset($welfareMap[$zid])) $welfareMap[$zid] = 0;
    $welfareMap[$zid] += floatval($item['total']);
}
foreach ($refundRows as $item) {
    $zid = intval($item['zid']);
    if ($item['direction'] === 'deduct') $refundDeductMap[$zid] = floatval($item['total']);
    else $refundMap[$zid] = floatval($item['total']);
}
foreach ($rechargeRows as $item) {
    $zid = intval($item['zid']);
    if (!isset($rechargeMap[$zid])) $rechargeMap[$zid] = 0;
    $rechargeMap[$zid] += floatval($item['total']);
}

$profitRate = ($total['revenue'] > 0) ? round($total['profit'] / $total['revenue'] * 100, 1) : 0;
$ownerProfitRate = ($total['revenue'] > 0) ? round($finance['owner_income'] / $total['revenue'] * 100, 1) : 0;
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
    <div><span>订单流水</span><strong>&#165;<?php echo number_format($total['revenue'],2);?></strong><small><?php echo intval($total['order_count']);?> 笔</small></div>
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
    <div><span>主站收入</span><strong>&#165;<?php echo number_format($finance['owner_income'],2);?></strong><small><?php echo $ownerProfitRate;?>% / 扣提成 &#165;<?php echo number_format($finance['commission'],2);?> / 扣福利 &#165;<?php echo number_format($finance['welfare'],2);?></small></div>
  </article>
</section>
<section class="admin-ops-stats admin-profit-stats" style="margin-bottom:16px;">
  <article class="admin-ops-stat admin-ui-stat">
    <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--success"><i class="fa fa-plus-circle"></i></span>
    <div><span>充值入账</span><strong>&#165;<?php echo number_format($finance['recharge_total'],2);?></strong><small>在线 &#165;<?php echo number_format($finance['recharge'],2);?> / 加款 &#165;<?php echo number_format($finance['manual_add'],2);?></small></div>
  </article>
  <article class="admin-ops-stat admin-ui-stat">
    <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--warning"><i class="fa fa-gift"></i></span>
    <div><span>福利赠送</span><strong>&#165;<?php echo number_format($finance['welfare'],2);?></strong><small>签到、返利、任务、开站赠送等</small></div>
  </article>
  <article class="admin-ops-stat admin-ui-stat">
    <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--danger"><i class="fa fa-undo"></i></span>
    <div><span>退款明细</span><strong>&#165;<?php echo number_format($finance['refund_to_balance'],2);?></strong><small><?php echo intval($finance['refund_order_count']);?> 笔订单 / 提成扣回 &#165;<?php echo number_format($finance['commission_refund'],2);?></small></div>
  </article>
  <article class="admin-ops-stat admin-ui-stat">
    <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--primary"><i class="fa fa-database"></i></span>
    <div><span>站点余额</span><strong>&#165;<?php echo number_format($finance['balance_total'],2);?></strong><small>可提现 &#165;<?php echo number_format($finance['balance_withdrawable'],2);?> / 待提现 &#165;<?php echo number_format($finance['withdraw_pending'],2);?></small></div>
  </article>
</section>
<div style="margin-bottom:4px;color:#888;font-size:12px;">
  <i class="fa fa-info-circle"></i>
  完成订单数：<?php echo intval($total['order_count']);?> 笔 &nbsp;|&nbsp;
  退款订单：<?php echo intval($finance['refund_order_count']);?> 笔 共 &#165;<?php echo number_format($finance['refund_order_money'],2);?> &nbsp;|&nbsp;
  主站净收入（含本期退款入账扣减）：&#165;<?php echo number_format($finance['owner_net_income'],2);?>
</div>

<?php if (!empty($pointBreakdown)) { ?>
<div class="table-responsive" style="margin-top:12px;">
<table class="table table-bordered table-hover" style="font-size:13px;">
  <thead style="background:#f8fafc;">
    <tr>
      <th>余额流水类型</th>
      <th>方向</th>
      <th style="text-align:right;">笔数</th>
      <th style="text-align:right;">金额</th>
      <th>统计说明</th>
    </tr>
  </thead>
  <tbody>
  <?php
  foreach ($pointBreakdown as $item) {
      $isIncome = $item['direction'] === 'income';
      $desc = '';
      if ($item['action'] === '充值') $desc = '在线充值和加款卡充值入账';
      elseif ($item['action'] === '加款') $desc = '后台手动加款入账';
      elseif ($item['action'] === '提成') $desc = '分站或上级站点获得的订单提成';
      elseif ($item['action'] === '赠送' || $item['action'] === '奖励' || $item['action'] === '返利' || $item['action'] === 'recharge_rebate') $desc = '签到、充值返利、站点任务、开站赠送等福利';
      elseif ($item['action'] === '退款' && !$isIncome) $desc = '退款时扣回上级提成';
      elseif ($item['action'] === '退款') $desc = '订单退款退回用户余额';
      elseif ($item['action'] === '消费') $desc = '用户余额消费';
      elseif ($item['action'] === '扣除') $desc = '后台手动扣款';
      elseif ($item['action'] === '提现') $desc = '站点余额提现';
      echo '<tr>';
      echo '<td>' . htmlspecialchars($item['action'], ENT_QUOTES, 'UTF-8') . '</td>';
      echo '<td>' . ($isIncome ? '<span class="label label-danger">收入</span>' : '<span class="label label-success">支出</span>') . '</td>';
      echo '<td style="text-align:right;">' . intval($item['cnt']) . '</td>';
      echo '<td style="text-align:right;"><b class="' . ($isIncome ? 'text-danger' : 'text-success') . '">&#165;' . number_format($item['total'], 2) . '</b></td>';
      echo '<td>' . htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') . '</td>';
      echo '</tr>';
  }
  ?>
  </tbody>
</table>
</div>
<?php } ?>

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
      <th style="text-align:right;">分站提成</th>
      <th style="text-align:right;">福利赠送</th>
      <th style="text-align:right;">充值入账</th>
      <th style="text-align:right;">退款/扣回</th>
      <th style="text-align:right;">主站收入</th>
      <th style="text-align:right;">利润率</th>
    </tr>
  </thead>
  <tbody>
<?php
if (empty($rows)) {
    echo '<tr><td colspan="12" style="text-align:center;color:#999;padding:30px;">暂无数据</td></tr>';
} else {
    foreach ($rows as $r) {
        $zid        = intval($r['zid']);
        $name       = $r['sitename']
            ? htmlspecialchars($r['sitename'], ENT_QUOTES, 'UTF-8')
            : ($zid === 1 ? '<b>主站</b>' : '<span style="color:#999;">未命名</span>');
        $commission   = isset($commissionMap[$zid]) ? $commissionMap[$zid] : 0;
        $gift         = isset($welfareMap[$zid]) ? $welfareMap[$zid] : 0;
        $recharge     = isset($rechargeMap[$zid]) ? $rechargeMap[$zid] : 0;
        $refund       = isset($refundMap[$zid]) ? $refundMap[$zid] : 0;
        $refundDeduct = isset($refundDeductMap[$zid]) ? $refundDeductMap[$zid] : 0;
        $actual        = round(floatval($r['profit']) - $commission - $gift, 2);
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
        echo '<td style="text-align:right;color:#8e44ad;">&#165;' . number_format($commission, 2) . '</td>';
        echo '<td style="text-align:right;color:#e67e22;">&#165;' . number_format($gift, 2) . '</td>';
        echo '<td style="text-align:right;color:#2980b9;">&#165;' . number_format($recharge, 2) . '</td>';
        echo '<td style="text-align:right;color:#c0392b;">退 &#165;' . number_format($refund, 2) . '<br><small>扣回 &#165;' . number_format($refundDeduct, 2) . '</small></td>';
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
      <td style="text-align:right;color:#8e44ad;">&#165;<?php echo number_format($finance['commission'],2);?></td>
      <td style="text-align:right;color:#e67e22;">&#165;<?php echo number_format($finance['welfare'],2);?></td>
      <td style="text-align:right;color:#2980b9;">&#165;<?php echo number_format($finance['recharge_total'],2);?></td>
      <td style="text-align:right;color:#c0392b;">退 &#165;<?php echo number_format($finance['refund_to_balance'],2);?><br><small>扣回 &#165;<?php echo number_format($finance['commission_refund'],2);?></small></td>
      <td style="text-align:right;color:#e67e22;font-size:15px;">&#165;<?php echo number_format($finance['owner_income'],2);?></td>
      <td style="text-align:right;"><?php echo $profitRate;?>%</td>
    </tr>
  </tfoot>
</table>
</div>
<p style="color:#888;font-size:12px;margin-top:8px;">
  <i class="fa fa-info-circle"></i>
  订单流水按已支付后进入订单表的待处理、已完成、正在处理订单统计；主站收入 = 订单流水 - 成本 - 分站提成 - 站点福利。充值入账只统计余额变化，不计入主站商品收入。
</p>
</div>
</div>
<?php include './foot.php'; ?>
</body>
</html>
