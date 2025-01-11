<?php

declare(strict_types=1);

namespace EMS\AdminUIBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class EMSAdminUIExtension extends Extension implements PrependExtensionInterface
{
    #[\Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('emsui.dev_server_url', $config['dev_server_url']);
    }

    #[\Override]
    public function prepend(ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (\is_array($bundles) && isset($bundles['TwigBundle'])) {
            $container->prependExtensionConfig('twig', [
                'globals' => [
                    'devServer' => '@emsadminui.helper.dev_server',
                ],
            ]);
        }
    }
}
