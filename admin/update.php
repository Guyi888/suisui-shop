<?php
include "../includes/common.php";

$title = "版本信息";
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

include "./head.php";
?>
<div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
    <div class="block">
        <div class="block-title">
            <h3 class="panel-title"><i class="fa fa-code-fork"></i> 版本信息</h3>
        </div>
        <div class="panel-body">
            <div class="alert alert-info">
                当前程序：<strong>岁岁云商城</strong><br>
                维护标识：<strong><?php echo OWNER_NAME; ?> <?php echo OWNER_HANDLE; ?></strong><br>
                官网：<strong><a href="<?php echo OWNER_SITE_URL; ?>" target="_blank"><?php echo OWNER_SITE_URL; ?></a></strong><br>
                缓存版本：<strong><?php echo defined('VERSION') ? VERSION : '20260519'; ?></strong>
            </div>
            <p>本页仅展示当前版本与维护信息，程序更新记录请查看后台“更新日志”。</p>
            <a class="btn btn-primary" href="./changelog.php"><i class="fa fa-list-alt"></i> 查看更新日志</a>
            <a class="btn btn-info" href="./support.php"><i class="fa fa-paper-plane"></i> 联系与赞助</a>
        </div>
    </div>
</div>
<?php include "./foot.php"; ?>
