<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Messenger\Message;

use Ramsey\Uuid\UuidInterface;

readonly class ActionMessage implements AsyncMessageInterface
{
    public function __construct(
        public UuidInterface $actionId,
    ) {
    }
}
