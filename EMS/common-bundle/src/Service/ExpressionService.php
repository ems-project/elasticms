<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Service;

use EMS\CommonBundle\Contracts\ExpressionServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ExpressionService implements ExpressionServiceInterface
{
    /** @var ExpressionLanguage|null */
    private $expressionLanguage;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->expressionLanguage = null;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluateToBool(string $expression, array $values = []): bool
    {
        try {
            $evaluation = $this->getExpressionLanguage()->evaluate($expression, $values);

            if (!\is_bool($evaluation)) {
                throw new \Exception('Expression did not evaluate to bool!');
            }

            return $evaluation;
        } catch (\Exception $e) {
            $this->logger->error('Expression failed: {message}', [
                'message' => $e->getMessage(),
                'values' => $values,
                'expression' => $expression,
            ]);

            return false;
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
            function ($date) {
                return \sprintf('(new \DateTime(%s))', $date);
            },
            function (array $values, $date) {
                return new \DateTime($date);
            }
        );
        $expressionLanguage->register(
            'date_modify',
            function ($date, $modify) {
                return \sprintf('%s->modify(%s)', $date, $modify);
            },
            function (array $values, $date, $modify) {
                if (!$date instanceof \DateTime) {
                    throw new \RuntimeException('date_modify() expects parameter 1 to be a Date');
                }

                return $date->modify($modify);
            }
        );

        return $expressionLanguage;
    }
}
