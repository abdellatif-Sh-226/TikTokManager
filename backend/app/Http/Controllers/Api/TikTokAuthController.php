<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\TikTokService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TikTokAuthController extends Controller
{
    public function __construct(
        private readonly TikTokService $tiktok
    ) {}

    public function redirect(): JsonResponse
    {
        $url = $this->tiktok->getAuthUrl();

        return response()->json(['url' => $url]);
    }

    public function callback(Request $request): JsonResponse|RedirectResponse
    {
        $code = $request->query('code');

        if (!$code) {
            return response()->json(['message' => 'Authorization code is required'], 400);
        }

        try {
            $tokenData = $this->tiktok->getAccessToken($code);

            $accessToken = $tokenData['access_token'];
            $openId = $tokenData['open_id'];
            $refreshToken = $tokenData['refresh_token'];
            $expiresIn = $tokenData['expires_in'];

            $userInfo = $this->tiktok->getUserInfo($accessToken, $openId);
            $tikTokUser = $userInfo['data']['user'];

            $user = User::updateOrCreate(
                ['tiktok_open_id' => $openId],
                [
                    'name' => $tikTokUser['display_name'] ?? 'TikTok User',
                    'tiktok_username' => $tikTokUser['username'] ?? null,
                    'avatar' => $tikTokUser['avatar_url'] ?? null,
                    'tiktok_access_token' => $accessToken,
                    'tiktok_refresh_token' => $refreshToken,
                    'tiktok_token_expires_at' => now()->addSeconds($expiresIn),
                    'email' => $openId . '@tiktok-user',
                    'password' => bcrypt(\Str::random(32)),
                ]
            );

            $token = $user->createToken('api-token')->plainTextToken;

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5174');

            return redirect()->away($frontendUrl . '/?token=' . $token);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'TikTok authentication failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
