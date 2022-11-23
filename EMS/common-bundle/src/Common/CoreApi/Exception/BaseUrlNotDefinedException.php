<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Exception;

use EMS\CommonBundle\Contracts\CoreApi\Exception\BaseUrlNotDefinedExceptionInterface;

final class BaseUrlNotDefinedException extends \RuntimeException implements BaseUrlNotDefinedExceptionInterface
{
    public function __construct()
    {
        parent::__construct('Core api base url not defined!');
    }
}
