<?php

declare(strict_types=1);

namespace EMS\CommonBundle\EventListener;

use EMS\CommonBundle\Common\ResponseHeader\ResponseHeaderManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseHeaderListener implements EventSubscriberInterface
{
    public function __construct(private readonly ResponseHeaderManager $responseHeaderManager)
    {
    }

    /**
     * @return array<string, array<mixed>>
     */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => [
                ['onKernelResponse', -20],
            ],
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $context = $this->responseHeaderManager->getContext();
    }
}
