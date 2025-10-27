<?php

declare(strict_types=1);

namespace EMS\Helpers\Env;

use EMS\Helpers\File\File;
use EMS\Helpers\Standard\Base64;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Text;

final readonly class RuntimeEnvPlaceholderResolver
{
    public function __construct(
        private bool $throwExceptionOnMissing = true,
    ) {
    }

    public function resolve(string $input): string
    {
        return (string) \preg_replace_callback(
            '/%env\(([^)]+)\)%/',
            fn (array $m) => $this->resolveOne($m[1]),
            $input
        );
    }

    private function resolveOne(string $expr): string
    {
        $parts = \explode(':', $expr);
        if (\count($parts) < 1) {
            return $this->handleMissing($expr, 'Invalid env expression syntax');
        }

        $varName = \array_pop($parts);
        $processors = $parts;

        $value = $this->getEnv($varName);
        if (null === $value) {
            return $this->handleMissing($varName, 'Environment variable not found');
        }

        foreach (\array_reverse($processors) as $proc) {
            $value = $this->applyProcessor($proc, $value, $varName);
        }

        return \is_scalar($value)
            ? (string) $value
            : Json::encode($value);
    }

    private function handleMissing(string $name, string $reason): string
    {
        if ($this->throwExceptionOnMissing) {
            throw new \RuntimeException(\sprintf('[env:%s] %s', $name, $reason));
        }

        return \sprintf('%%env(%s)%%', $name);
    }

    private function getEnv(string $name): ?string
    {
        return $_ENV[$name]
            ?? $_SERVER[$name]
            ?? (false !== \getenv($name) ? \getenv($name) : null);
    }

    private function applyProcessor(string $proc, mixed $value, string $varName): mixed
    {
        $proc = \strtolower(\trim($proc));

        return match ($proc) {
            'string' => (string) $value,
            'int', 'integer' => (int) $value,
            'float', 'double' => (float) $value,
            'bool', 'boolean' => (bool) \filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'trim' => \trim((string) $value),
            'super_trim' => Text::superTrim((string) $value),
            'base64', 'base64decode' => Base64::decode((string) $value),
            'base64encode' => Base64::encode((string) $value),
            'json', 'json_decode' => Json::decode((string) $value),
            'json_encode' => Json::encode((string) $value),
            'urlencode', 'url_encode' => \rawurlencode((string) $value),
            'urldecode', 'url_decode' => \rawurldecode((string) $value),
            'file' => File::fromFilename((string) $value)->getContents(),
            default => throw new \RuntimeException(\sprintf('Unknown env processor "%s" for variable %s', $proc, $varName)),
        };
    }
}
