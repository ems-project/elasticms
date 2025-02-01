<?php

declare(strict_types=1);

namespace EMS\FormBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class EMSFormExtension extends Extension
{
    /**
     * @param mixed[] $configs
     */
    #[\Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('emsf.hashcash.difficulty', $config[Configuration::HASHCASH_DIFFICULTY]);
        $container->setParameter('emsf.endpoints', $config[Configuration::ENDPOINTS]);
        $container->setParameter('emsf.ems_config', [
            Configuration::TYPE => $config['instance'][Configuration::TYPE],
            Configuration::TYPE_FORM_FIELD => $config['instance'][Configuration::TYPE_FORM_FIELD],
            Configuration::TYPE_FORM_MARKUP => $config['instance'][Configuration::TYPE_FORM_MARKUP],
            Configuration::TYPE_FORM_SUBFORM => $config['instance'][Configuration::TYPE_FORM_SUBFORM],
            Configuration::TYPE_FORM_CHOICE => $config['instance'][Configuration::TYPE_FORM_CHOICE],
            Configuration::FORM_FIELD => $config['instance'][Configuration::FORM_FIELD],
            Configuration::FORM_SUBFORM_FIELD => $config['instance'][Configuration::FORM_SUBFORM_FIELD],
            Configuration::FORM_TEMPLATE_FIELD => $config['instance'][Configuration::FORM_TEMPLATE_FIELD],
            Configuration::THEME_FIELD => $config['instance'][Configuration::THEME_FIELD],
            Configuration::SUBMISSION_FIELD => $config['instance'][Configuration::SUBMISSION_FIELD],
            Configuration::DOMAIN_FIELD => $config['instance'][Configuration::DOMAIN_FIELD],
            Configuration::LOAD_FROM_JSON => $config['instance'][Configuration::LOAD_FROM_JSON],
            Configuration::CACHEABLE => $config['instance'][Configuration::CACHEABLE],
            Configuration::NAME_FIELD => $config['instance'][Configuration::NAME_FIELD],
            Configuration::TYPE_FORM_VALIDATION => $config['instance'][Configuration::TYPE_FORM_VALIDATION],
        ]);
    }
}
