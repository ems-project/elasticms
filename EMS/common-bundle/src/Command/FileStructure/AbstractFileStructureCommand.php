<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\FileStructure;

use Elastica\Result;
use EMS\CommonBundle\Common\Command\AbstractCommand;
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
    }

    protected function getDocument(string $index): ?Result
    {
        $query = $this->elasticaService->getTermsQuery($this->termField, [$this->identifier]);
        $search = $this->elasticaService->generateSearch([$index], $query);
        $search->setSources([$this->structureField]);
        $result = $this->elasticaService->search($search);
        $result = $result->getResults();
        if (0 === \count($result)) {
            $this->io->warning(\sprintf('Document %s=%s not found in index %s', $this->termField, $this->identifier, $index));

            return null;
        }

        if (\count($result) > 1) {
            $this->io->warning(\sprintf('%d documents found for %s=%s in index %s', \count($result), $this->termField, $this->identifier, $index));

            return null;
        }

        return $result[0];
    }
}
