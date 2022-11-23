<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Log;

use Psr\Log\LoggerInterface;

interface LocalizedLoggerFactoryInterface
{
    public function __invoke(LoggerInterface $logger, string $translationDomain): LocalizedLoggerInterface;
}
