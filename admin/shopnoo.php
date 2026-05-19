<?php
include "../includes/common.php";

$title = "商品介绍批量替换";
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission("shop", 1);

if (!function_exists('q8_shopnoo_escape')) {
    function q8_shopnoo_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('q8_shopnoo_replace')) {
    function q8_shopnoo_replace($search, $replace, $preview = true)
    {
        global $DB;

        if ($search === '') {
            return array("status" => 0, "msg" => "查找内容不能为空");
        }

        $rows = $DB->getAll("SELECT tid,name,`desc` FROM pre_tools WHERE `desc` LIKE :pattern", array(':pattern' => "%{$search}%"));
        if (!$rows) {
            return array("status" => 0, "msg" => "未找到匹配的商品介绍");
        }

        $results = array();
        $affected = 0;
        foreach ($rows as $row) {
            $newDesc = str_replace($search, $replace, $row['desc']);
            $changed = $newDesc !== $row['desc'];
            $results[] = array(
                "tid" => intval($row['tid']),
                "name" => $row['name'],
                "old_desc" => $row['desc'],
                "new_desc" => $newDesc,
                "changed" => $changed
            );

            if ($changed && !$preview) {
                $DB->exec("UPDATE pre_tools SET `desc`=:newDesc,`uptime`=NOW() WHERE tid=:tid", array(':newDesc' => $newDesc, ':tid' => intval($row['tid'])));
                $affected++;
            }
        }

        return array(
            "status" => 1,
            "msg" => $preview ? "找到 " . count($rows) . " 条匹配记录" : "成功替换 {$affected} 条记录",
            "data" => $results,
            "affected" => $affected
        );
    }
}

$my = isset($_GET["my"]) ? trim((string)$_GET["my"]) : "index";
$search = isset($_POST['search']) ? (string)$_POST['search'] : '';
$replace = isset($_POST['replace']) ? (string)$_POST['replace'] : '';

if ($my === "execute") {
    adminpermission("shop", 2);
    if ($search === '') {
        showmsg("查找内容不能为空", 3);
        exit;
    }
    $result = q8_shopnoo_replace($search, $replace, false);
    if (isset($result['affected']) && intval($result['affected']) > 0) {
        $content = "批量替换商品介绍，影响 {$result['affected']} 条记录";
        $DB->exec("INSERT INTO `pre_toollogs` (`content`,`date`,`addtime`,`active`) VALUES (:content,CURDATE(),NOW(),1)", array(':content' => $content));
    }
    showmsg(q8_shopnoo_escape($result['msg']) . "<br/><br/><a href='./shopnoo.php'>&gt;&gt;继续操作</a><br/><a href='./shoplist.php'>&gt;&gt;返回商品列表</a>", $result['status'] ? 1 : 3);
    exit;
}

$result = null;
$changedItems = array();
if ($my === "preview") {
    if ($search === '') {
        showmsg("查找内容不能为空", 3);
        exit;
    }
    $result = q8_shopnoo_replace($search, $replace, true);
    if (!$result['status']) {
        showmsg(q8_shopnoo_escape($result['msg']), 3);
        exit;
    }
    foreach ($result['data'] as $item) {
        if ($item['changed']) {
            $changedItems[] = $item;
        }
    }
}

