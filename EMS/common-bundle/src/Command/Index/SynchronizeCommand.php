<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Index;

use Elastica\Aggregation\Max;
use Elastica\Aggregation\Terms as TermsAggregation;
use Elastica\Query;
use Elastica\Query\MatchAll;
use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Cluster\AggregationResult;
use EMS\CommonBundle\Common\Cluster\BucketResponse;
use EMS\CommonBundle\Common\Cluster\SimpleIndexClient;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Elasticsearch\Document\EMSSource;
use EMS\Helpers\ArrayHelper\ArrayHelper;
use EMS\Helpers\Standard\Json;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::INDEX_SYNCHRONIZE,
    description: 'Copy, or keep in sync, an ElasticMS indexes into another one',
    hidden: false
)]
class SynchronizeCommand extends AbstractCommand
{
    private const string ARGUMENT_SOURCE = 'source';
    private const string ARGUMENT_TARGET = 'target';
    private const string OPTION_BULK_SIZE = 'bulk-size';
    private const string OPTION_FORCE = 'force';
    public const string OPTION_SOURCE_HEADERS = 'source-headers';
    public const string OPTION_TARGET_HEADERS = 'target-headers';
    private const string AGGREGATION_CONTENT_TYPE = 'content-types';
    private const string AGGREGATION_PUBLISHED = 'published';
    private const string AGGREGATION_FINALIZED = 'finalized';
    private string $source;
    private string $target;
    private int $bulkSize;
    /**
     * @var string[]
     */
    private array $sourceHeaders;
    /**
     * @var string[]
     */
    private array $targetHeaders;
    private bool $force;
    private SimpleIndexClient $sourceClient;
    private SimpleIndexClient $targetClient;

    public function __construct(public readonly LoggerInterface $logger)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(
                self::ARGUMENT_SOURCE,
                InputArgument::REQUIRED,
                'The source to copy'
            )
            ->addArgument(
                self::ARGUMENT_TARGET,
                InputArgument::REQUIRED,
                'The target to copy'
            )
            ->addOption(
                self::OPTION_BULK_SIZE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Number of bulk size to copy',
                100
            )
            ->addOption(
                self::OPTION_FORCE,
                null,
                InputOption::VALUE_NONE,
                'A new index will be created and populated',
            )
            ->addOption(
                self::OPTION_SOURCE_HEADERS,
                null,
                InputOption::VALUE_OPTIONAL,
                'Extra headers of the source client (JSON encoded)',
                '[]'
            )
            ->addOption(
                self::OPTION_TARGET_HEADERS,
                null,
                InputOption::VALUE_OPTIONAL,
                'Extra headers of the target client (JSON encoded)',
                '[]'
            );
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->source = $this->getArgumentString(self::ARGUMENT_SOURCE);
        $this->target = $this->getArgumentString(self::ARGUMENT_TARGET);
        $this->bulkSize = $this->getOptionInt(self::OPTION_BULK_SIZE);
        $this->force = $this->getOptionBool(self::OPTION_FORCE);
        $this->targetHeaders = Json::decode($this->getOptionString(self::OPTION_TARGET_HEADERS));
        $this->sourceHeaders = Json::decode($this->getOptionString(self::OPTION_SOURCE_HEADERS));
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title(\sprintf('Synchronizing %s to %s', $this->source, $this->target));
        $this->sourceClient = SimpleIndexClient::create($this->source, $this->targetHeaders);
        if (!$this->sourceClient->isDefined()) {
            throw new \RuntimeException('Source index not found');
        }
        $this->io->info(\sprintf('Source index %s', $this->sourceClient->getIndex()));

        $this->targetClient = SimpleIndexClient::create($this->target, $this->sourceHeaders);
        $this->alignMappings();
        $this->io->info(\sprintf('Target index %s', $this->targetClient->getIndex()));
        $this->synchronizeContentTypes();
        $this->targetClient->switchAlias();

        return self::EXECUTE_SUCCESS;
    }

    private function alignMappings(): void
    {
        $sourceMapping = $this->sourceClient->getMappings();
        $targetMapping = [];
        if (!$this->force && $this->targetClient->isDefined()) {
            $targetMapping = $this->targetClient->getMappings();
        }
        unset($sourceMapping['_meta']);
        unset($targetMapping['_meta']);
        if (ArrayHelper::arrays_are_equal_recursive($sourceMapping, $targetMapping)) {
            $this->io->info('Target\'s mappings are aligned');

            return;
        }
        $metas = [
            'generator' => 'EMS synchronize command',
        ];
        if (!$this->force && $this->targetClient->isDefined() && $this->targetClient->updateMapping($sourceMapping, $metas)) {
            return;
        }

        $this->targetClient->createIndex($sourceMapping, $this->sourceClient->getSettings(), $metas);
    }

    private function synchronizeContentTypes(): void
    {
        $sourceContentTypes = $this->getContentTypes($this->sourceClient);
        $targetContentTypes = $this->getContentTypes($this->targetClient);
        foreach ($sourceContentTypes->getBuckets() as $contentType) {
            if ($targetContentTypes->hasKey($contentType->getKey())) {
                $inTarget = $targetContentTypes->getBucketByKey($contentType->getKey());
                if (
                    $contentType->getDocCount() === $inTarget->getDocCount()
                    && $contentType->getAggregation(self::AGGREGATION_PUBLISHED) === $inTarget->getAggregation(self::AGGREGATION_PUBLISHED)
                    && $contentType->getAggregation(self::AGGREGATION_FINALIZED) === $inTarget->getAggregation(self::AGGREGATION_FINALIZED)
                ) {
                    $this->io->info(\sprintf('Content type %s is aligned', $contentType->getKey()));
                    continue;
                }
            }
            $this->synchronizeDocuments($contentType);
        }
    }

    private function getContentTypes(SimpleIndexClient $sourceClient): AggregationResult
    {
        $aggregation = new TermsAggregation(self::AGGREGATION_CONTENT_TYPE);
        $aggregation->setSize(50);
        $aggregation->setField(EMSSource::FIELD_CONTENT_TYPE);
        $maxPublished = new Max(self::AGGREGATION_PUBLISHED);
        $maxPublished->setField(EMSSource::FIELD_PUBLICATION_DATETIME);
        $aggregation->addAggregation($maxPublished);
        $maxFinalized = new Max(self::AGGREGATION_FINALIZED);
        $maxFinalized->setField(EMSSource::FIELD_FINALIZATION_DATETIME);
        $aggregation->addAggregation($maxFinalized);
        $search = new MatchAll();
        $query = new Query($search);
        $query->setSize(0);
        $query->addAggregation($aggregation);

        return $sourceClient->search($query)->getAggregation(self::AGGREGATION_CONTENT_TYPE);
    }

    private function synchronizeDocuments(BucketResponse $contentType): void
    {
        $this->io->section(\sprintf('Synchronized the %s documents', $contentType->getKey()));
    }
}
