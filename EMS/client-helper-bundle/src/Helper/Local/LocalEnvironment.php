<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Local;

use EMS\ClientHelperBundle\Helper\Elasticsearch\Settings;
use EMS\ClientHelperBundle\Helper\Environment\Environment;
use EMS\ClientHelperBundle\Helper\Routing\RoutingFile;
use EMS\ClientHelperBundle\Helper\Templating\TemplateFiles;
use EMS\ClientHelperBundle\Helper\Translation\TranslationFiles;
use Symfony\Component\Filesystem\Filesystem;

final class LocalEnvironment
{
    private readonly Filesystem $fileSystem;

    private ?RoutingFile $routingFile = null;
    private ?TemplateFiles $templatesFiles = null;
    private ?TranslationFiles $translationFiles = null;

    public function __construct(
        private readonly Environment $environment,
        private readonly string $directory,
    ) {
        $this->fileSystem = new Filesystem();
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    public function getRouting(Settings $settings): RoutingFile
    {
        if (null === $this->routingFile) {
            $this->routingFile = new RoutingFile($this->directory, $this->getTemplates($settings));
        }

        return $this->routingFile;
    }

    public function getTemplates(Settings $settings): TemplateFiles
    {
        if (null === $this->templatesFiles) {
            $this->templatesFiles = new TemplateFiles($this->directory, $settings);
        }

        return $this->templatesFiles;
    }

    public function getTranslations(): TranslationFiles
    {
        if (null === $this->translationFiles) {
            $this->translationFiles = new TranslationFiles($this->directory);
        }

        return $this->translationFiles;
    }

    public function getVersionLockFile(): VersionLockFile
    {
        return new VersionLockFile($this->directory);
    }

    public function isPulled(): bool
    {
        return $this->fileSystem->exists($this->getDirectory());
    }

    public function refresh(Settings $settings): void
    {
        $this->templatesFiles = new TemplateFiles($this->directory, $settings);
        $this->routingFile = new RoutingFile($this->directory, $this->templatesFiles);
        $this->translationFiles = new TranslationFiles($this->directory);
    }
}
