<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Exception;

use EMS\CommonBundle\Common\CoreApi\Result;
use EMS\CommonBundle\Contracts\CoreApi\Exception\NotSuccessfulExceptionInterface;

final class NotSuccessfulException extends \RuntimeException implements NotSuccessfulExceptionInterface
{
    public function __construct(public readonly Result $result)
    {
        $info = $result->response->getInfo();
        $message = \sprintf('[%s] %s was not successful! (Check logs!)', $info['http_method'], $info['url']);

        parent::__construct($message, $result->response->getStatusCode());
    }
}
