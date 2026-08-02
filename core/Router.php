<?php

class Router {
    private $routes = [];
    private $uri;
    
    public function get(string $path, $handler) {
        $this->routes['GET'][$path] = $handler;
    }
    
    public function post(string $path, $handler) {
        $this->routes['POST'][$path] = $handler;
    }
    
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->uri = rtrim($this->uri, '/') ?: '/';
        
        if (isset($this->routes[$method][$this->uri])) {
            $this->callHandler($this->routes[$method][$this->uri]);
        } else {
            http_response_code(404);
            echo "404 Not found.";
        }
            
    }
    private function callHandler($handler) {
        [$controller, $method] = explode('::', $handler);
        $ctrl = new $controller();
        $ctrl->$method();
    } 
}