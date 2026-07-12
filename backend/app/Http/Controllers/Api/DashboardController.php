<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $posts = $user->posts;

        $stats = [
            'followers' => 12500,
            'followersChange' => 5.2,
            'views' => $posts->sum('views'),
            'viewsChange' => 12.8,
            'likes' => $posts->sum('likes'),
            'likesChange' => -2.3,
            'comments' => $posts->sum('comments'),
            'commentsChange' => 8.7,
        ];

        return response()->json(['data' => $stats]);
    }

    public function dailyStats(Request $request): JsonResponse
    {
        $dailyStats = $request->user()
            ->dailyStats()
            ->orderBy('date')
            ->get()
            ->map(fn ($stat) => [
                'date' => $stat->date,
                'views' => $stat->views,
                'likes' => $stat->likes,
                'comments' => $stat->comments,
                'shares' => $stat->shares,
            ]);

        return response()->json(['data' => $dailyStats]);
    }
}
