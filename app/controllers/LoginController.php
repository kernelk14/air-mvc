<?php

class LoginController extends Controller {
    public function index(Request $request) {
        $this->loadView('login', ['title' => 'Login to MVC testing']);
    }
}
