<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Action;

use EMS\CoreBundle\Core\Messenger\Message\ActionMessage;
use EMS\CoreBundle\Core\User\UserManager;
use EMS\CoreBundle\Entity\Action;
use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Repository\ActionRepository;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ActionService
{
    public function __construct(
        private readonly ActionRepository $repository,
        private readonly UserManager $userManager,
        private readonly MessageBusInterface $bus
    ) {
    }

    public function getById(UuidInterface $id): Action
    {
        if (null === $action = $this->repository->findOneBy(['id' => $id])) {
            throw new \RuntimeException(\sprintf('Action with the id %s not found.', $id));
        }

        return $action;
    }

    /** @param array<mixed> $request */
    public function requestFromRevision(Revision $revision, array $request): Action
    {
        $user = $this->userManager->getAuthenticatedUser();

        $action = new Action(
            sender: 'revision',
            senderId: (string) $revision->getId(),
            createdBy: $user->getUsername(),
            request: $request
        );
        $this->repository->save($action);

        $this->bus->dispatch(new ActionMessage($action->getId()));

        return $action;
    }
}
