<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Templating;

use EMS\ClientHelperBundle\Helper\Environment\Environment;
use EMS\ClientHelperBundle\Helper\Environment\EnvironmentHelper;
use Twig\Loader\LoaderInterface;
use Twig\Source;

/**
 * @see EMSClientHelperExtension::defineTwigLoader()
 */
final class TemplateLoader implements LoaderInterface
{
    public function __construct(private readonly EnvironmentHelper $environmentHelper, private readonly TemplateBuilder $builder)
    {
    }

    /**
     * @param string $name
     */
    public function getSourceContext($name): Source
    {
        $environment = $this->getEnvironment();
        $templateName = new TemplateName($name);

        if ($environment->isLocalPulled()) {
            $template = $this->builder->buildFile($environment, $templateName);

            return new Source($template->getCode(), $name, $template->getPath());
        }

        $template = $this->builder->buildTemplate($environment, $templateName);

        return new Source($template->getCode(), $name);
    }

    /**
     * @param string $name
     */
    public function getCacheKey($name): string
    {
        $environment = $this->getEnvironment();
        $key = ['twig', $environment->getAlias(), $name];

        if ($environment->isLocalPulled()) {
            $key[] = 'local';
        }

        return \implode('_', $key);
    }

    /**
     * @param string $name
     * @param int    $time
     */
    public function isFresh($name, $time): bool
    {
        return $this->builder->isFresh($this->getEnvironment(), new TemplateName($name), $time);
    }

    /**
     * @param string $name
     */
    public function exists($name): bool
    {
        if (null === $this->environmentHelper->getCurrentEnvironment()) {
            return false;
        }
        if (!TemplateName::validate($name)) {
            return false;
        }
        $environment = $this->getEnvironment();
        $templateName = new TemplateName($name);

        return $this->builder->exists($environment, $templateName);
    }

    private function getEnvironment(): Environment
    {
        if (null === $environment = $this->environmentHelper->getCurrentEnvironment()) {
            throw new \RuntimeException('Can not load template without environment!');
        }

        return $environment;
    }
}
