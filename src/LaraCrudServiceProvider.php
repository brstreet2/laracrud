<?php

namespace Laracrud;

use Illuminate\Support\ServiceProvider;
use Laracrud\Console\ApiCrudCommand;

class LaraCrudServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            ApiCrudCommand::class,
        ]);
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/stubs' => base_path('stubs'),
        ], 'api-crud-stubs');
    }
}
