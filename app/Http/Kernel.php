<?php

namespace Masso\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Masso\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Masso\Http\Middleware\Language::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Masso\Http\Middleware\VerifyCsrfToken::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Masso\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'guest' => \Masso\Http\Middleware\RedirectIfAuthenticated::class,
        'rbac' => \Masso\Http\Middleware\Rbac::class,
        'is_admin' => \Masso\Http\Middleware\IsAdmin::class
    ];
}
