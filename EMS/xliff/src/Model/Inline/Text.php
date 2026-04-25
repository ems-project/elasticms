<?php

declare(strict_types=1);

namespace EMS\Xliff\Model\Inline;

class Text extends Node
{
    public function __construct(public readonly string $text)
    {
    }
}
