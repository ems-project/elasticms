<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Log;

use EMS\CommonBundle\Common\Log\DoctrineHandler;
use EMS\CommonBundle\Repository\LogRepository;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DoctrineHandlerAiTest extends TestCase
{
    private DoctrineHandler $doctrineHandler;
    private LogRepository $logRepository;
    private TokenStorageInterface $tokenStorage;

    #[\Override]
    protected function setUp(): void
    {
        $this->logRepository = $this->createMock(LogRepository::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->doctrineHandler = new DoctrineHandler($this->logRepository, $this->tokenStorage, Logger::WARNING);
    }

    public function testWrite(): void
    {
        $record = new LogRecord(
            new \DateTimeImmutable(),
            'testChannel',
            Level::Error,
            'testMessage',
            ['api_key' => '123456'],
            [],
            'testFormatted'
        );

        $secretValue = new \ReflectionClassConstant(DoctrineHandler::class, 'SECRET_VALUE');

        $this->logRepository->expects($this->once())
            ->method('insertRecord')
            ->with($this->callback(fn ($subject) => $subject['context']['api_key'] === $secretValue->getValue() && $subject['message'] === $record['message']));

        $method = new \ReflectionMethod(DoctrineHandler::class, 'write');

        $method->invoke($this->doctrineHandler, $record);
    }

    public function testSecretContext(): void
    {
        $context = [
            'api_key' => '123456',
            'other_key' => 'value',
        ];

        $secretValue = new \ReflectionClassConstant(DoctrineHandler::class, 'SECRET_VALUE');
        $expected = [
            'api_key' => $secretValue->getValue(),
            'other_key' => 'value',
        ];

        $method = new \ReflectionMethod(DoctrineHandler::class, 'secretContext');

        $result = $method->invoke($this->doctrineHandler, $context);

        $this->assertEquals($expected, $result);
    }
}
