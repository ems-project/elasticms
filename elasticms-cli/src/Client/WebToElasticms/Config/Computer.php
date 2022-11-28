<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Config;

class Computer
{
    private string $property;
    private string $expression;
    private bool $jsonDecode = false;
    private string $condition = 'true';

    public function getProperty(): string
    {
        return $this->property;
    }

    public function setProperty(string $property): void
    {
        $this->property = $property;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function setExpression(string $expression): void
    {
        $this->expression = $expression;
    }

    public function isJsonDecode(): bool
    {
        return $this->jsonDecode;
    }

    public function setJsonDecode(bool $jsonDecode): void
    {
        $this->jsonDecode = $jsonDecode;
    }

    public function getCondition(): string
    {
        return $this->condition;
    }

    public function setCondition(string $condition): void
    {
        $this->condition = $condition;
    }
}
