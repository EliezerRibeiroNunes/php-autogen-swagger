<?php

namespace AutoGen\Commands;

use Illuminate\Console\Command;
use AutoGen\GenerateDoc;

class GenerateDocCommand extends Command
{
    protected $signature = 'gen-swagger-doc';
    protected $description = 'Generate Swagger documentation';

    public function handle()
    {
        $generateDoc = new GenerateDoc();
        $generateDoc->generate();

    }
}
