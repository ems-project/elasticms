<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Log;

use EMS\CommonBundle\Contracts\Log\LocalizedLoggerInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class LocalizedLogger extends AbstractLogger implements LocalizedLoggerInterface
{
    private const PATTERN = '/%(?<parameter>(_|)[[:alnum:]_]*)%/m';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator,
        private readonly string $translationDomain,
    ) {
    }

    public function message(string $level, TranslatableMessage $message, array $context = []): void
    {
        $this->logger->log($level, $message->trans($this->translator), $context);
    }

    public function messageError(TranslatableMessage $message, array $context = []): void
    {
        $this->message('error', $message, $context);
    }

    public function messageWarning(TranslatableMessage $message, array $context = []): void
    {
        $this->message('warning', $message, $context);
    }

    public function messageNotice(TranslatableMessage $message, array $context = []): void
    {
        $this->message('notice', $message, $context);
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

        return \preg_replace_callback(
            pattern: self::PATTERN,
            callback: static fn ($match) => $context[$match['parameter']] ?? $match['parameter'],
            subject: $translation
        ) ?? (string) $message;
    }
}
