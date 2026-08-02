<?php

class HomeController extends Controller {
    public function index(Request $request) {
        $this->loadView('home');
    }
    
    public function clickBtn(Request $request) {
        $message = $request->input('enter', "Probably null");
        $this->loadView('clicks', ['message' => $message]);
    }
    
    public function show(Request $request, $id) {
        $this->loadView('show', ['id' => $id]);
    }
}