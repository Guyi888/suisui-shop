<?php
include "../includes/common.php";

$title = "秒杀商品管理";
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission("shop", 1);

if (!function_exists('q8_seckill_escape')) {
    function q8_seckill_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('q8_seckill_category_options')) {
    function q8_seckill_category_options($DB)
    {
        $select = '<option value="0">请选择商品分类</option>';
        $rows = $DB->getAll("SELECT cid,name FROM pre_class WHERE active=1 ORDER BY sort ASC,cid ASC");
        foreach ($rows as $res) {
            $select .= '<option value="' . intval($res["cid"]) . '">' . q8_seckill_escape($res["name"]) . '</option>';
        }
        return $select;
    }
}

$my = isset($_GET["my"]) ? trim((string)$_GET["my"]) : null;

if ($my === "add_submit" || $my === "edit_submit") {
    adminpermission("shop", 2);
    $id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
    $tid = isset($_POST["tid"]) ? intval($_POST["tid"]) : 0;
    $price = isset($_POST["price"]) ? trim((string)$_POST["price"]) : "";
    $value = isset($_POST["value"]) ? trim((string)$_POST["value"]) : "";
    $num = isset($_POST["num"]) ? trim((string)$_POST["num"]) : "";
    $sort = isset($_POST["sort"]) ? trim((string)$_POST["sort"]) : "";
    $startDate = isset($_POST["starttime"]) ? trim((string)$_POST["starttime"]) : "";
    $startClock = isset($_POST["starttimes"]) ? trim((string)$_POST["starttimes"]) : "";
    $endDate = isset($_POST["endtime"]) ? trim((string)$_POST["endtime"]) : "";
    $endClock = isset($_POST["endtimes"]) ? trim((string)$_POST["endtimes"]) : "";

    if ($my === "edit_submit") {
        $exists = $DB->getRow("SELECT * FROM pre_seckillshop WHERE id=:id LIMIT 1", array(':id' => $id));
        if (!$exists) {
            showmsg("当前记录不存在！", 3);
            exit;
        }
        $tid = intval($exists["tid"]);
    }

    if ($tid <= 0 || $price === "" || $value === "" || $num === "" || $sort === "" || $startDate === "" || $startClock === "" || $endDate === "" || $endClock === "") {
        showmsg("保存错误，请确认每项都不为空", 3);
        exit;
    }

    $starttime = $startDate . " " . $startClock . ":00";
    $endtime = $endDate . " " . $endClock . ":00";

    if ($my === "add_submit") {
        $ok = $DB->exec("INSERT INTO `pre_seckillshop` (`tid`,`price`,`value`,`num`,`sort`,`starttime`,`endtime`,`addtime`,`active`) VALUES (:tid,:price,:value,:num,:sort,:starttime,:endtime,:addtime,1)", array(
            ':tid' => $tid,
            ':price' => $price,
            ':value' => $value,
            ':num' => $num,
            ':sort' => $sort,
            ':starttime' => $starttime,
            ':endtime' => $endtime,
            ':addtime' => $date
        ));
        showmsg($ok !== false ? "添加秒杀商品成功！<br/><br/><a href=\"./seckill.php\">&gt;&gt;返回秒杀商品列表</a>" : "添加秒杀商品失败！" . $DB->error(), $ok !== false ? 1 : 4);
    } else {
        $ok = $DB->exec("UPDATE `pre_seckillshop` SET `price`=:price,`value`=:value,`num`=:num,`sort`=:sort,`starttime`=:starttime,`endtime`=:endtime WHERE `id`=:id", array(
            ':price' => $price,
            ':value' => $value,
            ':num' => $num,
            ':sort' => $sort,
            ':starttime' => $starttime,
            ':endtime' => $endtime,
            ':id' => $id
        ));
        showmsg($ok !== false ? "修改秒杀商品成功！<br/><br/><a href=\"./seckill.php\">&gt;&gt;返回秒杀商品列表</a>" : "修改秒杀商品失败！" . $DB->error(), $ok !== false ? 1 : 4);
    }
    exit;
}

