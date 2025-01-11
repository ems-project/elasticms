<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Config;

class Extractor
{
    final public const string ONE = '1';
    final public const string ZERO_ONE = '0-1';
    final public const string N = 'n';
    final public const string FIRST = 'first';
    private string $selector;
    private ?string $attribute = null;
    private string $property;
    /** @var string[] */
    private array $filters = [];
    private string $strategy = self::ONE;

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function setSelector(string $selector): void
    {
        $this->selector = $selector;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function setProperty(string $property): void
    {
        $this->property = $property;
    }

    /**
     * @return string[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param string[] $filters
     */
    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    public function getAttribute(): ?string
    {
        return $this->attribute;
    }

    public function setAttribute(?string $attribute): void
    {
        $this->attribute = $attribute;
    }

    public function getStrategy(): string
    {
        return $this->strategy;
    }

    public function setStrategy(string $strategy): void
    {
        $this->strategy = $strategy;
    }
}
