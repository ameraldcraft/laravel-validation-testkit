<?php

namespace Amerald\LaravelValidationTestkit\Tests\Stubs;

use Illuminate\Foundation\Http\FormRequest;

class Request extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'addresses' => 'array',
            'addresses.*.country' => 'required_with:addresses|string',
        ];
    }
}
