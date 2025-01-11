<?php

declare(strict_types=1);

namespace App\Admin\DependencyInjection\EnvVarProcessor;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

class UrlEncodeEnvVarProcessor implements EnvVarProcessorInterface
{
    #[\Override]
    public function getEnv(string $prefix, string $name, \Closure $getEnv): string
    {
        $env = $getEnv($name);

        return \urlencode((string) $env);
    }

    #[\Override]
    public static function getProvidedTypes(): array
    {
        return [
            'urlencode' => 'string',
        ];
    }
}
