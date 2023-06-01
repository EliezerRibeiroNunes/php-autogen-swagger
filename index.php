<?php 

require_once 'vendor/autoload.php';
use AutoGen\AutoGenSwagger;

$autoGen = new AutoGenSwagger;
$autoGen->handle();
