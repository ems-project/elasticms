<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Storage;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Storage\StorageManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::CLEAR_CACHE,
    description: 'Clear storage services caches',
    hidden: false
)]
class ClearCacheCommand extends AbstractCommand
{
    public function __construct(private readonly StorageManager $storageManager)
    {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Clear cache from storage services');
        $this->io->writeln(\sprintf('%d caches have been deleted', $this->storageManager->clearCaches()));

        return self::EXECUTE_SUCCESS;
    }
}
