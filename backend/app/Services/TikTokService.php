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

        $baseUri = $this->sandbox
            ? 'https://open-api.tiktok.com/'
            : 'https://open-api.tiktok.com/';

        $this->client = new Client([
            'base_uri' => $baseUri,
            'timeout' => 10,
        ]);
    }

    public function getAuthUrl(): string
    {
        $params = http_build_query([
            'client_key' => $this->clientId,
            'scope' => 'user.info.basic,video.list,video.upload',
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'state' => csrf_token(),
        ]);

        return "https://www.tiktok.com/v2/auth/authorize?{$params}";
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
            'query' => [
                'access_token' => $accessToken,
                'fields' => 'open_id,union_id,avatar_url,display_name,username',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @throws GuzzleException
     */
    public function getVideos(string $accessToken, string $openId, int $cursor = 0, int $maxCount = 20): array
    {
        $response = $this->client->get('v2/video/list/', [
            'query' => [
                'access_token' => $accessToken,
                'open_id' => $openId,
                'cursor' => $cursor,
                'max_count' => $maxCount,
                'fields' => 'id,title,cover_image_url,view_count,like_count,comment_count,share_count,create_time',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}
