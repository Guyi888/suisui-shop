-- 博客地址：zhonguo.ren
-- QQ群：qqfaka
-- 开发者：岁岁 @qqfaka
-- 文件说明：为pre_pay表添加address字段
-- 创建时间：2026-02-01

-- 为pre_pay表添加address字段
ALTER TABLE `pre_pay` ADD COLUMN `address` VARCHAR(500) DEFAULT '' COMMENT '收货地址' AFTER `input`;

-- 为pre_orders表添加address字段（如果存在）
ALTER TABLE `pre_orders` ADD COLUMN `address` VARCHAR(500) DEFAULT '' COMMENT '收货地址' AFTER `input`;
