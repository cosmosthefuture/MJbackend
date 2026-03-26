<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInternalSecret
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $incomingSecret = $request->header('X-Internal-Secret');
        $expectedSecret = config('services.web_socket.internal_secret');

        if (!$incomingSecret) {
            return response()->json([
                'message' => 'Forbidden: missing internal secret',
            ], 403);
        }

        if (!$expectedSecret || ($expectedSecret !== $incomingSecret)) {
            return response()->json([
                'message' => 'Forbidden: invalid internal secret',
            ], 403);
        }

        return $next($request);
    }
}
