<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'description' => $this->description,
            'videoUrl' => $this->video_url,
            'thumbnailUrl' => $this->thumbnail_url,
            'views' => $this->views,
            'likes' => $this->likes,
            'comments' => $this->comments,
            'shares' => $this->shares,
            'createdAt' => $this->created_at->toIso8601String(),
            'status' => $this->status,
        ];
    }
}
