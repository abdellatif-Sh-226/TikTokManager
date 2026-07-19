<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

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
        $this->client = new Client(['verify' => false, 'timeout' => 120]);

        Log::info('[CloudinaryService] Initialized', [
            'cloud_name' => $this->cloudName ? substr($this->cloudName, 0, 5) . '...' : 'NOT SET',
            'api_key_set' => !empty($this->apiKey),
            'api_secret_set' => !empty($this->apiSecret),
        ]);
    }

    public function upload(UploadedFile $file, string $folder = ''): string
    {
        $timestamp = time();
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();

        Log::info('[CloudinaryService] upload called', [
            'original_name' => $originalName,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'folder' => $folder,
            'timestamp' => $timestamp,
            'real_path' => $file->getRealPath(),
            'file_exists' => file_exists($file->getRealPath()),
        ]);

        $params = [
            'folder' => $folder,
            'timestamp' => $timestamp,
        ];

        $signature = $this->generateSignature($params);

        Log::info('[CloudinaryService] Generated signature', [
            'string_to_sign' => "folder={$folder}&timestamp={$timestamp}",
            'signature' => $signature,
        ]);

        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/auto/upload";

        Log::info('[CloudinaryService] Uploading to Cloudinary', [
            'url' => $url,
            'folder' => $folder,
        ]);

        try {
            $response = $this->client->post($url, [
                'multipart' => [
                    ['name' => 'file', 'contents' => fopen($file->getRealPath(), 'r'), 'filename' => $originalName],
                    ['name' => 'folder', 'contents' => $folder],
                    ['name' => 'timestamp', 'contents' => (string) $timestamp],
                    ['name' => 'api_key', 'contents' => $this->apiKey],
                    ['name' => 'signature', 'contents' => $signature],
                ],
            ]);

            $result = json_decode($response->getBody(), true);

            Log::info('[CloudinaryService] Upload success', [
                'secure_url' => $result['secure_url'] ?? null,
                'public_id' => $result['public_id'] ?? null,
                'resource_type' => $result['resource_type'] ?? null,
                'format' => $result['format'] ?? null,
                'bytes' => $result['bytes'] ?? null,
                'width' => $result['width'] ?? null,
                'height' => $result['height'] ?? null,
            ]);

            return $result['secure_url'];
        } catch (\Exception $e) {
            Log::error('[CloudinaryService] Upload FAILED', [
                'error' => $e->getMessage(),
                'original_name' => $originalName,
                'file_size' => $fileSize,
                'url' => $url,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function generateSignature(array $params): string
    {
        ksort($params);
        $stringToSign = implode('&', array_map(
            fn($k, $v) => "{$k}={$v}",
            array_keys($params),
            $params
        ));

        $signature = sha1($stringToSign . $this->apiSecret);

        Log::debug('[CloudinaryService] generateSignature', [
            'string_to_sign' => $stringToSign,
            'signature' => $signature,
        ]);

        return $signature;
    }
}
