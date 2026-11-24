<?php

declare(strict_types=1);

namespace App\Requests;

use Air\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|min:4',
        ];
    }
}
