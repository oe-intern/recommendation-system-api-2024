<?php

namespace App\Providers;

use App\Storage\Commands\User as UserCommand;
use App\Contracts\Commands\User as IUserCommand;
use App\Storage\Commands\ShopCommand;
use App\Contracts\Commands\IShopCommand;
use App\Contracts\Commands\IProductCommand;
use App\Storage\Commands\ProductCommand;
use App\Storage\Commands\InteractionProductCommand;
use App\Contracts\Commands\IInteractionProductCommand;
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
            IInteractionProductCommand::class,
            InteractionProductCommand::class
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
