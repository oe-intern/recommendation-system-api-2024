<?php

namespace App\Providers;

use App\Contracts\Commands\IShopRecommendationCommand;
use App\Contracts\Commands\IShopSettingCommand;
use App\Contracts\Mail\IEmailSender;
use App\Services\Mail\EmailSenderService;
use App\Storage\Commands\ShopRecommendationCommand;
use App\Storage\Commands\ShopSettingCommand;
use App\Storage\Commands\User as UserCommand;
use App\Contracts\Commands\User as IUserCommand;
use App\Storage\Commands\ShopCommand;
use App\Contracts\Commands\IShopCommand;
use App\Contracts\Commands\IProductCommand;
use App\Storage\Commands\ProductCommand;
use App\Storage\Commands\EventCommand;
use App\Contracts\Commands\IEventCommand;
use App\Contracts\Commands\IJobRecommendationCommand;
use App\Storage\Commands\JobRecommendationCommand;
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
            IEventCommand::class,
            EventCommand::class
        );

        $this->app->bind(
            IJobRecommendationCommand::class,
            JobRecommendationCommand::class
        );

        $this->app->bind(
            IShopSettingCommand::class,
            ShopSettingCommand::class
        );

        $this->app->bind(
            IShopRecommendationCommand::class,
            ShopRecommendationCommand::class
        );

        $this->app->bind(
            IEmailSender::class,
            EmailSenderService::class
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
