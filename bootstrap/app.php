<?php

use App\Http\Middleware\AccessControlHeaders;
use App\Http\Middleware\CspHeader;
use App\Http\Middleware\EnsureShopifyInstalled;
use App\Http\Middleware\EnsureShopifySession;
use App\Http\Middleware\VerifyAuthenticationToken;
use App\Http\Middleware\VerifyHmac;
use App\Http\Middleware\IdentifyShopDomain;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function ($router) {
            Route::prefix('')
                ->group(base_path('routes/webhook.php'));
            Route::prefix('')
                ->middleware(['web', 'auth:admin'])
                ->group(base_path('routes/admin.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'shopify.install' => EnsureShopifyInstalled::class,
            'shopify.auth' => EnsureShopifySession::class,
            'verify.hmac' => VerifyHmac::class,
            'verify.token' => VerifyAuthenticationToken::class,
            'access_control_headers' => AccessControlHeaders::class,
            'csp_header' => CspHeader::class,
            'identify.shop.domain' => IdentifyShopDomain::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
