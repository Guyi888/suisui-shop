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
                        <li>移除残留远程系统接口常量，自动转账接口改为后台自定义 Api_Url，不再写死第三方远程地址。</li>
                        <li>同步程序功能修复与后台管理页面更新，保留当前仓库的 config.php 配置文件。</li>
                        <li>同步数据库版本到 DB_VERSION 1013，并将静态资源缓存版本更新为 20260519。</li>
                        <li>移除后台赞助、推广 QQ、站长推荐轮播、推荐支付、防红推荐和对接推荐等展示信息。</li>
                        <li>移除远程公告弹窗与未引用的远程广告接口，避免后台加载无关外部推广内容。</li>
                        <li>业务页面图标从 glyphicon 统一替换为 FontAwesome。</li>
                        <li>版权与维护标识统一为：岁岁 @qqfaka。</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "./foot.php"; ?>
