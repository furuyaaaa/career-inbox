<?php

namespace App\Providers;

use App\Services\JobMatchScorer;
use App\Services\MatchingService;
use App\Services\PythonMatchingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MatchingService::class, function (): MatchingService {
            return config('matching.driver') === 'python'
                ? new PythonMatchingService()
                : new JobMatchScorer();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
