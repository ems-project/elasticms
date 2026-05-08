<?php

declare(strict_types=1);

namespace EMS\Xliff;

final readonly class Version
{
    public const string V12 = '1.2';
    public const string V12_VERSION = 'version="1.2"';
    public const string V12_NAMESPACE = 'urn:oasis:names:tc:xliff:document:1.2';
    public const string V22 = '2.2';
    public const string V22_VERSION = 'version="2.2"';
    public const string V22_NAMESPACE = 'urn:oasis:names:tc:xliff:document:2.2';

    /** @var string[] */
    public const array ALL = [self::V12, self::V22];
}
