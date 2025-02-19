<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Bridge\Core;

use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Contracts\Bridge\Core\CoreInfoBridgeInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\MetaInterface;

readonly class CoreInfoApiBridge implements CoreInfoBridgeInterface
{
    use CoreBridgeTrait;

    public function __construct(private MetaInterface $metaApi)
    {
    }

    #[\Override]
    public function documents(array $environments, EMSLink ...$emsLinks): CoreBridgeResponse
    {
        return $this->response(fn () => $this->metaApi->getInfoDocuments(
            environments: $environments,
            emsLinks: \array_values(\array_map(static fn (EMSLink $link) => $link->getEmsId(), $emsLinks))
        ));
    }
}
