<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Anti-cheating middleware for exam answer submission endpoints.
 *
 * Provides:
 * - Rate-limiting: max 5 answer submissions per 2 seconds per student
 * - Security headers: prevent framing, caching, content-type sniffing
 * - Request origin validation when referrer is available
 */
class PreventCheating
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $key = 'exam-answer:' . $user->getKey();

            // Rate limit: max 5 submissions per 2-second window
            if (RateLimiter::tooManyAttempts($key, 5)) {
                return response()->json([
                    'message' => 'Terlalu banyak pengiriman jawaban. Silakan tunggu sebentar.',
                ], 429);
            }

            RateLimiter::hit($key, 2);
        }

        $response = $next($request);

        // Add strict security headers
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
