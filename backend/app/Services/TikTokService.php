<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class TikTokService
{
    private Client $client;
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    private bool $sandbox;

    public function __construct()
    {
        $this->clientId = config('services.tiktok.client_id');
        $this->clientSecret = config('services.tiktok.client_secret');
        $this->redirectUri = config('services.tiktok.redirect_uri');
        $this->sandbox = config('services.tiktok.sandbox', true);

        $baseUri = 'https://open.tiktokapis.com/';

        $this->client = new Client([
            'base_uri' => $baseUri,
            'timeout' => 120,
            'verify' => false,
        ]);

        Log::info('[TikTokService] Initialized', [
            'base_uri' => $baseUri,
            'client_id_set' => !empty($this->clientId),
            'client_secret_set' => !empty($this->clientSecret),
            'redirect_uri' => $this->redirectUri,
            'sandbox' => $this->sandbox,
        ]);
    }

    public function getAuthUrl(): string
    {
        $params = http_build_query([
            'client_key' => $this->clientId,
            'scope' => 'user.info.basic,video.publish,video.upload,user.info.profile,user.info.stats,video.list',
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'state' => csrf_token(),
        ]);

        $url = "https://www.tiktok.com/v2/auth/authorize/?{$params}";

        Log::info('[TikTokService] Generated auth URL', ['url' => $url]);

        return $url;
    }

    /**
     * @throws GuzzleException
     */
    public function getAccessToken(string $code): array
    {
        Log::info('[TikTokService] getAccessToken called', ['code' => substr($code, 0, 10) . '...']);

        try {
            $response = $this->client->post('v2/oauth/token/', [
                'form_params' => [
                    'client_key' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $this->redirectUri,
                ],
            ]);

            $body = json_decode($response->getBody(), true);

            Log::info('[TikTokService] getAccessToken success', [
                'has_access_token' => isset($body['access_token']),
                'has_open_id' => isset($body['open_id']),
                'expires_in' => $body['expires_in'] ?? null,
                'scope' => $body['scope'] ?? null,
            ]);

            return $body;
        } catch (GuzzleException $e) {
            Log::error('[TikTokService] getAccessToken FAILED', [
                'error' => $e->getMessage(),
                'code' => substr($code, 0, 10) . '...',
            ]);
            throw $e;
        }
    }

    /**
     * @throws GuzzleException
     */
    public function refreshToken(string $refreshToken): array
    {
        Log::info('[TikTokService] refreshToken called');

        try {
            $response = $this->client->post('v2/oauth/token/', [
                'form_params' => [
                    'client_key' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                ],
            ]);

            $body = json_decode($response->getBody(), true);

            Log::info('[TikTokService] refreshToken success', [
                'has_access_token' => isset($body['access_token']),
                'expires_in' => $body['expires_in'] ?? null,
            ]);

            return $body;
        } catch (GuzzleException $e) {
            Log::error('[TikTokService] refreshToken FAILED', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * @throws GuzzleException
     */
    public function getUserInfo(string $accessToken, string $openId): array
    {
        Log::info('[TikTokService] getUserInfo called', [
            'open_id' => $openId,
            'token_prefix' => substr($accessToken, 0, 15) . '...',
        ]);

        try {
            $response = $this->client->get('v2/user/info/', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'query' => [
                    'fields' => 'open_id,union_id,avatar_url,display_name,username,follower_count,following_count,likes_count,video_count',
                ],
            ]);

            $body = json_decode($response->getBody(), true);

            Log::info('[TikTokService] getUserInfo response', [
                'http_code' => $response->getStatusCode(),
                'has_error' => isset($body['error']),
                'error_code' => $body['error']['code'] ?? null,
                'user_display_name' => $body['data']['user']['display_name'] ?? null,
                'user_username' => $body['data']['user']['username'] ?? null,
                'follower_count' => $body['data']['user']['follower_count'] ?? null,
                'likes_count' => $body['data']['user']['likes_count'] ?? null,
                'video_count' => $body['data']['user']['video_count'] ?? null,
            ]);

            return $body;
        } catch (GuzzleException $e) {
            Log::error('[TikTokService] getUserInfo FAILED', [
                'error' => $e->getMessage(),
                'open_id' => $openId,
            ]);
            throw $e;
        }
    }

    /**
     * @throws GuzzleException
     */
    public function getVideos(string $accessToken, string $openId, int $cursor = 0, int $maxCount = 20): array
    {
        Log::info('[TikTokService] getVideos called', [
            'open_id' => $openId,
            'cursor' => $cursor,
            'max_count' => $maxCount,
        ]);

        try {
            $response = $this->client->post('v2/video/list/?' . http_build_query([
                'fields' => 'id,title,cover_image_url,view_count,like_count,comment_count,share_count,create_time',
            ]), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'max_count' => $maxCount,
                    'cursor' => $cursor,
                ],
            ]);

            $body = json_decode($response->getBody(), true);

            $videoCount = count($body['data']['videos'] ?? []);

            Log::info('[TikTokService] getVideos response', [
                'http_code' => $response->getStatusCode(),
                'has_error' => isset($body['error']),
                'error_code' => $body['error']['code'] ?? null,
                'video_count' => $videoCount,
                'has_more' => $body['data']['has_more'] ?? null,
                'cursor' => $body['data']['cursor'] ?? null,
            ]);

            return $body;
        } catch (GuzzleException $e) {
            Log::error('[TikTokService] getVideos FAILED', [
                'error' => $e->getMessage(),
                'open_id' => $openId,
            ]);
            throw $e;
        }
    }

    public function getVideoById(string $accessToken, array $videoIds): array
    {
        Log::info('[TikTokService] getVideoById called', ['video_ids' => $videoIds]);

        try {
            $response = $this->client->post('v2/video/query/?' . http_build_query([
                'fields' => 'id,title,cover_image_url,view_count,like_count,comment_count,share_count,create_time,share_url',
            ]), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'filters' => ['video_ids' => $videoIds],
                ],
            ]);

            $body = json_decode($response->getBody(), true);

            Log::info('[TikTokService] getVideoById response', [
                'http_code' => $response->getStatusCode(),
                'video_count' => count($body['data']['videos'] ?? []),
            ]);

            return $body;
        } catch (GuzzleException $e) {
            Log::error('[TikTokService] getVideoById FAILED', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    // ============================================================
    // Content Posting API (video.publish / video.upload)
    // ============================================================

    public function queryCreatorInfo(string $accessToken): array
    {
        Log::info('[TikTokService] queryCreatorInfo called', [
            'token_prefix' => substr($accessToken, 0, 15) . '...',
        ]);

        try {
            $response = $this->client->post('v2/post/publish/creator_info/query/', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            $body = json_decode($response->getBody(), true);

            Log::info('[TikTokService] queryCreatorInfo response', [
                'http_code' => $response->getStatusCode(),
                'has_error' => isset($body['error']),
                'error_code' => $body['error']['code'] ?? null,
                'error_message' => $body['error']['message'] ?? null,
                'creator_username' => $body['data']['creator_username'] ?? null,
                'privacy_level_options' => $body['data']['privacy_level_options'] ?? null,
                'max_video_post_duration_sec' => $body['data']['max_video_post_duration_sec'] ?? null,
            ]);

            return $body;
        } catch (GuzzleException $e) {
            Log::error('[TikTokService] queryCreatorInfo FAILED', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function initPublish(string $accessToken, string $title, string $privacyLevel = 'SELF_ONLY', bool $disableComment = false, int $videoSize = 0, int $chunkSize = 10000000): array
    {
        $chunkSize = min($chunkSize, $videoSize);
        $totalChunkCount = (int) ceil($videoSize / $chunkSize);

        $requestData = [
            'post_info' => [
                'title' => $title,
                'privacy_level' => $privacyLevel,
                'disable_comment' => $disableComment,
                'disable_duet' => false,
                'disable_stitch' => false,
            ],
            'source_info' => [
                'source' => 'FILE_UPLOAD',
                'video_size' => $videoSize,
                'chunk_size' => $chunkSize,
                'total_chunk_count' => $totalChunkCount,
            ],
        ];

        Log::info('[TikTokService] initPublish called (FILE_UPLOAD)', [
            'title' => $title,
            'privacy_level' => $privacyLevel,
            'disable_comment' => $disableComment,
            'video_size' => $videoSize,
            'chunk_size' => $chunkSize,
            'total_chunk_count' => $totalChunkCount,
        ]);

        try {
            $response = $this->client->post('v2/post/publish/video/init/', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json; charset=UTF-8',
                ],
                'json' => $requestData,
            ]);

            $body = json_decode($response->getBody(), true);

            Log::info('[TikTokService] initPublish response', [
                'http_code' => $response->getStatusCode(),
                'has_error' => isset($body['error']),
                'error_code' => $body['error']['code'] ?? null,
                'error_message' => $body['error']['message'] ?? null,
                'has_data' => isset($body['data']),
                'publish_id' => $body['data']['publish_id'] ?? null,
                'has_upload_url' => isset($body['data']['upload_url']),
            ]);

            return $body;
        } catch (GuzzleException $e) {
            Log::error('[TikTokService] initPublish FAILED', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function uploadVideo(string $uploadUrl, string $filePath, int $videoSize, int $chunkSize = 10000000): bool
    {
        $totalChunkCount = (int) ceil($videoSize / $chunkSize);

        Log::info('[TikTokService] uploadVideo called', [
            'upload_url' => $uploadUrl,
            'file_path' => $filePath,
            'video_size' => $videoSize,
            'total_chunks' => $totalChunkCount,
        ]);

        try {
            $handle = fopen($filePath, 'rb');
            if (!$handle) {
                throw new \RuntimeException("Cannot open file: {$filePath}");
            }

            for ($chunkIndex = 0; $chunkIndex < $totalChunkCount; $chunkIndex++) {
                $start = $chunkIndex * $chunkSize;
                $end = min($start + $chunkSize - 1, $videoSize - 1);
                $chunkLength = $end - $start + 1;

                $chunkData = fread($handle, $chunkLength);

                Log::info('[TikTokService] Uploading chunk ' . ($chunkIndex + 1) . '/' . $totalChunkCount, [
                    'start' => $start,
                    'end' => $end,
                    'chunk_size' => $chunkLength,
                ]);

                $response = $this->client->put($uploadUrl, [
                    'headers' => [
                        'Content-Type' => 'video/mp4',
                        'Content-Range' => "bytes {$start}-{$end}/{$videoSize}",
                    ],
                    'body' => $chunkData,
                ]);

                Log::info('[TikTokService] Chunk ' . ($chunkIndex + 1) . ' uploaded', [
                    'status' => $response->getStatusCode(),
                ]);
            }

            fclose($handle);

            Log::info('[TikTokService] uploadVideo completed successfully');
            return true;
        } catch (GuzzleException $e) {
            Log::error('[TikTokService] uploadVideo FAILED', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function getPublishStatus(string $accessToken, string $publishId): array
    {
        Log::info('[TikTokService] getPublishStatus called', ['publish_id' => $publishId]);

        try {
            $response = $this->client->post('v2/post/publish/status/fetch/', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json; charset=UTF-8',
                ],
                'json' => [
                    'publish_id' => $publishId,
                ],
            ]);

            $body = json_decode($response->getBody(), true);

            Log::info('[TikTokService] getPublishStatus response', [
                'http_code' => $response->getStatusCode(),
                'has_error' => isset($body['error']),
                'error_code' => $body['error']['code'] ?? null,
                'status' => $body['data']['status'] ?? null,
                'full_response' => $body,
            ]);

            return $body;
        } catch (GuzzleException $e) {
            Log::error('[TikTokService] getPublishStatus FAILED', [
                'error' => $e->getMessage(),
                'publish_id' => $publishId,
            ]);
            throw $e;
        }
    }
}
