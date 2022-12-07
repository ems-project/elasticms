<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Config;

interface ConfigFactoryInterface
{
    /** @param array<mixed> $options */
    public function create(array $options): ConfigInterface;
}
