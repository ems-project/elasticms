<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Action;

use EMS\CoreBundle\Entity\LogAction;
use EMS\CoreBundle\Repository\LogActionRepository;

class ActionLogService
{
    public function __construct(
        private readonly LogActionRepository $repository,
    ) {
    }

    /** @param array<mixed> $request */
    public function newRequest(array $request): LogAction
    {
        $logAction = new LogAction($request);
        $this->repository->save($logAction);

        return $logAction;
    }
}
