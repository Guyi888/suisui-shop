<?php
include "../includes/common.php";

$title = "加款卡密列表";
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission("site", 1);
if (function_exists('q8_kms_ensure_use_columns')) {
    q8_kms_ensure_use_columns();
}

if (!function_exists('q8_kmlist_escape')) {
    function q8_kmlist_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('getkm')) {
    function getkm($len = 18)
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        $max = strlen($chars) - 1;
        $code = "";
        for ($i = 0; $i < $len; $i++) {
            $code .= $chars[mt_rand(0, $max)];
        }
        return $code;
    }
}

$my = isset($_GET["my"]) ? trim((string)$_GET["my"]) : null;

if ($my === "add") {
    if (!checkRefererHost()) {
        exit;
    }
    adminpermission("site", 2);
    $money = isset($_POST["money"]) ? trim((string)$_POST["money"]) : "";
    $num = isset($_POST["num"]) ? intval($_POST["num"]) : 0;
    $useLimit = isset($_POST["use_limit"]) ? intval($_POST["use_limit"]) : 1;
    if (!is_numeric($money) || !preg_match("/^[0-9.]+$/", $money)) {
        showmsg("金额输入不规范", 3);
        exit;
    }
    if ($num <= 0 || $num > 1000) {
        showmsg("生成数量需要在 1 到 1000 之间", 3);
        exit;
    }
    if ($useLimit <= 0 || $useLimit > 1000) {
        showmsg("每张卡可使用人数需要在 1 到 1000 之间", 3);
        exit;
    }
    $generatedKms = array();

    include "./head.php";
    ?>
    <div class="col-xs-12 admin-ops-page admin-kmlist-page">
        <div class="block admin-ops-panel">
            <div class="block-title">
                <div>
                    <h3>成功生成以下卡密</h3>
                    <p>面额：<?php echo q8_kmlist_escape($money); ?> 元，共 <?php echo intval($num); ?> 张，每张可使用 <?php echo intval($useLimit); ?> 次。</p>
                </div>
                <div class="block-options">
                    <button type="button" class="btn btn-primary" onclick="return q8CopyGeneratedKms()"><i class="fa fa-copy"></i> 一键复制</button>
                    <a href="./kmlist.php" class="btn btn-default"><i class="fa fa-arrow-left"></i> 返回列表</a>
                </div>
            </div>
            <textarea id="q8GeneratedKms" class="form-control" rows="8" readonly style="margin-bottom:12px;"></textarea>
            <div class="list-group">
            <?php
            for ($i = 0; $i < $num; $i++) {
                $km = getkm(18);
                $sql = $DB->exec("INSERT INTO `pre_kms` (`type`,`km`,`money`,`use_limit`,`use_count`,`addtime`) VALUES (0,:km,:money,:use_limit,0,:addtime)", array(':km' => $km, ':money' => $money, ':use_limit' => $useLimit, ':addtime' => $date));
                if ($sql) {
                    $generatedKms[] = $km;
                    echo '<div class="list-group-item"><i class="fa fa-key"></i> <b>' . q8_kmlist_escape($km) . '</b></div>';
                }
            }
            ?>
            </div>
        </div>
    </div>
    <script>
    function q8CopyGeneratedKms() {
        var textarea = document.getElementById('q8GeneratedKms');
        if (!textarea || !textarea.value) return false;
        textarea.select();
        document.execCommand('copy');
        alert('\u5df2\u590d\u5236\u751f\u6210\u7684\u5361\u5bc6');
        return false;
    }
    document.getElementById('q8GeneratedKms').value = <?php echo json_encode(implode("\n", $generatedKms)); ?>;
    </script>
    </body>
    </html>
    <?php
    exit;
}

if ($my === "del") {
    if (!checkRefererHost()) {
        exit;
    }
    adminpermission("site", 2);
    $id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
    $sql = $DB->exec("DELETE FROM pre_kms WHERE kid=:kid", array(':kid' => $id));
    showmsg($sql ? "删除成功！<br/><br/><a href=\"./kmlist.php\">&gt;&gt;返回卡密列表</a>" : "删除失败！<br/><br/><a href=\"./kmlist.php\">&gt;&gt;返回卡密列表</a>", $sql ? 1 : 4);
    exit;
}

if ($my === "qk2" || $my === "qkuse2") {
    if (!checkRefererHost()) {
        exit;
    }
    adminpermission("site", 2);
    $sql = $my === "qk2" ? "DELETE FROM pre_kms WHERE type=0" : "DELETE FROM pre_kms WHERE type=0 AND status=1";
    $ok = $DB->exec($sql) !== false;
    showmsg(($ok ? "清空成功。" : "清空失败。") . "<br/><br/><a href=\"./kmlist.php\">&gt;&gt;返回卡密列表</a>", $ok ? 1 : 4);
    exit;
}

