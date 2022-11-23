<?php

declare(strict_types=1);

namespace EMS\FormBundle\Contracts;

use EMS\FormBundle\Service\Endpoint\EndpointInterface;

interface EndpointManagerInterface
{
    public function getEndpointByFieldName(string $fieldName): EndpointInterface;
}
