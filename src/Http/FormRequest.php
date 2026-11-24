<?php

declare(strict_types=1);

namespace Air\Http;

use Air\Validation\Validator;

abstract class FormRequest extends Request
{
    /** @return array<string,string|array<int,string>> */
    abstract public function rules(): array;

    /** @return array<string,mixed> */
    public function validated(): array
    {
        $validator = Validator::make($this->all(), $this->rules());
        if ($validator->fails()) {
            throw new \Air\Exceptions\HttpException(422, json_encode(['errors' => $validator->errors()], JSON_UNESCAPED_SLASHES) ?: 'Validation failed');
        }
        return $validator->validated();
    }
}
