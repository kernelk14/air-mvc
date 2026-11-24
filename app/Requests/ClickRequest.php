<?php

declare(strict_types=1);

namespace App\Requests;

use Air\Http\FormRequest;

final class ClickRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'enter' => 'required|min:1|max:255',
        ];
    }
}
