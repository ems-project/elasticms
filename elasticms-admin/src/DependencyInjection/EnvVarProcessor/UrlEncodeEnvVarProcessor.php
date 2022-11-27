<?php

declare(strict_types=1);

namespace App\Admin\DependencyInjection\EnvVarProcessor;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

class UrlEncodeEnvVarProcessor implements EnvVarProcessorInterface
{
    /**
     * @return mixed
     */
    public function getEnv(string $prefix, string $name, \Closure $getEnv)
    {
        $env = $getEnv($name);

        return \urlencode($env);
    }

    public static function getProvidedTypes(): array
    {
        return [
            'urlencode' => 'string',
        ];
    }
}
