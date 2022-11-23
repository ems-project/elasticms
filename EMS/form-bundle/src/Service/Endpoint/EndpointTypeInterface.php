<?php

declare(strict_types=1);

namespace EMS\FormBundle\Service\Endpoint;

interface EndpointTypeInterface
{
    public function canExecute(EndpointInterface $endpoint): bool;
}
