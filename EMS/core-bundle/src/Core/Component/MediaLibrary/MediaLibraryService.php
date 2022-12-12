<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary;

use Elastica\Document;
use Elastica\Query\AbstractQuery;
use Elastica\Query\Exists;
use Elastica\Query\Nested;
use Elastica\ResultSet;
use EMS\CommonBundle\Search\Search;
use EMS\CommonBundle\Service\ElasticaService;
use EMS\CoreBundle\Service\DataService;
use EMS\CoreBundle\Service\Mapping;
use EMS\CoreBundle\Service\Revision\RevisionService;
use Ramsey\Uuid\Uuid;

class MediaLibraryService
{
    public function __construct(
        private readonly ElasticaService $elasticaService,
        private readonly RevisionService $revisionService,
        private readonly DataService $dataService
    ) {
    }

    /**
     * @param array{filename: string, filesize: string, mimetype: string} $file
     */
    public function createFile(MediaLibraryConfig $config, string $fileHash, array $file): bool
    {
        return $this->create($config, [
            $config->fieldPath => $file['filename'],
            $config->fieldFile => \array_merge($file, [
                Mapping::HASH_FIELD => $fileHash,
            ]),
        ]);
    }

    public function createFolder(MediaLibraryConfig $config, string $folderName): bool
    {
        return $this->create($config, [
            $config->fieldPath => $folderName,
        ]);
    }

    /**
     * @return array<int, array{
     *      path: string,
     *      file?: array{filename: string, filesize: int, mimetype: string, sha1: string }
     * }>
     */
    public function getFiles(MediaLibraryConfig $config): array
    {
        $searchQuery = $this->elasticaService->getBoolQuery();
        $searchQuery->addMust((new Nested())->setPath($config->fieldFile)->setQuery(new Exists($config->fieldFile)));

        $docs = $this->search($config, $searchQuery)->getDocuments();

        return \array_map(fn (Document $doc) => MediaLibraryFile::createFromDocument($config, $doc)->toArray(), $docs);
    }

    /**
     * @return array<int, array{ name: string }>
     */
    public function getFolders(MediaLibraryConfig $config): array
    {
        $searchQuery = $this->elasticaService->getBoolQuery();
        $searchQuery->addMustNot((new Nested())->setPath($config->fieldFile)->setQuery(new Exists($config->fieldFile)));

        $docs = $this->search($config, $searchQuery)->getDocuments();

        return \array_map(fn (Document $doc) => ['name' => $doc->get($config->fieldPath)], $docs);
    }

    /**
     * @param array<mixed> $rawData
     */
    private function create(MediaLibraryConfig $config, array $rawData): bool
    {
        $revision = $this->revisionService->create($config->contentType, Uuid::uuid4(), $rawData);

        $form = $this->revisionService->createRevisionForm($revision);
        $this->dataService->finalizeDraft($revision, $form);

        return 0 === $form->getErrors(true)->count();
    }

    private function search(MediaLibraryConfig $config, AbstractQuery $query): ResultSet
    {
        $search = new Search([$config->contentType->giveEnvironment()->getAlias()], $query);
        $search->setContentTypes([$config->contentType->getName()]);
        $search->setFrom(0);
        $search->setSize(100);

        return $this->elasticaService->search($search);
    }
}
