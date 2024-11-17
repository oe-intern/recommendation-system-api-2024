<?php

namespace App\Providers;

use App\Contracts\Objects\Transform\ShopifyTransform;
use App\Objects\Transform\Order;
use App\Objects\Transform\Product;
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
            Product::class
        );

        $this->app->bind('shopify.transform.product', Product::class);
        $this->app->bind('shopify.transform.order', Order::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
