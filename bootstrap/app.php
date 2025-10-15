<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            $modulesPath = app_path('Modules');

            if (File::isDirectory($modulesPath)) {
                foreach (File::directories($modulesPath) as $module) {
                    $routesPath = $module.'/Routes/api.php';

                    if (File::exists($routesPath)) {
                        Route::middleware('api')
                            ->prefix('api')
                            ->group(function () use ($routesPath) {
                                require $routesPath;
                            });
                    }
                }
            }
        },
    )

    ->withMiddleware(function (Middleware $middleware): void {

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
