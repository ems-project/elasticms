<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Messenger\Handler;

use EMS\CoreBundle\Core\Messenger\Message\ActionMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ActionHandler
{
    public function __invoke(ActionMessage $message): void
    {
        throw new \RuntimeException('Not implemented');
    }
}
