<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts;

interface ExpressionServiceInterface
{
    /**
     * @param array<mixed> $values
     */
    public function evaluateToBool(string $expression, array $values = []): bool;

    /**
     * @param array<mixed> $values
     */
    public function evaluateToString(string $expression, array $values = []): ?string;
}
