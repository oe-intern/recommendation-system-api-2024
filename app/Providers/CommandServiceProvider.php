<?php

namespace App\Providers;

use App\Storage\Commands\User as UserCommand;
use App\Contracts\Commands\User as IUserCommand;
use App\Storage\Commands\Shop as ShopCommand;
use App\Contracts\Commands\Shop as IShopCommand;
use App\Contracts\Commands\Product as IProductCommand;
use App\Storage\Commands\Product as ProductCommand;
use App\Contracts\Commands\Order as IOrderCommand;
use App\Storage\Commands\Order as OrderCommand;
use App\Contracts\Commands\RelationshipScore as IRelationshipScoreCommand;
use App\Storage\Commands\RelationshipScore as RelationshipScoreCommand;
use App\Storage\Commands\InteractionProduct as InteractionProductCommand;
use App\Contracts\Commands\InteractionProduct as IInteractionProductCommand;
use App\Contracts\Commands\OrderTypeQuantity as IOrderTypeQuantityCommand;
use App\Storage\Commands\OrderTypeQuantity as OrderTypeQuantityCommand;
use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            IUserCommand::class,
            UserCommand::class
        );

        $this->app->bind(
            IShopCommand::class,
            ShopCommand::class
        );

        $this->app->bind(
            IProductCommand::class,
            ProductCommand::class
        );

        $this->app->bind(
            IOrderCommand::class,
            OrderCommand::class
        );

        $this->app->bind(
            IRelationshipScoreCommand::class,
            RelationshipScoreCommand::class
        );

        $this->app->bind(
            IInteractionProductCommand::class,
            InteractionProductCommand::class
        );

        $this->app->bind(
            IOrderTypeQuantityCommand::class,
            OrderTypeQuantityCommand::class
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
