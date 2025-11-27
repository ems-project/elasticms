<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Service;

use EMS\CoreBundle\Core\Messenger\Message\WebhookSubscriberMessage;
use EMS\CoreBundle\Repository\WebhookSubscriptionRepository;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\MessageBusInterface;

class WebhookService
{
    public function __construct(
        private readonly WebhookSubscriptionRepository $repository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    /**
     * @param mixed[] $data
     */
    public function dispatch(string $eventName, array $data): int
    {
        $payload = [
            'event' => $eventName,
            'data' => $data,
        ];

        $counter = 0;
        foreach ($this->repository->findEnabled() as $subscription) {
            if (!\in_array($eventName, $subscription->getEvents(), true)) {
                continue;
            }

            $this->bus->dispatch(
                new WebhookSubscriberMessage($subscription->getId(), $eventName, $payload)
            );
            ++$counter;
        }

        return $counter;
    }

    public function disable(WorkerMessageFailedEvent $event, WebhookSubscriberMessage $message): void
    {
        $this->repository->disable($message->subscriptionId, $event->getThrowable()->getMessage());
    }
}
