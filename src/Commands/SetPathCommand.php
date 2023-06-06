<?php

namespace AutoGen\Commands;

use AutoGen\GenerateDoc;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class SetPathCommand extends Command
{
    protected $signature = 'autogen-doc:setpath {--path=}';

    public function handle()
    {
        $path = $this->option('path');
        
        $generateDoc = new GenerateDoc();
        $generateDoc->setPath($path);
        $this->info("Path: $path was defined sucessfully!" );
    }
}
