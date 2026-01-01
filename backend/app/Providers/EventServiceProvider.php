<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\ProductionOrderCompleted::class => [
            \App\Listeners\NotifyProductionOrderCompleted::class,
        ],
        \App\Events\LowStockAlert::class => [
            \App\Listeners\NotifyLowStock::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
