<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:1000'],
            'status' => ['sometimes', 'in:published,draft,scheduled'],
        ];
    }
}
