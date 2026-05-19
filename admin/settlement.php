<?php
include "../includes/common.php";
$title = "&#36890;&#36947;&#32467;&#31639;";
if ($islogin != 1) exit("<script>window.location.href='./login.php';</script>");
adminpermission("record", 1);
include "./head.php";

function settle_h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function settle_money($n){ return number_format((float)$n, 2, '.', ''); }
function settle_date($key, $default){
    $value = isset($_GET[$key]) ? trim($_GET[$key]) : $default;
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : $default;
}
function settle_num($key, $default){
    $value = isset($_GET[$key]) ? trim($_GET[$key]) : $default;
    return is_numeric($value) ? (float)$value : (float)$default;
}
function settle_ident($value){
    $value = (string)$value;
    return preg_match('/^[A-Za-z0-9_]+$/', $value) ? $value : 'shua';
}
function settle_fetch_site($cfg, $start, $end, $channel){
    $empty = array('count' => 0, 'amount' => 0, 'alipay' => 0, 'wxpay' => 0, 'rows' => array(), 'error' => '');
    $mysqli = @new mysqli($cfg['host'], $cfg['user'], $cfg['pwd'], $cfg['dbname'], intval($cfg['port']));
    if ($mysqli->connect_errno) {
        $empty['error'] = 'DB connect failed: ' . $mysqli->connect_errno;
        return $empty;
    }
    $mysqli->set_charset('utf8mb4');
    $table = settle_ident($cfg['dbqz']) . '_pay';
    $sql = "SELECT type, COUNT(*) AS paid_count, ROUND(COALESCE(SUM(CAST(money AS DECIMAL(12,2))),0),2) AS paid_amount FROM `{$table}` WHERE status=1 AND channel=? AND endtime>=? AND endtime<? GROUP BY type ORDER BY type";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $empty['error'] = 'SQL prepare failed';
        $mysqli->close();
        return $empty;
    }
    $stmt->bind_param('sss', $channel, $start, $end);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = array();
    $totalAmount = 0;
    $totalCount = 0;
    $alipay = 0;
    $wxpay = 0;
    while ($row = $res->fetch_assoc()) {
        $type = (string)$row['type'];
        $amount = (float)$row['paid_amount'];
        $count = (int)$row['paid_count'];
        $rows[] = array('type' => $type, 'count' => $count, 'amount' => $amount);
        $totalAmount += $amount;
        $totalCount += $count;
        if ($type === 'alipay') $alipay += $amount;
        if ($type === 'wxpay') $wxpay += $amount;
    }
    $stmt->close();
    $mysqli->close();
    return array('count' => $totalCount, 'amount' => round($totalAmount, 2), 'alipay' => round($alipay, 2), 'wxpay' => round($wxpay, 2), 'rows' => $rows, 'error' => '');
}
function settle_calc($data, $refund, $rate){
    $fee = round(((float)$data['amount']) * $rate / 100, 2);
    $settle = round(((float)$data['amount']) - $fee - $refund, 2);
    return array('fee' => $fee, 'settle' => $settle);
}

$settleConfigFile = ROOT . 'config/settlement_calc.php';
$settleConfig = is_file($settleConfigFile) ? include $settleConfigFile : array();
if (!is_array($settleConfig)) $settleConfig = array();
$defaultDate = date('Y-m-d', strtotime('-1 day'));
$date = settle_date('date', $defaultDate);
$rate = max(0, settle_num('rate', isset($settleConfig['rate_default']) ? $settleConfig['rate_default'] : 6));
$localRefund = max(0, settle_num('refund', 0));
$channel = isset($settleConfig['channel']) && $settleConfig['channel'] !== '' ? $settleConfig['channel'] : 'epay4';
$start = $date . ' 00:00:00';
$end = date('Y-m-d 00:00:00', strtotime($date . ' +1 day'));

