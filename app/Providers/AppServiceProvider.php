<?php

namespace App\Providers;

use App\Models\BookIssue;
use App\Models\FeeCollection;
use App\Models\Mark;
use App\Observers\BookIssueObserver;
use App\Observers\FeeCollectionObserver;
use App\Observers\MarkObserver;
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
        FeeCollection::observe(FeeCollectionObserver::class);
        Mark::observe(MarkObserver::class);
        BookIssue::observe(BookIssueObserver::class);
    }
}
