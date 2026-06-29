<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-Key') ?? $request->query('api_key');

        $apiKey = ApiKey::where('key', $key)->first();

        if (! $apiKey) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $apiKey->update(['last_used_at' => now()]);

        return $next($request);
    }
}
