<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Elasticsearch;

interface QueryLoggerInterface
{
    public function isEnabled(): bool;

    public function disable(): void;

    public function enable(): void;
}
