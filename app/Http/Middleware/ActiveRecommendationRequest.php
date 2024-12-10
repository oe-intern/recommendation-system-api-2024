<?php

namespace App\Http\Middleware;

use App\Objects\Enums\RecommendationState;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Validation\Rules\Enum;

class ActiveRecommendationRequest
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
            'active' => [
                'required',
                'string',
                new Enum(RecommendationState::class),
            ],
        ]);

        return $next($request);
    }
}
