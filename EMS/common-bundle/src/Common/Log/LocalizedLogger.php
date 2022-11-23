<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Log;

use EMS\CommonBundle\Contracts\Log\LocalizedLoggerInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LocalizedLogger extends AbstractLogger implements LocalizedLoggerInterface
{
    private const PATTERN = '/%(?<parameter>(_|)[[:alnum:]_]*)%/m';

    private LoggerInterface $logger;
    private TranslatorInterface $translator;
    private string $translationDomain;

    public function __construct(LoggerInterface $logger, TranslatorInterface $translator, string $translationDomain)
    {
        $this->logger = $logger;
        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
    }

    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $this->translateMessage($message, $context), $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function translateMessage(string $message, array &$context): string
    {
        $context['translation_message'] = $message;
        $translation = $this->translator->trans($message, [], $this->translationDomain);

        return \preg_replace_callback(self::PATTERN, function ($match) use ($context) {
            return $context[$match['parameter']] ?? $match['parameter'];
        }, $translation) ?? $message;
    }
}
