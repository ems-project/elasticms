<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\InlineEdit\Dto;

use EMS\CommonBundle\Common\EMSLink;

class PayloadDto
{
    public ?string $url = null;
    public ?ElementDto $element = null;
    /** @var ElementDto[] */
    public array $elements = [];

    /**
     * @param array<int, array{ emsId: string, path: string, tag: string, selector: string }> $elements
     */
    public function setElements(array $elements): void
    {
        foreach ($elements as $element) {
            $this->elements[] = ElementDto::fromArray($element);
        }
    }

    /**
     * @return EMSLink[]
     */
    public function getEmsLinks(): array
    {
        $emsIds = \array_map(fn (ElementDto $element) => $element->emsId, $this->elements);
        $uniqueEmsIds = \array_unique($emsIds);

        return \array_map(fn (string $v) => EMSLink::fromText($v), $uniqueEmsIds);
    }

    /** @return ElementDto[] */
    public function getElementsByEmsLink(string $emsId): array
    {
        return \array_filter($this->elements, fn (ElementDto $element) => $element->emsId === $emsId);
    }
}
