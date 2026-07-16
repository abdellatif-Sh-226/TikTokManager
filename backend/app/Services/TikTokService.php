<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

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
            'timeout' => 10,
            'verify' => false,
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

        return "https://www.tiktok.com/v2/auth/authorize/?{$params}";
    }

    /**
     * @throws GuzzleException
     */
    public function getAccessToken(string $code): array
    {
        $response = $this->client->post('v2/oauth/token/', [
            'form_params' => [
                'client_key' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->redirectUri,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @throws GuzzleException
     */
    public function refreshToken(string $refreshToken): array
    {
        $response = $this->client->post('v2/oauth/token/', [
            'form_params' => [
                'client_key' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @throws GuzzleException
     */
    public function getUserInfo(string $accessToken, string $openId): array
    {
        $response = $this->client->get('v2/user/info/', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'query' => [
                'fields' => 'open_id,union_id,avatar_url,display_name,username,follower_count,following_count,likes_count,video_count',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @throws GuzzleException
     */
    public function getVideos(string $accessToken, string $openId, int $cursor = 0, int $maxCount = 20): array
    {
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

        return json_decode($response->getBody(), true);
    }

    public function getVideoById(string $accessToken, array $videoIds): array
    {
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

        return json_decode($response->getBody(), true);
    }
}
