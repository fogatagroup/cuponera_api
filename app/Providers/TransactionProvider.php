<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TransactionProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        \App\Transaction::observe(\App\Observers\TransactionObserver::class);
    }
}
