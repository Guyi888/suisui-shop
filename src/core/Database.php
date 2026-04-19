<?php
// 数据库操作类
namespace core;

use PDO;
use PDOException;

class Database {
    // PDO实例
    protected $pdo;
    
    // 数据库配置
    protected $config;
    
    // 构造函数
    public function __construct($config) {
        $this->config = $config;
        $this->connect();
    }
    
    // 连接数据库
    protected function connect() {
        try {
            // 构建DSN
            $dsn = "{$this->config['type']}:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']};charset={$this->config['charset']}";
            
            // 创建PDO实例
            $this->pdo = new PDO($dsn, $this->config['username'], $this->config['password'], $this->config['params']);
        } catch (PDOException $e) {
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    // 执行查询并返回所有结果
    public function query($sql, $params = []) {
        try {
            $stmt = $this->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->handleError($e);
            return false;
        }
    }
    
    // 执行查询并返回单行结果
    public function queryOne($sql, $params = []) {
        try {
            $stmt = $this->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->handleError($e);
            return false;
        }
    }
    
    // 执行增删改操作
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->prepare($sql);
            $result = $stmt->execute($params);
            
            // 如果是插入操作，返回最后插入的ID
            if (strpos(strtolower($sql), 'insert') === 0) {
                return $this->pdo->lastInsertId();
            }
            
            // 返回受影响的行数
            return $result ? $stmt->rowCount() : 0;
        } catch (PDOException $e) {
            $this->handleError($e);
            return false;
        }
    }
    
    // 预处理语句
    public function prepare($sql) {
        try {
            return $this->pdo->prepare($sql);
        } catch (PDOException $e) {
            $this->handleError($e);
            return false;
        }
    }
    
    // 开启事务
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    // 提交事务
    public function commit() {
        return $this->pdo->commit();
    }
    
    // 回滚事务
    public function rollBack() {
        return $this->pdo->rollBack();
    }
    
    // 错误处理
    protected function handleError($e) {
        if ($this->config['debug']) {
            echo '<pre>';
            echo 'Database Error: ' . $e->getMessage() . '\n';
            echo 'File: ' . $e->getFile() . ' (line ' . $e->getLine() . ')\n';
            echo '</pre>';
        }
        
        // 记录错误日志
        $this->logError($e);
    }
    
    // 记录错误日志
    protected function logError($e) {
        $error = date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . "\n";
        $error .= 'File: ' . $e->getFile() . ' (line ' . $e->getLine() . ')\n\n';
        
        // 确保日志目录存在
        $logDir = RUNTIME_PATH . 'logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        // 写入日志文件
        file_put_contents($logDir . 'database_' . date('Y-m-d') . '.log', $error, FILE_APPEND);
    }
    
    // 获取PDO实例
    public function getPdo() {
        return $this->pdo;
    }
    
    // 获取表前缀
    public function getPrefix() {
        return $this->config['prefix'];
    }
    
    // 为表名添加前缀
    public function table($tableName) {
        return $this->config['prefix'] . $tableName;
    }
}
