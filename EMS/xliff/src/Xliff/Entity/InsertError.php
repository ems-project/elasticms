<?php

declare(strict_types=1);

namespace EMS\Xliff\Xliff\Entity;

class InsertError
{
    public function __construct(private readonly string $expectedSourceValue, private readonly string $sourceValue, private readonly string $sourcePropertyPath, private readonly string $contentType, private readonly string $ouuid, private readonly string $revisionId)
    {
    }

    public function getRevisionIdentifier(): string
    {
        return \join('_', [
            $this->contentType,
            $this->ouuid,
            $this->revisionId,
        ]);
    }

    public function getExpected(): string
    {
        return $this->expectedSourceValue;
    }

    public function getReceived(): string
    {
        return $this->sourceValue;
    }

    public function getSourcePropertyPath(): string
    {
        return $this->sourcePropertyPath;
    }
}
