<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Bridge\Core;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreBridgeInterface;
use EMS\CommonBundle\Contracts\Bridge\Core\CoreDataBridgeInterface;
use EMS\CommonBundle\Contracts\Bridge\Core\CoreInfoBridgeInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;

readonly class CoreApiBridge implements CoreBridgeInterface
{
    public function __construct(private CoreApiInterface $coreApi)
    {
    }

    public function versions(): array
    {
        return $this->coreApi->admin()->getVersions();
    }

    public function data(string $contentType): CoreDataBridgeInterface
    {
        return new CoreDataApiBridge($this->coreApi->data($contentType));
    }

    public function info(): CoreInfoBridgeInterface
    {
        return new CoreInfoApiBridge($this->coreApi->meta());
    }
}
