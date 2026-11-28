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
    private const string PATTERN = '/%(?<parameter>(_|)[[:alnum:]_]*)%/m';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[\Override]
    public function messageError(TranslatableMessage $message, array $context = []): void
    {
        $this->logger->log('error', $message->trans($this->translator), $context);
    }

    #[\Override]
    public function messageWarning(TranslatableMessage $message, array $context = []): void
    {
        $this->logger->log('warning', $message->trans($this->translator), $context);
    }

    #[\Override]
    public function messageNotice(TranslatableMessage $message, array $context = []): void
    {
        $this->logger->log('notice', $message->trans($this->translator), $context);
    }

    #[\Override]
    public function messageInfo(TranslatableMessage $message, array $context = []): void
    {
        $this->logger->log('info', $message->trans($this->translator), $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    #[\Override]
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
        $translation = $this->translator->trans((string) $message, []);

        return \preg_replace_callback(
            pattern: self::PATTERN,
            callback: static fn ($match) => $context[$match['parameter']] ?? $match['parameter'],
            subject: $translation
        ) ?? (string) $message;
    }
}
