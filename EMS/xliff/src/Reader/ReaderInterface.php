<?php

declare(strict_types=1);

namespace EMS\Xliff\Reader;

use EMS\Xliff\Model\Package;

interface ReaderInterface
{
    public function supports(string $xml): bool;

    public function read(string $xml): Package;
}
