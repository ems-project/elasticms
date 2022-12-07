<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary;

use Elastica\Document;
use EMS\CommonBundle\Search\Search;
use EMS\CommonBundle\Service\ElasticaService;

class MediaLibraryService
{
    public function __construct(private readonly ElasticaService $elasticaService)
    {
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

        $search = new Search([$config->contentType->giveEnvironment()->getAlias()], $query);
        $search->setContentTypes([$config->contentType->getName()]);
        $search->setFrom(0);
        $search->setSize(10);

        $docs = $this->elasticaService->search($search)->getDocuments();

        return \array_map(fn (Document $doc) => MediaLibraryFile::createFromDocument($config, $doc)->toArray(), $docs);
    }
}
