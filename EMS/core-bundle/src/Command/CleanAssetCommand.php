<?php

namespace EMS\CoreBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Entity\UploadedAsset;
use EMS\CoreBundle\Repository\RevisionRepository;
use EMS\CoreBundle\Repository\UploadedAssetRepository;
use EMS\CoreBundle\Service\FileService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanAssetCommand extends EmsCommand
{
    protected static $defaultName = 'ems:asset:clean';
    /** @var Registry */
    protected $doctrine;
    /** @var FileService */
    protected $fileService;
    /** @var LoggerInterface */
    protected $logger;

    public function __construct(LoggerInterface $logger, Registry $doctrine, FileService $fileService)
    {
        $this->doctrine = $doctrine;
        $this->fileService = $fileService;
        $this->logger = $logger;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Unreference useless assets (no files are deleted from storages)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        /** @var UploadedAssetRepository $repository */
        $repository = $em->getRepository(UploadedAsset::class);
        /** @var RevisionRepository $revRepo */
        $revRepo = $em->getRepository(Revision::class);

        $this->formatStyles($output);

        $progress = new ProgressBar($output, $repository->countHashes());
        $progress->start();

        $page = 0;
        $filesDereference = 0;
        $filesInUsed = 0;
        $totalCounter = 0;
        while (true) {
            $hashes = $repository->getHashes($page);
            if (empty($hashes)) {
                break;
            }
            ++$page;

            foreach ($hashes as $hash) {
                $usedCounter = $revRepo->hashReferenced($hash['hash']);
                if (0 === $usedCounter) {
                    $repository->dereference($hash['hash']);
                    ++$filesDereference;
                } else {
                    ++$filesInUsed;
                    $totalCounter += $usedCounter;
                }
                $progress->advance();
            }
        }

        $progress->finish();
        $output->writeln('');
        if ($filesDereference) {
            $output->writeln("<comment>$filesDereference files have been dereferenced</comment>");
        }
        if ($filesInUsed) {
            $output->writeln("<comment>$filesInUsed files are referenced $totalCounter times</comment>");
        }

        return 0;
    }
}
