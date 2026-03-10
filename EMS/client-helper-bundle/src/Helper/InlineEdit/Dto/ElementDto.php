<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\InlineEdit\Dto;

use EMS\CommonBundle\Common\EMSLink;

readonly class ElementDto
{
    public EMSLink $emsLink;

    public function __construct(
        public string $emsId,
        public string $path,
        public string $tag,
        public string $selector,
    ) {
        $this->emsLink = EMSLink::fromText($this->emsId);
    }

    /**
     * @param array{ emsId: string, path: string, tag: string, selector: string } $element
     */
    public static function fromArray(array $element): ElementDto
    {
        return new self(
            emsId: $element['emsId'],
            path: $element['path'],
            tag: $element['tag'],
            selector: $element['selector'],
        );
    }
}
