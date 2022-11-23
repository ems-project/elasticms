<?php

namespace EMS\Xliff\Xliff\Entity;

class InsertError
{
    private string $expectedSourceValue;
    private string $sourceValue;
    private string $sourcePropertyPath;
    private string $contentType;
    private string $ouuid;
    private string $revisionId;

    public function __construct(string $expectedSourceValue, string $sourceValue, string $sourcePropertyPath, string $contentType, string $ouuid, string $revisionId)
    {
        $this->expectedSourceValue = $expectedSourceValue;
        $this->sourceValue = $sourceValue;
        $this->sourcePropertyPath = $sourcePropertyPath;
        $this->contentType = $contentType;
        $this->ouuid = $ouuid;
        $this->revisionId = $revisionId;
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
