<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Service;

use EMS\CommonBundle\Contracts\ExpressionServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ExpressionService implements ExpressionServiceInterface
{
    private ?ExpressionLanguage $expressionLanguage = null;

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    #[\Override]
    public function evaluateToBool(string $expression, array $values = []): bool
    {
        $evaluate = $this->evaluate($expression, $values);

        return \is_bool($evaluate) ? $evaluate : false;
    }

    #[\Override]
    public function evaluateToString(string $expression, array $values = []): ?string
    {
        $evaluate = $this->evaluate($expression, $values);

        return \is_string($evaluate) ? $evaluate : null;
    }

    /**
     * @param array<mixed> $values
     */
    private function evaluate(string $expression, array $values = []): bool|string|null
    {
        try {
            return $this->getExpressionLanguage()->evaluate($expression, $values);
        } catch (\Throwable $e) {
            $this->logger->error('Expression failed: {message}', [
                'message' => $e->getMessage(),
                'values' => $values,
                'expression' => $expression,
                'noFlash' => true,
            ]);

            return null;
        }
    }

    private function getExpressionLanguage(): ExpressionLanguage
    {
        if (null === $this->expressionLanguage) {
            $this->expressionLanguage = $this->createExpressionLanguage();
        }

        return $this->expressionLanguage;
    }

    private function createExpressionLanguage(): ExpressionLanguage
    {
        $expressionLanguage = new ExpressionLanguage();
        $expressionLanguage->register(
            'date',
            fn ($date) => \sprintf('(new \DateTime(%s))', $date),
            fn (array $values, $date) => new \DateTime($date)
        );
        $expressionLanguage->register(
            'date_modify',
            fn ($date, $modify) => \sprintf('%s->modify(%s)', $date, $modify),
            function (array $values, $date, $modify) {
                if (!$date instanceof \DateTime) {
                    throw new \RuntimeException('date_modify() expects parameter 1 to be a Date');
                }

                return $date->modify($modify);
            }
        );
        $expressionLanguage->register(
            'substr',
            fn ($str, $offset, $length = null) => \sprintf('(is_string(%1$s) ? substr(%1$s, %2$d, %3$d) : %1$s)', $str, $offset, $length),
            function ($arguments, $str, $offset, $length = null) {
                if (!\is_string($str)) {
                    return $str;
                }

                return \substr($str, $offset, $length);
            }
        );

        return $expressionLanguage;
    }
}
