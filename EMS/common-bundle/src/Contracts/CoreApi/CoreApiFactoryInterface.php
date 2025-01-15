<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi;

interface CoreApiFactoryInterface
{
    public function create(?string $baseUrl = null): CoreApiInterface;
}
