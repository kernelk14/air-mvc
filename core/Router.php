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

    public function put(string $path, $handler) {
        $this->routes['PUT'][$path] = $handler;
    }
    
    public function patch(string $path, $handler) {
        $this->routes['PATCH'][$path] = $handler;
    }

    public function delete(string $path, $handler) {
        $this->routes['DELETE'][$path] = $handler;
    }
    
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->uri = rtrim($this->uri, '/') ?: '/';
        
        foreach ($this->routes[$method] ?? [] as $pattern => $handler) {
            $params = $this->matchRoute($pattern, $this->uri);
            if ($params !== false) {
                $this->callHandler($handler, $params);
                return;
            }
        }
        
        http_response_code(404);
        echo "404 Not found.";
    }
    
    private function matchRoute($pattern, $uri) {
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '([^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches);
            return $matches;
        }
        return false;
    }
    
    private function callHandler($handler, $params) {
        [$controller, $method] = explode('::', $handler);
        $ctrl = new $controller();
        $request = new Request();
        
        // Pass Request as first arg, then route params
        $ctrl->$method($request, ...$params);
    }
}