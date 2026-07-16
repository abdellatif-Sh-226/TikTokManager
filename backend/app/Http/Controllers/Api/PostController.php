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

class PostController extends Controller
{
    public function __construct(
        private readonly CloudinaryService $cloudinary,
        private readonly TikTokService $tiktok
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->tiktok_access_token && $user->tiktok_open_id) {
            try {
                $videosData = $this->tiktok->getVideos($user->tiktok_access_token, $user->tiktok_open_id);
                $tikTokVideos = $videosData['data']['videos'] ?? [];
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
                    return response()->json(['data' => $transformed]);
                }
            } catch (\Exception $e) {}
        }

        $posts = $user->posts()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => PostResource::collection($posts),
        ]);
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('video')) {
            $data['video_url'] = $this->cloudinary->upload($request->file('video'), 'posts/videos');
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail_url'] = $this->cloudinary->upload($request->file('thumbnail'), 'posts/thumbnails');
        }

        $post = $request->user()->posts()->create($data);

        return response()->json([
            'data' => new PostResource($post),
        ], 201);
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
