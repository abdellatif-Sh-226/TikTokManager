<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'avatar', 'tiktok_username'])]
#[Hidden(['password', 'remember_token', 'tiktok_access_token', 'tiktok_refresh_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tiktok_token_expires_at' => 'datetime',
        ];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function dailyStats()
    {
        return $this->hasMany(DailyStat::class);
    }
}
