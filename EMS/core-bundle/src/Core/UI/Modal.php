<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\UI;

class Modal implements \JsonSerializable
{
    public function __construct(
        public ?string $title = null,
        public ?string $body = null,
        public ?string $footer = null
    ) {
    }

    /**
     * @return array{modalTitle?: string, modalBody?: string, modalFooter?: string}
     */
    public function jsonSerialize(): array
    {
        return \array_filter([
            'modalTitle' => $this->title,
            'modalBody' => $this->body,
            'modalFooter' => $this->footer,
        ]);
    }
}
