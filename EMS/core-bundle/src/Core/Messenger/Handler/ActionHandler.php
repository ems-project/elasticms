<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Messenger\Handler;

use EMS\CoreBundle\Core\Action\ActionService;
use EMS\CoreBundle\Core\Messenger\Message\ActionMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ActionHandler
{
    public function __construct(
        private readonly ActionService $actionService
    ) {
    }

    public function __invoke(ActionMessage $message): void
    {
        $action = $this->actionService->getById($message->actionId);
    }
}
