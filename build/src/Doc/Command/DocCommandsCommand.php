<?php

declare(strict_types=1);

namespace Build\Doc\Command;

use App\Admin\Kernel;
use Build\Doc\Markdown\Content;
use Build\Doc\Markdown\MarkdownFile;
use EMS\Helpers\Standard\Json;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Dotenv\Dotenv;

class DocCommandsCommand extends Command
{
    protected static $defaultName = 'commands';

    private Application $application;
    private BufferedOutput $output;
    private DescriptorHelper $descriptorHelper;

    private const CONFIG = [
        ['EMS\CommonBundle\Command\\', __DIR__.'/../../../../doc/ems/common/commands.md'],
    ];

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->application = $this->getAdminApplication();
        $this->output = new BufferedOutput();
        $this->descriptorHelper = new DescriptorHelper();
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
        $this->descriptorHelper->describe($this->output, $command, ['format' => 'json']);
        $json = Json::decode($this->output->fetch());

        $content->newLine();
        $content->startStopAutoGeneration('command');
        $content->write('...');
        $content->startStopAutoGeneration('command');
        $content->newLine();
    }
}
