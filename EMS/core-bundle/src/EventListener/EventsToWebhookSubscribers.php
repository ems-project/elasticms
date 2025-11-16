<?php

declare(strict_types=1);

namespace EMS\CoreBundle\EventListener;

use EMS\CoreBundle\Core\Messenger\Message\WebhookSubscriberMessage;
use EMS\CoreBundle\Event\RevisionPublishEvent;
use EMS\CoreBundle\Repository\WebhookSubscriptionRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class EventsToWebhookSubscribers implements EventSubscriberInterface
{
    public function __construct(
        private WebhookSubscriptionRepository $repository,
        private MessageBusInterface $bus,
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RevisionPublishEvent::class => 'onRevisionPublished',
            WorkerMessageFailedEvent::class => 'onMessageFailed',
        ];
    }

    public function onRevisionPublished(RevisionPublishEvent $event): void
    {
        $eventName = \sprintf('content.published.%s', $event->getEnvironment()->getName());
        $payload = [
            'event' => $eventName,
            'data' => [
                'environment' => $event->getEnvironment()->getName(),
                'alias' => $event->getEnvironment()->getAlias(),
                'content_type' => $event->getRevision()->giveContentType()->getName(),
                'ouuid' => $event->getRevision()->getOuuid(),
            ],
        ];

        foreach ($this->repository->findEnabled() as $subscription) {
            if (!\in_array($eventName, $subscription->getEvents(), true)) {
                continue;
            }

            $this->bus->dispatch(
                new WebhookSubscriberMessage($subscription->getId(), $eventName, $payload)
            );
        }
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        if ($event->willRetry()) {
            return;
        }
        $message = $event->getEnvelope()->getMessage();
        if (!$message instanceof WebhookSubscriberMessage) {
            return;
        }
        $this->repository->disable($message->subscriptionId, $event->getThrowable()->getMessage());
        $this->logger->error('webhook.subscriber.disabled', [
            'subscriptionId' => $message->subscriptionId,
            'event' => $message->eventName,
            'errorMessage' => $event->getThrowable()->getMessage(),
        ]);
    }
}
