<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Service;

use EMS\CommonBundle\Common\Admin\AdminHelper;
use Psr\Log\LoggerInterface;

class ApiStorage extends HttpStorage
{
    public function __construct(LoggerInterface $logger, AdminHelper $adminHelper, int $usage, int $hotSynchronizeLimit = 0)
    {
        parent::__construct($logger, $adminHelper->getCoreApi()->getBaseUrl(), '/public/file/', $usage, $adminHelper->getCoreApi()->getToken(), $hotSynchronizeLimit);
    }
}
