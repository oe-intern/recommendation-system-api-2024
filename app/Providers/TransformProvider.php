<?php

namespace App\Providers;

use App\Contracts\Objects\Transform\ShopifyTransform;
use App\Objects\Transform\OrderTransform;
use App\Objects\Transform\ProductTransform;
use Illuminate\Support\ServiceProvider;

class TransformProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the service transform default.
        $this->app->bind(
            ShopifyTransform::class,
            ProductTransform::class
        );

        $this->app->bind('shopify.transform.product', ProductTransform::class);
        $this->app->bind('shopify.transform.order', OrderTransform::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
