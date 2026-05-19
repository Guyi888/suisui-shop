# 更新日志

## 2026-05-19

- 统一前台、后台 favicon 使用本地 Q8 图标资源，补齐 `favicon.ico` 与 `assets/img/favicon/favicon.ico`，避免继续热链远程域名。
- 补齐本地 Q8 logo 资源，后台登录页右侧改为图片展示并移除旧文字说明；站点 logo 与背景设置默认指向本地 logo。
- 清理后台登录页遗留的远程背景图片外链和旧模板注释，登录页视觉资源统一走本地 logo 与 CSS 渐变。
- 后台公共头部接入新的 admin shell 资源版本，统一品牌、favicon helper 与页面基础视觉结构。
- 清理自动转账接口的旧硬编码远程地址，改为后台配置项 `transfer_api_url`，并增加 URL 基础校验。
- 修复 `admin/ajax.php` 中上传文章图片、favicon 上传、卡密详情和首页统计附近的乱码注释、断裂输出与潜在转义问题。
- 修复 `includes/function.php` 中分类下拉输出的乱码与 HTML 转义问题，分类名称统一通过 `htmlspecialchars` 输出。
- 修复用户中心头部禁用/到期提示、分站加价模板说明、商品列表注释中的乱码，减少前台可见异常字符。
- 重构 `shoprank.php`、`fakalist.php`、`shopnoo.php` 和秒杀商品管理界面，统一为新后台 shell 风格，补充统计卡、工具栏、空状态、移动端布局和明确事件绑定。
- 秒杀商品管理同步重构 `seckill-table.php`、`assets/js/seckill.js`、`assets/js/seckilledit.js`，移除旧内联点击事件，中文 JS 提示改为 Unicode 转义。
- 重构 `kmlist.php` 加款卡密界面，补充生成结果页、金额统计、搜索弹窗、清空确认页和删除确认事件。
- 重构 `support.php` 联系与赞助界面，改为统一维护信息页并清理历史乱码。
- 清理更新记录文件本身的历史乱码，后台 `admin/changelog.php` 改为实体输出，避免页面再次出现编码断裂。
- 当前缓存版本已同步到 `2026051907`，后台资源版本同步到 `20260519suisuiops06`。

## 更新要求

- 后续每次更新都必须同步维护本文档和后台程序内 `admin/changelog.php`。
- 不写旧上游基线合并类表述，项目定位统一为岁岁云商城独立运营版本。
- 更新前后必须检查乱码、硬编码外链、资源 404、PHP 语法和关键页面 HTTP 状态。
