<?php

namespace Edujugon\TableDiff\Providers;

use Edujugon\TableDiff\TableDiff;
use Illuminate\Support\ServiceProvider;

class TableDiffServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(){}

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(TableDiff::class, function ($app) {
            return new TableDiff();
        });
    }
}
