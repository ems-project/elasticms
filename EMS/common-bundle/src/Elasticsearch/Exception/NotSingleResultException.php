<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Exception;

class NotSingleResultException extends \Exception
{
    private int $total;

    public function __construct(int $total)
    {
        $this->total = $total;
        parent::__construct(\sprintf('Not single result exception: 1 result was expected, got %d', $total));
    }

    public function getTotal(): int
    {
        return $this->total;
    }
}
