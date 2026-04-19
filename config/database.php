<?php
// 数据库配置文件
return [
    // 数据库类型
    'type' => 'mysql',
    // 服务器地址
    'host' => 'localhost',
    // 端口
    'port' => 3306,
    // 数据库名
    'database' => '1',
    // 用户名
    'username' => '1',
    // 密码
    'password' => '1',
    // 表前缀
    'prefix' => 'shua_',
    // 字符集
    'charset' => 'utf8mb4',
    // 数据库连接参数
    'params' => [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false
    ],
    // 数据库调试模式
    'debug' => true,
    // 是否开启长连接
    'persistent' => false
];
