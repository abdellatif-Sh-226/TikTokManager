<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\CloudinaryService;
use App\Services\TikTokService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    public function __construct(
        private readonly CloudinaryService $cloudinary,
        private readonly TikTokService $tiktok
    ) {
        Log::info('[PostController] Initialized');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        Log::info('[PostController] index called', ['user_id' => $user->id]);

        // Try to fetch from TikTok API first
        if ($user->tiktok_access_token && $user->tiktok_open_id) {
            Log::info('[PostController] Fetching TikTok videos', [
                'user_id' => $user->id,
                'open_id' => $user->tiktok_open_id,
                'token_prefix' => substr($user->tiktok_access_token, 0, 15) . '...',
                'token_expires_at' => $user->tiktok_token_expires_at?->toIso8601String(),
                'token_expired' => $user->tiktok_token_expires_at ? $user->tiktok_token_expires_at->isPast() : null,
            ]);

            try {
                // Check if token is expired
                if ($user->tiktok_token_expires_at && $user->tiktok_token_expires_at->isPast()) {
                    Log::warning('[PostController] TikTok token expired, attempting refresh', [
                        'user_id' => $user->id,
                        'expired_at' => $user->tiktok_token_expires_at->toIso8601String(),
                    ]);

                    if ($user->tiktok_refresh_token) {
                        try {
                            $tiktokService = app(TikTokService::class);
                            $refreshResult = $tiktokService->refreshToken($user->tiktok_refresh_token);

                            if (isset($refreshResult['access_token'])) {
                                $user->update([
                                    'tiktok_access_token' => $refreshResult['access_token'],
                                    'tiktok_refresh_token' => $refreshResult['refresh_token'] ?? $user->tiktok_refresh_token,
                                    'tiktok_token_expires_at' => now()->addSeconds($refreshResult['expires_in'] ?? 3600),
                                ]);
                                Log::info('[PostController] Token refreshed successfully', ['user_id' => $user->id]);
                            }
                        } catch (\Exception $e) {
                            Log::error('[PostController] Token refresh failed', [
                                'user_id' => $user->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }

                $videosData = $this->tiktok->getVideos($user->tiktok_access_token, $user->tiktok_open_id);
                $tikTokVideos = $videosData['data']['videos'] ?? [];

                Log::info('[PostController] TikTok videos fetched', [
                    'user_id' => $user->id,
                    'video_count' => count($tikTokVideos),
                    'has_more' => $videosData['data']['has_more'] ?? null,
                ]);

                if (!empty($tikTokVideos)) {
                    $transformed = collect($tikTokVideos)->map(fn($v) => [
                        'id' => $v['id'] ?? '—',
                        'description' => $v['title'] ?? '—',
                        'hashtags' => '',
                        'videoUrl' => null,
                        'thumbnailUrl' => $v['cover_image_url'] ?? null,
                        'views' => (int) ($v['view_count'] ?? 0),
                        'likes' => (int) ($v['like_count'] ?? 0),
                        'comments' => (int) ($v['comment_count'] ?? 0),
                        'shares' => (int) ($v['share_count'] ?? 0),
                        'createdAt' => isset($v['create_time'])
                            ? \Carbon\Carbon::createFromTimestamp($v['create_time'])->toIso8601String()
                            : now()->toIso8601String(),
                        'status' => 'published',
                    ])->toArray();

                    Log::info('[PostController] Returning TikTok videos', [
                        'user_id' => $user->id,
                        'count' => count($transformed),
                    ]);

                    return response()->json(['data' => $transformed]);
                }
            } catch (\Exception $e) {
                Log::error('[PostController] Failed to fetch TikTok videos, falling back to local', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        } else {
            Log::info('[PostController] No TikTok connection, using local posts only', ['user_id' => $user->id]);
        }

        // Fallback to local posts
        $posts = $user->posts()
            ->orderBy('created_at', 'desc')
            ->get();

        Log::info('[PostController] Returning local posts', [
            'user_id' => $user->id,
            'count' => $posts->count(),
        ]);

        return response()->json([
            'data' => PostResource::collection($posts),
        ]);
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        set_time_limit(300);
        $user = $request->user();

        Log::info('[PostController] store called', [
            'user_id' => $user->id,
            'has_tiktok_token' => !empty($user->tiktok_access_token),
            'has_tiktok_open_id' => !empty($user->tiktok_open_id),
            'request_data' => [
                'description' => $request->input('description'),
                'hashtags' => $request->input('hashtags'),
                'privacy_level' => $request->input('privacy_level'),
                'disable_comment' => $request->input('disable_comment'),
                'publish_to_tiktok' => $request->input('publish_to_tiktok'),
                'has_video' => $request->hasFile('video'),
                'has_thumbnail' => $request->hasFile('thumbnail'),
                'video_size' => $request->file('video')?->getSize(),
                'video_mime' => $request->file('video')?->getMimeType(),
                'video_name' => $request->file('video')?->getClientOriginalName(),
            ],
        ]);

        // Step 1: Upload video to Cloudinary
        Log::info('[PostController] Step 1: Uploading video to Cloudinary', ['user_id' => $user->id]);

        try {
            $videoUrl = $this->cloudinary->upload($request->file('video'), 'posts/videos');
        } catch (\Exception $e) {
            Log::error('[PostController] Cloudinary video upload FAILED', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Failed to upload video to Cloudinary',
                'error' => $e->getMessage(),
            ], 500);
        }

        if (!$videoUrl) {
            Log::error('[PostController] Cloudinary returned empty URL', ['user_id' => $user->id]);
            return response()->json(['message' => 'Failed to upload video to Cloudinary'], 500);
        }

        Log::info('[PostController] Video uploaded to Cloudinary', [
            'user_id' => $user->id,
            'video_url' => $videoUrl,
        ]);

        $validated = $request->validated();
        $validated['video_url'] = $videoUrl;

        // Step 2: Upload thumbnail if provided
        if ($request->hasFile('thumbnail')) {
            Log::info('[PostController] Step 2: Uploading thumbnail', ['user_id' => $user->id]);
            try {
                $validated['thumbnail_url'] = $this->cloudinary->upload($request->file('thumbnail'), 'posts/thumbnails');
                Log::info('[PostController] Thumbnail uploaded', [
                    'user_id' => $user->id,
                    'thumbnail_url' => $validated['thumbnail_url'],
                ]);
            } catch (\Exception $e) {
                Log::warning('[PostController] Thumbnail upload failed, continuing without it', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $validated['status'] = $validated['status'] ?? 'draft';

        // Step 3: Create post in database
        Log::info('[PostController] Step 3: Creating post in database', ['user_id' => $user->id]);

        try {
            $post = $user->posts()->create($validated);
            Log::info('[PostController] Post created in database', [
                'user_id' => $user->id,
                'post_id' => $post->id,
                'description' => $post->description,
                'video_url' => $post->video_url,
                'status' => $post->status,
            ]);
        } catch (\Exception $e) {
            Log::error('[PostController] Database post creation FAILED', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'validated_data' => $validated,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Failed to create post in database',
                'error' => $e->getMessage(),
            ], 500);
        }

        // Step 4: Publish to TikTok
        $tiktokStatus = null;
        $publishToTikTok = $request->input('publish_to_tiktok', '1');

        Log::info('[PostController] Step 4: TikTok publish check', [
            'user_id' => $user->id,
            'publish_to_tiktok' => $publishToTikTok,
            'has_tiktok_token' => !empty($user->tiktok_access_token),
            'has_tiktok_open_id' => !empty($user->tiktok_open_id),
            'token_expired' => $user->tiktok_token_expires_at ? $user->tiktok_token_expires_at->isPast() : null,
        ]);

        if ($user->tiktok_access_token && $publishToTikTok === '1') {
            try {
                $videoFile = $request->file('video');
                $videoSize = $videoFile->getSize();
                $videoPath = $videoFile->getRealPath();

                Log::info('[PostController] Step 4a: Querying TikTok creator info', ['user_id' => $user->id]);

                $creatorInfo = $this->tiktok->queryCreatorInfo($user->tiktok_access_token);

                Log::info('[PostController] TikTok creator info received', [
                    'user_id' => $user->id,
                    'creator_info' => $creatorInfo,
                ]);

                $privacyLevel = $validated['privacy_level'] ?? 'SELF_ONLY';
                $disableComment = (bool) ($request->input('disable_comment', '0') === '1');
                $title = $validated['description'] ?? '';

                Log::info('[PostController] Step 4b: Calling initPublish (FILE_UPLOAD)', [
                    'user_id' => $user->id,
                    'title' => $title,
                    'video_size' => $videoSize,
                    'privacy_level' => $privacyLevel,
                    'disable_comment' => $disableComment,
                ]);

                $result = $this->tiktok->initPublish(
                    $user->tiktok_access_token,
                    $title,
                    $privacyLevel,
                    $disableComment,
                    $videoSize
                );

                Log::info('[PostController] initPublish result', [
                    'user_id' => $user->id,
                    'result' => $result,
                ]);

                if (isset($result['data']['publish_id']) && isset($result['data']['upload_url'])) {
                    $publishId = $result['data']['publish_id'];
                    $uploadUrl = $result['data']['upload_url'];

                    Log::info('[PostController] TikTok publish initiated, uploading video', [
                        'user_id' => $user->id,
                        'publish_id' => $publishId,
                        'upload_url' => $uploadUrl,
                    ]);

                    $this->tiktok->uploadVideo($uploadUrl, $videoPath, $videoSize);

                    Log::info('[PostController] Video uploaded to TikTok, checking status', [
                        'user_id' => $user->id,
                        'publish_id' => $publishId,
                    ]);

                    $statusResult = null;
                    $maxRetries = 5;
                    $retryDelay = 3;

                    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                        Log::info('[PostController] Step 4c: Checking publish status (attempt ' . $attempt . '/' . $maxRetries . ')', [
                            'user_id' => $user->id,
                            'publish_id' => $publishId,
                        ]);

                        sleep($retryDelay);

                        try {
                            $statusResult = $this->tiktok->getPublishStatus($user->tiktok_access_token, $publishId);
                            $tikStatus = $statusResult['data']['status'] ?? 'UNKNOWN';

                            Log::info('[PostController] Publish status check result', [
                                'user_id' => $user->id,
                                'publish_id' => $publishId,
                                'attempt' => $attempt,
                                'status' => $tikStatus,
                                'full_status_response' => $statusResult,
                            ]);

                            if (in_array($tikStatus, ['PUBLISH_COMPLETE', 'FAILED', 'ERROR'])) {
                                Log::info('[PostController] Publish status is final', [
                                    'user_id' => $user->id,
                                    'status' => $tikStatus,
                                ]);
                                break;
                            }

                            if ($attempt < $maxRetries) {
                                Log::info('[PostController] Still processing, retrying...', [
                                    'user_id' => $user->id,
                                    'attempt' => $attempt,
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::error('[PostController] Status check failed on attempt ' . $attempt, [
                                'user_id' => $user->id,
                                'error' => $e->getMessage(),
                            ]);

                            if ($attempt < $maxRetries) {
                                continue;
                            }
                        }
                    }

                    $tikStatus = $statusResult['data']['status'] ?? 'UNKNOWN';

                    $tiktokStatus = [
                        'publish_id' => $publishId,
                        'status' => $tikStatus,
                    ];

                    if (isset($statusResult['data']['error'])) {
                        $tiktokStatus['error'] = $statusResult['data']['error']['code'] ?? 'unknown';
                        $tiktokStatus['message'] = $statusResult['data']['error']['message'] ?? 'TikTok error';
                    }

                    $post->update([
                        'tiktok_publish_id' => $publishId,
                        'tiktok_status' => $tikStatus,
                    ]);

                    Log::info('[PostController] Post updated with TikTok status', [
                        'user_id' => $user->id,
                        'post_id' => $post->id,
                        'tiktok_status' => $tiktokStatus,
                    ]);
                } elseif (isset($result['error'])) {
                    Log::error('[PostController] TikTok initPublish returned error', [
                        'user_id' => $user->id,
                        'error' => $result['error'],
                    ]);

                    $tiktokStatus = [
                        'error' => $result['error']['code'] ?? 'unknown',
                        'message' => $result['error']['message'] ?? 'TikTok publish failed',
                    ];
                } else {
                    Log::warning('[PostController] TikTok initPublish returned unexpected response', [
                        'user_id' => $user->id,
                        'result' => $result,
                    ]);

                    $tiktokStatus = [
                        'error' => 'unexpected_response',
                        'message' => 'Unexpected response from TikTok API',
                    ];
                }
            } catch (\Exception $e) {
                Log::error('[PostController] TikTok publish exception', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $tiktokStatus = [
                    'error' => 'exception',
                    'message' => $e->getMessage(),
                ];
            }
        } else {
            Log::info('[PostController] Skipping TikTok publish', [
                'user_id' => $user->id,
                'reason' => !$user->tiktok_access_token ? 'no_tiktok_token' : 'publish_to_tiktok_disabled',
            ]);
        }

        $responseData = (new PostResource($post))->toArray($request);
        $responseData['tiktokStatus'] = $tiktokStatus;

        Log::info('[PostController] store completed', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'tiktok_status' => $tiktokStatus,
            'video_url' => $videoUrl,
        ]);

        return response()->json([
            'data' => $responseData,
        ], 201);
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();

        Log::info('[PostController] destroy called', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        if ($post->user_id !== $user->id) {
            Log::warning('[PostController] Forbidden: user tried to delete another user post', [
                'user_id' => $user->id,
                'post_owner_id' => $post->user_id,
                'post_id' => $post->id,
            ]);
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $post->delete();

        Log::info('[PostController] Post deleted', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        return response()->json(['message' => 'Deleted']);
    }
}
