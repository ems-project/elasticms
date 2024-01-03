<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Service;

use EMS\CommonBundle\Common\CoreApi\TokenStore;
use Psr\Log\LoggerInterface;

class ApiStorage extends HttpStorage
{
    public function __construct(LoggerInterface $logger, TokenStore $tokenStore, int $usage, int $hotSynchronizeLimit = 0)
    {
        parent::__construct($logger, $tokenStore->giveBaseUrl(), '/public/file/', $usage, $tokenStore->giveToken(), $hotSynchronizeLimit);
    }
}
