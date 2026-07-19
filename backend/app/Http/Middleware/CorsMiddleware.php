<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('OPTIONS')) {
            Log::debug('[CorsMiddleware] Handling OPTIONS preflight', [
                'origin' => $request->header('Origin'),
                'path' => $request->path(),
            ]);

            return $this->addHeaders(response('', 204), $request);
        }

        return $this->addHeaders($next($request), $request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $this->addHeaders($response, $request);
    }

    private function addHeaders(Response $response, Request $request): Response
    {
        $origin = $request->header('Origin');

        if (!$origin) {
            return $response;
        }

        $allowedOrigins = config('cors.allowed_origins', []);
        $isAllowed = in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins);

        if (!$isAllowed) {
            Log::debug('[CorsMiddleware] Origin not allowed', [
                'origin' => $origin,
                'allowed' => $allowedOrigins,
            ]);
            return $response;
        }

        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');

        if (!$response->headers->has('Vary')) {
            $response->headers->set('Vary', 'Origin');
        }

        return $response;
    }
}
