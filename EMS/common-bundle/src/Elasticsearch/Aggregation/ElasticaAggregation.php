<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Aggregation;

use Elastica\Aggregation\AbstractAggregation;

class ElasticaAggregation extends AbstractAggregation
{
    private ?string $basename = null;

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

    #[\Override]
    public function toArray(): array
    {
        $array = parent::toArray();

        if ('reverse_nested' === $this->basename) {
            $array['reverse_nested'] = (object) $array['reverse_nested'];
        }

        return $array;
    }

    // phpcs:disable
    #[\Override]
    protected function _getBaseName(): string
    {
        if (null === $this->basename) {
            throw new \RuntimeException('Unexpected null aggregation');
        }

        return $this->basename;
    }

    // phpcs:enable
}
