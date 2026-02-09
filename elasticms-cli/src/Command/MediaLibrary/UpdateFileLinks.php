<?php

declare(strict_types=1);

namespace App\CLI\Command\MediaLibrary;

use App\CLI\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Search\Search;
use EMS\Helpers\PropertyAccess\PropertyAccessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::UPDATE_FILE_LINKS,
    description: 'Convert ems file links into ems object link',
    hidden: false
)]
final class UpdateFileLinks extends AbstractCommand
{
    private const string ARGUMENT_CONTENT_TYPE = 'content-type';
    private const string ARGUMENT_FIELDS = 'fields';
    private CoreApiInterface $coreApi;
    private string $contentTypeName;
    private array $fields;

    public function __construct(
        private readonly AdminHelper $adminHelper
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(self::ARGUMENT_CONTENT_TYPE, InputArgument::REQUIRED, 'Content type\'s name')
            ->addArgument(self::ARGUMENT_FIELDS, InputArgument::IS_ARRAY, 'Fields to search for. Write words separated by spaces')
        ;
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->contentTypeName = $this->getArgumentString(self::ARGUMENT_CONTENT_TYPE);
        $this->fields = $this->getArgumentStringArray(self::ARGUMENT_FIELDS);
        $this->coreApi = $this->adminHelper->getCoreApi();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title(\sprintf('Convert ems file links into ems object link in %s', $this->contentTypeName));
        
        if (!$this->coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run ems:admin:login', $this->adminHelper->getCoreApi()->getBaseUrl()));
            return self::EXECUTE_ERROR;
        }
        
        $defaultAlias = $this->coreApi->meta()->getDefaultContentTypeEnvironmentAlias($this->contentTypeName);
        $search = new Search([$defaultAlias]);
        $search->setContentTypes([$this->contentTypeName]);

        $this->io->section('Start analyzing theme document');
        $this->io->progressStart($this->coreApi->search()->count($search));
        $errorTable = [];
        
        foreach($this->coreApi->search()->scroll($search) as $hit) {
            $propertyAccessor = PropertyAccessor::createPropertyAccessor();
            dump($hit);
            
            $this->io->progressAdvance();
        }$this->io->progressFinish();
        
        return self::EXECUTE_SUCCESS;
    }
}