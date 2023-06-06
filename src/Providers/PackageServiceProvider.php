<?php

namespace AutoGen\Providers;

use Illuminate\Support\ServiceProvider;
use AutoGen\Commands\GenerateDocCommand;
use SeuPacote\Console\Commands\SetPathCommand;

class PackageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateDocCommand::class,
                SetPathCommand::class
            ]);
        }
    }
}
