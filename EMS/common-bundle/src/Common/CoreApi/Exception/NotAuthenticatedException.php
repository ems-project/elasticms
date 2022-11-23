<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Exception;

use EMS\CommonBundle\Contracts\CoreApi\Exception\NotAuthenticatedExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class NotAuthenticatedException extends \RuntimeException implements NotAuthenticatedExceptionInterface
{
    public function __construct(ResponseInterface $response)
    {
        $info = $response->getInfo();
        $message = \sprintf('%s Unauthorized for [%s] %s', $info['http_code'], $info['http_method'], $info['url']);

        parent::__construct($message, $response->getStatusCode());
    }
}
