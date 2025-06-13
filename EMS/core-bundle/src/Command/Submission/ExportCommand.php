<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Command\Submission;

use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Storage\File\FileInterface;
use EMS\CommonBundle\Storage\NotFoundException;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\CoreBundle\Commands;
use EMS\CoreBundle\Core\Submission\ExportConfig;
use EMS\CoreBundle\Core\Submission\SubmissionExporter;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::SUBMISSION_EXPORT,
    description: 'Extract form submissions',
    hidden: false
)]
class ExportCommand extends AbstractCommand
{
    public const string MAIL_TEMPLATE = '@EMSCore/email/submissions-export.html.twig';
    public const string ARGUMENT_CONFIG = 'config-file';
    private ExportConfig $exportConfig;

    public function __construct(
        private readonly SubmissionExporter $exporter,
        private readonly StorageManager $storageManager,
        private readonly AdminHelper $adminHelper,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addArgument(self::ARGUMENT_CONFIG, InputArgument::REQUIRED, 'JSON config file (path or JSON)');
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->exportConfig = $this->getExportConfig();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->exporter->export($this->exportConfig, $this->io);

        return self::EXECUTE_SUCCESS;
    }

    private function getExportConfig(): ExportConfig
    {
        $input = $this->getArgumentString(self::ARGUMENT_CONFIG);
        
        $config = match (true) {
            Json::isJson($input) => $input,
            $this->getFile($input) instanceof FileInterface => $this->getFile($input)->getContent(),
        };
        
        return ExportConfig::fromJson($config);
    }

    private function getFile(string $fileIdentifier): FileInterface
    {
        try {
            return $this->storageManager->getFile($fileIdentifier);
        } catch (NotFoundException) {
            return $this->adminHelper->getCoreApi()->file()->getFile($fileIdentifier);
        }
    }
}
