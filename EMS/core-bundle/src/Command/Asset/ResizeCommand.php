<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Command\Asset;

use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CoreBundle\Commands;
use EMS\CoreBundle\Service\Revision\RevisionService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResizeCommand extends AbstractCommand
{
    protected static $defaultName = Commands::ASSET_IMAGE_RESIZE;

    public function __construct(private RevisionService $revisionService)
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
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();

        return self::EXECUTE_SUCCESS;
    }
}
