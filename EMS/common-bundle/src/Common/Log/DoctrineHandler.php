<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Log;

use Doctrine\Persistence\ManagerRegistry;
use EMS\CommonBundle\Repository\LogRepository;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DoctrineHandler extends AbstractProcessingHandler
{
    private ?LogRepository $logRepository = null;

    private const string SECRET_VALUE = '***';
    private const array SECRET_KEYS = ['api_key'];

    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly int $minLevel
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function write(LogRecord $record): void
    {
        $logArray = $record->toArray();
        if ($logArray['level'] < $this->minLevel) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        $logArray['username'] = $token instanceof TokenInterface ? $token->getUserIdentifier() : null;
        $logArray['impersonator'] = $token instanceof SwitchUserToken ? $token->getOriginalToken()->getUserIdentifier() : null;
        $logArray['formatted'] = $record->formatted ?? $record->message;
        $logArray['context'] = DoctrineHandler::secretContext($logArray['context']);

        $this->getLogRepository()->insertRecord($logArray);
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

    private function getLogRepository(): LogRepository
    {
        if (null === $this->logRepository) {
            /** @var LogRepository $logRepository */
            $logRepository = $this->doctrine->getManager()->getRepository(LogRepository::class);
            $this->logRepository = $logRepository;
        }

        return $this->logRepository;
    }
}
