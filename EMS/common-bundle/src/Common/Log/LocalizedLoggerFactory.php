<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Log;

use EMS\CommonBundle\Contracts\Log\LocalizedLoggerFactoryInterface;
use EMS\CommonBundle\Contracts\Log\LocalizedLoggerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class LocalizedLoggerFactory implements LocalizedLoggerFactoryInterface
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function __invoke(LoggerInterface $logger, string $translationDomain): LocalizedLoggerInterface
    {
        return new LocalizedLogger($logger, $this->translator, $translationDomain);
    }
}
