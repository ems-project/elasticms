<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\App;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EMS\CommonBundle\EMSCommonBundle;
use EMS\SubmissionBundle\EMSSubmissionBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    #[\Override]
    public function getCacheDir(): string
    {
        return \sys_get_temp_dir().'/cache-'.\spl_object_hash($this);
    }

    #[\Override]
    public function getLogDir(): string
    {
        return \sys_get_temp_dir().'/log-'.\spl_object_hash($this);
    }

    #[\Override]
    public function registerBundles(): array
    {
        return [
            new EMSSubmissionBundle(),
            new FrameworkBundle(),
            new TwigBundle(),
            new EMSCommonBundle(),
            new DoctrineBundle(),
        ];
    }

    #[\Override]
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config.yaml');
    }
}
