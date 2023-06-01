<?php

namespace AutoGen;

use Illuminate\Support\ServiceProvider;
use AutoGen\Console\GenerateDocCommand;

class PackageServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            GenerateDocCommand::class,
        ]);
    }
}
