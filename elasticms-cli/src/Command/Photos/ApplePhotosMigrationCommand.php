<?php

namespace App\CLI\Command\Photos;

use App\CLI\Client\Photos\ApplePhotosLibrary;
use App\CLI\Client\Photos\PhotosLibraryInterface;
use App\CLI\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApplePhotosMigrationCommand extends AbstractPhotosMigrationCommand
{
    protected static $defaultName = Commands::APPLE_PHOTOS_MIGRATION;

    public const ARG_PHOTOS_LIBRARY_PATH = 'photos-library-path';
    private string $applePhotosPathPath;

    public function __construct(AdminHelper $adminHelper)
    {
        parent::__construct($adminHelper);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Migrate Apple Photo library to elaticms documents')
            ->addArgument(
                self::ARG_PHOTOS_LIBRARY_PATH,
                InputArgument::REQUIRED,
                'Path to an Apple Photos library'
            );
        parent::configure();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->applePhotosPathPath = $this->getArgumentString(self::ARG_PHOTOS_LIBRARY_PATH);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title(\sprintf('Start migrating Apple Photos Library %s', $this->applePhotosPathPath));

        return parent::execute($input, $output);
    }

    protected function getLibrary(): PhotosLibraryInterface
    {
        return new ApplePhotosLibrary($this->applePhotosPathPath);
    }
}
