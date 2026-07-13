<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'user_id', 'description', 'hashtags', 'video_url', 'thumbnail_url',
        'views', 'likes', 'comments', 'shares', 'status',
    ];

    protected function casts(): array
    {
        return [
            'views' => 'integer',
            'likes' => 'integer',
            'comments' => 'integer',
            'shares' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
