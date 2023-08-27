<?php

declare(strict_types=1);

namespace EMS\Release\Command\Repository;

use EMS\Release\Github\Repository\Repository;
use EMS\Release\Github\RepositoryCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RepositoryListCommand  extends Command
{
    protected static $defaultName = 'repository:list';

    public function __construct(private readonly RepositoryCollection $repositories)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $style->title('Repository list');

        $list = [];

        $groupedRepositories = $this->repositories->grouped();

        foreach ($groupedRepositories as $group => $repository) {
            $list = [...$list, ...\array_map(static fn (Repository $repository) => [
                $repository->name,
                $group,
                $repository->getLastRelease()->tag,
                $repository->getLastRelease()->draft,
                $repository->getLastRelease()->createdAt,
                $repository->getLastRelease()->sha,
            ], $repository)];
        }

        $style->table(
            [['name', 'group', 'latest', 'draft', 'createdAt']],
            $list,
        );

        return 0;
    }
}