<?php

namespace JWebb\Unleash\Middleware;

use Closure;
use Illuminate\Http\Request;
use JWebb\Unleash\Unleash;

class CheckFeature
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $featureName
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $featureName)
    {
        if (!app(Unleash::class)->isEnabled($featureName)) {
            abort(404);
        }

        return $next($request);
    }
}
