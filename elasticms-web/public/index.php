<?php

use App\Web\Kernel;
use Symfony\Component\HttpFoundation\Request;

file_exists(dirname(__DIR__).'/vendor/autoload_runtime.php') ?
    require_once dirname(__DIR__).'/vendor/autoload_runtime.php' :
    require_once dirname(__DIR__).'/../vendor/autoload_runtime.php';

$_SERVER['APP_RUNTIME_OPTIONS'] = [
    'disable_dotenv' => ('true' === ($_SERVER['APP_DISABLE_DOTENV'] ?? false)),
    'prod_envs' => ['prod', 'redis', 'store_data'],
    'project_dir' => dirname(__DIR__),
];

return function (Request $request, array $context) {
    if ($trustedProxies = $context['TRUSTED_PROXIES'] ?? false) {
        foreach (['PROTO', 'PORT', 'FOR', 'HOST'] as $forwardedName) {
            $customHeader = $context['HTTP_CUSTOM_FORWARDED_'.$forwardedName] ?? null;
            if (\is_string($customHeader) && null !== ($context[$customHeader] ?? null)) {
                $_SERVER['HTTP_X_FORWARDED_'.$forwardedName] = $context[$customHeader];
            }
        }

        Request::setTrustedProxies(explode(',', $trustedProxies),
            Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PROTO
            | Request::HEADER_X_FORWARDED_PORT);
    }

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
