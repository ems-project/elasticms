<?php

declare(strict_types=1);

namespace EMS\Release\Command\Repository;

use EMS\Release\Github\Repository\Release;
use EMS\Release\Github\RepositoryCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class RepositoryInfoCommand extends Command
{
    protected static $defaultName = 'repository:info';

    public function __construct(private readonly RepositoryCollection $repositories)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'repository name');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null !== $input->getArgument('name')) {
            return;
        }

        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        $question = new ChoiceQuestion('Repository?', $this->repositories->getNames());

        $input->setArgument('name', $questionHelper->ask($input, $output, $question));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repository = $this->repositories->get($input->getArgument('name'));

        $style = new SymfonyStyle($input, $output);
        $style->title(\sprintf('Repository info "%s"', $repository->name));

        $style->info($repository->url());

        $style->section('Releases');
        $style->table(
            [['Tag', 'Draft', 'Created At', 'Sha']],
            \array_map(fn (Release $release) => [
                $release->url(),
                $release->draft ? 'true' : 'false',
                $release->createdAt,
                $release->sha,
            ], $repository->getReleases())
        );

        $output->writeln('<href=https://symfony.com>Symfony Homepage</>');

        return 0;
    }
}
