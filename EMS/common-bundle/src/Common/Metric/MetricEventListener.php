<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Metric;

use EMS\CommonBundle\Controller\MetricController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class MetricEventListener implements EventSubscriberInterface
{
    public function __construct(private MetricCollector $metricCollector)
    {
    }

    /**
     * @return array<string, array<mixed>>
     */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => [
                ['metricCollect', 300],
            ],
        ];
    }

    public function metricCollect(TerminateEvent $event): void
    {
        $controller = $event->getRequest()->get('_controller');

        if (MetricController::METRICS === $controller && !$this->metricCollector->isInMemoryStorage()) {
            $this->metricCollector->collectWithValidity();
        }
    }
}
