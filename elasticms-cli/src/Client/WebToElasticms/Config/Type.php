<?php

declare(strict_types=1);

namespace App\Client\WebToElasticms\Config;

class Type
{
    /** @var array<mixed> */
    private array $defaultData = [];
    private string $name;
    /** @var Computer[] */
    private array $computers = [];
    /** @var string[] */
    private $tempFields = [];

    /**
     * @return mixed[]
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Computer[]
     */
    public function getComputers(): array
    {
        return $this->computers;
    }

    /**
     * @param Computer[] $computers
     */
    public function setComputers(array $computers): void
    {
        $this->computers = $computers;
    }

    /**
     * @return string[]
     */
    public function getTempFields(): array
    {
        return $this->tempFields;
    }

    /**
     * @param string[] $tempFields
     */
    public function setTempFields(array $tempFields): void
    {
        $this->tempFields = $tempFields;
    }
}
