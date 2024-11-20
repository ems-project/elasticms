<?php

declare(strict_types=1);

namespace Build\Doc\Command;

use App\Admin\Kernel;
use Build\Doc\Markdown\Content;
use Build\Doc\Markdown\MarkdownFile;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Dotenv\Dotenv;

class DocCommandsCommand extends Command
{
    protected static $defaultName = 'commands';

    private Application $application;

    private const CONFIG = [
        ['EMS\CommonBundle\Command\\', __DIR__.'/../../../../doc/ems/common/commands.md'],
    ];

    private const EXCLUDE_OPTIONS = ['help', 'quiet', 'verbose', 'version', 'ansi', 'no-interaction', 'env', 'no-debug'];

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->application = $this->getAdminApplication();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Doc: commands');

        try {
            foreach (self::CONFIG as list($namespace, $filename)) {
                $groupedCommands = $this->getGroupedCommands($namespace);

                $file = new MarkdownFile($filename);
                $sectionCommands = $file->block->getSection('Commands');
                $sectionCommands->content->newLine();

                foreach ($groupedCommands as $group => $commands) {
                    $parentSection = (\count($commands) > 1) ? $sectionCommands->getSection(\ucfirst($group)) : $sectionCommands;
                    $parentSection->content->newLine();

                    foreach ($commands as $name => $command) {
                        $content = $parentSection->getSection(\ucfirst($name))->content;
                        $this->writeCommand($command, $content);
                    }
                }
            }
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
        }

        return self::SUCCESS;
    }

    private function getAdminApplication(): Application
    {
        (new Dotenv())->load(__DIR__.'/../../../../elasticms-admin/.env');
        $adminKernel = new Kernel('test', true);
        $adminKernel->boot();

        return new Application($adminKernel);
    }

    /**
     * @return array<string, array<string, Command>>
     */
    private function getGroupedCommands(string $namespace): array
    {
        $allCommands = \array_filter(
            $this->application->all(),
            fn (Command $command) => \str_starts_with(\get_class($command), $namespace)
        );

        $result = [];
        foreach ($allCommands as $command) {
            if (null === $name = $command->getName()) {
                continue;
            }

            $explodeName = \explode(':', $name);
            $group = $explodeName[1];
            $name = \array_pop($explodeName);
            $result[$group][$name] = $command;
        }

        return $result;
    }

    private function writeCommand(Command $command, Content $content): void
    {
        $arguments = $command->getDefinition()->getArguments();
        $options = \array_filter(
            array: $command->getDefinition()->getOptions(),
            callback: fn (InputOption $option) => !\in_array($option->getName(), self::EXCLUDE_OPTIONS)
        );

        $content
            ->newLine()
            ->startStopAutoGeneration('command')
                ->write('' !== $command->getDescription() ? $command->getDescription() : null, true)
                ->writeCode('bash', $command->getSynopsis(true))
                ->write(\count($arguments) > 0 ? '**Arguments**' : null, true)
                ->list(\array_map([$this, 'parseInput'], $arguments))
                ->write(\count($options) > 0 ? '**Options**' : null, true)
                ->list(\array_map([$this, 'parseInput'], $options))
            ->startStopAutoGeneration('command')
            ->newLine();
    }

    /**
     * @return array{'title': string, 'content': string[]}
     */
    private function parseInput(InputArgument|InputOption $input): array
    {
        $name = $input instanceof InputOption ? '--'.$input->getName() : $input->getName();
        $shortcut = $input instanceof InputOption && \is_string($input->getShortcut())
            ? \sprintf('```-%s``` ', $input->getShortcut())
            : '';

        $extra = [];
        if ($input instanceof InputArgument && $input->isRequired()) {
            $extra[] = 'required';
        }

        if (null !== $input->getDefault() && (!$input instanceof InputOption || $input->acceptValue())) {
            $default = match (\gettype($input->getDefault())) {
                'boolean' => true === $input->getDefault() ? 'true' : 'false',
                'array' => \sprintf('["%s"]', \implode('", "', $input->getDefault())),
                'string' => \sprintf('"%s"', $input->getDefault()),
                default => (string) $input->getDefault()
            };
            $extra[] = \sprintf('default: %s', $default);
        }

        if ($input->isArray()) {
            $extra[] = 'multiple values allowed';
        }

        $extraString = \count($extra) ? ' '.\implode(' ', $extra) : '';

        return [
            'title' => \sprintf('%s```%s```%s', $shortcut, $name, $extraString),
            'content' => [
                \sprintf('> %s', $input->getDescription()),
            ],
        ];
    }
}
