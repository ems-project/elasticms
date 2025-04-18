<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Exception;

class RunnerNotFoundException extends \Exception
{
    public function __construct(private readonly string $tag)
    {
        parent::__construct(\sprintf('Runner for tag "%s" not found', $tag));
    }

    public function getTag(): string
    {
        return $this->tag;
    }
}
