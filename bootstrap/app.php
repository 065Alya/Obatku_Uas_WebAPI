<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\RoleMiddleware;
use App\Providers\OpenFdaServiceProvider;
use App\Providers\PushNotificationServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        OpenFdaServiceProvider::class,
        PushNotificationServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'     => RoleMiddleware::class,
            'active'   => EnsureUserIsActive::class,
            'api.key'  => \App\Http\Middleware\VerifyApiKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $status = $status === 0 ? 500 : $status;
                
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    $status = 401;
                } elseif ($e instanceof \Illuminate\Validation\ValidationException) {
                    $status = 422;
                } elseif ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    $status = 404;
                }

                $response = [
                    'status' => 'Error',
                    'message' => $e->getMessage() ?: 'Internal Server Error',
                ];

                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $response['errors'] = $e->errors();
                }

                if (config('app.debug')) {
                    $response['trace'] = $e->getTrace();
                }

                return response()->json($response, $status);
            }
        });
    })->create();
