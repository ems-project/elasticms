<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Log;

use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatableMessage;

interface LocalizedLoggerInterface extends LoggerInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function message(string $level, TranslatableMessage $message, array $context = []): void;

    /**
     * @param array<string, mixed> $context
     */
    public function messageError(TranslatableMessage $message, array $context = []): void;

    /**
     * @param array<string, mixed> $context
     */
    public function messageWarning(TranslatableMessage $message, array $context = []): void;

    /**
     * @param array<string, mixed> $context
     */
    public function messageNotice(TranslatableMessage $message, array $context = []): void;
}
