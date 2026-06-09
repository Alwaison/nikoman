<?php

declare(strict_types=1);

use App\Domain\Member\Exceptions\DuplicateEmailException;
use App\Domain\Member\Exceptions\MemberNotFoundException;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (MemberNotFoundException $e, Request $request): JsonResponse {
            return response()->json(['message' => $e->getMessage()], 404);
        });

        $exceptions->render(function (DuplicateEmailException $e, Request $request): JsonResponse {
            return response()->json([
                'message' => 'The email has already been taken.',
                'errors' => ['email' => ['The email has already been taken.']],
            ], 422);
        });
    })->create();
