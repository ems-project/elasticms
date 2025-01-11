<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Log;

use EMS\CommonBundle\Common\Log\LocalizedLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LocalizedLoggerAiTest extends TestCase
{
    private LocalizedLogger $localizedLogger;
    private LoggerInterface $logger;
    private TranslatorInterface $translator;
    private string $translationDomain = 'test_domain';

    #[\Override]
    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->localizedLogger = new LocalizedLogger($this->logger, $this->translator, $this->translationDomain);
    }

    public function testLog(): void
    {
        $level = 'info';
        $message = 'Hello %name%';
        $translatedMessage = 'Bonjour %name%';
        $context = [
            'name' => 'John',
            'translation_message' => $message,
        ];

        $this->translator->expects($this->once())
            ->method('trans')
            ->with($message, [], $this->translationDomain)
            ->willReturn($translatedMessage);

        $this->logger->expects($this->once())
            ->method('log')
            ->with($level, 'Bonjour John', $context);

        $this->localizedLogger->log($level, $message, $context);
    }

    public function testLogWithoutReplacement(): void
    {
        $level = 'info';
        $message = 'Hello World';
        $translatedMessage = 'Bonjour le Monde';
        $context = [
            'translation_message' => $message,
        ];

        $this->translator->expects($this->once())
            ->method('trans')
            ->with($message, [], $this->translationDomain)
            ->willReturn($translatedMessage);

        $this->logger->expects($this->once())
            ->method('log')
            ->with($level, $translatedMessage, $context);

        $this->localizedLogger->log($level, $message, $context);
    }
}
