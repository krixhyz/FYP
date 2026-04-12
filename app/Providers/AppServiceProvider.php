<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Review;
use App\Models\Dispute;
use App\Observers\ReviewObserver;
use App\Observers\DisputeObserver;

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
        Review::observe(ReviewObserver::class);
        Dispute::observe(DisputeObserver::class);
    }
}
