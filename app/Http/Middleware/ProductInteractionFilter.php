<?php

namespace App\Http\Middleware;

use App\Objects\Enums\InteractionType;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ProductInteractionFilter
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
            'product_id' => 'sometimes|nullable|string',
            'start_date' => 'required|date_format:Y-m-d|before_or_equal:end_date',
            'end_date' => 'required|date_format:Y-m-d|before_or_equal:today',
        ]);

        return $next($request);
    }
}
