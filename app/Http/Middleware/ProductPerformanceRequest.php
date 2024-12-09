<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ProductPerformanceRequest
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
            'start_date' => 'required|date_format:Y-m-d|before_or_equal:end_date',
            'end_date' => 'required|date_format:Y-m-d|before_or_equal:today',
        ]);

        return $next($request);
    }
}
