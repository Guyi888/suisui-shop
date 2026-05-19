<?php
include "../includes/common.php";

$title = "商品销量排行";
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

if (!function_exists('q8_shoprank_escape')) {
    function q8_shoprank_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$todayStart = date("Y-m-d") . " 00:00:00";
$yesterdayStart = date("Y-m-d", strtotime("-1 day")) . " 00:00:00";
$isYesterday = isset($_GET["last"]) && intval($_GET["last"]) === 1;
$rangeStart = $isYesterday ? $yesterdayStart : $todayStart;
$rangeEnd = $isYesterday ? $todayStart : null;
$rangeTitle = $isYesterday ? "昨日销量排行" : "今日销量排行";
$rangeLabel = $isYesterday ? date("Y-m-d", strtotime("-1 day")) : date("Y-m-d");

if ($isYesterday) {
    $sql = "SELECT B.tid,B.name,COUNT(A.id) num FROM pre_orders A LEFT JOIN pre_tools B ON A.tid=B.tid WHERE A.addtime>='{$rangeStart}' AND A.addtime<'{$rangeEnd}' GROUP BY B.tid ORDER BY num DESC LIMIT 20";
    $totalOrders = intval($DB->getColumn("SELECT COUNT(*) FROM pre_orders WHERE addtime>='{$rangeStart}' AND addtime<'{$rangeEnd}'"));
    $productCount = intval($DB->getColumn("SELECT COUNT(DISTINCT tid) FROM pre_orders WHERE addtime>='{$rangeStart}' AND addtime<'{$rangeEnd}'"));
} else {
    $sql = "SELECT B.tid,B.name,COUNT(A.id) num FROM pre_orders A LEFT JOIN pre_tools B ON A.tid=B.tid WHERE A.addtime>='{$rangeStart}' GROUP BY B.tid ORDER BY num DESC LIMIT 20";
    $totalOrders = intval($DB->getColumn("SELECT COUNT(*) FROM pre_orders WHERE addtime>='{$rangeStart}'"));
    $productCount = intval($DB->getColumn("SELECT COUNT(DISTINCT tid) FROM pre_orders WHERE addtime>='{$rangeStart}'"));
}

$rows = $DB->getAll($sql);
$topCount = count($rows);
$topOrders = 0;
foreach ($rows as $row) {
    $topOrders += intval($row["num"]);
}
$topRate = $totalOrders > 0 ? round($topOrders * 100 / $totalOrders, 1) : 0;

include "./head.php";
?>
<div class="col-xs-12 admin-ops-page admin-shoprank-page">
    <section class="admin-ops-hero">
        <div>
            <p class="admin-ops-hero__eyebrow">商品排行</p>
            <h2><?php echo q8_shoprank_escape($rangeTitle); ?></h2>
            <p>按订单量统计商品热度，帮助运营快速判断今日爆款、昨日余温和需要补库存的发卡商品。</p>
        </div>
        <div class="admin-ops-hero__actions">
            <a href="./shoplist.php" class="admin-ops-chip"><i class="fa fa-cubes"></i> 商品列表</a>
            <a href="./fakalist.php" class="admin-ops-chip"><i class="fa fa-credit-card"></i> 发卡库存</a>
            <a href="./toollogs.php" class="admin-ops-chip"><i class="fa fa-line-chart"></i> 商品动态</a>
        </div>
    </section>

    <section class="admin-ops-stats">
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--primary"><i class="fa fa-shopping-bag"></i></span>
            <div><span>订单总数</span><strong><?php echo $totalOrders; ?></strong></div>
        </article>
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--success"><i class="fa fa-cube"></i></span>
            <div><span>动销商品</span><strong><?php echo $productCount; ?></strong></div>
        </article>
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--warning"><i class="fa fa-trophy"></i></span>
            <div><span>榜单商品</span><strong><?php echo $topCount; ?></strong></div>
        </article>
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--accent"><i class="fa fa-pie-chart"></i></span>
            <div><span>榜单占比</span><strong><?php echo $topRate; ?>%</strong></div>
        </article>
    </section>

    <div class="block admin-ops-panel">
        <div class="block-title">
            <div>
                <h3><?php echo q8_shoprank_escape($rangeTitle); ?></h3>
                <p>统计日期：<?php echo q8_shoprank_escape($rangeLabel); ?>，最多展示前 20 名。</p>
            </div>
            <div class="block-options">
                <a href="shoprank.php" class="btn <?php echo $isYesterday ? 'btn-default' : 'btn-primary'; ?>"><i class="fa fa-calendar-check-o"></i> 今日</a>
                <a href="shoprank.php?last=1" class="btn <?php echo $isYesterday ? 'btn-primary' : 'btn-default'; ?>"><i class="fa fa-calendar-minus-o"></i> 昨日</a>
                <a href="javascript:window.location.reload();" class="btn btn-default"><i class="fa fa-refresh"></i> 刷新</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped admin-ops-table">
                <thead>
                <tr>
                    <th class="text-center">排名</th>
                    <th class="text-center">商品 ID</th>
                    <th>商品名称</th>
                    <th class="text-center">订单数量</th>
                    <th class="text-center">操作</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($rows) {
                    $rank = 1;
                    foreach ($rows as $res) {
                        $tid = intval($res["tid"]);
                        $name = $res["name"] !== null && $res["name"] !== '' ? $res["name"] : "已删除商品";
                ?>
                <tr>
                    <td class="text-center"><span class="badge"><?php echo $rank; ?></span></td>
                    <td class="text-center"><b><?php echo $tid; ?></b></td>
                    <td><?php echo q8_shoprank_escape($name); ?></td>
                    <td class="text-center"><strong><?php echo intval($res["num"]); ?></strong></td>
                    <td class="text-center">
                        <?php if ($tid > 0) { ?>
                        <a href="./shopedit.php?my=edit&tid=<?php echo $tid; ?>" class="btn btn-xs btn-default"><i class="fa fa-pencil"></i> 编辑</a>
                        <a href="./list.php?tid=<?php echo $tid; ?>" class="btn btn-xs btn-info"><i class="fa fa-list-alt"></i> 订单</a>
                        <?php } else { ?>
                        <span class="text-muted">无可用商品</span>
                        <?php } ?>
                    </td>
                </tr>
                <?php
                        $rank++;
                    }
                } else { ?>
                <tr>
                    <td colspan="5" class="text-center text-muted admin-ops-empty">
                        <i class="fa fa-inbox"></i>
                        <span>当前日期还没有可统计的商品订单。</span>
                    </td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
