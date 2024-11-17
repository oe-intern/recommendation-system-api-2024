<?php

namespace App\Providers;

use App\Contracts\Shopify\Graphql\ShopLocale;
use App\Services\Shopify\Graphql\ShopLocaleService;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Shopify\Graphql\Shop as IShop;
use App\Services\Shopify\Graphql\ShopService;
use App\Contracts\Shopify\Graphql\Queries\Order as IOrder;
use App\Services\Shopify\Graphql\Queries\OrderService;
use App\Contracts\Shopify\Graphql\Queries\Product as IProduct;
use App\Services\Shopify\Graphql\Queries\ProductService;

class ShopifyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            IShop::class,
            ShopService::class
        );

        $this->app->bind(
            ShopLocale::class,
            ShopLocaleService::class
        );

        $this->app->bind(
            IOrder::class,
            OrderService::class
        );

        $this->app->bind(
            IProduct::class,
            ProductService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
