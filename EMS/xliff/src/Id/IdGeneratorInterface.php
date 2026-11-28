<?php

declare(strict_types=1);

namespace EMS\Xliff\Id;

interface IdGeneratorInterface
{
    public function nextUnitId(): string;

    public function nextUnitGroupId(): string;

    public function nextSegmentId(): string;

    public function nextGroupId(): string;

    public function nextInlineCodeId(): string;

    public function nextEndInlineCodeId(): string;

    public function nextPlaceholderId(): string;

    public function nextReferenceId(): string;
}
