<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Exception;

use EMS\CommonBundle\Contracts\CoreApi\Exception\NotSuccessfulExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class NotSuccessfulException extends \RuntimeException implements NotSuccessfulExceptionInterface
{
    public function __construct(ResponseInterface $response)
    {
        $info = $response->getInfo();
        $message = \sprintf('[%s] %s was not successful! (Check logs!)', $info['http_method'], $info['url']);

        parent::__construct($message, $response->getStatusCode());
    }
}
