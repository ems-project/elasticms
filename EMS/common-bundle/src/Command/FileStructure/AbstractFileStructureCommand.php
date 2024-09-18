<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\FileStructure;

use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Common\PropertyAccess\PropertyAccessor;
use EMS\CommonBundle\Elasticsearch\Document\Document;
use EMS\CommonBundle\Service\ElasticaService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AbstractFileStructureCommand extends AbstractCommand
{
    public const ARGUMENT_IDENTIFIER = 'identifier';
    public const OPTION_TERM_FIELD = 'term-field';
    public const OPTION_STRUCTURE_FIELD = 'structure-field';
    protected string $identifier;
    protected string $termField;
    protected string $structureField;
    protected PropertyAccessor $propertyAccessor;

    public function __construct(private readonly ElasticaService $elasticaService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(self::ARGUMENT_IDENTIFIER, InputArgument::REQUIRED, 'Document identifier');
        $this
            ->addOption(self::OPTION_TERM_FIELD, null, InputOption::VALUE_OPTIONAL, 'Term field used to get the document', '_id')
            ->addOption(self::OPTION_STRUCTURE_FIELD, null, InputOption::VALUE_OPTIONAL, 'Document field path containing the JSON menu nested structure', 'structure')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->identifier = $this->getArgumentString(self::ARGUMENT_IDENTIFIER);
        $this->termField = $this->getOptionString(self::OPTION_TERM_FIELD);
        $this->structureField = $this->getOptionString(self::OPTION_STRUCTURE_FIELD);
        $this->propertyAccessor = PropertyAccessor::createPropertyAccessor();
    }

    protected function getDocument(string $index): Document
    {
        $query = $this->elasticaService->getTermsQuery($this->termField, [$this->identifier]);
        $search = $this->elasticaService->generateSearch([$index], $query);
        $search->setSources([$this->structureField]);
        $results = $this->elasticaService->search($search)->getResults();

        if (0 === \count($results)) {
            throw new \RuntimeException(\sprintf('Document %s=%s not found in index %s', $this->termField, $this->identifier, $index));
        }

        if (\count($results) > 1) {
            throw new \RuntimeException(\sprintf('%d documents found for %s=%s in index %s', \count($results), $this->termField, $this->identifier, $index));
        }

        return Document::fromResult($results[0]);
    }
}
