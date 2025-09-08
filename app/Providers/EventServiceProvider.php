<?php

namespace App\Providers;

use App\Models\OpeningStock;
use App\Events\JournalEntryPosted;
use App\Observers\OpeningStockObserver;
use App\Listeners\CreateAccountTransactions;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        JournalEntryPosted::class => [
            CreateAccountTransactions::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        OpeningStock::observe(OpeningStockObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
