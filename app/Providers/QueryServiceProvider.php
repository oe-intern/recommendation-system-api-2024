<?php

namespace App\Providers;

use App\Storage\Queries\User as UserQuery;
use App\Contracts\Queries\User as IUserQuery;
use App\Storage\Queries\ShopQuery;
use App\Contracts\Queries\IShopQuery;
use App\Storage\Queries\ProductQuery;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IEventQuery;
use App\Storage\Queries\EventQuery;
use App\Contracts\Queries\IJobRecommendationQuery;
use App\Storage\Queries\JobRecommendationQuery;
use Illuminate\Support\ServiceProvider;

class QueryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            IUserQuery::class,
            UserQuery::class
        );

        $this->app->bind(
            IShopQuery::class,
            ShopQuery::class
        );

        $this->app->bind(
            IProductQuery::class,
            ProductQuery::class
        );

        $this->app->bind(
            IEventQuery::class,
            EventQuery::class
        );

        $this->app->bind(
            IJobRecommendationQuery::class,
            JobRecommendationQuery::class
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
