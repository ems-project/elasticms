<?php

declare(strict_types=1);

namespace App\CLI\Client\Data\Column;

use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TransformContext
{
    public function __construct(public CoreApiInterface $coreApi, public SymfonyStyle $io)
    {
    }
}
