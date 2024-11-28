<?php

namespace App\Http\Middleware;

use App\Objects\Enums\InteractionType;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Validation\Rules\Enum;

class ProductInteractionRequest
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
            'product_id' => 'required|string',
            'number_of_interactions' => 'sometimes|nullable|integer',
            'interaction_type' => [
                'required',
                'string',
                new Enum(InteractionType::class),
            ]
        ]);

        return $next($request);
    }
}
