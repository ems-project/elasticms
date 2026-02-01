<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Log;

use Doctrine\Persistence\ManagerRegistry;
use EMS\CommonBundle\Common\Log\DoctrineHandler;
use Monolog\Level;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DoctrineHandlerAiTest extends TestCase
{
    private DoctrineHandler $doctrineHandler;
    private ManagerRegistry $doctrine;
    private TokenStorageInterface $tokenStorage;

    #[\Override]
    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->doctrineHandler = new DoctrineHandler($this->doctrine, $this->tokenStorage, Level::Warning->value);
    }

    #[AllowMockObjectsWithoutExpectations]
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
