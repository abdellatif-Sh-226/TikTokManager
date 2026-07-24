<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\TikTokService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TikTokAuthController extends Controller
{
    public function __construct(
        private readonly TikTokService $tiktok
    ) {
        Log::info('[TikTokAuthController] Initialized');
    }

    public function redirect(): JsonResponse
    {
        Log::info('[TikTokAuthController] redirect called');

        $url = $this->tiktok->getAuthUrl();

        Log::info('[TikTokAuthController] Returning TikTok auth URL', [
            'url' => $url,
        ]);

        return response()->json(['url' => $url]);
    }

    public function callback(Request $request): JsonResponse|RedirectResponse
    {
        $code = $request->query('code');
        $state = $request->query('state');
        $error = $request->query('error');
        $errorDescription = $request->query('error_description');

        Log::info('[TikTokAuthController] callback called', [
            'has_code' => !empty($code),
            'code_prefix' => $code ? substr($code, 0, 10) . '...' : null,
            'state' => $state,
            'error' => $error,
            'error_description' => $errorDescription,
        ]);

        if ($error) {
            Log::error('[TikTokAuthController] TikTok returned error', [
                'error' => $error,
                'error_description' => $errorDescription,
            ]);

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return redirect()->away($frontendUrl . '/login?error=' . urlencode($errorDescription ?: $error));
        }

        if (!$code) {
            Log::error('[TikTokAuthController] No authorization code received');

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return redirect()->away($frontendUrl . '/login?error=no_code');
        }

        try {
            Log::info('[TikTokAuthController] Exchanging code for access token');

            $tokenData = $this->tiktok->getAccessToken($code);

            Log::info('[TikTokAuthController] Token data received', [
                'has_access_token' => isset($tokenData['access_token']),
                'has_open_id' => isset($tokenData['open_id']),
                'has_refresh_token' => isset($tokenData['refresh_token']),
                'expires_in' => $tokenData['expires_in'] ?? null,
                'scope' => $tokenData['scope'] ?? null,
            ]);

            if (!isset($tokenData['access_token'])) {
                Log::error('[TikTokAuthController] No access_token in response', [
                    'token_data' => $tokenData,
                ]);

                $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
                return redirect()->away($frontendUrl . '/login?error=no_access_token');
            }

            $accessToken = $tokenData['access_token'];
            $openId = $tokenData['open_id'];
            $refreshToken = $tokenData['refresh_token'];
            $expiresIn = $tokenData['expires_in'];

            Log::info('[TikTokAuthController] Fetching TikTok user info');

            $userInfo = $this->tiktok->getUserInfo($accessToken, $openId);
            $tikTokUser = $userInfo['data']['user'] ?? null;

            if (!$tikTokUser) {
                Log::error('[TikTokAuthController] No user data in TikTok response', [
                    'user_info' => $userInfo,
                ]);

                $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
                return redirect()->away($frontendUrl . '/login?error=no_user_data');
            }

            Log::info('[TikTokAuthController] TikTok user info received', [
                'display_name' => $tikTokUser['display_name'] ?? null,
                'username' => $tikTokUser['username'] ?? null,
                'open_id' => $openId,
            ]);

            Log::info('[TikTokAuthController] Creating/updating user in database');

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

            Log::info('[TikTokAuthController] User created/updated', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tiktok_username' => $user->tiktok_username,
                'tiktok_open_id' => $user->tiktok_open_id,
                'token_expires_at' => $user->tiktok_token_expires_at?->toIso8601String(),
            ]);

            $token = $user->createToken('api-token')->plainTextToken;

            Log::info('[TikTokAuthController] Sanctum token created', [
                'user_id' => $user->id,
                'token_prefix' => substr($token, 0, 15) . '...',
            ]);

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

            Log::info('[TikTokAuthController] Redirecting to frontend', [
                'frontend_url' => $frontendUrl,
            ]);

            return redirect()->away($frontendUrl . '?token=' . $token);
        } catch (\Exception $e) {
            Log::error('[TikTokAuthController] TikTok authentication FAILED', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return redirect()->away($frontendUrl . '/login?error=' . urlencode($e->getMessage()));
        }
    }
}
