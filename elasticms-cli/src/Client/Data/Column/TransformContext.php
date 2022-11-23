<?php

declare(strict_types=1);

namespace App\Client\Data\Column;

use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TransformContext
{
    public CoreApiInterface $coreApi;
    public SymfonyStyle $io;

    public function __construct(CoreApiInterface $coreApi, SymfonyStyle $io)
    {
        $this->coreApi = $coreApi;
        $this->io = $io;
    }
}
