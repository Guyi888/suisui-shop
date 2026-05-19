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
                        <li>修复 XHY-01 首页头部、查单说明、客服帮助、新手帮助和今日上架推荐中的乱码与断裂标签，避免前台出现异常字符。</li>
                        <li>补齐后台侧边栏对应的缺失页面和资源：自动补单、自动同步、渠道结算、收益统计、价格管控、站点日志、自定义 CSS、Cloudflare IP 名单、代理工具等入口不再落到 404。</li>
                        <li>重构自动同步设置页、同步执行 API 与自动补单配置页，并去除旧固定域名、旧作者群信息和旧远程品牌注释。</li>
                        <li>新增 hooks/custom CSS 公共能力，后台自定义 CSS 页面可正常保存并在后台渲染。</li>
                        <li>新增通道结算默认配置文件，避免结算页缺少 config/settlement_calc.php 时报错；默认只统计当前站点，不携带任何外部数据库账号。</li>
                        <li>未引入旧远程广告/在线更新入口，避免后台重新加载无关推广或下载执行远程更新包。</li>
                        <li>缓存版本更新为 2026051902，后台资源版本同步更新为 20260519suisuiops01。</li>
                        <li>补齐后台整体外壳与侧边栏 UI，新增 admin-shell 专用 CSS/JS，并同步更新后台资源缓存版本。</li>
                        <li>移除后台外壳中原远程公告脚本，头像展示改用站点联系方式 helper，避免重新引入外部赞助/公告依赖。</li>
                        <li>重构 XHY-01 模板视觉与交互骨架，补齐背景图、本地 vendor 依赖、查单卡片、赚钱/抽奖/更多服务等模块样式。</li>
                        <li>更新后台首页管理中心 UI，新增统计卡片、交易图表、访问统计、运营待办和访问详情弹窗资源。</li>
                        <li>移除 XHY-01 中外部客服注入，在线客服兜底跳转岁岁 @qqfaka 官方 Telegram。</li>
                        <li>补充二开版本品牌化清理：后台标题、搭建教程、插件作者元数据和默认推广文案统一为岁岁云商城。</li>
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
