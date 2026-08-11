<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        RateLimiter::for('landing', function (Request $request): array {
            $key = 'landing:'.($request->user()?->getAuthIdentifier() ?? $request->ip() ?? 'unknown');

            return [
                Limit::perMinute(60)->by($key.':minute'),
                Limit::perHour(600)->by($key.':hour'),
            ];
        });

        RateLimiter::for('oauth', fn (Request $request): Limit => Limit::perMinute(10)
            ->by('oauth:'.($request->ip() ?? 'unknown')));

        RateLimiter::for('availability', fn (Request $request): Limit => Limit::perMinute(90)
            ->by('availability:'.($request->user()?->getAuthIdentifier() ?? $request->ip() ?? 'unknown')));

        RateLimiter::for('booking', function (Request $request): array {
            $key = 'booking:'.($request->user()?->getAuthIdentifier() ?? $request->ip() ?? 'unknown');

            return [
                Limit::perMinute(5)->by($key.':minute'),
                Limit::perHour(20)->by($key.':hour'),
            ];
        });

        RateLimiter::for('cancellation', fn (Request $request): Limit => Limit::perMinute(10)
            ->by('cancellation:'.($request->user()?->getAuthIdentifier() ?? $request->ip() ?? 'unknown')));
    }
}
