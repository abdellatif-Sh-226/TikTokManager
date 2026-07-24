<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->load('posts');

        Log::info('[AuthController] user called', [
            'user_id' => $user->id,
            'posts_count' => $user->posts->count(),
            'has_tiktok' => !is_null($user->tiktok_open_id),
        ]);

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }
}
