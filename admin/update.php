<?php
include "../includes/common.php";

$title = "版本信息";
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

include "./head.php";
$githubUrl = 'https://github.com/Guyi888/6v6faka';
?>
<style>
.q8-update-wrap{max-width:1120px;margin:0 auto 24px;float:none}
.q8-update-hero{border:1px solid #dbe7ff;background:linear-gradient(135deg,#f7fbff 0%,#eef7ff 52%,#fff 100%);border-radius:8px;padding:22px 24px;margin-bottom:16px}
.q8-update-hero h2{margin:0;color:#163b70;font-weight:900;display:flex;align-items:center;gap:10px}
.q8-update-hero p{margin:8px 0 0;color:#5f6f86;line-height:1.7}
.q8-update-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:16px}
.q8-update-card{border:1px solid #e6eefb;background:#fff;border-radius:8px;padding:16px;min-height:118px}
.q8-update-card b{display:block;color:#163b70;font-size:13px;margin-bottom:8px}
.q8-update-card strong{display:block;color:#1677ff;font-size:22px;line-height:1.2;word-break:break-all}
.q8-update-card span{display:block;color:#7a8aa0;font-size:12px;margin-top:8px;line-height:1.6}
.q8-update-actions{display:flex;flex-wrap:wrap;gap:10px}
.q8-update-actions .btn{display:inline-flex;align-items:center;gap:7px}
@media(max-width:768px){.q8-update-grid{grid-template-columns:1fr}.q8-update-hero{padding:18px}.q8-update-hero h2{font-size:20px}}
</style>
<div class="col-xs-12 q8-update-wrap">
    <div class="q8-update-hero">
        <h2><i class="fa fa-code-fork"></i> 版本更新检查</h2>
        <p>&#24403;&#21069;&#39029;&#38754;&#29992;&#20110;&#26597;&#30475;&#26412;&#22320;&#32531;&#23384;&#29256;&#26412;&#12289;&#39033;&#30446;&#28304;&#22320;&#22336;&#21644;&#32500;&#25252;&#20837;&#21475;&#12290;&#32447;&#19978;&#26356;&#26032;&#21069;&#35831;&#20808;&#22791;&#20221;&#20195;&#30721;&#21644;&#25968;&#25454;&#24211;&#12290;</p>
    </div>
    <div class="q8-update-grid">
        <div class="q8-update-card">
            <b>&#31243;&#24207;&#26631;&#35782;</b>
            <strong><?php echo OWNER_NAME; ?></strong>
            <span><?php echo OWNER_HANDLE; ?></span>
        </div>
        <div class="q8-update-card">
            <b>缓存版本</b>
            <strong><?php echo defined('VERSION') ? VERSION : '20260519'; ?></strong>
            <span>&#20462;&#25913; CSS/JS &#21518;&#38656;&#21516;&#27493;&#26356;&#26032;&#12290;</span>
        </div>
        <div class="q8-update-card">
            <b>GitHub</b>
            <strong style="font-size:15px;"><a href="<?php echo $githubUrl; ?>" target="_blank" rel="noreferrer"><?php echo $githubUrl; ?></a></strong>
            <span>当前项目地址已写入版本信息页。</span>
        </div>
    </div>
    <div class="block">
        <div class="block-title">
            <h3 class="panel-title"><i class="fa fa-list-alt"></i> 维护入口</h3>
        </div>
        <div class="panel-body">
            <div class="q8-update-actions">
                <a class="btn btn-primary" href="./changelog.php"><i class="fa fa-list-alt"></i> 查看更新日志</a>
                <a class="btn btn-info" href="./support.php"><i class="fa fa-paper-plane"></i> 联系与赞助</a>
                <a class="btn btn-default" href="<?php echo $githubUrl; ?>" target="_blank" rel="noreferrer"><i class="fa fa-github"></i> &#25171;&#24320; GitHub</a>
            </div>
        </div>
    </div>
</div>
<?php include "./foot.php"; ?>
