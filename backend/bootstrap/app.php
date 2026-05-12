<?php

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn ($request, $throwable) => $request->is('api/*'));

        $exceptions->render(function (Throwable $throwable, $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($throwable instanceof ValidationException) {
                return response()->json([
                    'message' => $throwable->getMessage() ?: 'The given data was invalid.',
                    'status' => 422,
                    'code' => 'validation_error',
                    'errors' => $throwable->errors(),
                ], 422);
            }

            if ($throwable instanceof AuthenticationException) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'status' => 401,
                    'code' => 'unauthenticated',
                    'errors' => [],
                ], 401);
            }

            if ($throwable instanceof AuthorizationException) {
                return response()->json([
                    'message' => $throwable->getMessage() ?: 'This action is unauthorized.',
                    'status' => 403,
                    'code' => 'forbidden',
                    'errors' => [],
                ], 403);
            }

            $status = $throwable instanceof HttpExceptionInterface
                ? $throwable->getStatusCode()
                : 500;

            $message = $status >= 500
                ? 'Server error.'
                : ($throwable->getMessage() ?: 'Request failed.');

            return response()->json([
                'message' => $message,
                'status' => $status,
                'code' => $status >= 500 ? 'server_error' : 'http_error',
                'errors' => [],
            ], $status);
        });
    })->create();
