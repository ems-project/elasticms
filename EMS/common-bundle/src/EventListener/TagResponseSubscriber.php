<?php

declare(strict_types=1);

namespace EMS\CommonBundle\EventListener;

use EMS\CommonBundle\Common\HttpCache\TagCollector;
use EMS\Helpers\Html\Headers;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class TagResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(private TagCollector $tagCollector)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($this->tagCollector->isEmpty() || !$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set(Headers::X_CACHE_TAGS, \implode(',', $this->tagCollector->all()));
    }
}
