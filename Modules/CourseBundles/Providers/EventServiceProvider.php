<?php

namespace Modules\CourseBundles\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\CourseBundles\Models\Bundle;
use Modules\CourseBundles\Observers\BundleObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [];


    /**
     * The model observers for your application.
     *
     * @var array
     */
    protected $observers = [
        Bundle::class              => [BundleObserver::class],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void
    {
        //
    }
}
