<?php

namespace App\Http\Middleware;

use App\Objects\Enums\RecommendationType;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class SetRecommendationStateRequest
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
            'product_id' => 'string',
            'recommendation_type' => [
                'required',
                new Enum(RecommendationType::class),
            ]
        ]);

        return $next($request);
    }
}
