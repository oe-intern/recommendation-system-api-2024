<?php

namespace App\Providers;

use App\Contracts\Queries\User;
use App\Jobs\ExportCsv;
use App\Lib\DbSessionStorage;
use App\Lib\Handlers\AppUninstalled;
use App\Lib\Handlers\Privacy\CustomersDataRequest;
use App\Lib\Handlers\Privacy\CustomersRedact;
use App\Lib\Handlers\Privacy\ShopRedact;
use App\Lib\Handlers\OrderCreate;
use App\Lib\Handlers\ProductCreate;
use App\Lib\Handlers\ProductDelete;
use App\Lib\Handlers\ProductUpdate;
use App\Lib\Handlers\ShopUpdate;
use App\Models\Export;
use App\Policies\ActivityPolicy;
use App\Services\Shopify\UserContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Shopify\ApiVersion;
use Shopify\Context;
use Shopify\Webhooks\Registry;
use Shopify\Webhooks\Topics;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(UserContext::class, function ($app) {
            return new UserContext($app->make(User::class));
        });

        $this->app->bind(
            \Filament\Actions\Exports\Models\Export::class,
            Export::class,
        );

        $this->app->bind(
            \Filament\Actions\Exports\Jobs\ExportCsv::class,
            ExportCsv::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $host = str_replace('https://', '', config('app.embedded_url'));

        $customDomain = env('SHOP_CUSTOM_DOMAIN', null);
        Context::initialize(
            config('shopify-app.api_key'),
            config('shopify-app.api_secret'),
            config('shopify-app.api_scopes'),
            $host,
            new DbSessionStorage,
            ApiVersion::LATEST,
            true,
            false,
            null,
            '',
            null,
            (array)$customDomain,
        );

        URL::forceScheme('https');

        Registry::addHandler(Topics::APP_UNINSTALLED, app(AppUninstalled::class));
        Registry::addHandler(Topics::SHOP_UPDATE, app(ShopUpdate::class));
        Registry::addHandler(Topics::PRODUCTS_CREATE, app(ProductCreate::class));
        Registry::addHandler(Topics::PRODUCTS_UPDATE, app(ProductUpdate::class));
        Registry::addHandler(Topics::PRODUCTS_DELETE, app(ProductDelete::class));
        Registry::addHandler(Topics::ORDERS_CREATE, app(OrderCreate::class));
        /*
         * This sets up the mandatory privacy webhooks. You’ll need to fill in the endpoint to be used by your app in
         * the “Privacy webhooks” section in the “App setup” tab, and customize the code when you store customer data
         * in the handlers being registered below.
         *
         * More details can be found on shopify.dev:
         * https://shopify.dev/docs/apps/webhooks/configuration/mandatory-webhooks
         *
         * Note that you'll only receive these webhooks if your app has the relevant scopes as detailed in the docs.
         */
        Registry::addHandler('CUSTOMERS_DATA_REQUEST', new CustomersDataRequest());
        Registry::addHandler('CUSTOMERS_REDACT', new CustomersRedact());
        Registry::addHandler('SHOP_REDACT', new ShopRedact());

        // Register policies
        Gate::policy(Activity::class, ActivityPolicy::class);
    }
}
