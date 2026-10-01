<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Twig\Components\Layout;

use EMS\CoreBundle\Entity\Channel;
use EMS\CoreBundle\Service\Channel\ChannelService;

class TopbarComponent
{
    public function __construct(
        private readonly ChannelService $channelService
    ) {
    }

    /**
     * @return Channel[]
     */
    public function getChannels(): array
    {
        return $this->channelService->getAll();
    }
}
