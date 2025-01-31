<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Translation;

use EMS\ClientHelperBundle\Helper\Environment\Environment;
use EMS\ClientHelperBundle\Helper\Environment\EnvironmentHelper;
use Symfony\Bundle\FrameworkBundle\Translation\Translator as SymfonyTranslator;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

final readonly class Translator implements CacheWarmerInterface
{
    public function __construct(
        private EnvironmentHelper $environmentHelper,
        private TranslationBuilder $builder,
        private SymfonyTranslator $translator
    ) {
    }

    public function addCatalogues(): void
    {
        $environment = $this->environmentHelper->getCurrentEnvironment();

        if ($environment) {
            $this->loadEnvironment($environment);
        }
    }

    #[\Override]
    public function isOptional(): bool
    {
        return false;
    }

    #[\Override]
    public function warmUp($cacheDir, ?string $buildDir = null): array
    {
        try {
            foreach ($this->environmentHelper->getEnvironments() as $environment) {
                $this->loadEnvironment($environment);
            }
        } catch (\Throwable) {
        }

        return [];
    }

    private function loadEnvironment(Environment $environment): void
    {
        if ($environment->isLocalPulled()) {
            foreach ($environment->getLocal()->getTranslations() as $file) {
                $this->translator->addResource($file->format, $file->resource, $file->locale, $environment->getName());
            }

            return;
        }

        foreach ($this->builder->buildMessageCatalogues($environment) as $messageCatalogue) {
            $catalogue = $this->translator->getCatalogue($messageCatalogue->getLocale());
            $catalogue->addCatalogue($messageCatalogue);
        }
    }
}
