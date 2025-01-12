<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Elastica;

use Elastica\ResultSet as ElasticaResultSet;

class ResultSet extends ElasticaResultSet
{
    #[\Override]
    public function getTotalHits(): int
    {
        return $this->getResponse()->getData()['hits']['total']['value'] ?? $this->getResponse()->getData()['hits']['total'] ?? 0;
    }
}
