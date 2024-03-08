<?php

declare(strict_types=1);

namespace EMS\CoreBundle\EventListener;

use EMS\CoreBundle\Core\User\UserManager;
use EMS\CoreBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
final class LoginListener implements EventSubscriberInterface
{
    public function __construct(private readonly UserManager $userManager)
    {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onLogin',
        ];
    }

    public function onLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof User) {
            $currentDateTime = new \DateTime();
            $expirationDate = $user->getExpirationDate();

            if ($expirationDate !== null && $currentDateTime > $expirationDate) {
                throw new AccessDeniedException('Access Denied: Your account has expired.');
            } else {
                $user->setLastLogin($currentDateTime);
                $this->userManager->update($user);
            }
        }
    }
}
