<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Command\Local;

use EMS\ClientHelperBundle\Helper\Environment\EnvironmentHelper;
use EMS\ClientHelperBundle\Helper\Local\LocalHelper;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class UploadAssetsCommand extends AbstractLocalCommand
{
    private const ARG_BASE_URL = 'base_url';
    private const OPTION_FILENAME = 'filename';
    private ?string $filename = null;

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
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->filename = $this->getOptionStringNull(self::OPTION_FILENAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Local development - Upload assets');
        $baseUrl = $input->getArgument(self::ARG_BASE_URL);
        if (!\is_string($baseUrl)) {
            $baseUrl = $this->assetLocalFolder;
        }
        if (!\is_string($baseUrl)) {
            $baseUrl = $this->environment->getAlias();
        }

        if (!$this->coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run emsch:local:login', $this->coreApi->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        try {
            $assetsArchive = $this->localHelper->makeAssetsArchives($baseUrl);
        } catch (\Throwable $e) {
            $this->io->error($e->getMessage());

            return self::EXECUTE_ERROR;
        }

        try {
            $progressBar = $this->io->createProgressBar(Type::integer(\filesize($assetsArchive)));
            $hash = $this->coreApi->file()->uploadFile(
                $assetsArchive,
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

            return self::EXECUTE_SUCCESS;
        } catch (\Throwable $e) {
            $this->io->error($e->getMessage());

            return self::EXECUTE_ERROR;
        }
    }
}
