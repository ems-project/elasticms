<?php

namespace EMS\CommonBundle\DependencyInjection\EnvVarProcessor;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

class UrlEncodeEnvVarProcessor implements EnvVarProcessorInterface
{
    public function getEnv($prefix, $name, \Closure $getEnv): string
    {
        $env = $getEnv($name);

        return \urlencode($env);
    }

    /**
     * @return array<string, string>
     */
    public static function getProvidedTypes(): array
    {
        return [
            'urlencode' => 'string',
        ];
    }
}
