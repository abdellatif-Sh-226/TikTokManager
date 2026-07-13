<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:2000'],
            'hashtags' => ['nullable', 'string', 'max:500'],
            'status' => ['sometimes', 'in:published,draft,scheduled'],
            'video' => ['nullable', 'file', 'mimes:mp4,mov,avi', 'max:51200'],
            'thumbnail' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }
}
