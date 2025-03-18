<?php

namespace EMS\CoreBundle\Core\Messenger\Middleware;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use EMS\CoreBundle\Core\Messenger\Stamp\UserTokenStamp;

class RestoreUserFromStampMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    )
    {}

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        /** @var UserTokenStamp|null $stamp */
        $stamp = $envelope->last(UserTokenStamp::class);

        if ($stamp) {
            $this->tokenStorage->setToken($stamp->token);
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
