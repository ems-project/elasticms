<?php

namespace EMS\CommonBundle\Elasticsearch\Aggregation;

use Elastica\Aggregation\AbstractAggregation;

class ElasticaAggregation extends AbstractAggregation
{
    /** @var ?string */
    private $basename;

    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    /**
     * @param array<string, mixed> $param
     */
    public function setConfig(string $basename, array $param): void
    {
        $this->basename = $basename;
        $this->setParams($param);
    }

    // phpcs:disable
    protected function _getBaseName(): string
    {
        if (null === $this->basename) {
            throw new \RuntimeException('Unexpected null aggregation');
        }

        return $this->basename;
    }

    // phpcs:enable
}
