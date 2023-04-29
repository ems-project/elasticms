<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\StoreData\Service;

use EMS\CommonBundle\Common\StoreData\StoreDataHelper;

interface StoreDataServiceInterface
{
    public function save(StoreDataHelper $data): void;

    public function read(string $key): ?StoreDataHelper;
}
