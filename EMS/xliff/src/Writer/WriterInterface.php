<?php

declare(strict_types=1);

namespace EMS\Xliff\Writer;

use EMS\Xliff\Model\Package;

interface WriterInterface
{
    public function supportsVersion(string $version): bool;

    public function write(Package $package, string $encoding = 'UTF-8'): string;
}
