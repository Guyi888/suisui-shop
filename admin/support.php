<?php
include "../includes/common.php";

$title = "联系与赞助";
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

include "./head.php";
?>
<div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
    <div class="block">
        <div class="block-title">
            <h3 class="panel-title"><i class="fa fa-paper-plane"></i> 联系与赞助</h3>
        </div>
        <div class="panel-body">
            <div class="alert alert-info">
                <p><strong>官方维护：</strong><?php echo OWNER_NAME; ?> <?php echo OWNER_HANDLE; ?></p>
                <p><strong>官网：</strong><a href="<?php echo OWNER_SITE_URL; ?>" target="_blank"><?php echo OWNER_SITE_URL; ?></a></p>
                <p><strong>客服/群组：</strong><?php echo OWNER_CONTACT; ?></p>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading"><i class="fa fa-usd"></i> 赞助地址</div>
                <div class="panel-body">
                    <p>如需支持维护版本，可通过 USDT TRC20 地址赞助：</p>
                    <input class="form-control" readonly value="<?php echo OWNER_USDT_TRC20; ?>">
                </div>
            </div>
            <p class="text-muted">本页仅展示程序维护方信息，不影响前台站长自行配置的客服联系方式。</p>
        </div>
    </div>
</div>
<?php include "./foot.php"; ?>
