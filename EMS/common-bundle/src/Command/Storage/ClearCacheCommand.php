<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Storage;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Storage\StorageManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCacheCommand extends AbstractCommand
{
    protected static $defaultName = Commands::CLEAR_CACHE;

    public function __construct(private readonly StorageManager $storageManager)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Clear storage services caches');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Clear cache from storage services');
        $this->io->writeln(\sprintf('%d caches have been deleted', $this->storageManager->clearCaches()));

        return self::EXECUTE_SUCCESS;
    }
}
