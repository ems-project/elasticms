<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Document;

use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class PublishCommand extends AbstractCommand
{
    private const string ARGUMENT_CONTENT_TYPE = 'content-type';
    private const string ARGUMENT_OUUID = 'ouuid';
    private const string ARGUMENT_TARGET_ENVIRONMENT = 'target-environment';
    private const string ARGUMENT_REVISION_ID = 'revisison-id';
    private string $contentTypeName;
    private string $ouuid;
    private string $targetEnvironmentName;
    private ?string $revisionId = null;

    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this
            ->addArgument(self::ARGUMENT_CONTENT_TYPE, InputArgument::REQUIRED, \sprintf('Content-type\'s name'))
            ->addArgument(self::ARGUMENT_OUUID, InputArgument::REQUIRED, \sprintf('OUUID of the document to publish'))
            ->addArgument(self::ARGUMENT_TARGET_ENVIRONMENT, InputArgument::REQUIRED, \sprintf('Environment\'s name to publish in'))
            ->addArgument(self::ARGUMENT_REVISION_ID, InputArgument::OPTIONAL, \sprintf('Revision ID of the revision to publish, will take the revision publish in the default environment otherwise.'))
        ;
    }

    #[\Override]
    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->adminHelper->setLogger(new ConsoleLogger($output));
        $this->contentTypeName = $this->getArgumentString(self::ARGUMENT_CONTENT_TYPE);
        $this->targetEnvironmentName = $this->getArgumentString(self::ARGUMENT_TARGET_ENVIRONMENT);
        $this->ouuid = $this->getArgumentString(self::ARGUMENT_OUUID);
        $this->revisionId = $this->getArgumentStringNull(self::ARGUMENT_REVISION_ID);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title(\sprintf('Publish the document %s to the environment %s', $this->ouuid, $this->targetEnvironmentName));
        $api = $this->adminHelper->getCoreApi()->data($this->contentTypeName);
        if (!$api->publish($this->ouuid, $this->targetEnvironmentName, $this->revisionId)) {
            $this->io->error('The document was not published');

            return self::EXECUTE_ERROR;
        }
        $this->io->error('The document has been successfully published');

        return self::EXECUTE_SUCCESS;
    }
}
