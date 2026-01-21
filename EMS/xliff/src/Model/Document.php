<?php

declare(strict_types=1);

namespace EMS\Xliff\Model;

class Document
{
    public function __construct(public readonly string $id)
    {
    }
}
