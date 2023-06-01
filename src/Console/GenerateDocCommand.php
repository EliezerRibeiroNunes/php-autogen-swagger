<?php

namespace AutoGen\Console;

use Illuminate\Console\Command;
use AutoGen\GenerateDoc;

class GenerateDocCommand extends Command
{
    protected $signature = 'gen-doc';
    protected $description = 'Gera a documentação automática do Swagger';

    public function handle()
    {
        $generateDoc = new GenerateDoc();
        $generateDoc->generate();

        $this->info('Documentação Swagger gerada com sucesso!');
    }
}
