<?php

include("../includes/common.php");
$title = '祥云数据迁移';
include './head.php';
if ($islogin == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
?>
<div class="col-sm-12 col-md-10 center-block" style="float: none;">
    <?php
    $my = isset($_GET['my']) ? $_GET['my'] : null;
    if($my=='add'){

    ?>
        <div class="block">
            <div class="block-title">
                <h3 class="panel-title">祥云数据迁移</h3>
            </div>
            <div class="">
                <form action="./datamove.php?my=add_submit" method="post" role="form">
                    <div class="form-group">
                        <label>选择可迁移的数据</label><br />
                        <label class="checkbox-inline"><input type="checkbox" name="permission[]" value="order"> 订单列表</label>
                        <label class="checkbox-inline"><input type="checkbox" name="permission[]" value="tools"> 商品列表</label>
                        <label class="checkbox-inline"><input type="checkbox" name="permission[]" value="shequ"> 社区对接列表</label>
                        <label class="checkbox-inline"><input type="checkbox" name="permission[]" value="price"> 加价模板</label>
                        <label class="checkbox-inline"><input type="checkbox" name="permission[]" value="faka"> 发卡库存</label>
                        <label class="checkbox-inline"><input type="checkbox" name="permission[]" value="site"> 分站/用户</label>
                        <label class="checkbox-inline"><input type="checkbox" name="permission[]" value="workorder"> 工单管理</label>
                        <label class="checkbox-inline"><input type="checkbox" name="permission[]" value="message"> 站内通知</label>
                        <label class="checkbox-inline"><input type="checkbox" name="permission[]" value="article"> 文章管理</label>
                        <label class="checkbox-inline"><input type="checkbox" name="permission[]" value="record"> 收支明细</label>
                    </div>
                    <div class="form-group">
                        <input type="submit" name="submit" value="添加" class="btn btn-primary btn-block" />
                    </div>
                </form>
            </div>
        </div>
    <?php
}elseif ($my == 'add_submit') {
        $permission = implode(',', $_POST['permission']);
        if (strpos($permission, 'order')) {
            $DB->exec("DELETE FROM {$dbconfig['dbqz']}_order");
            $DB->exec("ALTER TABLE `shua_order` RENAME TO `{$dbconfig['dbqz']}_order`");
        }
        if (strpos($permission, 'tools')) {
            $DB->exec("DELETE FROM {$dbconfig['dbqz']}_tools");
            $DB->exec("ALTER TABLE `shua_tools` RENAME TO `{$dbconfig['dbqz']}_tools`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_tools` ADD `valiserv` VARCHAR(15) NULL AFTER `uptime`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_tools` ADD `sales` INT(11) NULL AFTER `validate`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_tools` ADD `stock` INT(11) NULL AFTER `sales`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_tools` ADD `addtime` datetime NULL AFTER `stock`");
        }
        if (strpos($permission, 'shequ')) {
            $DB->exec("DELETE FROM {$dbconfig['dbqz']}_shequ");
            $DB->exec("ALTER TABLE `shua_shequ` RENAME TO `{$dbconfig['dbqz']}_shequ`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_shequ` ADD `result` tinyint(1) NULL AFTER `type`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_tools` ADD `remark` VARCHAR(255) NULL AFTER `status`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_tools` ADD `protocol` tinyint(1) NULL AFTER `remark`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_tools` ADD `monitor` tinyint(1) NULL AFTER `protocol`");
        }
        if (strpos($permission, 'price')) {
            $DB->exec("DELETE FROM {$dbconfig['dbqz']}_price");
            $DB->exec("ALTER TABLE `shua_price` RENAME TO `{$dbconfig['dbqz']}_price`");
        }
        if (strpos($permission, 'faka')) {
            $DB->exec("DELETE FROM {$dbconfig['dbqz']}_faka");
            $DB->exec("ALTER TABLE `shua_faka` RENAME TO `{$dbconfig['dbqz']}_faka`");
        }
        if (strpos($permission, 'site')) {
            $DB->exec("DELETE FROM {$dbconfig['dbqz']}_site");
            $DB->exec("ALTER TABLE `shua_site` RENAME TO `{$dbconfig['dbqz']}_site`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_site` ADD `email` tinyint(1) NULL AFTER `pwd`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_site` ADD `phone` VARCHAR(255) NULL AFTER `email`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_site` ADD `qq_openid` varchar(64) NULL AFTER `phone`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_site` ADD `wx_openid` varchar(64) NULL AFTER `qq_openid`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_site` ADD `nickname` varchar(64) NULL AFTER `wx_openid`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_site` ADD `faceimg` varchar(150) NULL AFTER `nickname`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_site` ADD `rmbtc` decimal(10,2) NULL AFTER `rmb`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_site` ADD `kfqq` varchar(12) NULL AFTER `description`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_site` ADD `kfwx` varchar(20) NULL AFTER `kfqq`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_site` ADD `appurl` varchar(150) NULL AFTER `iprice`");
        }
        if (strpos($permission, 'workorder')) {
            $DB->exec("DELETE FROM {$dbconfig['dbqz']}_workorder");
            $DB->exec("ALTER TABLE `shua_workorder` RENAME TO `{$dbconfig['dbqz']}_workorder`");
        }
        if (strpos($permission, 'message')) {
            $DB->exec("DELETE FROM {$dbconfig['dbqz']}_message");
            $DB->exec("ALTER TABLE `shua_message` RENAME TO `{$dbconfig['dbqz']}_message`");
        }
        if (strpos($permission, 'article')) {
            $DB->exec("DELETE FROM {$dbconfig['dbqz']}_article");
            $DB->exec("ALTER TABLE `shua_article_list` RENAME TO `{$dbconfig['dbqz']}_article`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_article` ADD `zid` INT(11) UNSIGNED NULL AFTER `id`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_article` RENAME COLUMN status TO active");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_article` RENAME COLUMN seoKeywords TO keywords");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_article` RENAME COLUMN seoDescription TO description");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_article` ADD `color` varchar(20) NULL AFTER `description`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_article` RENAME COLUMN createTime TO addtime");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_article` ADD `count` INT(11) NULL AFTER `addtime`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_article` ADD `top` tinyint(1) NULL AFTER `count`");
        }
        if (strpos($permission, 'record')) {
            $DB->exec("DELETE FROM {$dbconfig['dbqz']}_points");
            $DB->exec("ALTER TABLE `shua_points` RENAME TO `{$dbconfig['dbqz']}_points`");
            $DB->exec("ALTER TABLE `{$dbconfig['dbqz']}_points` ADD `status` tinyint(1) NULL AFTER `orderid`");
        }
        showmsg('祥云数据迁移成功', 1);
    } else {
        // 默认页面内容
    ?>
        <div class="block">
            <div class="block-title">
                <h3 class="panel-title">祥云数据迁移</h3>
            </div>
            <div class="">
                <p>此工具用于将祥云系统的数据迁移到当前系统。</p>
                <p>迁移过程会删除当前系统对应的数据，然后将祥云系统的数据重命名并添加必要的字段。</p>
                <a href="./datamove.php?my=add" class="btn btn-primary">开始迁移</a>
            </div>
        </div>
    <?php
    }
    ?>
