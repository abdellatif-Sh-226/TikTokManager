<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private readonly CloudinaryService $cloudinary
    ) {}

    public function index(Request $request): JsonResponse
    {
        $posts = $request->user()
            ->posts()
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
