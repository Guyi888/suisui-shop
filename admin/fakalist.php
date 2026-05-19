<?php
include "../includes/common.php";

$title = "发卡库存管理";
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission("faka", 1);

if (!function_exists('q8_faka_escape')) {
    function q8_faka_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$categoryRows = $DB->getAll("SELECT cid,name FROM pre_class WHERE active=1 ORDER BY sort ASC,cid ASC");
$categoryOptions = '<option value="0">所有分类</option>';
foreach ($categoryRows as $categoryRow) {
    $categoryOptions .= '<option value="' . intval($categoryRow["cid"]) . '">' . q8_faka_escape($categoryRow["name"]) . '</option>';
}

$my = isset($_GET["my"]) ? trim((string)$_GET["my"]) : null;
if ($my === "move") {
    adminpermission("faka", 2);
    $action = isset($_POST["cid"]) ? intval($_POST["cid"]) : 0;
    $checkbox = isset($_POST["checkbox"]) && is_array($_POST["checkbox"]) ? $_POST["checkbox"] : array();
    if (!$action || !$checkbox) {
        exit("<script language='javascript'>alert('\\u8bf7\\u9009\\u62e9\\u5546\\u54c1\\u548c\\u6279\\u91cf\\u64cd\\u4f5c');history.go(-1);</script>");
    }

    $count = 0;
    foreach ($checkbox as $tid) {
        $tid = intval($tid);
        if ($tid <= 0) {
            continue;
        }
        if ($action === -1) {
            $DB->exec("UPDATE pre_tools SET active=1 WHERE tid='{$tid}' LIMIT 1");
        } elseif ($action === -2) {
            $DB->exec("UPDATE pre_tools SET active=0 WHERE tid='{$tid}' LIMIT 1");
        } elseif ($action === -3) {
            $DB->exec("DELETE FROM pre_tools WHERE tid='{$tid}' LIMIT 1");
        }
        $count++;
    }
    exit("<script language='javascript'>alert('\\u6279\\u91cf\\u5904\\u7406\\u6210\\u529f\\uff0c\\u5171\\u5904\\u7406{$count}\\u4e2a\\u5546\\u54c1');window.location.href='./fakalist.php';</script>");
}

$cid = isset($_GET["cid"]) ? intval($_GET["cid"]) : 0;
$tid = isset($_GET["tid"]) ? intval($_GET["tid"]) : 0;
$where = " is_curl=4";
$link = "";
if ($cid > 0) {
    $where .= " AND cid='{$cid}'";
    $link .= "&cid={$cid}";
} elseif ($tid > 0) {
    $where .= " AND tid='{$tid}'";
    $link .= "&tid={$tid}";
}

$numrows = intval($DB->getColumn("SELECT COUNT(*) FROM pre_tools WHERE{$where}"));
$totalCardGoods = intval($DB->getColumn("SELECT COUNT(*) FROM pre_tools WHERE is_curl=4"));
$visibleCardGoods = intval($DB->getColumn("SELECT COUNT(*) FROM pre_tools WHERE is_curl=4 AND active=1"));
$hiddenCardGoods = intval($DB->getColumn("SELECT COUNT(*) FROM pre_tools WHERE is_curl=4 AND active=0"));
$totalCards = intval($DB->getColumn("SELECT COUNT(*) FROM pre_faka"));
$leftCards = intval($DB->getColumn("SELECT COUNT(*) FROM pre_faka WHERE orderid=0"));
$soldCards = max(0, $totalCards - $leftCards);

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
$rows = $DB->getAll("SELECT a.*,(SELECT COUNT(b.tid) FROM pre_faka AS b WHERE b.tid=a.tid AND orderid=0) AS leftcount,(SELECT COUNT(b.tid) FROM pre_faka AS b WHERE b.tid=a.tid AND orderid!=0) AS sellcount FROM pre_tools AS a WHERE{$where} ORDER BY sort ASC LIMIT {$offset},{$pagesize}");

include "./head.php";
?>
<div class="col-xs-12 admin-ops-page admin-faka-page">
    <section class="admin-ops-hero">
        <div>
            <p class="admin-ops-hero__eyebrow">发卡库存</p>
            <h2>统一管理发卡商品、剩余卡密、售出数量和批量上下架</h2>
            <p>这里专注发卡库存运营，不改动下单流程。可以按分类进入库存、查看卡密、补卡、追踪订单和批量处理商品状态。</p>
        </div>
        <div class="admin-ops-hero__actions">
            <a href="./fakakms.php?my=add" class="admin-ops-chip"><i class="fa fa-plus-circle"></i> 添加卡密</a>
            <a href="./shoplist.php?type=4" class="admin-ops-chip"><i class="fa fa-cubes"></i> 发卡商品</a>
            <a href="./shoprank.php" class="admin-ops-chip"><i class="fa fa-trophy"></i> 销量排行</a>
        </div>
    </section>

    <section class="admin-ops-stats">
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--primary"><i class="fa fa-credit-card"></i></span>
            <div><span>发卡商品</span><strong><?php echo $totalCardGoods; ?></strong></div>
        </article>
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--success"><i class="fa fa-eye"></i></span>
            <div><span>上架 / 下架</span><strong><?php echo $visibleCardGoods; ?> / <?php echo $hiddenCardGoods; ?></strong></div>
        </article>
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--warning"><i class="fa fa-database"></i></span>
            <div><span>剩余卡密</span><strong><?php echo $leftCards; ?></strong></div>
        </article>
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--accent"><i class="fa fa-check-circle"></i></span>
            <div><span>已售卡密</span><strong><?php echo $soldCards; ?></strong></div>
        </article>
    </section>

    <div class="block admin-ops-panel">
        <div class="block-title">
            <div>
                <h3>库存列表</h3>
                <p>当前筛选共 <?php echo $numrows; ?> 个发卡商品，按商品排序展示。</p>
            </div>
            <form action="fakalist.php" method="get" class="admin-ops-inline-form">
                <select name="cid" class="form-control" data-default="<?php echo $cid; ?>"><?php echo $categoryOptions; ?></select>
                <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> 进入分类</button>
                <a href="./fakalist.php" class="btn btn-default"><i class="fa fa-refresh"></i> 重置</a>
            </form>
        </div>

        <form name="form1" method="post" action="fakalist.php?my=move" id="fakaBatchForm">
            <div class="admin-ops-batchbar">
                <label class="admin-ops-checkall"><input name="chkAll1" type="checkbox" id="chkAll1"> 全选当前页</label>
                <select name="cid" class="form-control">
                    <option value="0">批量操作</option>
                    <option value="-1">改为上架中</option>
                    <option value="-2">改为已下架</option>
                    <option value="-3">删除选中</option>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> 执行</button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped admin-ops-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>商品名称</th>
                        <th class="text-center">剩余卡密</th>
                        <th class="text-center">已售出</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($rows) {
                        foreach ($rows as $res) {
                            $rowTid = intval($res["tid"]);
                    ?>
                    <tr>
                        <td><label class="admin-ops-idcheck"><input type="checkbox" name="checkbox[]" value="<?php echo $rowTid; ?>"> <b><?php echo $rowTid; ?></b></label></td>
                        <td><?php echo q8_faka_escape($res["name"]); ?></td>
                        <td class="text-center"><span class="label label-<?php echo intval($res["leftcount"]) > 0 ? 'success' : 'danger'; ?>"><?php echo intval($res["leftcount"]); ?></span></td>
                        <td class="text-center"><?php echo intval($res["sellcount"]); ?></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-xs <?php echo intval($res["active"]) === 1 ? 'btn-success' : 'btn-warning'; ?> js-faka-active" data-tid="<?php echo $rowTid; ?>" data-active="<?php echo intval($res["active"]) === 1 ? 0 : 1; ?>">
                                <i class="fa <?php echo intval($res["active"]) === 1 ? 'fa-eye' : 'fa-eye-slash'; ?>"></i>
                                <?php echo intval($res["active"]) === 1 ? '上架中' : '已下架'; ?>
                            </button>
                        </td>
                        <td class="text-center">
                            <a href="./fakakms.php?tid=<?php echo $rowTid; ?>" class="btn btn-info btn-xs"><i class="fa fa-key"></i> 查看卡密</a>
                            <a href="./fakakms.php?my=add&tid=<?php echo $rowTid; ?>" class="btn btn-success btn-xs"><i class="fa fa-plus"></i> 加卡</a>
                            <a href="./list.php?tid=<?php echo $rowTid; ?>" class="btn btn-warning btn-xs"><i class="fa fa-list-alt"></i> 订单</a>
                            <a href="./shopedit.php?my=delete&tid=<?php echo $rowTid; ?>" class="btn btn-danger btn-xs js-faka-delete"><i class="fa fa-trash"></i> 删除</a>
                        </td>
                    </tr>
                    <?php
                        }
                    } else { ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted admin-ops-empty"><i class="fa fa-inbox"></i><span>当前筛选下暂无发卡商品。</span></td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </form>

        <?php if ($pages > 1) { ?>
        <ul class="pagination admin-ops-pagination">
            <?php
            $first = 1;
            $prev = max(1, $page - 1);
            $next = min($pages, $page + 1);
            $start = max(1, $page - 5);
            $end = min($pages, $page + 5);
            echo $page > 1 ? '<li><a href="fakalist.php?page=' . $first . $link . '">首页</a></li><li><a href="fakalist.php?page=' . $prev . $link . '">&laquo;</a></li>' : '<li class="disabled"><a>首页</a></li><li class="disabled"><a>&laquo;</a></li>';
            for ($i = $start; $i <= $end; $i++) {
                echo $i === $page ? '<li class="active"><span>' . $i . '</span></li>' : '<li><a href="fakalist.php?page=' . $i . $link . '">' . $i . '</a></li>';
            }
            echo $page < $pages ? '<li><a href="fakalist.php?page=' . $next . $link . '">&raquo;</a></li><li><a href="fakalist.php?page=' . $pages . $link . '">尾页</a></li>' : '<li class="disabled"><a>&raquo;</a></li><li class="disabled"><a>尾页</a></li>';
            ?>
        </ul>
        <?php } ?>
    </div>
</div>

<script>
(function () {
    var categorySelect = document.querySelector('select[name="cid"][data-default]');
    if (categorySelect) {
        categorySelect.value = categorySelect.getAttribute('data-default') || '0';
    }

    var checkAll = document.getElementById('chkAll1');
    var batchForm = document.getElementById('fakaBatchForm');
    if (checkAll && batchForm) {
        checkAll.addEventListener('change', function () {
            var items = batchForm.querySelectorAll('input[name="checkbox[]"]');
            for (var i = 0; i < items.length; i++) {
                items[i].checked = checkAll.checked;
            }
        });
        batchForm.addEventListener('submit', function (event) {
            var action = batchForm.querySelector('select[name="cid"]').value;
            var checked = batchForm.querySelectorAll('input[name="checkbox[]"]:checked').length;
            if (action === '0' || checked === 0) {
                event.preventDefault();
                alert('\u8bf7\u5148\u9009\u62e9\u5546\u54c1\u548c\u6279\u91cf\u64cd\u4f5c');
            } else if (action === '-3' && !confirm('\u786e\u5b9a\u5220\u9664\u9009\u4e2d\u7684\u53d1\u5361\u5546\u54c1\u5417\uff1f')) {
                event.preventDefault();
            }
        });
    }

    $('.js-faka-active').on('click', function () {
        var button = $(this);
        $.ajax({
            type: 'GET',
            url: 'ajax_shop.php?act=setTools&tid=' + encodeURIComponent(button.data('tid')) + '&active=' + encodeURIComponent(button.data('active')),
            dataType: 'json',
            success: function () {
                window.location.reload();
            },
            error: function () {
                alert('\u670d\u52a1\u5668\u9519\u8bef');
            }
        });
    });

    $('.js-faka-delete').on('click', function (event) {
        if (!confirm('\u786e\u5b9a\u5220\u9664\u8be5\u53d1\u5361\u5546\u54c1\u5417\uff1f')) {
            event.preventDefault();
        }
    });
})();
</script>
</body>
</html>
