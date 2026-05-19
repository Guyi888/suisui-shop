<?php
include "../includes/common.php";
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission("shop", 1);

if (!function_exists('q8_seckill_table_escape')) {
    function q8_seckill_table_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$link = "";
if (isset($_GET["kw"]) && trim((string)$_GET["kw"]) !== "") {
    $kw = trim(daddslashes($_GET["kw"]));
    $sql = " B.name LIKE '%" . $kw . "%'";
    $numrows = intval($DB->getColumn("SELECT COUNT(*) FROM pre_seckillshop A LEFT JOIN pre_tools B ON A.tid=B.tid WHERE" . $sql));
    $con = "包含 <b>" . q8_seckill_table_escape($kw) . "</b> 的秒杀商品，共 <b>" . $numrows . "</b> 个";
    $link = "&kw=" . urlencode($kw);
} elseif (isset($_GET["id"])) {
    $id = intval($_GET["id"]);
    $numrows = intval($DB->getColumn("SELECT COUNT(*) FROM pre_seckillshop WHERE id='" . $id . "'"));
    $sql = " A.id='" . $id . "'";
    $con = "秒杀商品列表";
    $link = "&id=" . $id;
} else {
    $numrows = intval($DB->getColumn("SELECT COUNT(*) FROM pre_seckillshop"));
    $sql = " 1";
    $con = "系统共有 <b>" . $numrows . "</b> 个秒杀商品";
}

$pagesize = isset($_GET["num"]) ? intval($_GET["num"]) : 30;
if (!in_array($pagesize, array(30, 50, 60, 80, 100), true)) {
    $pagesize = 30;
}
$pages = max(1, intval(ceil($numrows / $pagesize)));
$page = isset($_GET["page"]) ? intval($_GET["page"]) : 1;
if ($page < 1) {
    $page = 1;
}
if ($page > $pages) {
    $page = $pages;
}
$offset = $pagesize * ($page - 1);
$rows = $DB->getAll("SELECT A.*,B.name FROM pre_seckillshop A LEFT JOIN pre_tools B ON A.tid=B.tid WHERE" . $sql . " ORDER BY A.sort ASC LIMIT " . $offset . "," . $pagesize);
?>
<div class="admin-ops-table-meta" data-title="<?php echo q8_seckill_table_escape($con); ?>"></div>
<div class="table-responsive">
    <table class="table table-striped admin-ops-table">
        <thead>
        <tr>
            <th>商品 ID</th>
            <th>商品名称</th>
            <th title="数字越小越靠前">排序</th>
            <th>秒杀设置</th>
            <th>剩余数量</th>
            <th>秒杀时间</th>
            <th class="text-center">状态</th>
            <th class="text-center">操作</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($rows) {
            foreach ($rows as $res) {
                $left = max(0, intval($res["value"]) - intval($res["count"]));
                $active = intval($res["active"]);
        ?>
        <tr>
            <td><a href="./shoplist.php?tid=<?php echo intval($res["tid"]); ?>"><?php echo intval($res["tid"]); ?></a></td>
            <td><?php echo q8_seckill_table_escape($res["name"]); ?></td>
            <td><?php echo intval($res["sort"]); ?></td>
            <td>秒杀价格：<b class="text-danger"><?php echo q8_seckill_table_escape($res["price"]); ?></b> 元，每单 <b><?php echo intval($res["num"]); ?></b> 件</td>
            <td>剩余 <b class="text-danger"><?php echo $left; ?></b> 件</td>
            <td><?php echo q8_seckill_table_escape($res["starttime"]); ?> 至 <?php echo q8_seckill_table_escape($res["endtime"]); ?></td>
            <td class="text-center">
                <button type="button" class="btn btn-xs <?php echo $active === 1 ? 'btn-success' : 'btn-warning'; ?> js-seckill-active" data-id="<?php echo intval($res["id"]); ?>" data-active="<?php echo $active === 1 ? 0 : 1; ?>">
                    <i class="fa <?php echo $active === 1 ? 'fa-eye' : 'fa-eye-slash'; ?>"></i>
                    <?php echo $active === 1 ? "显示" : "隐藏"; ?>
                </button>
            </td>
            <td class="text-center">
                <a href="./seckill.php?my=edit&id=<?php echo intval($res["id"]); ?>" class="btn btn-info btn-xs"><i class="fa fa-pencil"></i> 编辑</a>
                <button type="button" class="btn btn-danger btn-xs js-seckill-delete" data-id="<?php echo intval($res["id"]); ?>"><i class="fa fa-trash"></i> 删除</button>
            </td>
        </tr>
        <?php
            }
        } else { ?>
        <tr>
            <td colspan="8" class="text-center text-muted admin-ops-empty"><i class="fa fa-inbox"></i><span>暂无秒杀商品。</span></td>
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
echo $page > 1 ? '<li><a href="javascript:void(0)" data-seckill-query="page=' . $first . $link . '">首页</a></li><li><a href="javascript:void(0)" data-seckill-query="page=' . $prev . $link . '">&laquo;</a></li>' : '<li class="disabled"><a>首页</a></li><li class="disabled"><a>&laquo;</a></li>';
for ($i = $start; $i <= $end; $i++) {
    echo $i === $page ? '<li class="active"><span>' . $i . '</span></li>' : '<li><a href="javascript:void(0)" data-seckill-query="page=' . $i . $link . '">' . $i . '</a></li>';
}
echo $page < $pages ? '<li><a href="javascript:void(0)" data-seckill-query="page=' . $next . $link . '">&raquo;</a></li><li><a href="javascript:void(0)" data-seckill-query="page=' . $pages . $link . '">尾页</a></li>' : '<li class="disabled"><a>&raquo;</a></li><li class="disabled"><a>尾页</a></li>';
?>
</ul>
<?php } ?>
