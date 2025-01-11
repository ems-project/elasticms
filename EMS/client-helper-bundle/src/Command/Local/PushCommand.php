<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Command\Local;

use EMS\ClientHelperBundle\Helper\Local\Status\Item;
use EMS\ClientHelperBundle\Helper\Local\Status\Status;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class PushCommand extends AbstractLocalCommand
{
    public const string OPTION_FORCE = 'force';
    private bool $force = false;

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this->addOption(self::OPTION_FORCE, null, InputOption::VALUE_NONE, 'Ignore environment\'s up-to --date chacks');
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->force = $this->getOptionBool(self::OPTION_FORCE);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Local development - push');
        $this->io->section(\sprintf('Pushing for environment %s', $this->environment->getName()));

        if (!$this->healthCheck()) {
            return self::EXECUTE_ERROR;
        }

        if (!$this->force && !$this->localHelper->isUpToDate($this->environment)) {
            $this->io->error('Not up to date, please commit/stash changes and run emsch:local:pull');

            return self::EXECUTE_ERROR;
        }

        if (!$this->coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run emsch:local:login', $this->coreApi->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        foreach ($this->localHelper->statuses($this->environment) as $status) {
            $this->pushStatus($status);
        }

        $this->localHelper->lockVersion($this->environment, true);

        return self::EXECUTE_SUCCESS;
    }

    private function pushStatus(Status $status): void
    {
        $this->io->section($status->getName());

        foreach ($status->itemsAdded() as $item) {
            $data = $this->coreApi->data($item->getContentType());
            $draft = $data->create($item->getDataLocal(), $item->getId());
            $data->finalize($draft->getRevisionId());
            $this->writeItem('<fg=green>Created</>', $item, $item->getId());
        }

        foreach ($status->itemsUpdated() as $item) {
            $data = $this->coreApi->data($item->getContentType());
            $draft = $data->update($item->getId(), $item->getDataLocal());
            $data->finalize($draft->getRevisionId());
            $this->writeItem('<fg=blue>Updated</>', $item, $item->getId());
        }

        foreach ($status->itemsDeleted() as $item) {
            if (null === $id = $item->getIdOrigin()) {
                continue;
            }

            $this->coreApi->data($item->getContentType())->delete($id);
            $this->writeItem('<fg=red>Deleted</>', $item, $id);
        }
    }

    private function writeItem(string $type, Item $item, string $id): void
    {
        $url = \vsprintf('%s - %s/data/revisions/%s:%s', [
            $item->getKey(),
            $this->coreApi->getBaseUrl(),
            $item->getContentType(),
            $id,
        ]);

        $this->io->writeln(\sprintf('[%s] %s', $type, $url));
    }
}
