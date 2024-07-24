<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Command\Asset;

use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Common\PropertyAccess\PropertyAccessor;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Storage\Processor\Processor;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\CoreBundle\Commands;
use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Service\Revision\RevisionService;
use EMS\Helpers\Html\MimeTypes;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResizeCommand extends AbstractCommand
{
    protected static $defaultName = Commands::ASSET_IMAGE_RESIZE;

    public function __construct(private RevisionService $revisionService, private StorageManager $storageManager, private Processor $processor)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Generate resized images base on the EMSCO_IMAGE_MAX_SIZE environment variable.');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $revisions = $this->revisionService->search([]);
        $this->io->progressStart($revisions->count());
        foreach ($revisions->getIterator() as $revision) {
            $this->resizeFileFields($revision);
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();

        return self::EXECUTE_SUCCESS;
    }

    private function resizeFileFields(Revision $revision): void
    {
        $propertyAccessor = PropertyAccessor::createPropertyAccessor();
        $rawData = $revision->getRawData();
        foreach ($propertyAccessor->fileFields($revision->getRawData()) as $propertyPath => $fileField) {
            $type = $fileField[EmsFields::CONTENT_MIME_TYPE_FIELD] ?? $fileField[EmsFields::CONTENT_MIME_TYPE_FIELD_] ?? null;
            if (!\in_array($type, [MimeTypes::IMAGE_PNG->value, MimeTypes::IMAGE_JPEG->value, MimeTypes::IMAGE_WEBP->value], true)) {
                continue;
            }
            $hash = $fileField[EmsFields::CONTENT_FILE_HASH_FIELD] ?? $fileField[EmsFields::CONTENT_FILE_HASH_FIELD_] ?? null;
            if (!\is_string($hash)) {
                continue;
            }
            $file = $this->storageManager->getFile($hash);
            \dump($file->getFilename());
            $propertyAccessor->setValue($rawData, $propertyPath, $fileField);
        }
    }
}
