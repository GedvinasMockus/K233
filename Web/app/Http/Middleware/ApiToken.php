<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class ApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $apiKey = config('services.nodejs.key');
        $providedApiKey = $request->header('api-key');
        if ($providedApiKey !== $apiKey) {
            return response()->json('Unauthorized', 401);
        }

        return $next($request);
    }
}
