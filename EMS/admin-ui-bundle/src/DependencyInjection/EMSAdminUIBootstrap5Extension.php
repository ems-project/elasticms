<?php

declare(strict_types=1);

namespace EMS\AdminUIBootstrap5Bundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class EMSAdminUIBootstrap5Extension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
    }

    public function prepend(ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (\is_array($bundles) && isset($bundles['TwigBundle'])) {
            $container->prependExtensionConfig('twig', [
                'globals' => [],
                'form_themes' => [
                    '@EMSAdminUIBootstrap5/form/fields.html.twig',
                ],
            ]);
        }
    }
}
