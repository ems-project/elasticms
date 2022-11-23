<?php

declare(strict_types=1);

namespace App\Client\WebToElasticms\Config;

class Analyzer
{
    private string $name;
    private string $type;
    /** @var Extractor[] */
    private array $extractors;
    /** @var array<mixed> */
    private array $defaultData;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return Extractor[]
     */
    public function getExtractors(): array
    {
        return $this->extractors;
    }

    /**
     * @param Extractor[] $extractors
     */
    public function setExtractors(array $extractors): void
    {
        $this->extractors = $extractors;
    }

    /**
     * @return array<mixed>
     */
    public function getDefaultData(): array
    {
        return $this->defaultData;
    }

    /**
     * @param mixed[] $defaultData
     */
    public function setDefaultData(array $defaultData): void
    {
        $this->defaultData = $defaultData;
    }
}
