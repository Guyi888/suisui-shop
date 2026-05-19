# 岁岁云商城

岁岁云商城是由岁岁 @qqfaka 维护的发卡/商城系统，面向自营发卡、商品销售、分站管理、订单管理、支付配置和后台运营场景。

## 当前版本

- 数据库版本：1013
- 缓存版本：2026051902
- 运行环境：PHP 7.4+，MySQL/MariaDB，Nginx 或 Apache
- 维护标识：岁岁 @qqfaka
- 官网：<https://t.me/qqfaka>
- 客服/群组：qqfaka
- 赞助地址：USDT TRC20 `TLzcw5ydRjHCM6KKAprjD8yzf2Dddddddd`

## 维护范围

- 同步程序功能修复与后台管理页面更新。
- 保留当前仓库的 `config.php`，避免覆盖线上数据库配置。
- 移除赞助、推广 QQ、站长推荐、推荐支付、防红推荐、对接推荐等无关推广展示。
- 移除后台远程公告弹窗和未引用的远程广告接口。
- 自动转账接口改为后台自定义 `Api_Url`，不再写死第三方远程地址。
- 前台站长客服保持可配置：填写数字时继续按 QQ 联系方式处理，填写 `qqfaka` 或 `@qqfaka` 时自动跳转 Telegram。
- 将业务页面图标统一为 FontAwesome。
- 增加后台“更新日志”和“联系与赞助”页面，并同步维护 `CHANGELOG.md`。

## 更新指南

宝塔覆盖更新和线上升级注意事项请查看 [`UPDATE.md`](UPDATE.md)。

## 更新规范

之后每次程序更新都需要同步维护两处记录：

1. 仓库根目录 `CHANGELOG.md`
2. 后台程序内 `admin/changelog.php`

涉及 CSS/JS 的更新需要同步调整缓存版本号，当前缓存版本定义在 `includes/common.php`。

## 版权

Copyright (c) 岁岁 @qqfaka
