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
            'description' => $this->description ?: '—',
            'hashtags' => $this->hashtags ?: '',
            'videoUrl' => $this->video_url ?: null,
            'thumbnailUrl' => $this->thumbnail_url ?: null,
            'views' => (int) ($this->views ?: 0),
            'likes' => (int) ($this->likes ?: 0),
            'comments' => (int) ($this->comments ?: 0),
            'shares' => (int) ($this->shares ?: 0),
            'createdAt' => $this->created_at->toIso8601String(),
            'status' => $this->status ?? 'draft',
            'tiktokPublishId' => $this->tiktok_publish_id,
            'tiktokStatus' => $this->tiktok_status,
        ];
    }
}
