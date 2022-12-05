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

    public function __construct(private readonly LoggerInterface $logger, private readonly TranslatorInterface $translator, private readonly string $translationDomain)
    {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->logger->log($level, $this->translateMessage($message, $context), $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function translateMessage(string|\Stringable $message, array &$context): string
    {
        $context['translation_message'] = $message;
        $translation = $this->translator->trans((string) $message, [], $this->translationDomain);

        return \preg_replace_callback(self::PATTERN, fn ($match) => $context[$match['parameter']] ?? $match['parameter'], $translation) ?? (string) $message;
    }
}