include "./head.php";
?>
<div class="col-xs-12 admin-ops-page admin-shopnoo-page">
    <section class="admin-ops-hero">
        <div>
            <p class="admin-ops-hero__eyebrow">批量工具</p>
            <h2>商品介绍批量替换</h2>
            <p>用于批量替换商品介绍里的固定文案、链接或 HTML 片段。先预览、再执行，避免误改线上商品详情。</p>
        </div>
        <div class="admin-ops-hero__actions">
            <a href="./shoplist.php" class="admin-ops-chip"><i class="fa fa-cubes"></i> 商品列表</a>
            <a href="./batch_tool.php" class="admin-ops-chip"><i class="fa fa-magic"></i> 批量工具</a>
            <a href="./toollogs.php" class="admin-ops-chip"><i class="fa fa-history"></i> 商品动态</a>
        </div>
    </section>

    <section class="admin-ops-stats">
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--primary"><i class="fa fa-search"></i></span>
            <div><span>匹配记录</span><strong><?php echo $result ? count($result['data']) : 0; ?></strong></div>
        </article>
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--warning"><i class="fa fa-pencil-square-o"></i></span>
            <div><span>将修改</span><strong><?php echo count($changedItems); ?></strong></div>
        </article>
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--success"><i class="fa fa-check"></i></span>
            <div><span>执行方式</span><strong>预览确认</strong></div>
        </article>
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--accent"><i class="fa fa-database"></i></span>
            <div><span>影响范围</span><strong>商品介绍</strong></div>
        </article>
    </section>

    <?php if ($my === "preview") { ?>
    <div class="block admin-ops-panel">
        <div class="block-title">
            <div>
                <h3>替换预览确认</h3>
                <p>共找到 <?php echo count($result['data']); ?> 条记录，其中 <?php echo count($changedItems); ?> 条将被修改。</p>
            </div>
            <div class="block-options">
                <a href="./shopnoo.php" class="btn btn-default"><i class="fa fa-arrow-left"></i> 重新编辑</a>
            </div>
        </div>
        <?php if ($changedItems) { ?>
        <form action="./shopnoo.php?my=execute" method="post" id="shopnooExecuteForm">
            <input type="hidden" name="search" value="<?php echo q8_shopnoo_escape($search); ?>">
            <input type="hidden" name="replace" value="<?php echo q8_shopnoo_escape($replace); ?>">
            <div class="table-responsive">
                <table class="table table-striped admin-ops-table">
                    <thead>
                    <tr>
                        <th>商品 ID</th>
                        <th>商品名称</th>
                        <th>替换前内容</th>
                        <th>替换后预览</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($changedItems as $item) { ?>
                    <tr>
                        <td><b><?php echo intval($item['tid']); ?></b></td>
                        <td><?php echo q8_shopnoo_escape($item['name']); ?></td>
                        <td><div class="admin-ops-desc-preview"><?php echo q8_shopnoo_escape($item['old_desc']); ?></div></td>
                        <td><div class="admin-ops-desc-preview"><?php echo q8_shopnoo_escape($item['new_desc']); ?></div></td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="block-content admin-ops-form-actions">
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> 确认执行替换</button>
                <a href="./shopnoo.php" class="btn btn-default"><i class="fa fa-chevron-left"></i> 返回重新编辑</a>
            </div>
        </form>
        <?php } else { ?>
        <div class="block-content">
            <div class="alert alert-warning"><i class="fa fa-info-circle"></i> 没有找到需要替换的内容，请检查查找条件是否正确。</div>
            <a href="./shopnoo.php" class="btn btn-default"><i class="fa fa-chevron-left"></i> 返回重新编辑</a>
        </div>
        <?php } ?>
    </div>
    <?php } else { ?>
    <div class="block admin-ops-panel">
        <div class="block-title">
            <div>
                <h3>替换内容</h3>
                <p>建议先复制一段精确文本进行预览，再执行正式替换。</p>
            </div>
        </div>
        <form action="./shopnoo.php?my=preview" method="post" class="form-horizontal form-bordered">
            <div class="form-group">
                <label class="col-sm-2 control-label" for="search"><span class="text-danger">*</span> 查找内容</label>
                <div class="col-sm-10">
                    <textarea class="form-control" id="search" name="search" rows="5" placeholder="请输入要查找的文本内容，支持 HTML 片段"><?php echo q8_shopnoo_escape($search); ?></textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="replace">替换为</label>
                <div class="col-sm-10">
                    <textarea class="form-control" id="replace" name="replace" rows="5" placeholder="请输入替换后的内容，留空则删除查找到的内容"><?php echo q8_shopnoo_escape($replace); ?></textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2">
                    <div class="alert alert-info">
                        <i class="fa fa-lightbulb-o"></i>
                        先预览匹配结果再执行。正式执行会直接修改数据库中的商品介绍，操作前请确认查找内容足够精确。
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 预览替换结果</button>
                </div>
            </div>
        </form>
    </div>
    <?php } ?>
</div>

<script>
(function () {
    var form = document.getElementById('shopnooExecuteForm');
    if (form) {
        form.addEventListener('submit', function (event) {
            if (!confirm('\u786e\u5b9a\u6267\u884c\u6279\u91cf\u66ff\u6362\u5417\uff1f\u6b64\u64cd\u4f5c\u4f1a\u76f4\u63a5\u4fee\u6539\u6570\u636e\u5e93\u4e2d\u7684\u5546\u54c1\u4ecb\u7ecd\u3002')) {
                event.preventDefault();
            }
        });
    }
})();
</script>
</body>
</html>
