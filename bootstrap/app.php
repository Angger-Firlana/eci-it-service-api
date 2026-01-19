<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use App\Helpers\APIResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\ForbiddenException;
use Symfony\Component\HttpKernel\Exception\ForbiddenHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\QueryException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {

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

        // 500 - DB
        $exceptions->render(function (QueryException $e) {
            return APIResponse::error(null, 500, 'Database Error');
        });

        // 500 - fallback (WAJIB PALING BAWAH)
        $exceptions->render(function (Throwable $e) {
            return APIResponse::error(null, 500, 'Internal Server Error');
        });

    })->create();
