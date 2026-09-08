<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTier
{
    public function handle(Request $request, Closure $next, string $minTier): Response
    {
        if (! $request->user()?->hasTierAtLeast($minTier)) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Your plan does not include this feature.',
                ], 403);
            }

            return redirect()->route('data6.plans')->with('error', 'Your plan does not include this feature.');
        }

        return $next($request);
    }
}
