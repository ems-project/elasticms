<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Local;

use EMS\ClientHelperBundle\Helper\Builder\Builders;
use EMS\ClientHelperBundle\Helper\ContentType\ContentTypeHelper;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestManager;
use EMS\ClientHelperBundle\Helper\Elasticsearch\Settings;
use EMS\ClientHelperBundle\Helper\Environment\Environment;
use EMS\ClientHelperBundle\Helper\Environment\EnvironmentApi;
use EMS\ClientHelperBundle\Helper\Local\Status\Status;
use EMS\CommonBundle\Common\CoreApi\TokenStore;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Contracts\CoreApi\Exception\NotAuthenticatedExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;

final class LocalHelper
{
    private readonly ClientRequest $clientRequest;

    public function __construct(
        private readonly TokenStore $tokenStore,
        ClientRequestManager $clientRequestManager,
        private readonly ContentTypeHelper $contentTypeHelper,
        private readonly Builders $builders,
        private readonly EnvironmentApi $environmentApi,
        private LoggerInterface $logger,
        private readonly string $projectDir
    ) {
        $this->clientRequest = $clientRequestManager->getDefault();
    }

    public function api(Environment $environment): CoreApiInterface
    {
        $coreApi = $this->environmentApi->api($environment);
        $coreApi->setLogger($this->logger);
        $token = $this->tokenStore->getToken($coreApi->getBaseUrl());
        if (null !== $token) {
            $coreApi->setToken($token);
        }

        return $coreApi;
    }

    public function getUrl(): string
    {
        return $this->clientRequest->getUrl();
    }

    public function health(): string
    {
        return $this->clientRequest->healthStatus();
    }

    public function tryIndexSearch(): void
    {
        $this->clientRequest->searchArgs([]);
    }

    /**
     * @throws NotAuthenticatedExceptionInterface
     */
    public function login(Environment $environment, string $username, string $password): CoreApiInterface
    {
        $coreApi = $this->environmentApi->login($environment, $username, $password);
        $coreApi->setLogger($this->logger);
        $this->tokenStore->saveToken($coreApi->getBaseUrl(), $coreApi->getToken());

        return $coreApi;
    }

    public function isUpToDate(Environment $environment): bool
    {
        $settings = $this->clientRequest->getSettings($environment);
        $lockVersion = $environment->getLocal()->getVersionLockFile()->getVersion($environment);

        return $lockVersion === $this->builders->getVersion($settings);
    }

    public function build(Environment $environment): void
    {
        $settings = $this->clientRequest->getSettings($environment);
        $directory = $environment->getLocal()->getDirectory();

        $this->builders->build($environment, $directory);
        $environment->getLocal()->refresh($settings);

        $this->lockVersion($environment);
    }

    public function lockVersion(Environment $environment, bool $refresh = false): void
    {
        if ($refresh) {
            if ('green' === $this->clientRequest->healthStatus()) {
                $this->clientRequest->refresh();
            }
            $this->contentTypeHelper->clear();
        }

        $settings = $this->clientRequest->getSettings($environment, false);
        $versionLockFile = $environment->getLocal()->getVersionLockFile();
        $versionLockFile->addVersion($environment, $this->builders->getVersion($settings))->save();
    }

    /**
     * @return Status[]
     */
    public function statuses(Environment $environment): array
    {
        $settings = $this->clientRequest->getSettings($environment);

        return [
            $this->statusTranslation($environment, $settings),
            $this->statusTemplating($environment, $settings),
            $this->statusRouting($environment, $settings),
        ];
    }

    private function statusRouting(Environment $environment, Settings $settings): Status
    {
        $status = new Status('Routing');
        $status->addBuilderDocuments($this->builders->routing()->getDocuments($environment));

        if (null === $contentTypeName = $settings->getRouteContentTypeName()) {
            return $status;
        }

        foreach ($environment->getLocal()->getRouting($settings)->getData() as $name => $data) {
            $status->addItemLocal($name, $contentTypeName, $data);
        }

        return $status;
    }

    private function statusTemplating(Environment $environment, Settings $settings): Status
    {
        $status = new Status('Templating');
        $status->addBuilderDocuments($this->builders->templating()->getDocuments($environment));

        foreach ($environment->getLocal()->getTemplates($settings) as $templateFile) {
            $mapping = $settings->getTemplateMapping($templateFile->getContentTypeName());

            $status->addItemLocal($templateFile->getName(), $templateFile->getContentTypeName(), [
                $mapping['name'] => $templateFile->getName(),
                $mapping['code'] => $templateFile->getCode(),
            ]);
        }

        return $status;
    }

    private function statusTranslation(Environment $environment, Settings $settings): Status
    {
        $status = new Status('Translations');
        $status->addBuilderDocuments($this->builders->translation()->getDocuments($environment));

        if (null === $contentTypeName = $settings->getTranslationContentTypeName()) {
            return $status;
        }

        foreach ($environment->getLocal()->getTranslations()->getData() as $name => $data) {
            $status->addItemLocal((string) $name, $contentTypeName, $data);
        }

        return $status;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function makeAssetsArchives(string $baseUrl): string
    {
        $directory = \implode(DIRECTORY_SEPARATOR, [$this->projectDir, 'public', $baseUrl]);
        if (!\is_dir($directory)) {
            throw new \RuntimeException(\sprintf('Directory not found %s', $baseUrl));
        }

        $zipFile = \tempnam(\sys_get_temp_dir(), 'zip');
        if (!\is_string($zipFile)) {
            throw new \RuntimeException('Error while generating a temporary zip file');
        }

        $zip = new \ZipArchive();
        $zip->open($zipFile, \ZipArchive::OVERWRITE);

        $finder = new Finder();
        $finder->files()->in($directory);

        if (!$finder->hasResults()) {
            throw new \RuntimeException('The directory is empty');
        }

        foreach ($finder as $file) {
            $filePath = $file->getRealPath();
            $filename = $file->getRelativePathname();
            if (!\is_string($filePath)) {
                throw new \RuntimeException(\sprintf('File %s path not found', $filename));
            }
            $zip->addFile($filePath, $filename);
        }

        $zip->addPattern('/.*/', $directory);
        $zip->close();

        return $zipFile;
    }
}
