<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Command\Local;

use EMS\ClientHelperBundle\Helper\Local\Status\Status;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class StatusCommand extends AbstractLocalCommand
{
    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Local development - status');

        if (!$this->healthCheck()) {
            return self::EXECUTE_ERROR;
        }

        $loggedInProfile = false;
        if ($this->coreApi->isAuthenticated()) {
            $profile = $this->coreApi->user()->getProfileAuthenticated();
            $loggedInProfile = \sprintf('%s (%s)', $profile->getUsername(), $profile->getDisplayName());
        }
        $upToDate = $this->localHelper->isUpToDate($this->environment);

        $this->io->definitionList(
            ['Environment' => $this->environment->getName()],
            ['Backend url' => $this->environment->getBackendUrl()],
            ['Cluster' => $this->localHelper->getUrl()],
            ['Logged in as' => ($loggedInProfile ? \sprintf('<fg=green>%s</>', $loggedInProfile) : '<fg=red>No</>')],
            ['Up to date' => $upToDate ? '<fg=green>Yes</>' : '<fg=red>No</>'],
        );

        $statuses = $this->localHelper->statuses($this->environment);

        $table = new Table($output);
        $table
            ->setHeaders(['', 'Added', 'Updated', 'Deleted'])
            ->setRows(\array_map(fn (Status $status) => [
                $status->getName(),
                \sprintf('<fg=green>%d</>', $status->itemsAdded()->count()),
                \sprintf('<fg=blue>%d</>', $status->itemsUpdated()->count()),
                \sprintf('<fg=red>%d</>', $status->itemsDeleted()->count()),
            ], $statuses))
            ->render();

        if ($output->isVerbose()) {
            foreach ($statuses as $status) {
                $this->printStatus($output, $status);
            }
        }

        return self::EXECUTE_SUCCESS;
    }

    private function printStatus(OutputInterface $output, Status $status): void
    {
        $rows = [];

        if ($status->itemsAdded()->count() > 0) {
            foreach ($status->itemsAdded() as $item) {
                $rows[] = ['<fg=green>Added</>', $item->getKey()];
            }
        }
        if ($status->itemsUpdated()->count() > 0) {
            foreach ($status->itemsUpdated() as $item) {
                $rows[] = ['<fg=blue>Updated</>', $item->getKey()];
            }
        }
        if ($status->itemsDeleted()->count() > 0) {
            foreach ($status->itemsDeleted() as $item) {
                $rows[] = ['<fg=red>Deleted</>', $item->getKey()];
            }
        }

        if (\count($rows) > 0) {
            $this->io->newLine();
            $table = new Table($output);
            $table
                ->setHeaders([new TableCell($status->getName(), ['colspan' => 2])])
                ->setRows($rows)
                ->render();
        }
    }
}
