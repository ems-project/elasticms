<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Twig;

use EMS\CoreBundle\Event\DispatchToWebhookEvent;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;

readonly class CoreExtension
{
    public function __construct(
        private LoggerInterface $logger,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @param mixed[] $data
     */
    #[AsTwigFunction('emsco_webhook')]
    public function dispatchWebhook(string $eventName, array $data = []): void
    {
        $this->dispatcher->dispatch(new DispatchToWebhookEvent($eventName, $data));
    }

    #[AsTwigFilter('emsco_log_error')]
    public function logError(string $error): void
    {
        $this->logger->error($error);
    }

    #[AsTwigFilter('emsco_log_notice')]
    public function logNotice(string $notice): void
    {
        $this->logger->notice($notice);
    }

    #[AsTwigFilter('emsco_log_warning')]
    public function logWarning(string $warning): void
    {
        $this->logger->warning($warning);
    }
}
