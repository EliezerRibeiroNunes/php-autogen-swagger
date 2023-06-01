<?php

namespace AutoGen;

use Illuminate\Support\ServiceProvider;
use AutoGen\Commands\GenerateDocCommand;

class PackageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateDocCommand::class,
            ]);
        }
    }
}
