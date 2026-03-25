<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(!$this->app->isProduction());

        Response::macro('format', function ($data = null, $message = 'Success', $code = 200, $success = true) {
            return response()->json([
                'success' => $success,
                'message' => __($message),
                'data' => $data,
            ], $code);
        });
    }
}
