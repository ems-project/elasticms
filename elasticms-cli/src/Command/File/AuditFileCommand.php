<?php

declare(strict_types=1);

namespace App\CLI\Command\File;

use App\CLI\Client\File\Report;
use App\CLI\Commands;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\Helpers\File\File;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Mime\MimeTypes;

#[AsCommand(
    name: Commands::FILE_AUDIT,
    description: 'Audit files in a folder structure.',
    hidden: false
)]
class AuditFileCommand extends AbstractCommand
{
    final public const string UPPERCASE_EXTENSION = 'ExtensionWithUppercase';
    final public const string EXTENSION_MISMATCH = 'ExtensionMismatch';

    private const string ARG_FOLDER = 'folder';
    private ConsoleLogger $logger;
    private string $folder;
    private readonly MimeTypes $mimeTypes;
    private readonly Report $report;

    public function __construct()
    {
        $this->mimeTypes = new MimeTypes();
        $this->report = new Report();
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(
                self::ARG_FOLDER,
                InputArgument::REQUIRED,
                'Path of the folder structure'
            );
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->logger = new ConsoleLogger($output);
        $this->folder = $this->getArgumentString(self::ARG_FOLDER);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title(\sprintf('Audit files in %s', $this->folder));
        $finder = new Finder();
        $finder->files()->in($this->folder);

        if (!$finder->hasResults()) {
            throw new \RuntimeException('No files found!');
        }
        $this->io->comment(\sprintf('%d files located', $finder->count()));
        $progressBar = $this->io->createProgressBar($finder->count());
        foreach ($finder as $file) {
            $pathInStructure = \substr($file->getPathname(), \strlen($this->folder));
            $info = new File($file);
            $extension = \strtolower($info->extension);
            if ($extension !== $info->extension) {
                $this->log(self::UPPERCASE_EXTENSION, $pathInStructure, \sprintf('The extension %s contains uppercase', $info->extension));
            }
            if (!\in_array($extension, $this->mimeTypes->getExtensions($info->mimeType))) {
                $this->log(self::EXTENSION_MISMATCH, $pathInStructure, \sprintf('The extension %s mismatch with the mime type %s', $info->extension, $info->mimeType));
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->io->newLine();
        $this->io->writeln(\sprintf('Audit report: %s', $this->report->generateXslxReport()));

        return self::EXECUTE_SUCCESS;
    }

    private function log(string $type, string $filename, string $message): void
    {
        $this->report->addWarning($type, $filename, $message);
        $this->logger->warning(\sprintf('%s: %s', $filename, $message));
    }
}
