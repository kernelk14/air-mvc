<?php

class Request {
    public $get;
    public $post;
    public $server;
    public $files;
    public $json;
    
    public function __construct() {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;
        $this->json = json_decode(file_get_contents('php://input'), true) ?? [];
    }
    
    public function input($key, $default = null) {
        return $this->post[$key] ?? $this->get[$key] ?? $this->json[$key] ?? $default;
    }
    
    public function all() {
        return array_merge($this->get, $this->post, $this->json);
    }
    
    public function method() {
        return $this->server['REQUEST_METHOD'];
    }
    
    public function isPost() {
        return $this->method() === 'POST';
    }
}