$keyword = isset($_GET["kw"]) ? trim((string)$_GET["kw"]) : "";
$link = "";
if ($keyword !== "") {
    $safeKeyword = daddslashes($keyword);
    $sql = " type=0 AND `km`='" . $safeKeyword . "'";
    $numrows = intval($DB->getColumn("SELECT COUNT(*) FROM pre_kms WHERE" . $sql));
    $titleText = "包含 " . q8_kmlist_escape($keyword) . " 的卡密，共 <b>" . $numrows . "</b> 个";
    $link = "&kw=" . urlencode($keyword);
} else {
    $sql = " type=0";
    $numrows = intval($DB->getColumn("SELECT COUNT(*) FROM pre_kms WHERE" . $sql));
    $titleText = "共有 <b>" . $numrows . "</b> 个加款卡密";
}

$unusedCount = intval($DB->getColumn("SELECT COUNT(*) FROM pre_kms WHERE type=0 AND status=0"));
$usedCount = intval($DB->getColumn("SELECT COUNT(*) FROM pre_kms WHERE type=0 AND status=1"));
$totalMoney = $DB->getColumn("SELECT SUM(money) FROM pre_kms WHERE type=0");
$unusedMoney = $DB->getColumn("SELECT SUM(money) FROM pre_kms WHERE type=0 AND status=0");
$totalMoney = $totalMoney === null ? 0 : round($totalMoney, 2);
$unusedMoney = $unusedMoney === null ? 0 : round($unusedMoney, 2);

$pagesize = 30;
$pages = max(1, intval(ceil($numrows / $pagesize)));
$page = isset($_GET["page"]) ? intval($_GET["page"]) : 1;
if ($page < 1) {
    $page = 1;
}
if ($page > $pages) {
    $page = $pages;
}
$offset = $pagesize * ($page - 1);
$rows = $DB->getAll("SELECT * FROM pre_kms WHERE" . $sql . " ORDER BY kid DESC LIMIT " . $offset . "," . $pagesize);

