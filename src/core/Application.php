<?php
// 应用程序核心类
namespace core;

class Application {
    // 应用配置
    protected $config = [];
    
    // 路由实例
    protected $router;
    
    // 数据库实例
    protected $db;
    
    // 请求实例
    protected $request;
    
    // 响应实例
    protected $response;
    
    // 构造函数
    public function __construct() {
        // 初始化常量
        $this->initConstants();
        
        // 加载配置
        $this->loadConfig();
        
        // 初始化环境
        $this->initEnvironment();
        
        // 初始化路由
        $this->initRouter();
        
        // 初始化数据库
        $this->initDatabase();
        
        // 初始化请求和响应
        $this->initRequestResponse();
    }
    
    // 初始化常量
    protected function initConstants() {
        // 定义根目录
        define('ROOT_PATH', dirname(dirname(dirname(__FILE__))) . '/');
        // 定义应用目录
        define('APP_PATH', ROOT_PATH . 'src/');
        // 定义配置目录
        define('CONFIG_PATH', ROOT_PATH . 'config/');
        // 定义运行时目录
        define('RUNTIME_PATH', ROOT_PATH . 'runtime/');
        // 定义公共资源目录
        define('PUBLIC_PATH', ROOT_PATH . 'public/');
    }
    
    // 加载配置
    protected function loadConfig() {
        $configFiles = ['app', 'database', 'routes'];
        foreach ($configFiles as $file) {
            $config = include CONFIG_PATH . $file . '.php';
            $this->config = array_merge($this->config, $config);
        }
    }
    
    // 初始化环境
    protected function initEnvironment() {
        // 设置时区
        date_default_timezone_set($this->config['timezone']);
        
        // 设置错误报告级别
        if ($this->config['debug']) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }
        
        // 初始化会话
        if ($this->config['session']['auto_start']) {
            session_name($this->config['session']['name']);
            session_set_cookie_params($this->config['session']['expire']);
            session_start();
        }
    }
    
    // 初始化路由
    protected function initRouter() {
        $this->router = new Router($this->config['routes']);
    }
    
    // 初始化数据库
    protected function initDatabase() {
        $dbConfig = $this->config;
        $this->db = new Database($dbConfig);
    }
    
    // 初始化请求和响应
    protected function initRequestResponse() {
        $this->request = new Request();
        $this->response = new Response();
    }
    
    // 运行应用
    public function run() {
        try {
            // 路由调度
            $routeInfo = $this->router->dispatch();
            
            if ($routeInfo) {
                list($controller, $action) = $routeInfo;
                
                // 实例化控制器
                $controllerClass = 'controllers\\' . $controller . 'Controller';
                
                if (class_exists($controllerClass)) {
                    $controllerInstance = new $controllerClass($this);
                    
                    // 调用方法
                    if (method_exists($controllerInstance, $action)) {
                        $result = $controllerInstance->$action();
                        
                        // 输出响应
                        $this->response->output($result);
                    } else {
                        throw new \Exception('Action not found: ' . $action);
                    }
                } else {
                    throw new \Exception('Controller not found: ' . $controller);
                }
            } else {
                throw new \Exception('Route not found');
            }
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    // 异常处理
    protected function handleException($e) {
        if ($this->config['debug']) {
            echo '<pre>';
            echo 'Error: ' . $e->getMessage() . '\n';
            echo 'File: ' . $e->getFile() . ' (line ' . $e->getLine() . ')\n';
            echo 'Stack trace:\n' . $e->getTraceAsString();
            echo '</pre>';
        } else {
            $this->response->setStatus(500);
            $this->response->output('Internal Server Error');
        }
    }
    
    // 获取配置
    public function getConfig($key = null) {
        if ($key === null) {
            return $this->config;
        }
        return isset($this->config[$key]) ? $this->config[$key] : null;
    }
    
    // 获取数据库实例
    public function getDb() {
        return $this->db;
    }
    
    // 获取请求实例
    public function getRequest() {
        return $this->request;
    }
    
    // 获取响应实例
    public function getResponse() {
        return $this->response;
    }
}
