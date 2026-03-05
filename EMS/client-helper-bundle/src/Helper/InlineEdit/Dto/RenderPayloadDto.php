<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\InlineEdit\Dto;

use EMS\CommonBundle\Common\EMSLink;

class RenderPayloadDto
{
    public string $url;
    /** @var array<int, array{ emsId: string, path: string, tag: string, selector: string }> */
    public array $elements = [];

    /** @return array<int, array{ path: string, tag: string, selector: string }> */
    public function getElementsByEmsLink(mixed $emsId): array
    {
        $elements = \array_filter($this->elements, fn (array $element) => $element['emsId'] === $emsId);

        return \array_map(function ($item) {
            unset($item['emsId']);

            return $item;
        }, $elements);
    }

    /**
     * @return EMSLink[]
     */
    public function getEmsLinks(): array
    {
        $emsIds = \array_column($this->elements, 'emsId');
        $uniqueEmsIds = \array_unique($emsIds);

        return \array_map(fn (string $v) => EMSLink::fromText($v), $uniqueEmsIds);
    }
}
