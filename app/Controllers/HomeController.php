<?php

declare(strict_types=1);

namespace App\Controllers;

use Air\Http\Controller;
use Air\Http\Request;
use Air\Http\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('home', ['title' => 'The First MVC']);
    }

    public function clickBtn(Request $request): Response
    {
        $message = (string) $request->input('enter', 'Probably null');

        return $this->view('clicks', ['message' => $message]);
    }

    public function show(Request $request, string $id): Response
    {
        return $this->view('show', ['id' => $id]);
    }
}
