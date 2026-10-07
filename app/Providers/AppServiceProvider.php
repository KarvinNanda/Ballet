<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        // Since Laravel 11 a session ended by a password change answers 401 unless a redirect is set.
        AuthenticateSession::redirectUsing(fn () => route('login'));
    }
}
