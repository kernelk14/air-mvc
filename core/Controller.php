<?php

abstract class Controller {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function loadView($viewName, $data = []) {
        extract($data);
        $viewFile = APP_DIR . 'views/' . $viewName . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            http_response_code(404);
            echo "View `$viewFile` not found.";
        }
    }
        
}