$categoryOptions = q8_seckill_category_options($DB);
$editRow = null;
$editToolName = "";
$startDate = "";
$startTime = "";
$endDate = "";
$endTime = "";
if ($my === "edit") {
    $id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
    $editRow = $DB->getRow("SELECT * FROM pre_seckillshop WHERE id=:id LIMIT 1", array(':id' => $id));
    if (!$editRow) {
        showmsg("当前记录不存在！", 3);
        exit;
    }
    $editToolName = (string)$DB->getColumn("SELECT name FROM pre_tools WHERE tid=:tid LIMIT 1", array(':tid' => intval($editRow["tid"])));
    $startParts = explode(" ", (string)$editRow["starttime"]);
    $endParts = explode(" ", (string)$editRow["endtime"]);
    $startDate = isset($startParts[0]) ? $startParts[0] : "";
    $endDate = isset($endParts[0]) ? $endParts[0] : "";
    $startTime = isset($startParts[1]) ? substr($startParts[1], 0, 5) : "";
    $endTime = isset($endParts[1]) ? substr($endParts[1], 0, 5) : "";
}

$totalSeckill = intval($DB->getColumn("SELECT COUNT(*) FROM pre_seckillshop"));
$activeSeckill = intval($DB->getColumn("SELECT COUNT(*) FROM pre_seckillshop WHERE active=1"));
$runningSeckill = intval($DB->getColumn("SELECT COUNT(*) FROM pre_seckillshop WHERE starttime<=NOW() AND endtime>=NOW() AND active=1"));
$endedSeckill = intval($DB->getColumn("SELECT COUNT(*) FROM pre_seckillshop WHERE endtime<NOW()"));

