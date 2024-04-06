<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$roles)
    {
        if (!auth()->guard('api')->user()->hasRole($roles)) {
            return response()->json([
                'code' => 403,
                'status' => 'error',
                'data' => null,
                'message' => 'Anda tidak memiliki izin untuk mengakses resource ini.'
            ], 403);
        }

        return $next($request);
    }
}
