<?php

namespace App\Providers;

use App\Contracts\Shopify\Graphql\ShopLocale;
use App\Services\Shopify\Graphql\ShopLocaleService;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Shopify\Graphql\Shop as IShop;
use App\Services\Shopify\Graphql\ShopService;
use App\Contracts\Shopify\Graphql\Queries\IOrderQueryShopify;
use App\Services\Shopify\Graphql\Queries\OrderQueryShopify;
use App\Contracts\Shopify\Graphql\Queries\IProductQueryShopify;
use App\Services\Shopify\Graphql\Queries\ProductQueryShopify;

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
            IOrderQueryShopify::class,
            OrderQueryShopify::class
        );

        $this->app->bind(
            IProductQueryShopify::class,
            ProductQueryShopify::class
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
