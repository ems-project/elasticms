<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Command\Local;

use EMS\ClientHelperBundle\Helper\Environment\EnvironmentHelper;
use EMS\ClientHelperBundle\Helper\Local\LocalHelper;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\ConfigTypes;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\Helpers\Html\MimeTypes;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class UploadAssetsCommand extends AbstractLocalCommand
{
    private const ARG_BASE_URL = 'base_url';
    private const OPTION_FILENAME = 'filename';
    private const OPTION_AS_STYLE_SET_ASSETS = 'as-style-set-assets';
    private const OPTION_ARCHIVE_TYPE = 'archive';
    private const ARCHIVE_ZIP = 'zip';
    private const ARCHIVE_EMS = 'ems';
    private ?string $filename;
    private bool $updateStyleSets;
    private string $baseUrl;
    private string $archiveType;

    public function __construct(EnvironmentHelper $environmentHelper, LocalHelper $localHelper, private readonly ?string $assetLocalFolder)
    {
        parent::__construct($environmentHelper, $localHelper);
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->addArgument(self::ARG_BASE_URL, InputArgument::OPTIONAL, 'Base url where the assets are located')
            ->addOption(self::OPTION_FILENAME, null, InputOption::VALUE_OPTIONAL, 'Save the asset\'s hash within the given file')
            ->addOption(self::OPTION_AS_STYLE_SET_ASSETS, null, InputOption::VALUE_NONE, 'Also update all style set\'s assets with this upload')
            ->addOption(self::OPTION_ARCHIVE_TYPE, null, InputOption::VALUE_OPTIONAL, \sprintf('The assets will be uploaded as an %s archive or a %s archive', self::ARCHIVE_EMS, self::ARCHIVE_ZIP), self::ARCHIVE_EMS)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->baseUrl = $this->getArgumentStringNull(self::ARG_BASE_URL) ?? $this->assetLocalFolder ?? $this->environment->getAlias();
        $this->filename = $this->getOptionStringNull(self::OPTION_FILENAME);
        $this->updateStyleSets = $this->getOptionBool(self::OPTION_AS_STYLE_SET_ASSETS);
        $this->archiveType = $this->getOptionString(self::OPTION_ARCHIVE_TYPE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Local development - Upload assets');

        if (!$this->coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run emsch:local:login', $this->coreApi->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        switch ($this->archiveType) {
            case self::ARCHIVE_ZIP:
                return $this->uploadZipArchive();
            case self::ARCHIVE_EMS:
                return $this->uploadEmsArchive();
        }
        $this->io->error(\sprintf('Archive format %s not supported', $this->archiveType));

        return self::EXECUTE_ERROR;
    }

    private function updateStyleSets(string $hash): void
    {
        if (!$this->updateStyleSets) {
            return;
        }
        $styleSetClient = $this->coreApi->admin()->getConfig(ConfigTypes::WYSIWYG_STYLE_SET->value);
        $styleSetNames = $styleSetClient->index();
        if (empty($styleSetNames)) {
            return;
        }
        foreach ($styleSetNames as $name) {
            $styleSet = $styleSetClient->get($name);
            $styleSet['properties']['assets'] = [
                EmsFields::CONTENT_FILE_HASH_FIELD => $hash,
                EmsFields::CONTENT_MIME_TYPE_FIELD => MimeTypes::APPLICATION_ZIP,
                EmsFields::CONTENT_FILE_NAME_FIELD => 'bundle.zip',
            ];
            $styleSetClient->update($name, $styleSet);
        }
        $this->io->success(\sprintf('%d style sets have been updated', \count($styleSetNames)));
    }

    private function uploadZipArchive(): int
    {
        try {
            $assetsArchive = $this->localHelper->makeAssetsArchives($this->baseUrl);
        } catch (\Throwable $e) {
            $this->io->error($e->getMessage());

            return self::EXECUTE_ERROR;
        }

        try {
            $progressBar = $this->io->createProgressBar($assetsArchive->getSize());
            $hash = $this->coreApi->file()->uploadFile(
                $assetsArchive->path,
                'application/zip',
                'bundle.zip',
                fn (string $chunk) => $progressBar->advance(\strlen($chunk))
            );

            $progressBar->finish();

            $this->io->newLine();
            $this->io->success(\sprintf('Assets %s have been uploaded', $hash));

            if (null !== $this->filename) {
                \file_put_contents($this->filename, $hash);
            }

            $this->updateStyleSets($hash);

            return self::EXECUTE_SUCCESS;
        } catch (\Throwable $e) {
            $this->io->error($e->getMessage());

            return self::EXECUTE_ERROR;
        }
    }

    private function uploadEmsArchive(): int
    {
        return self::EXECUTE_SUCCESS;
    }
}
