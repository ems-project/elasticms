<?php

declare(strict_types=1);

namespace EMS\CommonBundle\DependencyInjection\EnvVarProcessor;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

class UrlEncodeEnvVarProcessor implements EnvVarProcessorInterface
{
    #[\Override]
    public function getEnv($prefix, $name, \Closure $getEnv): string
    {
        $env = $getEnv($name);

        return \urlencode((string) $env);
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public static function getProvidedTypes(): array
    {
        return [
            'urlencode' => 'string',
        ];
    }
}
