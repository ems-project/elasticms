<?php

declare(strict_types=1);

namespace EMS\Xliff\Id;

final class SequentialIdGenerator implements IdGeneratorInterface
{
    private int $counter = 0;

    public function nextUnitId(): string
    {
        return \sprintf('tu%d', ++$this->counter);
    }

    public function nextUnitGroupId(): string
    {
        return \sprintf('grp%d', ++$this->counter);
    }

    public function nextSegmentId(): string
    {
        return \sprintf('s%d', ++$this->counter);
    }

    public function nextGroupId(): string
    {
        return \sprintf('g%d', ++$this->counter);
    }

    public function nextInlineCodeId(): string
    {
        return \sprintf('bx%d', ++$this->counter);
    }

    public function nextEndInlineCodeId(): string
    {
        return \sprintf('ex%d', ++$this->counter);
    }

    public function nextPlaceholderId(): string
    {
        return \sprintf('x%d', ++$this->counter);
    }

    public function nextReferenceId(): string
    {
        return \sprintf('rid%d', ++$this->counter);
    }
}
