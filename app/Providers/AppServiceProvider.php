<?php

namespace App\Providers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Gate;
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

        $isHead = fn (User $user) => $user->role === 'head';
        foreach (['finance.manage', 'stock.manage', 'transaction.delete', 'class.freeze-price', 'attendance.record'] as $ability) {
            Gate::define($ability, $isHead);
        }
        // Allowlist: head always; admin only while the row is exactly 'Unpaid' (legacy 'lunas' and NULL rows stay locked).
        Gate::define('transaction.edit-paid', fn (User $user, Transaction $transaction) => $user->role === 'head'
            || ($user->role === 'admin' && $transaction->payment_status === 'Unpaid'));
    }
}
