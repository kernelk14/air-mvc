<?php

declare(strict_types=1);

namespace App\Controllers;

use Air\Auth\AuthManager;
use Air\Http\Controller;
use Air\Http\Request;
use Air\Http\Response;
use App\Requests\LoginRequest;

class LoginController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('login', ['title' => 'Login to MVC testing', 'error' => null]);
    }

    public function store(LoginRequest $request, AuthManager $auth): Response
    {
        $data = $request->validated();
        // Demo auth: accept any valid email/password; M-next should use DB users + password_hash.
        $auth->login($data['email']);

        return $this->redirect('/');
    }

    public function logout(Request $request, AuthManager $auth): Response
    {
        $auth->logout();
        return $this->redirect('/');
    }
}
