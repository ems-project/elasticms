<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Contracts\Elasticsearch;

interface ClientRequestManagerInterface
{
    public function getDefault(): ClientRequestInterface;
}
