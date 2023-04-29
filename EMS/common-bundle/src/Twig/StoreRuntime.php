<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Common\Store\StoreDataHelper;
use Twig\Extension\RuntimeExtensionInterface;

final class StoreRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
    }

    public function save(StoreDataHelper $data): void
    {
    }

    public function read(string $key): StoreDataHelper
    {
        return new StoreDataHelper();
    }
}
