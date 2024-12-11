<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class SettingAutoRecommendationRequest
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $request->validate([
            'number_of_items' => 'required|integer|min:1|max:6',
            'layout' => 'required|string',
            'background_color' => 'required|string',
            'text_color' => 'required|string',
        ]);

        return $next($request);
    }
}
