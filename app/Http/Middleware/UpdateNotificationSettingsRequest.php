<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UpdateNotificationSettingsRequest
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
            'email_notification' => 'required|boolean',
            'email' => 'required_if:email_notification,true|email',
        ]);

        return $next($request);
    }
}