$localCfg = array(
    'name' => '当前站点',
    'host' => $dbconfig['host'],
    'port' => $dbconfig['port'],
    'user' => $dbconfig['user'],
    'pwd' => $dbconfig['pwd'],
    'dbname' => $dbconfig['dbname'],
    'dbqz' => $dbconfig['dbqz'],
);
$sites = array(
    'local' => array('cfg' => $localCfg, 'refund' => $localRefund),
);
if (!empty($settleConfig['sites']) && is_array($settleConfig['sites'])) {
    foreach ($settleConfig['sites'] as $key => $cfg) {
        if (!is_array($cfg) || empty($cfg['host']) || empty($cfg['user']) || empty($cfg['dbname'])) continue;
        $safeKey = settle_ident($key);
        $cfg['name'] = isset($cfg['name']) && $cfg['name'] !== '' ? $cfg['name'] : strtoupper($safeKey);
        $sites[$safeKey] = array('cfg' => $cfg, 'refund' => max(0, settle_num('refund_' . $safeKey, 0)));
    }
}
foreach ($sites as $key => $item) {
    $sites[$key]['data'] = settle_fetch_site($item['cfg'], $start, $end, $channel);
    $sites[$key]['calc'] = settle_calc($sites[$key]['data'], $item['refund'], $rate);
}
$total = array('count' => 0, 'amount' => 0, 'refund' => 0, 'fee' => 0, 'settle' => 0, 'alipay' => 0, 'wxpay' => 0);
foreach ($sites as $item) {
    $total['count'] += (int)$item['data']['count'];
    $total['amount'] += (float)$item['data']['amount'];
    $total['refund'] += (float)$item['refund'];
    $total['fee'] += (float)$item['calc']['fee'];
    $total['settle'] += (float)$item['calc']['settle'];
    $total['alipay'] += (float)$item['data']['alipay'];
    $total['wxpay'] += (float)$item['data']['wxpay'];
}
?>
<style>
.settle-stat-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 18px;
}
.settle-stat-card {
    position: relative;
    min-height: 128px;
    padding: 24px 88px 22px 24px;
    background: #fff;
    border: 1px solid #e8eef5;
    border-radius: 8px;
    box-shadow: 0 8px 22px rgba(15, 23, 42, .04);
}
.settle-stat-card__label {
    margin-bottom: 12px;
    color: #243b5a;
    font-size: 14px;
    font-weight: 700;
}
.settle-stat-card__value {
    color: #050b18;
    font-size: 28px;
    font-weight: 700;
    line-height: 1.1;
}
.settle-stat-card__value small {
    margin-right: 4px;
    color: #243b5a;
    font-size: 18px;
    font-weight: 600;
}
.settle-stat-card__meta {
    margin-top: 12px;
    color: #6b7a90;
    font-size: 13px;
}
.settle-stat-card__meta b {
    color: #4f6fba;
}
.settle-stat-card__icon {
    position: absolute;
    right: 22px;
    top: 44px;
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.settle-stat-card--orders .settle-stat-card__icon { background: #e8fbf4; color: #2bbf91; }
.settle-stat-card--amount .settle-stat-card__icon { background: #fff5e6; color: #f4a62a; }
.settle-stat-card--fee .settle-stat-card__icon { background: #eef4ff; color: #537bff; }
.settle-stat-card--settle .settle-stat-card__icon { background: #fff0f0; color: #ff6b6b; }
@media (max-width: 991px) {
    .settle-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 560px) {
    .settle-stat-grid { grid-template-columns: 1fr; }
    .settle-stat-card { padding-right: 76px; }
}
</style>
<div class="col-sm-12 col-md-10 center-block" style="float: none;">
  <div class="settle-stat-grid">
    <div class="settle-stat-card settle-stat-card--orders">
      <div class="settle-stat-card__label">&#36890;&#36947;&#35746;&#21333;&#25968;</div>
      <div class="settle-stat-card__value"><?php echo intval($total['count']); ?></div>
      <div class="settle-stat-card__meta"><?php echo settle_h($date); ?> / <b><?php echo settle_h($channel); ?></b></div>
      <div class="settle-stat-card__icon"><i class="fa fa-list-alt"></i></div>
    </div>
    <div class="settle-stat-card settle-stat-card--amount">
      <div class="settle-stat-card__label">&#36890;&#36947;&#20132;&#26131;&#39069;</div>
      <div class="settle-stat-card__value"><small>&#165;</small><?php echo settle_money($total['amount']); ?></div>
      <div class="settle-stat-card__meta">&#25903;&#20184;&#23453; <b>&#165;<?php echo settle_money($total['alipay']); ?></b> / &#24494;&#20449; <b>&#165;<?php echo settle_money($total['wxpay']); ?></b></div>
      <div class="settle-stat-card__icon"><i class="fa fa-line-chart"></i></div>
    </div>
    <div class="settle-stat-card settle-stat-card--fee">
      <div class="settle-stat-card__label">&#39044;&#35745;&#25163;&#32493;&#36153;</div>
      <div class="settle-stat-card__value"><small>&#165;</small><?php echo settle_money($total['fee']); ?></div>
      <div class="settle-stat-card__meta">&#36153;&#29575; <b><?php echo settle_h($rate); ?>%</b> / &#36864;&#27454; <b>&#165;<?php echo settle_money($total['refund']); ?></b></div>
      <div class="settle-stat-card__icon"><i class="fa fa-credit-card"></i></div>
    </div>
    <div class="settle-stat-card settle-stat-card--settle">
      <div class="settle-stat-card__label">&#39044;&#35745;&#32467;&#31639;&#39069;</div>
      <div class="settle-stat-card__value"><small>&#165;</small><?php echo settle_money($total['settle']); ?></div>
      <div class="settle-stat-card__meta">&#20132;&#26131;&#39069;-&#25163;&#32493;&#36153;-&#36864;&#27454;</div>
      <div class="settle-stat-card__icon"><i class="fa fa-refresh"></i></div>
    </div>
  </div>
  <div class="block">
    <div class="block-title"><h3 class="panel-title"><i class="fa fa-calculator"></i> &#36890;&#36947;&#32467;&#31639;</h3></div>
    <form class="form-inline" method="get" action="settlement.php" style="margin-bottom:15px;">
      <div class="form-group"><label>&#26085;&#26399;</label> <input type="date" name="date" value="<?php echo settle_h($date); ?>" class="form-control"></div>
      <div class="form-group"><label>&#36153;&#29575;%</label> <input type="number" step="0.01" min="0" name="rate" value="<?php echo settle_h($rate); ?>" class="form-control" style="width:90px;"></div>
      <div class="form-group"><label>&#36864;&#27454;&#39069;</label> <input type="number" step="0.01" min="0" name="refund" value="<?php echo settle_h($localRefund); ?>" class="form-control" style="width:110px;"></div>
      <?php foreach ($sites as $key => $item) { if ($key === 'local') continue; ?>
      <div class="form-group"><label><?php echo settle_h($item['cfg']['name']); ?>&#36864;&#27454;</label> <input type="number" step="0.01" min="0" name="refund_<?php echo settle_h($key); ?>" value="<?php echo settle_h($item['refund']); ?>" class="form-control" style="width:110px;"></div>
      <?php } ?>
      <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> &#26597;&#35810;</button>
    </form>
    <div class="alert alert-info" style="margin-bottom:15px;"><i class="fa fa-info-circle"></i> &#21482;&#32479;&#35745; <b><?php echo settle_h($channel); ?></b> &#24050;&#25903;&#20184;&#35746;&#21333;&#65288;status=1&#65292;&#25353;endtime&#65289;&#12290;&#36864;&#27454;&#39069;&#38656;&#25163;&#21160;&#22635;&#20889;&#65292;&#32467;&#31639;=&#20132;&#26131;&#39069;-&#25163;&#32493;&#36153;-&#36864;&#27454;&#12290;</div>
    <div class="table-responsive"><table class="table table-bordered table-striped table-vcenter"><thead><tr><th>&#31449;&#28857;</th><th>&#31508;&#25968;</th><th>&#25903;&#20184;&#23453;</th><th>&#24494;&#20449;</th><th>&#20132;&#26131;&#39069;</th><th>&#36864;&#27454;</th><th>&#25163;&#32493;&#36153;</th><th>&#32467;&#31639;&#39069;</th></tr></thead><tbody>
      <?php foreach ($sites as $key => $item) { ?><tr><td><b><?php echo settle_h($item['cfg']['name']); ?></b><?php if ($item['data']['error']) { ?><br><span class="text-danger"><?php echo settle_h($item['data']['error']); ?></span><?php } ?></td><td><?php echo intval($item['data']['count']); ?></td><td><?php echo settle_money($item['data']['alipay']); ?></td><td><?php echo settle_money($item['data']['wxpay']); ?></td><td><b><?php echo settle_money($item['data']['amount']); ?></b></td><td><?php echo settle_money($item['refund']); ?></td><td><?php echo settle_money($item['calc']['fee']); ?></td><td><b class="text-success"><?php echo settle_money($item['calc']['settle']); ?></b></td></tr><?php } ?>
      <tr class="active"><td><b>&#21512;&#35745;</b></td><td><b><?php echo intval($total['count']); ?></b></td><td><b><?php echo settle_money($total['alipay']); ?></b></td><td><b><?php echo settle_money($total['wxpay']); ?></b></td><td><b><?php echo settle_money($total['amount']); ?></b></td><td><b><?php echo settle_money($total['refund']); ?></b></td><td><b><?php echo settle_money($total['fee']); ?></b></td><td><b class="text-success"><?php echo settle_money($total['settle']); ?></b></td></tr>
    </tbody></table></div>
    <p class="text-muted">&#35828;&#26126;&#65306;&#40664;&#35748;&#32479;&#35745;&#24403;&#21069;&#31449;&#28857;&#25903;&#20184;&#35746;&#21333;&#65292;&#22914;&#38656;&#32479;&#35745;&#20854;&#20182;&#31449;&#28857;&#65292;&#21487;&#22312; <code>config/settlement_calc.php</code> &#20013;&#28155;&#21152;&#25968;&#25454;&#24211;&#36830;&#25509;&#20449;&#24687;&#12290;</p>
  </div>
</div>
<?php include './foot.php'; ?>
