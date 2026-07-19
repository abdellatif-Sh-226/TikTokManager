<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyStat;
use App\Services\TikTokService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct(
        private readonly TikTokService $tiktok
    ) {
        Log::info('[DashboardController] Initialized');
    }

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        Log::info('[DashboardController] stats called', [
            'user_id' => $user->id,
            'has_tiktok_token' => !empty($user->tiktok_access_token),
            'has_tiktok_open_id' => !empty($user->tiktok_open_id),
            'token_expires_at' => $user->tiktok_token_expires_at?->toIso8601String(),
        ]);

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
            'displayName' => null,
            'username' => null,
        ];

        if (!$user->tiktok_access_token || !$user->tiktok_open_id) {
            Log::info('[DashboardController] No TikTok connection, returning default stats', ['user_id' => $user->id]);
            return response()->json(['data' => $default]);
        }

        // Check if token is expired and try to refresh
        if ($user->tiktok_token_expires_at && $user->tiktok_token_expires_at->isPast()) {
            Log::warning('[DashboardController] TikTok token expired, attempting refresh', [
                'user_id' => $user->id,
                'expired_at' => $user->tiktok_token_expires_at->toIso8601String(),
            ]);

            if ($user->tiktok_refresh_token) {
                try {
                    $refreshResult = $this->tiktok->refreshToken($user->tiktok_refresh_token);

                    if (isset($refreshResult['access_token'])) {
                        $user->update([
                            'tiktok_access_token' => $refreshResult['access_token'],
                            'tiktok_refresh_token' => $refreshResult['refresh_token'] ?? $user->tiktok_refresh_token,
                            'tiktok_token_expires_at' => now()->addSeconds($refreshResult['expires_in'] ?? 3600),
                        ]);

                        Log::info('[DashboardController] Token refreshed successfully', ['user_id' => $user->id]);

                        // Reload user with new token
                        $user->refresh();
                    }
                } catch (\Exception $e) {
                    Log::error('[DashboardController] Token refresh failed', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        try {
            Log::info('[DashboardController] Fetching TikTok user info', [
                'user_id' => $user->id,
                'open_id' => $user->tiktok_open_id,
            ]);

            $info = $this->tiktok->getUserInfo($user->tiktok_access_token, $user->tiktok_open_id);
            $tikTokUser = $info['data']['user'] ?? [];

            Log::info('[DashboardController] TikTok user info received', [
                'user_id' => $user->id,
                'display_name' => $tikTokUser['display_name'] ?? null,
                'username' => $tikTokUser['username'] ?? null,
                'follower_count' => $tikTokUser['follower_count'] ?? null,
                'likes_count' => $tikTokUser['likes_count'] ?? null,
                'video_count' => $tikTokUser['video_count'] ?? null,
            ]);

            $videoViews = 0;
            $videoComments = 0;
            $videoShares = 0;
            $videoLikes = 0;

            try {
                Log::info('[DashboardController] Fetching TikTok videos for aggregated stats', ['user_id' => $user->id]);

                $videosData = $this->tiktok->getVideos($user->tiktok_access_token, $user->tiktok_open_id);
                $tikTokVideos = $videosData['data']['videos'] ?? [];

                Log::info('[DashboardController] TikTok videos received', [
                    'user_id' => $user->id,
                    'video_count' => count($tikTokVideos),
                ]);

                $videoViews = collect($tikTokVideos)->sum('view_count');
                $videoLikes = collect($tikTokVideos)->sum('like_count');
                $videoComments = collect($tikTokVideos)->sum('comment_count');
                $videoShares = collect($tikTokVideos)->sum('share_count');

                Log::info('[DashboardController] Aggregated video stats', [
                    'user_id' => $user->id,
                    'total_views' => $videoViews,
                    'total_likes' => $videoLikes,
                    'total_comments' => $videoComments,
                    'total_shares' => $videoShares,
                ]);

                DailyStat::updateOrCreate(
                    ['user_id' => $user->id, 'date' => now()->toDateString()],
                    [
                        'views' => $videoViews,
                        'likes' => $videoLikes,
                        'comments' => $videoComments,
                        'shares' => $videoShares,
                    ]
                );

                Log::info('[DashboardController] Daily stats updated', ['user_id' => $user->id, 'date' => now()->toDateString()]);
            } catch (\Exception $e) {
                Log::error('[DashboardController] Failed to fetch videos for daily stats', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $result = [
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
            ];

            Log::info('[DashboardController] stats returning TikTok data', [
                'user_id' => $user->id,
                'result' => $result,
            ]);

            return response()->json(['data' => $result]);
        } catch (\Exception $e) {
            Log::error('[DashboardController] Failed to fetch TikTok stats', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['data' => $default]);
        }
    }

    public function dailyStats(Request $request): JsonResponse
    {
        $user = $request->user();

        Log::info('[DashboardController] dailyStats called', ['user_id' => $user->id]);

        $dailyStats = $user->dailyStats()
            ->orderBy('date')
            ->get()
            ->map(fn ($stat) => [
                'date' => $stat->date,
                'views' => $stat->views ?? 0,
                'likes' => $stat->likes ?? 0,
                'comments' => $stat->comments ?? 0,
                'shares' => $stat->shares ?? 0,
            ]);

        Log::info('[DashboardController] dailyStats returned', [
            'user_id' => $user->id,
            'count' => $dailyStats->count(),
            'stats' => $dailyStats->toArray(),
        ]);

        return response()->json(['data' => $dailyStats]);
    }
}
