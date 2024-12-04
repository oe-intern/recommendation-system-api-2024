<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ProductAddToCartRequest
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
            'number_of_items' => 'sometimes|nullable|integer',
        ]);

        return $next($request);
    }
}
