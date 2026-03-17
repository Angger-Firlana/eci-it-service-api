<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use App\Helpers\APIResponse;
use App\Exceptions\ApiException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\ForbiddenHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'role' => \App\Http\Middleware\UserMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (ApiException $e) {
            return APIResponse::error(
                $e->getErrors(),
                $e->getStatusCode(),
                $e->getMessage(),
                $e->getErrorCode()
            );
        });

        // 404 (Model & Route)
        $exceptions->render(function (
            ModelNotFoundException|NotFoundHttpException $e
        ) {
            return APIResponse::error(null, 404, 'Data Not Found');
        });

        // 405
        $exceptions->render(function (MethodNotAllowedHttpException $e) {
            return APIResponse::error(null, 405, 'Method Not Allowed');
        });

        // 401
        $exceptions->render(function (UnauthorizedHttpException $e) {
            return APIResponse::error(null, 401, 'Unauthorized');
        });

        // 401 - auth middleware
        $exceptions->render(function (AuthenticationException $e) {
            return APIResponse::error(null, 401, 'Unauthenticated');
        });

        // 403
        $exceptions->render(function (ForbiddenHttpException $e) {
            return APIResponse::error(null, 403, 'Forbidden');
        });

        // 422
        $exceptions->render(function (ValidationException $e) {
            return APIResponse::error(
                $e->errors(),
                422,
                'Validation Error'
            );
        });

        // 400
        $exceptions->render(function (InvalidArgumentException $e) {
            return APIResponse::error(null, 400, $e->getMessage());
        });

        // abort(4xx/5xx) + other HTTP exceptions
        $exceptions->render(function (HttpException $e) {
            if ($e->getStatusCode() >= 500) {
                Log::error('HTTP Exception (5xx)', ['exception' => $e]);
                return APIResponse::error(null, $e->getStatusCode(), 'Internal Server Error');
            }

            $message = trim((string) $e->getMessage());
            if ($message === '') {
                $message = match ($e->getStatusCode()) {
                    400 => 'Bad Request',
                    401 => 'Unauthorized',
                    403 => 'Forbidden',
                    404 => 'Data Not Found',
                    405 => 'Method Not Allowed',
                    409 => 'Conflict',
                    422 => 'Validation Error',
                    429 => 'Too Many Requests',
                    default => 'Error',
                };
            }

            return APIResponse::error(null, $e->getStatusCode(), $message);
        });

        // 500 - DB
        $exceptions->render(function (QueryException $e) {
            Log::error('Database Error', ['exception' => $e]);
            return APIResponse::error(null, 500, 'Database Error');
        });

        // 500 - fallback (WAJIB PALING BAWAH)
        $exceptions->render(function (Throwable $e) {
            Log::error('Internal Server Error', ['exception' => $e]);
            return APIResponse::error(null, 500, 'Internal Server Error');
        });

    })->create();
