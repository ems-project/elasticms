<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Log;

use EMS\CommonBundle\Repository\LogRepository;
use Monolog\Handler\AbstractProcessingHandler;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DoctrineHandler extends AbstractProcessingHandler
{
    private LogRepository $logRepository;
    private TokenStorageInterface $tokenStorage;
    private int $minLevel;

    private const SECRET_VALUE = '***';
    private const SECRET_KEYS = ['api_key'];

    public function __construct(LogRepository $logRepository, TokenStorageInterface $tokenStorage, int $minLevel)
    {
        parent::__construct();

        $this->logRepository = $logRepository;
        $this->tokenStorage = $tokenStorage;
        $this->minLevel = $minLevel;
    }

    /**
     * @param array{message: string, level: int, level_name: string, context: array<mixed>, channel: string, formatted: string, datetime: \DateTimeImmutable, extra: array<mixed>} $record
     */
    protected function write(array $record): void
    {
        if ($record['level'] < $this->minLevel) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        $record['username'] = $token instanceof TokenInterface ? $token->getUsername() : null;
        $record['impersonator'] = $token instanceof SwitchUserToken ? $token->getOriginalToken()->getUsername() : null;

        $record['context'] = DoctrineHandler::secretContext($record['context']);

        $this->logRepository->insertRecord($record);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    private static function secretContext(array $context): array
    {
        $contextKeys = \array_keys($context);
        $secretKeys = \array_filter($contextKeys, fn ($key) => \in_array($key, self::SECRET_KEYS));

        foreach ($secretKeys as $secretKey) {
            $context[$secretKey] = self::SECRET_VALUE;
        }

        return $context;
    }
}
