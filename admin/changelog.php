<?php
include "../includes/common.php";

$title = "更新日志";
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

include "./head.php";
?>
<div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
    <div class="block">
        <div class="block-title">
            <h3 class="panel-title"><i class="fa fa-list-alt"></i> 更新日志</h3>
        </div>
        <div class="panel-body">
            <div class="timeline-list">
                <div class="alert alert-success">
                    <h4><i class="fa fa-check-circle"></i> 2026-05-19 岁岁云商城维护更新</h4>
                    <ul>
                        <li>新增后台“联系与赞助”页面，展示官网、客服/群组和 USDT TRC20 赞助地址。</li>
                        <li>官方维护信息统一为：岁岁 @qqfaka，官网 https://t.me/qqfaka。</li>
                        <li>安装默认客服/群组调整为 qqfaka，并更新默认公告、站点描述、邮件页脚和邀请文案。</li>
                        <li>前台站长客服继续保留为可配置项：数字联系方式走 QQ，非数字联系方式自动跳转 Telegram。</li>
                        <li>前台展示文案从“QQ客服/客服QQ/售后卖家QQ”等调整为“在线客服/卖家售后”等中性文字。</li>
                        <li>移除后台赞助、推广 QQ、站长推荐轮播、推荐支付、防红推荐和对接推荐等展示信息。</li>
                        <li>移除远程公告弹窗与未引用的远程广告接口，避免后台加载无关外部推广内容。</li>
                        <li>自动转账接口改为后台自定义 Api_Url，不再写死第三方远程地址。</li>
                        <li>业务页面图标从 glyphicon 统一替换为 FontAwesome。</li>
                        <li>新增 UPDATE.md，说明宝塔覆盖更新、数据库备份和必须保留的本地文件。</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "./foot.php"; ?>
