<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        Log::info('[AuthController] login called', [
            'email' => $request->email,
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            Log::warning('[AuthController] User not found', ['email' => $request->email]);
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            Log::warning('[AuthController] Wrong password', ['email' => $request->email, 'user_id' => $user->id]);
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        Log::info('[AuthController] Login successful', [
            'user_id' => $user->id,
            'email' => $user->email,
            'token_prefix' => substr($token, 0, 15) . '...',
        ]);

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Log::info('[AuthController] logout called', [
            'user_id' => $request->user()->id,
        ]);

        $request->user()->currentAccessToken()->delete();

        Log::info('[AuthController] Logout successful', [
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Logged out']);
    }

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
