<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:[
            __DIR__.'/../routes/web.php',
            __DIR__.'/../routes/auth.php',
            __DIR__.'/../routes/images.php',
        ],
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
        then:function (){
            Route::prefix('api/v2')->group(function () {
                require __DIR__.'/../routes/api_v2.php';
            });
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // if request is to api, always accept json else return normal response
        // if (str_starts_with(request()->path(), 'api/')) {
        //     $middleware->prepend(\App\Http\Middleware\AlwaysAcceptJson::class);
        // }

    })
    ->withExceptions(function (Exceptions $exceptions): void {
          $exceptions->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Object not found'], 404);
            }
        });
    })->create();
