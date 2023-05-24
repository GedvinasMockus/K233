<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleCheckMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $acceptedRoles = array_slice(func_get_args(), 2);
        foreach ($acceptedRoles as $role) {
            if (auth()->user()->role == $role) return $next($request);
        }
        return abort(404);
    }
}
