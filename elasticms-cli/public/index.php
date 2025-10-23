<?php

use App\CLI\Kernel;
use Symfony\Component\HttpFoundation\Request;

file_exists(dirname(__DIR__).'/vendor/autoload_runtime.php') ?
    require_once dirname(__DIR__).'/vendor/autoload_runtime.php' :
    require_once dirname(__DIR__).'/../vendor/autoload_runtime.php';

$_SERVER['APP_ENV'] ??= 'prod';
$_SERVER['APP_RUNTIME_OPTIONS'] = [
    'disable_dotenv' => ('true' === ($_SERVER['APP_DISABLE_DOTENV'] ?? false)),
    'prod_envs' => ['prod', 'redis', 'store_data'],
    'project_dir' => dirname(__DIR__),
];

return function (Request $request, array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
