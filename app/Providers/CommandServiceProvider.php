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
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
