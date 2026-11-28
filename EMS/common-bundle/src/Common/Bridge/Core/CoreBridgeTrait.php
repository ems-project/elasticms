<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Bridge\Core;

trait CoreBridgeTrait
{
    protected function response(callable $callable): CoreBridgeResponse
    {
        try {
            return CoreBridgeResponse::onSuccess($callable());
        } catch (\Throwable $throwable) {
            return CoreBridgeResponse::onError($throwable);
        }
    }
}
