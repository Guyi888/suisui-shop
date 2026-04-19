<?php
// 路由类
namespace core;

class Router {
    // 路由规则
    protected $routes = [];
    
    // 当前请求URI
    protected $uri;
    
    // 构造函数
    public function __construct($routes = []) {
        $this->routes = $routes;
        $this->uri = $this->getCurrentUri();
    }
    
    // 获取当前请求URI
    protected function getCurrentUri() {
        $uri = $_SERVER['REQUEST_URI'];
        
        // 移除查询字符串
        if (strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        
        // 标准化URI
        $uri = trim($uri, '/');
        
        return $uri;
    }
    
    // 路由调度
    public function dispatch() {
        // 检查是否有精确匹配的路由
        if (isset($this->routes['/' . $this->uri])) {
            return $this->parseRoute($this->routes['/' . $this->uri]);
        }
        
        // 检查是否有根路径匹配
        if ($this->uri === '' && isset($this->routes['/'])) {
            return $this->parseRoute($this->routes['/']);
        }
        
        // 检查是否有通配符匹配
        foreach ($this->routes as $pattern => $route) {
            // 将路由模式转换为正则表达式
            $regex = $this->convertToRegex($pattern);
            
            if (preg_match($regex, '/' . $this->uri, $matches)) {
                // 移除第一个匹配项（完整匹配）
                array_shift($matches);
                
                // 解析路由
                list($controller, $action) = $this->parseRoute($route);
                
                // 将匹配的参数存储到请求中
                $this->storeRouteParams($matches);
                
                return [$controller, $action];
            }
        }
        
        return null;
    }
    
    // 将路由模式转换为正则表达式
    protected function convertToRegex($pattern) {
        // 替换路由参数，例如 :id 转换为 ([^/]+)
        $regex = preg_replace('/:([^/]+)/', '([^/]+)', $pattern);
        
        // 添加开始和结束标记
        $regex = '#^' . $regex . '$#';
        
        return $regex;
    }
    
    // 解析路由字符串
    protected function parseRoute($route) {
        $parts = explode('/', $route);
        
        $controller = isset($parts[0]) ? $parts[0] : 'Index';
        $action = isset($parts[1]) ? $parts[1] : 'index';
        
        return [$controller, $action];
    }
    
    // 存储路由参数
    protected function storeRouteParams($params) {
        $_GET['route_params'] = $params;
    }
    
    // 添加路由规则
    public function add($uri, $route) {
        $this->routes[$uri] = $route;
    }
    
    // 获取所有路由规则
    public function getRoutes() {
        return $this->routes;
    }
}
