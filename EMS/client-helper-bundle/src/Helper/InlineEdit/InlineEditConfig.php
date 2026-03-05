<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\InlineEdit;

use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;

readonly class InlineEditConfig
{
    public function __construct(
        public DocumentInterface $document,
        public string $path,
        public string $element = 'div',
        /** @var array<string, scalar|null> */
        public array $attributes = [],
    ) {
    }
}
