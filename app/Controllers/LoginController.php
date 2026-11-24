<?php

declare(strict_types=1);

namespace App\Controllers;

use Air\Http\Controller;
use Air\Http\Request;
use Air\Http\Response;

class LoginController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('login', ['title' => 'Login to MVC testing']);
    }
}
