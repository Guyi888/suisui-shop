# 更新记录

## 2026-05-19

- 同步程序功能修复与后台管理页面更新，保留当前仓库 `config.php`。
- 同步数据库版本到 `DB_VERSION = 1013`。
- 新增静态资源缓存版本 `VERSION = 20260519`。
- 新增后台程序内更新日志：`admin/changelog.php`。
- 重写后台版本信息页：`admin/update.php`。
- 移除赞助、推广 QQ、站长推荐轮播、推荐支付、防红推荐、推荐对接网站等展示信息。
- 移除远程公告弹窗和未引用的远程广告接口。
- 将业务页面 glyphicon 图标替换为 FontAwesome。
- 统一版权与维护标识为：岁岁 @qqfaka。

## 更新规则

之后每一次程序更新，都需要同步更新：

- `CHANGELOG.md`
- `admin/changelog.php`
- 涉及 CSS/JS 时同步更新 `includes/common.php` 中的缓存版本号
