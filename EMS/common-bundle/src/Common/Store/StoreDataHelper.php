<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Store;

class StoreDataHelper
{
    public function get(string $property): mixed
    {
        return 'Hello';
    }

    public function set(string $property, mixed $data): void
    {
    }
}
