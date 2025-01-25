<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Integration\App;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EMS\CommonBundle\EMSCommonBundle;
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
            new FrameworkBundle(),
            new DoctrineBundle(),
            new EMSCommonBundle(),
            new TwigBundle(),
        ];
    }

    #[\Override]
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config.yaml');
    }
}
