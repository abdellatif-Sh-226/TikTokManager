<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    private string $cloudName;
    private string $apiKey;
    private string $apiSecret;
    private Client $client;

    public function __construct()
    {
        $this->cloudName = config('cloudinary.cloud_name');
        $this->apiKey = config('cloudinary.api_key');
        $this->apiSecret = config('cloudinary.api_secret');
        $this->client = new Client();
    }

    public function upload(UploadedFile $file, string $folder = ''): string
    {
        $timestamp = time();
        $params = [
            'folder' => $folder,
            'timestamp' => $timestamp,
        ];

        $signature = $this->generateSignature($params);

        $response = $this->client->post(
            "https://api.cloudinary.com/v1_1/{$this->cloudName}/auto/upload",
            [
                'multipart' => [
                    ['name' => 'file', 'contents' => fopen($file->getRealPath(), 'r'), 'filename' => $file->getClientOriginalName()],
                    ['name' => 'folder', 'contents' => $folder],
                    ['name' => 'timestamp', 'contents' => (string) $timestamp],
                    ['name' => 'api_key', 'contents' => $this->apiKey],
                    ['name' => 'signature', 'contents' => $signature],
                ],
            ]
        );

        $result = json_decode($response->getBody(), true);

        return $result['secure_url'];
    }

    private function generateSignature(array $params): string
    {
        ksort($params);
        $stringToSign = implode('&', array_map(
            fn($k, $v) => "{$k}={$v}",
            array_keys($params),
            $params
        ));

        return sha1($stringToSign . $this->apiSecret);
    }
}
