<?php

declare(strict_types=1);

namespace EMS\FormBundle\Submission;

abstract class AbstractHandler
{
    public function canHandle(string $class): bool
    {
        return $class === static::class;
    }

    abstract public function handle(HandleRequestInterface $handleRequest): HandleResponseInterface;
}