include "./head.php";
?>
<div class="col-xs-12 admin-ops-page admin-kmlist-page">
    <section class="admin-ops-hero">
        <div>
            <p class="admin-ops-hero__eyebrow">财务工具</p>
            <h2>加款卡密管理</h2>
            <p>生成、搜索、删除和清理加款卡密，帮助运营做线下充值、活动赠送和渠道分发。</p>
        </div>
        <div class="admin-ops-hero__actions">
            <a href="./record.php" class="admin-ops-chip"><i class="fa fa-list-alt"></i> 收支明细</a>
            <a href="./userlist.php" class="admin-ops-chip"><i class="fa fa-users"></i> 用户列表</a>
            <a href="./kmlist.php" class="admin-ops-chip"><i class="fa fa-refresh"></i> 全部卡密</a>
        </div>
    </section>

    <section class="admin-ops-stats">
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--primary"><i class="fa fa-key"></i></span>
            <div><span>卡密总数</span><strong><?php echo $numrows; ?></strong></div>
        </article>
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--success"><i class="fa fa-check-circle"></i></span>
            <div><span>未使用</span><strong><?php echo $unusedCount; ?></strong></div>
        </article>
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--warning"><i class="fa fa-clock-o"></i></span>
            <div><span>已使用</span><strong><?php echo $usedCount; ?></strong></div>
        </article>
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--accent"><i class="fa fa-money"></i></span>
            <div><span>未用面额 / 总面额</span><strong><?php echo $unusedMoney; ?> / <?php echo $totalMoney; ?></strong></div>
        </article>
    </section>

    <?php if ($my === "qk" || $my === "qkuse") { ?>
    <div class="block admin-ops-panel">
        <div class="block-title">
            <div>
                <h3><?php echo $my === "qk" ? "清空全部加款卡密" : "清空已使用加款卡密"; ?></h3>
                <p>清空后无法恢复，请确认已经备份或不再需要这些记录。</p>
            </div>
        </div>
        <div class="block-content admin-ops-form-actions">
            <a href="./kmlist.php?my=<?php echo $my === "qk" ? "qk2" : "qkuse2"; ?>" class="btn btn-danger"><i class="fa fa-trash"></i> 确认清空</a>
            <a href="./kmlist.php" class="btn btn-default"><i class="fa fa-arrow-left"></i> 返回列表</a>
        </div>
    </div>
    <?php } else { ?>
    <div class="modal" id="search" tabindex="-1" role="dialog" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="searchModalLabel">搜索卡密</h4>
                </div>
                <form action="kmlist.php" method="get">
                    <div class="modal-body">
                        <input type="text" class="form-control" name="kw" value="<?php echo q8_kmlist_escape($keyword); ?>" placeholder="请输入加款卡密">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 搜索</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="block admin-ops-panel">
        <div class="block-title">
            <div>
                <h3><?php echo $titleText; ?></h3>
                <p>生成新卡密后会直接写入数据库，可用于用户充值余额。</p>
            </div>
        </div>
        <form action="kmlist.php?my=add" method="post" class="block-content admin-ops-inline-form">
            <input type="text" class="form-control" name="money" placeholder="面额">
            <input type="number" min="1" max="1000" class="form-control" name="num" placeholder="生成个数">
            <input type="number" min="1" max="1000" class="form-control" name="use_limit" value="1" placeholder="每张可用人数">
            <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> 生成</button>
            <a href="kmlist.php?my=qk" class="btn btn-danger"><i class="fa fa-trash"></i> 清空</a>
            <a href="kmlist.php?my=qkuse" class="btn btn-danger"><i class="fa fa-eraser"></i> 清空已使用</a>
            <a href="#" data-toggle="modal" data-target="#search" class="btn btn-success"><i class="fa fa-search"></i> 搜索</a>
        </form>
        <div class="table-responsive">
            <table class="table table-striped admin-ops-table">
                <thead>
                <tr>
                    <th>卡密</th>
                    <th>面额</th>
                    <th>使用额度</th>
                    <th>状态</th>
                    <th>添加时间</th>
                    <th>使用时间</th>
                    <th class="text-center">操作</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($rows) {
                    foreach ($rows as $res) {
                        $isUsed = intval($res["status"]) === 1;
                ?>
                <tr>
                    <td><b><?php echo q8_kmlist_escape($res["km"]); ?></b></td>
                    <td><?php echo q8_kmlist_escape($res["money"]); ?> 元</td>
                    <td><?php echo intval(isset($res["use_count"]) ? $res["use_count"] : ($isUsed ? 1 : 0)); ?> / <?php echo intval(isset($res["use_limit"]) ? max(1, $res["use_limit"]) : 1); ?> 次</td>
                    <td><?php echo $isUsed ? '<span class="label label-danger">已用完</span> <span class="text-muted">ZID:' . intval($res["zid"]) . '</span>' : '<span class="label label-success">可使用</span>'; ?></td>
                    <td><?php echo q8_kmlist_escape($res["addtime"]); ?></td>
                    <td><?php echo q8_kmlist_escape($res["usetime"]); ?></td>
                    <td class="text-center">
                        <a href="./kmlist.php?my=del&id=<?php echo intval($res["kid"]); ?>" class="btn btn-xs btn-danger js-kmlist-delete"><i class="fa fa-trash"></i> 删除</a>
                    </td>
                </tr>
                <?php
                    }
                } else { ?>
                <tr>
                    <td colspan="7" class="text-center text-muted admin-ops-empty"><i class="fa fa-inbox"></i><span>暂无加款卡密。</span></td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <?php if ($pages > 1) { ?>
        <ul class="pagination admin-ops-pagination">
            <?php
            $first = 1;
            $prev = max(1, $page - 1);
            $next = min($pages, $page + 1);
            $start = max(1, $page - 5);
            $end = min($pages, $page + 5);
            echo $page > 1 ? '<li><a href="kmlist.php?page=' . $first . $link . '">首页</a></li><li><a href="kmlist.php?page=' . $prev . $link . '">&laquo;</a></li>' : '<li class="disabled"><a>首页</a></li><li class="disabled"><a>&laquo;</a></li>';
            for ($i = $start; $i <= $end; $i++) {
                echo $i === $page ? '<li class="active"><span>' . $i . '</span></li>' : '<li><a href="kmlist.php?page=' . $i . $link . '">' . $i . '</a></li>';
            }
            echo $page < $pages ? '<li><a href="kmlist.php?page=' . $next . $link . '">&raquo;</a></li><li><a href="kmlist.php?page=' . $pages . $link . '">尾页</a></li>' : '<li class="disabled"><a>&raquo;</a></li><li class="disabled"><a>尾页</a></li>';
            ?>
        </ul>
        <?php } ?>
    </div>
    <?php } ?>
</div>
<script>
(function () {
    $('.js-kmlist-delete').on('click', function (event) {
        if (!confirm('\u786e\u5b9a\u5220\u9664\u8be5\u52a0\u6b3e\u5361\u5bc6\u5417\uff1f')) {
            event.preventDefault();
        }
    });
})();
</script>
</body>
</html>
