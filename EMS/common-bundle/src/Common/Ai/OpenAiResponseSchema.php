<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Ai;

class OpenAiResponseSchema
{
    private string $type = 'object';
    /** @var array<mixed> */
    private array $properties = [];
    /** @var array<mixed> */
    private array $required = [];
    private bool $additionalProperties = false;

    public function addStringProperty(string $key): void
    {
        $this->properties[$key] = ['type' => 'string'];
        $this->required[] = $key;
    }

    /** @return array<mixed> */
    public function toArray(): array
    {
        $propertiesArray = [];
        foreach ($this->properties as $key => $property) {
            $propertiesArray[$key] = $property instanceof self ? $property->toArray() : $property;
        }

        return [
            'type' => $this->type,
            'properties' => $propertiesArray,
            'required' => $this->required,
            'additionalProperties' => $this->additionalProperties,
        ];
    }
}
