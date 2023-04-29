<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\StoreData\Factory;

use EMS\CommonBundle\Common\StoreData\Service\StoreDataServiceInterface;

interface StoreDataFactoryInterface
{
    public const CONFIG_TYPE = 'type';

    public function getType(): string;

    /**
     * @param mixed[] $parameters
     */
    public function createService(array $parameters): StoreDataServiceInterface;
}
