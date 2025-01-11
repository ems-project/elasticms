<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Command\Local;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PullCommand extends AbstractLocalCommand
{
    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Local development - pull');
        $this->io->section(\sprintf('Pulling for environment %s', $this->environment->getName()));

        if (!$this->healthCheck()) {
            return self::EXECUTE_ERROR;
        }

        $this->localHelper->build($this->environment);

        if ($this->environment->isLocalPulled()) {
            $this->io->success(\sprintf('Pulled successfully into %s', $this->localEnvironment->getDirectory()));
        }

        $list = [];
        foreach ($this->localHelper->statuses($this->environment) as $status) {
            $list[] = [$status->getName() => $status->itemsLocal()->count()];
        }

        $this->io->definitionList(...$list);

        return self::EXECUTE_SUCCESS;
    }
}
