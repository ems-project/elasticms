<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Log;

use EMS\CommonBundle\Common\Log\LocalizedLogger;
use EMS\CommonBundle\Common\Log\LocalizedLoggerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LocalizedLoggerFactoryAiTest extends TestCase
{
    private LocalizedLoggerFactory $localizedLoggerFactory;
    private TranslatorInterface $translator;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->localizedLoggerFactory = new LocalizedLoggerFactory($this->translator);
    }

    public function testInvoke(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $translationDomain = 'test_domain';

        $localizedLogger = ($this->localizedLoggerFactory)($logger, $translationDomain);

        $this->assertInstanceOf(LocalizedLogger::class, $localizedLogger);
    }
}
