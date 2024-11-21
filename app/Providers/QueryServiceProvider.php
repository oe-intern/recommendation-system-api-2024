<?php

namespace App\Providers;

use App\Storage\Queries\User as UserQuery;
use App\Contracts\Queries\User as IUserQuery;
use App\Storage\Queries\Shop as ShopQuery;
use App\Contracts\Queries\Shop as IShopQuery;
use App\Storage\Queries\Product as ProductQuery;
use App\Contracts\Queries\Product as IProductQuery;
use App\Contracts\Queries\RelationshipScore as IRelationshipScoreQuery;
use App\Storage\Queries\RelationshipScore as RelationshipScoreQuery;
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
            IRelationshipScoreQuery::class,
            RelationshipScoreQuery::class
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
