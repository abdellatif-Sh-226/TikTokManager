<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyStat;
use App\Services\TikTokService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly TikTokService $tiktok
    ) {}

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $default = [
            'followers' => 0,
            'followersChange' => 0,
            'views' => 0,
            'viewsChange' => 0,
            'likes' => 0,
            'likesChange' => 0,
            'comments' => 0,
            'commentsChange' => 0,
            'shares' => 0,
            'sharesChange' => 0,
            'avatar' => null,
        ];

        if (!$user->tiktok_access_token || !$user->tiktok_open_id) {
            return response()->json(['data' => $default]);
        }

        try {
            $info = $this->tiktok->getUserInfo($user->tiktok_access_token, $user->tiktok_open_id);
            $tikTokUser = $info['data']['user'] ?? [];

            $videoViews = 0;
            $videoComments = 0;
            $videoShares = 0;
            $videoLikes = 0;
            try {
                $videosData = $this->tiktok->getVideos($user->tiktok_access_token, $user->tiktok_open_id);
                $tikTokVideos = $videosData['data']['videos'] ?? [];
                $videoViews = collect($tikTokVideos)->sum('view_count');
                $videoLikes = collect($tikTokVideos)->sum('like_count');
                $videoComments = collect($tikTokVideos)->sum('comment_count');
                $videoShares = collect($tikTokVideos)->sum('share_count');

                DailyStat::updateOrCreate(
                    ['user_id' => $user->id, 'date' => now()->toDateString()],
                    [
                        'views' => $videoViews,
                        'likes' => $videoLikes,
                        'comments' => $videoComments,
                        'shares' => $videoShares,
                    ]
                );
            } catch (\Exception $e) {}

            return response()->json(['data' => [
                'followers' => (int) ($tikTokUser['follower_count'] ?? 0),
                'followersChange' => 0,
                'views' => $videoViews,
                'viewsChange' => 0,
                'likes' => (int) ($tikTokUser['likes_count'] ?? 0),
                'likesChange' => 0,
                'comments' => $videoComments,
                'commentsChange' => 0,
                'shares' => $videoShares,
                'sharesChange' => 0,
                'avatar' => $tikTokUser['avatar_url'] ?? null,
                'displayName' => $tikTokUser['display_name'] ?? null,
                'username' => $tikTokUser['username'] ?? null,
            ]]);
        } catch (\Exception $e) {
            return response()->json(['data' => $default]);
        }
    }

    public function dailyStats(Request $request): JsonResponse
    {
        $dailyStats = $request->user()
            ->dailyStats()
            ->orderBy('date')
            ->get()
            ->map(fn ($stat) => [
                'date' => $stat->date,
                'views' => $stat->views ?? 0,
                'likes' => $stat->likes ?? 0,
                'comments' => $stat->comments ?? 0,
                'shares' => $stat->shares ?? 0,
            ]);

        return response()->json(['data' => $dailyStats]);
    }
}
