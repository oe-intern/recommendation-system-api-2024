<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\ResponseFactory;

class ResponseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param ResponseFactory $factory
     * @return void
     */
    public function boot(ResponseFactory $factory): void
    {
        $factory->macro('success', function ($message = '', $data = null, $status_code = 200) use ($factory) {
            $format = [
                'status' => 'ok',
                'message' => $message,
                'data' => $data,
            ];

            return $factory->make($format, $status_code);
        });

        $factory->macro('error', function (string $message = '', $errors = [], $status_code = 400) use ($factory) {
            $format = [
                'status' => 'error',
                'message' => $message,
                'errors' => $errors,
            ];

            return $factory->make($format, $status_code);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
