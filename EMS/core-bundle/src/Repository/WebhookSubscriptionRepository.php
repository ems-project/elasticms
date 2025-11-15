<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use EMS\CoreBundle\Entity\Channel;
use EMS\CoreBundle\Entity\WebhookSubscription;

/**
 * @extends ServiceEntityRepository<WebhookSubscription>
 *
 * @method WebhookSubscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method WebhookSubscription|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method WebhookSubscription[]    findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class WebhookSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(Registry $registry)
    {
        parent::__construct($registry, WebhookSubscription::class);
    }

    /**
     * @param string[] $events
     */
    public function create(string $endpointUrl, array $events): WebhookSubscription
    {
        $subscription = new WebhookSubscription();
        $subscription->setEndpointUrl($endpointUrl);
        $subscription->setEvents($events);
        $subscription->setSecret(\bin2hex(\random_bytes(32)));
        $this->getEntityManager()->persist($subscription);
        $this->getEntityManager()->flush();

        return $subscription;
    }
}
