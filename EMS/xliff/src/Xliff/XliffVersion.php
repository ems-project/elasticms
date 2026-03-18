<?php

declare(strict_types=1);

namespace EMS\Xliff\Xliff;

abstract class XliffVersion
{
    final public const string XLIFF_1_2 = '1.2';
    final public const string XLIFF_2_0 = '2.0';
    final public const array XLIFF_VERSIONS = [self::XLIFF_1_2, self::XLIFF_2_0];

    public function __construct(protected readonly string $xliffVersion)
    {
        if (!\in_array($xliffVersion, self::XLIFF_VERSIONS)) {
            throw new \RuntimeException(\sprintf('Unsupported XLIFF version "%s", use one of the supported one: %s', $xliffVersion, \implode(', ', self::XLIFF_VERSIONS)));
        }
    }

    protected function getResourceNameAttribute(): string
    {
        return \version_compare($this->xliffVersion, '2.0') < 0 ? 'resname' : 'name';
    }

    public function getVersion(): string
    {
        return $this->xliffVersion;
    }
}
