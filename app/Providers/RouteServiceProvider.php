<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Writes only: GET/HEAD pages are not limited. Per user, or per IP for guests.
        RateLimiter::for('writes', function (Request $request) {
            return in_array($request->method(), ['GET', 'HEAD'], true)
                ? Limit::none()
                : Limit::perMinute(30)->by('writes:'.($request->user()?->id ?: $request->ip()));
        });

        // Creating an account sends an email.
        RateLimiter::for('account-create', fn (Request $request) => Limit::perHour(10)->by('account-create:'.($request->user()?->id ?: $request->ip())));
    }
}
