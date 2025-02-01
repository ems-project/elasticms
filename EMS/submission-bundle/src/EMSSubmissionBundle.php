<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle;

use EMS\SubmissionBundle\DependencyInjection\EMSSubmissionExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EMSSubmissionBundle extends AbstractBundle
{
    #[\Override]
    public function getContainerExtension(): ExtensionInterface
    {
        return new EMSSubmissionExtension();
    }
}
