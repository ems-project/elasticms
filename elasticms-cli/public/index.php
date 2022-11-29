<?php

use App\CLI\Kernel;

file_exists(dirname(__DIR__).'/vendor/autoload_runtime.php') ?
    require_once dirname(__DIR__).'/vendor/autoload_runtime.php' :
    require_once dirname(__DIR__).'/../vendor/autoload_runtime.php';

return fn(array $context) => new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
