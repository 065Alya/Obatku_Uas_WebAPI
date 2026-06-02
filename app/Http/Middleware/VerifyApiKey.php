<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('x-api-key');

        if (!$apiKey || $apiKey !== config('app.api_key', env('API_KEY'))) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Unauthorized. Invalid or missing API Key.'
            ], 401);
        }

        return $next($request);
    }
}
