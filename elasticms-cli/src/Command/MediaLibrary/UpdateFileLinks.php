<?php

declare(strict_types=1);

namespace App\CLI\Command\MediaLibrary;

use App\CLI\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;
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
    private array $logReports = [];
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
        $search->setSources(['_source', 'fr', 'nl']);

        $this->io->section(sprintf('Start analyzing %s', $this->contentTypeName));
        $this->io->progressStart($this->coreApi->search()->count($search));

        foreach($this->coreApi->search()->scroll($search) as $hit) {
            $this->updateDocument($hit);
            $this->io->progressAdvance();
        }$this->io->progressFinish();

        if($this->logReports !== []) {
            $this->io->section('Generating log report');
            
        }
        return self::EXECUTE_SUCCESS;
    }

    private function updateDocument(DocumentInterface $document): void
    {
        foreach($this->fields as $field) {
            $this->updateField($document, $field);
        }
    }

    private function updateField(DocumentInterface $document, string $propertyPath): void
    {
        $propertyAccessor = PropertyAccessor::createPropertyAccessor();
        $rawData = $document->getSource();
        
        foreach ($propertyAccessor->iterator($propertyPath, $rawData) as $key => $value) {
            $this->updateProperty($rawData, $key, $value);
        }
    }

    private function updateProperty(array $rawData, mixed $key, mixed $value) : void
    {
        if (!is_string($value) || !preg_match_all(EMSLink::PATTERN, $value, $matches, PREG_SET_ORDER)) {
            return;
        }
        foreach($matches as $match) {
            if($match['link_type'] === 'asset') {
                $this->replaceAssetLink($key, $match, $value);
            }
        }
    }
    

    private function replaceAssetLink(mixed $key, array $match, mixed &$value): void
    {
        $link = EMSLink::fromMatch($match);
        $sha1 = $link->getOuuid();
        
        $found = $this->findMediaFileBySha1($sha1);
        dump($found);
        if($found) {
            $newLink = EMSLink::fromContentTypeOuuid($found->getContentType(), $found->getId());
            $value = str_replace($found->getOuuid(), $newLink, $value);
        }else{
            $this->logAssetLink($key, EMSLink::fromMatch($match), $value);
        }
    }

    private function findMediaFileBySha1(string $sha1): ?DocumentInterface //check Search.setQueryArray pour réduire la recherche
    {
        $alias = $this->coreApi->meta()->getDefaultContentTypeEnvironmentAlias('media_file');
        $search = new Search([$alias]);
        $search->setContentTypes(['media_file']);
        $result = $this->coreApi->search()->scroll($search);
        foreach ($result as $item) {
            if ($item->getOuuid() === $sha1){
                return $item;
            }
        } return null;
    }

    private function logAssetLink(mixed $key, EMSLink $emsLink, string $value) : void
    {
        $this->logReports[] = [
            'key' => $key,
            'emsLink' => $emsLink,
            'value' => $value,
        ];
    }
}