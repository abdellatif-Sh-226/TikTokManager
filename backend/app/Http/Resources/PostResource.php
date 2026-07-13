<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'description' => $this->description,
            'hashtags' => $this->hashtags,
            'videoUrl' => $this->video_url ? Storage::url($this->video_url) : null,
            'thumbnailUrl' => $this->thumbnail_url ? Storage::url($this->thumbnail_url) : null,
            'views' => (int) $this->views,
            'likes' => (int) $this->likes,
            'comments' => (int) $this->comments,
            'shares' => (int) $this->shares,
            'createdAt' => $this->created_at->toIso8601String(),
            'status' => $this->status ?? 'draft',
        ];
    }
}
