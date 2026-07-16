<?php

namespace Database\Seeders;

use App\Models\DailyStat;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@tiktok.com',
            'password' => bcrypt('demo'),
            'tiktok_username' => '@demouser',
        ]);

        Post::create([
            'user_id' => $user->id,
            'description' => '—',
            'views' => 99,
            'likes' => 99,
            'comments' => 99,
            'shares' => 99,
            'created_at' => now(),
            'status' => 'draft',
        ]);

        Post::create([
            'user_id' => $user->id,
            'description' => '—',
            'views' => 99,
            'likes' => 99,
            'comments' => 99,
            'shares' => 99,
            'created_at' => now(),
            'status' => 'draft',
        ]);

        Post::create([
            'user_id' => $user->id,
            'description' => '—',
            'views' => 99,
            'likes' => 99,
            'comments' => 99,
            'shares' => 99,
            'created_at' => now(),
            'status' => 'draft',
        ]);

        $today = now();
        for ($i = 6; $i >= 0; $i--) {
            DailyStat::create([
                'user_id' => $user->id,
                'date' => $today->copy()->subDays($i)->format('Y-m-d'),
                'views' => 99,
                'likes' => 99,
                'comments' => 99,
                'shares' => 99,
            ]);
        }
    }
}
