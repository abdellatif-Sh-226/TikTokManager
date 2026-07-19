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
            'video' => ['required', 'file', 'mimes:mp4,mov,avi', 'max:51200'],
            'thumbnail' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'privacy_level' => ['nullable', 'string', 'in:PUBLIC_TO_EVERYONE,MUTUAL_FOLLOW_FRIENDS,SELF_ONLY'],
            'disable_comment' => ['nullable', 'string'],
            'publish_to_tiktok' => ['nullable', 'string', 'in:0,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'Video description is required',
            'video.required' => 'Please select a video to upload',
            'video.mimes' => 'Video must be MP4, MOV, or AVI format',
            'video.max' => 'Video size must be less than 50MB',
            'thumbnail.mimes' => 'Thumbnail must be JPG or PNG format',
            'thumbnail.max' => 'Thumbnail size must be less than 5MB',
            'privacy_level.in' => 'Privacy level must be Public or Private',
        ];
    }
}
