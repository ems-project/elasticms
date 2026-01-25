<?php

declare(strict_types=1);

namespace EMS\Xliff\Model\Inline;

class PairedCode extends Node
{
    public function __construct(
        public readonly string $id,
        public readonly string $endId,
        public readonly string $referenceId,
        public readonly string $resourceName,
        public readonly string $equivalentOpeningText,
        public readonly string $equivalentClosingText,
    ) {
        parent::__construct();
    }
}
