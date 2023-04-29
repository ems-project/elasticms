<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\StoreData;

class StoreDataManager
{
    public function save(StoreDataHelper $data): void
    {
    }

    public function read(string $key): StoreDataHelper
    {
        return new StoreDataHelper();
    }
}
