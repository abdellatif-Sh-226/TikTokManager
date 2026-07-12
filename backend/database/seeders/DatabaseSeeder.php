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
            'description' => 'Check out this new dance challenge! #dance #viral',
            'views' => 45200,
            'likes' => 8200,
            'comments' => 1340,
            'shares' => 560,
            'created_at' => '2026-07-10T14:30:00Z',
            'status' => 'published',
        ]);

        Post::create([
            'user_id' => $user->id,
            'description' => 'Behind the scenes of my latest video',
            'views' => 23100,
            'likes' => 4100,
            'comments' => 890,
            'shares' => 230,
            'created_at' => '2026-07-08T09:15:00Z',
            'status' => 'published',
        ]);

        Post::create([
            'user_id' => $user->id,
            'description' => 'Coming soon... new collab',
            'views' => 0,
            'likes' => 0,
            'comments' => 0,
            'shares' => 0,
            'created_at' => '2026-07-12T20:00:00Z',
            'status' => 'draft',
        ]);

        $dailyData = [
            ['date' => '2026-07-06', 'views' => 3200, 'likes' => 540, 'comments' => 89, 'shares' => 34],
            ['date' => '2026-07-07', 'views' => 4800, 'likes' => 720, 'comments' => 120, 'shares' => 45],
            ['date' => '2026-07-08', 'views' => 6100, 'likes' => 910, 'comments' => 156, 'shares' => 67],
            ['date' => '2026-07-09', 'views' => 3900, 'likes' => 580, 'comments' => 98, 'shares' => 23],
            ['date' => '2026-07-10', 'views' => 7200, 'likes' => 1050, 'comments' => 178, 'shares' => 89],
            ['date' => '2026-07-11', 'views' => 5600, 'likes' => 830, 'comments' => 145, 'shares' => 56],
            ['date' => '2026-07-12', 'views' => 8400, 'likes' => 1200, 'comments' => 210, 'shares' => 78],
        ];

        foreach ($dailyData as $data) {
            DailyStat::create(array_merge($data, ['user_id' => $user->id]));
        }
    }
}
