<?php

class Helper {
    private $assetPath;
    
    public function __construct() {
        $this->assetPath = '../assets/';
    }
    
    public function asset($assetName) {
        $fullPath = $this->assetPath . $assetName;
        return $fullPath;
    }
}