<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
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
            $uploadedFile = $request->file('video')->storeOnCloudinary('posts/videos');
            $data['video_url'] = $uploadedFile->getSecurePath();
        }

        if ($request->hasFile('thumbnail')) {
            $uploadedFile = $request->file('thumbnail')->storeOnCloudinary('posts/thumbnails');
            $data['thumbnail_url'] = $uploadedFile->getSecurePath();
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
