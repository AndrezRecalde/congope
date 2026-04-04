<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    return \App\Helpers\ApiResponse::notFound();
                }
                if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    return \App\Helpers\ApiResponse::forbidden();
                }
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return \App\Helpers\ApiResponse::error($e->getMessage(), $e->errors());
                }
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return \App\Helpers\ApiResponse::unauthorized();
                }
                
                $message = config('app.debug') ? $e->getMessage() : 'Error interno del servidor';
                return \App\Helpers\ApiResponse::serverError($message);
            }
        });
    })->create();
