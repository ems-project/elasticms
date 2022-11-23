<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Request;

interface RequestInterface
{
    public function getScroll(): string;

    public function setSize(int $size): void;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
