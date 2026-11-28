<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command;

use Elastica\Aggregation\Max;
use Elastica\Aggregation\Terms as TermsAggregation;
use Elastica\Query;
use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Elasticsearch\Document\EMSSource;
use EMS\CommonBundle\Elasticsearch\Sync\Aggregation;
use EMS\CommonBundle\Elasticsearch\Sync\Bucket;
use EMS\CommonBundle\Elasticsearch\Sync\BulkRequest;
use EMS\CommonBundle\Elasticsearch\Sync\SearchResponse;
use EMS\CommonBundle\Elasticsearch\Sync\Synchronizer;
use EMS\Helpers\ArrayHelper\ArrayHelper;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
    private const string OPTION_KEEP_ALIVE = 'keep-alive';
    private const string OPTION_FORCE = 'force';
    public const string OPTION_SOURCE_HEADERS = 'source-headers';
    public const string OPTION_TARGET_HEADERS = 'target-headers';
    public const string OPTION_KEYWORD_FIELD = 'keyword-field';
    public const string OPTION_AGGS_SIZE = 'aggs-size';
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
    private Synchronizer $sourceClient;
    private Synchronizer $targetClient;
    private string $keepAlive;
    private string $keywordField;
    private int $aggsSize;

    public function __construct(private readonly HttpClientInterface $httpClient)
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
                500
            )
            ->addOption(
                self::OPTION_KEEP_ALIVE,
                null,
                InputOption::VALUE_OPTIONAL,
                'TTL of the bulk/scroll',
                '2m'
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
            )
            ->addOption(
                self::OPTION_KEYWORD_FIELD,
                null,
                InputOption::VALUE_OPTIONAL,
                'The keyword field used to group data',
                EMSSource::FIELD_CONTENT_TYPE
            )
            ->addOption(
                self::OPTION_AGGS_SIZE,
                null,
                InputOption::VALUE_OPTIONAL,
                'The aggregation size on the keyword field',
                50
            );
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->source = $this->getArgumentString(self::ARGUMENT_SOURCE);
        $this->target = $this->getArgumentString(self::ARGUMENT_TARGET);
        $this->bulkSize = $this->getOptionInt(self::OPTION_BULK_SIZE);
        $this->aggsSize = $this->getOptionInt(self::OPTION_AGGS_SIZE);
        $this->keepAlive = $this->getOptionString(self::OPTION_KEEP_ALIVE);
        $this->force = $this->getOptionBool(self::OPTION_FORCE);
        $this->targetHeaders = Json::decode($this->getOptionString(self::OPTION_TARGET_HEADERS));
        $this->sourceHeaders = Json::decode($this->getOptionString(self::OPTION_SOURCE_HEADERS));
        $this->keywordField = $this->getOptionString(self::OPTION_KEYWORD_FIELD);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title(\sprintf('Synchronizing %s to %s', $this->source, $this->target));
        $this->sourceClient = Synchronizer::create($this->httpClient, $this->source, $this->sourceHeaders);
        if (!$this->sourceClient->isDefined()) {
            throw new \RuntimeException('Source index not found');
        }
        $this->io->info(\sprintf('Source index %s', $this->sourceClient->getIndex()));

        $this->targetClient = Synchronizer::create($this->httpClient, $this->target, $this->targetHeaders);
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
            $this->io->info("Target's mappings are aligned");

            return;
        }
        $metas = [
            'generator' => 'EMS synchronize command',
        ];
        if (!$this->force && $this->targetClient->isDefined() && $this->targetClient->updateMapping($sourceMapping, $metas)) {
            return;
        }

        $this->targetClient->createIndex($this->httpClient, $sourceMapping, $this->sourceClient->getSettings(), $metas);
    }

    private function synchronizeContentTypes(): void
    {
        $sourceContentTypes = $this->getContentTypes($this->sourceClient);
        $targetContentTypes = $this->getContentTypes($this->targetClient);
        foreach ($sourceContentTypes->getBuckets() as $contentType) {
            $inTarget = null;
            if (!$this->force && $targetContentTypes->hasKey($contentType->getKey())) {
                $inTarget = $targetContentTypes->getBucketByKey($contentType->getKey());
                if (
                    $contentType->getDocCount() === $inTarget->getDocCount()
                    && $contentType->getAggregation(self::AGGREGATION_PUBLISHED)->getValueAsString() === $inTarget->getAggregation(self::AGGREGATION_PUBLISHED)->getValueAsString()
                    && $contentType->getAggregation(self::AGGREGATION_FINALIZED)->getValueAsString() === $inTarget->getAggregation(self::AGGREGATION_FINALIZED)->getValueAsString()
                ) {
                    $this->io->info(\sprintf('Content type %s is aligned with %d documents', $contentType->getKey(), $inTarget->getDocCount()));
                    continue;
                }
            }
            $ids = $this->synchronizeDocuments($contentType, $this->force || null === $inTarget);
            $this->deleteDocuments($contentType, $ids);
        }

        foreach ($targetContentTypes->getBuckets() as $contentType) {
            if ($sourceContentTypes->hasKey($contentType->getKey())) {
                continue;
            }
            $this->deleteDocuments($contentType);
        }
    }

    private function getContentTypes(Synchronizer $sourceClient): Aggregation
    {
        $aggregation = new TermsAggregation(self::AGGREGATION_CONTENT_TYPE);
        $aggregation->setSize($this->aggsSize);
        $aggregation->setField($this->keywordField);

        $maxPublished = new Max(self::AGGREGATION_PUBLISHED);
        $maxPublished->setField(EMSSource::FIELD_PUBLICATION_DATETIME);

        $aggregation->addAggregation($maxPublished);
        $maxFinalized = new Max(self::AGGREGATION_FINALIZED);
        $maxFinalized->setField(EMSSource::FIELD_FINALIZATION_DATETIME);

        $aggregation->addAggregation($maxFinalized);
        $query = new Query(['query' => ['bool' => ['must' => []]]]);
        $query->setSize(0);
        $query->addAggregation($aggregation);

        return $sourceClient->search($query)->getAggregation(self::AGGREGATION_CONTENT_TYPE);
    }

    /**
     * @return array<string, int>
     */
    private function synchronizeDocuments(Bucket $contentType, bool $force): array
    {
        $this->io->section(\sprintf('Synchronized the %s documents', $contentType->getKey()));
        $search = new Query\Terms($this->keywordField, [$contentType->getKey()]);
        $query = new Query($search);
        $query->setSize($this->bulkSize);

        $documents = $this->sourceClient->search($query, $this->keepAlive);
        $this->io->progressStart($documents->getTotal());
        $status = [];
        do {
            $status = \array_merge($status, $this->synchronizeBulk($documents, $force));
            $this->io->progressAdvance($documents->countHits());
            $scrollId = $documents->getScrollId();
            $documents = $this->sourceClient->scroll($scrollId, $this->keepAlive);
        } while ($documents->countHits() > 0);
        $this->sourceClient->closeScroll($scrollId);
        $this->io->progressFinish();

        return $status;
    }

    /**
     * @return array<string, int>
     */
    private function synchronizeBulk(SearchResponse $documents, bool $force): array
    {
        $status = [];
        $documentsInTarget = null;
        if (!$force) {
            $search = new Query\Terms(Synchronizer::ID, $documents->getIds());
            $query = new Query($search);
            $query->setSize($documents->countHits());
            $query->setSource([
                EMSSource::FIELD_HASH,
                EMSSource::FIELD_FINALIZATION_DATETIME,
                EMSSource::FIELD_PUBLICATION_DATETIME,
            ]);
            $documentsInTarget = $this->targetClient->search($query);
        }

        $bulk = new BulkRequest();
        foreach ($documents->getHits() as $document) {
            if (
                null !== $documentsInTarget
                && null !== ($target = $documentsInTarget->getById($document->getId()))
                && $target->get(EMSSource::FIELD_HASH) === $document->get(EMSSource::FIELD_HASH)
                && $target->get(EMSSource::FIELD_FINALIZATION_DATETIME) === $document->get(EMSSource::FIELD_FINALIZATION_DATETIME)
                && $target->get(EMSSource::FIELD_PUBLICATION_DATETIME) === $document->get(EMSSource::FIELD_PUBLICATION_DATETIME)
            ) {
                $status[$document->getId()] = 200;
                continue;
            }
            $bulk->index($document->getId(), $document->getSource());
        }
        if ($bulk->empty()) {
            return $status;
        }

        return \array_merge($status, $this->targetClient->bulk($bulk));
    }

    /**
     * @param array<string, int> $butIds
     */
    private function deleteDocuments(Bucket $contentType, array $butIds = []): void
    {
        $search = new Query\Terms($this->keywordField, [$contentType->getKey()]);
        $query = new Query($search);
        $query->setSource(false);
        $query->setSize($this->bulkSize);

        $documents = $this->targetClient->search($query, $this->keepAlive);
        if ($documents->getTotal() <= \count($butIds)) {
            $this->sourceClient->closeScroll($documents->getScrollId());

            return;
        }

        $this->io->section(\sprintf('Delete %d of the %s documents', $documents->getTotal() - \count($butIds), $contentType->getKey()));
        $this->io->progressStart($documents->getTotal());
        do {
            $this->deleteBulk($documents, $butIds);
            $this->io->progressAdvance($documents->countHits());
            $scrollId = $documents->getScrollId();
            $documents = $this->sourceClient->scroll($scrollId, $this->keepAlive);
        } while ($documents->countHits() > 0);
        $this->sourceClient->closeScroll($scrollId);
        $this->io->progressFinish();
    }

    /**
     * @param array<string, int> $butIds
     */
    private function deleteBulk(SearchResponse $documents, array $butIds): void
    {
        $bulk = new BulkRequest();
        foreach ($documents->getHits() as $document) {
            if (isset($butIds[$document->getId()])) {
                continue;
            }
            $bulk->delete($document->getId());
        }
        if ($bulk->empty()) {
            return;
        }
        $this->targetClient->bulk($bulk);
    }
}
