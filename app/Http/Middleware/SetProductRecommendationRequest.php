<?php

namespace App\Http\Middleware;

use App\Objects\Enums\RecommendationType;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class SetProductRecommendationRequest
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
            'recommended_ids' => 'required|array',
            'recommended_ids.*' => 'string',
            'recommendation_type' => [
                'sometimes',
                'nullable',
                'string',
                new Enum(RecommendationType::class),
            ]
        ]);

        return $next($request);
    }
}
