<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\InlineEditor;

class InlineEditorResponse implements \JsonSerializable
{
    /**
     * @param array<mixed> $data
     */
    public function __construct(private array $data = [])
    {
    }

    public function render(string $className, string $content): self
    {
        $this->data['render'][$className] = $content;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
