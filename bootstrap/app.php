<?php

use App\Http\Middleware\BindCurrentMerchant;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ThrottlePublicRegistration;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Trust Caddy reverse proxy headers (X-Forwarded-Proto, X-Forwarded-For)
        $middleware->trustProxies(at: '*');

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            BindCurrentMerchant::class,
            ThrottlePublicRegistration::class,
        ]);

        $middleware->prependToPriorityList(
            SubstituteBindings::class,
            BindCurrentMerchant::class,
        );

        $middleware->preventRequestForgery(except: [
            'webhooks/whatsapp',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (Throwable $exception, Request $request) {
            $isThrottled = $exception instanceof HttpExceptionInterface
                && $exception->getStatusCode() === 429;

            if ($isThrottled && $request->header('X-Inertia')) {
                Inertia::flash('toast', [
                    'type' => 'error',
                    'message' => 'Trop de tentatives. Merci de patienter un instant avant de réessayer.',
                ]);

                return back();
            }
        });
    })->create();
