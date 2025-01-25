<?php

declare(strict_types=1);

namespace App\CLI\Command\Photos;

use App\CLI\Client\Photos\Photo;
use App\CLI\Client\Photos\PhotosLibraryInterface;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Helper\EmsFields;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mime\MimeTypes;

abstract class AbstractPhotosMigrationCommand extends AbstractCommand
{
    private const OPTION_CONTENT_TYPE_NAME = 'content-type-name';
    private const OPTION_UPLOAD_ORIGINAL = 'upload-original';
    private string $contentTypeName;
    private PhotosLibraryInterface $library;
    private readonly MimeTypes $mimeTypes;
    private bool $uploadOriginal;

    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
        $this->mimeTypes = new MimeTypes();
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addOption(self::OPTION_CONTENT_TYPE_NAME, null, InputOption::VALUE_OPTIONAL, 'Content type name in elasticms', 'photo');
        $this->addOption(self::OPTION_UPLOAD_ORIGINAL, null, InputOption::VALUE_NONE, 'Uploads original file');
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->contentTypeName = $this->getOptionString(self::OPTION_CONTENT_TYPE_NAME);
        $this->uploadOriginal = $this->getOptionBool(self::OPTION_UPLOAD_ORIGINAL);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->adminHelper->getCoreApi()->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run ems:admin:login', $this->adminHelper->getCoreApi()->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        $this->library = $this->getLibrary();
        $dataApi = $this->adminHelper->getCoreApi()->data($this->contentTypeName);
        $progressBar = $this->io->createProgressBar($this->library->photosCount());
        foreach ($this->library->getPhotos() as $photo) {
            $this->uploadPreview($photo);
            if ($this->uploadOriginal) {
                $this->uploadOriginal($photo);
            }
            $dataApi->save($photo->getOuuid(), $photo->getData());
            $progressBar->advance();
        }

        return self::EXECUTE_SUCCESS;
    }

    abstract protected function getLibrary(): PhotosLibraryInterface;

    private function uploadPreview(Photo $photo): void
    {
        $previewFile = $this->library->getPreviewFile($photo);
        if (null === $previewFile) {
            return;
        }
        $photo->setPreviewFile($this->uploadFile($previewFile));
    }

    private function uploadOriginal(Photo $photo): void
    {
        $originalFile = $this->library->getOriginalFile($photo);
        if (null === $originalFile) {
            return;
        }
        $photo->setOriginalFile($this->uploadFile($originalFile));
    }

    /**
     * @return mixed[]
     */
    private function uploadFile(\SplFileInfo $file): array
    {
        $mimeType = $this->mimeTypes->guessMimeType($file->getPathname()) ?? 'application/bin';
        $hash = $this->adminHelper->getCoreApi()->file()->uploadFile($file->getPathname(), $file->getFilename(), $mimeType);

        return [
            EmsFields::CONTENT_FILE_HASH_FIELD => $hash,
            EmsFields::CONTENT_FILE_NAME_FIELD => $file->getFilename(),
            EmsFields::CONTENT_MIME_TYPE_FIELD => $mimeType,
            EmsFields::CONTENT_FILE_SIZE_FIELD => $file->getSize(),
        ];
    }
}
