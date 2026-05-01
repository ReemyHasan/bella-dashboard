<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
       web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/',
    )
    ->withMiddleware(function (Middleware $middleware): void {
         $middleware->alias([
            'api.blocked' => App\Http\Middleware\RedirectIfBlocked::class,
            'permission' => App\Http\Middleware\CheckPermission::class,
            'user.type' => App\Http\Middleware\TypeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            $className = get_class($e);

            $handlers = App\Exceptions\ApiExceptionHandler::$handlers;

            if (array_key_exists($className, $handlers)) {
                $method = $handlers[$className];
                $apiHandler = new App\Exceptions\ApiExceptionHandler();
                return $apiHandler->$method($e, $request);
            }
            return response()->format(null, $e->getMessage(), 500, false);

        });
    })->create();