include "./head.php";
?>
<div class="col-xs-12 admin-ops-page admin-seckill-page">
    <section class="admin-ops-hero">
        <div>
            <p class="admin-ops-hero__eyebrow">营销工具</p>
            <h2>秒杀商品管理</h2>
            <p>集中管理秒杀商品、限购数量、活动时间和显示状态。列表支持搜索、分页、刷新、启停和删除。</p>
        </div>
        <div class="admin-ops-hero__actions">
            <a href="./seckill.php?my=add" class="admin-ops-chip"><i class="fa fa-plus-circle"></i> 添加秒杀</a>
            <a href="./shoplist.php" class="admin-ops-chip"><i class="fa fa-cubes"></i> 商品列表</a>
            <a href="./coupons.php" class="admin-ops-chip"><i class="fa fa-ticket"></i> 优惠券</a>
        </div>
    </section>

    <section class="admin-ops-stats">
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--primary"><i class="fa fa-bolt"></i></span>
            <div><span>秒杀商品</span><strong><?php echo $totalSeckill; ?></strong></div>
        </article>
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--success"><i class="fa fa-eye"></i></span>
            <div><span>显示中</span><strong><?php echo $activeSeckill; ?></strong></div>
        </article>
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--warning"><i class="fa fa-clock-o"></i></span>
            <div><span>进行中</span><strong><?php echo $runningSeckill; ?></strong></div>
        </article>
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--accent"><i class="fa fa-history"></i></span>
            <div><span>已结束</span><strong><?php echo $endedSeckill; ?></strong></div>
        </article>
    </section>

    <?php if ($my === "add" || $my === "edit") { ?>
    <div class="block admin-ops-panel">
        <div class="block-title">
            <div>
                <h3><?php echo $my === "add" ? "添加秒杀商品" : "修改秒杀商品"; ?></h3>
                <p>活动保存后会按开始和结束时间生效，显示状态可在列表中单独切换。</p>
            </div>
            <div class="block-options">
                <a href="./seckill.php" class="btn btn-default"><i class="fa fa-arrow-left"></i> 返回列表</a>
            </div>
        </div>
        <form action="./seckill.php?my=<?php echo $my === "add" ? "add_submit" : "edit_submit&id=" . intval($editRow["id"]); ?>" method="post" class="form-horizontal form-bordered">
            <?php if ($my === "add") { ?>
            <div class="form-group">
                <label class="col-sm-2 control-label">选择商品</label>
                <div class="col-sm-10 admin-ops-split-inputs">
                    <select id="cid" class="form-control"><?php echo $categoryOptions; ?></select>
                    <select id="tid" name="tid" class="form-control"></select>
                </div>
            </div>
            <?php } else { ?>
            <div class="form-group">
                <label class="col-sm-2 control-label">商品名称</label>
                <div class="col-sm-10">
                    <input type="text" value="<?php echo q8_seckill_escape($editToolName); ?>" class="form-control" disabled>
                </div>
            </div>
            <?php } ?>
            <div class="form-group">
                <label class="col-sm-2 control-label">秒杀价格</label>
                <div class="col-sm-10"><input type="text" name="price" value="<?php echo $editRow ? q8_seckill_escape($editRow["price"]) : ""; ?>" class="form-control" placeholder="输入秒杀价格" required></div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">秒杀限制</label>
                <div class="col-sm-10"><input type="number" min="1" name="value" value="<?php echo $editRow ? intval($editRow["value"]) : ""; ?>" class="form-control" placeholder="输入本轮秒杀库存限制" required></div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">下单数量</label>
                <div class="col-sm-10"><input type="number" min="1" name="num" value="<?php echo $editRow ? intval($editRow["num"]) : ""; ?>" class="form-control" placeholder="输入每次下单数量" required></div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">开始时间</label>
                <div class="col-sm-10 admin-ops-split-inputs">
                    <input type="date" class="form-control" name="starttime" value="<?php echo q8_seckill_escape($startDate); ?>" required>
                    <input type="time" class="form-control" name="starttimes" value="<?php echo q8_seckill_escape($startTime); ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">结束时间</label>
                <div class="col-sm-10 admin-ops-split-inputs">
                    <input type="date" class="form-control" name="endtime" value="<?php echo q8_seckill_escape($endDate); ?>" required>
                    <input type="time" class="form-control" name="endtimes" value="<?php echo q8_seckill_escape($endTime); ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">排序数字</label>
                <div class="col-sm-10"><input type="number" min="1" max="1000" name="sort" value="<?php echo $editRow ? intval($editRow["sort"]) : ""; ?>" class="form-control" placeholder="数字越小越靠前" required></div>
            </div>
            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 admin-ops-form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> <?php echo $my === "add" ? "添加" : "保存修改"; ?></button>
                    <a href="./seckill.php" class="btn btn-default"><i class="fa fa-chevron-left"></i> 返回列表</a>
                </div>
            </div>
        </form>
    </div>
    <script src="<?php echo $cdnpublic; ?>layer/3.1.1/layer.js"></script>
    <script src="assets/js/seckilledit.js?ver=<?php echo VERSION; ?>"></script>
    <?php } else { ?>
    <div class="block admin-ops-panel">
        <div class="block-title">
            <div>
                <h3 id="blocktitle">秒杀商品列表</h3>
                <p>按排序数字展示秒杀商品，可搜索商品名并调整每页数量。</p>
            </div>
            <div class="block-options">
                <select id="pagesize" class="form-control">
                    <option value="30">30 条</option>
                    <option value="50">50 条</option>
                    <option value="60">60 条</option>
                    <option value="80">80 条</option>
                    <option value="100">100 条</option>
                </select>
            </div>
        </div>
        <form id="seckillSearchForm" method="get" class="block-content admin-ops-inline-form">
            <a href="./seckill.php?my=add" class="btn btn-primary"><i class="fa fa-plus"></i> 添加秒杀商品</a>
            <input type="text" class="form-control" name="kw" placeholder="请输入商品名称">
            <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> 搜索</button>
            <button type="button" class="btn btn-default" id="seckillRefresh"><i class="fa fa-refresh"></i> 刷新</button>
        </form>
        <div id="listTable" class="admin-ops-table-shell"></div>
    </div>
    <script src="<?php echo $cdnpublic; ?>layer/3.1.1/layer.js"></script>
    <script src="assets/js/seckill.js?ver=<?php echo VERSION; ?>"></script>
    <?php } ?>
</div>
</body>
</html>
