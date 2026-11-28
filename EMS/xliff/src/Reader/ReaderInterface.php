<?php

declare(strict_types=1);

namespace EMS\Xliff\Reader;

use EMS\Xliff\Model\Package;
use EMS\Xliff\Report\InsertReport;

interface ReaderInterface
{
    public function supports(string $xml): bool;

    public function read(string $xml, InsertReport $insertReport): Package;
}
