<?php

namespace AutoGen\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class SetPathCommand extends Command
{
    protected $signature = 'autogen-doc:setpath {path}';

    public function handle()
    {
        $path = $this->argument('path');

        Config::set('autogen-doc.set-path', $path);
        $this->info('Path was defined sucessfully !');
    }
}
