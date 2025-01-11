<?php

declare(strict_types=1);

namespace EMS\CommonBundle\EventListener;

use EMS\CommonBundle\Routes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class IpAddressListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestMatcherInterface $requestMatcher,
        private readonly bool $metricEnabled,
    ) {
    }

    /**
     * @return array<string, array<string|int>>
     */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 512],
        ];
    }

    public function onKernelRequest(RequestEvent $requestEvent): void
    {
        if (!$requestEvent->isMainRequest()) {
            return;
        }

        $request = $requestEvent->getRequest();

        if ($this->metricEnabled && $request->getPathInfo() === Routes::METRICS_PATH->value) {
            return;
        }

        if (!$this->requestMatcher->matches($request)) {
            throw new AccessDeniedHttpException();
        }
    }
}
