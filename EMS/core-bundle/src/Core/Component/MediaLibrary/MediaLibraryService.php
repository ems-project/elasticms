<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary;

use Elastica\Document;
use Elastica\Query\Exists;
use Elastica\Query\Nested;
use EMS\CommonBundle\Search\Search;
use EMS\CommonBundle\Service\ElasticaService;
use EMS\CoreBundle\Service\DataService;
use EMS\CoreBundle\Service\Revision\RevisionService;
use Ramsey\Uuid\Uuid;

class MediaLibraryService
{
    public function __construct(
        private readonly ElasticaService $elasticaService,
        private readonly RevisionService $revisionService,
        private readonly DataService $dataService,
    ) {
    }

    public function createFolder(MediaLibraryConfig $config, string $folderName): bool
    {
        $revision = $this->revisionService->create($config->contentType, Uuid::uuid4(), [
           $config->fieldPath => $folderName,
        ]);

        $form = $this->revisionService->createRevisionForm($revision);
        $this->dataService->finalizeDraft($revision, $form);

        return 0 === $form->getErrors(true)->count();
    }

    /**
     * @return array<int, array{
     *      path: string,
     *      file?: array{filename: string, filesize: int, mimetype: string, sha1: string }
     * }>
     */
    public function getFiles(MediaLibraryConfig $config): array
    {
        $query = $this->elasticaService->getBoolQuery();
        $query->addMust((new Nested())->setPath($config->fieldFile)->setQuery(new Exists($config->fieldFile)));

        $search = new Search([$config->contentType->giveEnvironment()->getAlias()], $query);
        $search->setContentTypes([$config->contentType->getName()]);
        $search->setFrom(0);
        $search->setSize(10);

        $docs = $this->elasticaService->search($search)->getDocuments();

        return \array_map(fn (Document $doc) => MediaLibraryFile::createFromDocument($config, $doc)->toArray(), $docs);
    }
}
