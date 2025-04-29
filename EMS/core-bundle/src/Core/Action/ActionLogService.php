<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Action;

use EMS\CoreBundle\Core\Messenger\Message\ActionMessage;
use EMS\CoreBundle\Entity\LogAction;
use EMS\CoreBundle\Repository\LogActionRepository;
use Symfony\Component\Messenger\MessageBusInterface;

class ActionLogService
{
    public function __construct(
        private readonly LogActionRepository $repository,
        private readonly MessageBusInterface $bus
    ) {
    }

    /** @param array<mixed> $request */
    public function newRequest(array $request): LogAction
    {
        $logAction = new LogAction($request);
        $this->repository->save($logAction);

        $this->bus->dispatch(new ActionMessage($logAction->getUuid()));

        return $logAction;
    }
}
