<?php

declare(strict_types=1);

namespace EMS\Xliff\Xliff\Entity;

class InsertReport
{
    /** @var InsertError[][] */
    private array $errors = [];

    public function addError(string $expectedSourceValue, string $sourceValue, string $sourcePropertyPath, string $contentType, string $ouuid, string $revisionId): void
    {
        $this->errors[$revisionId][] = new InsertError($expectedSourceValue, $sourceValue, $sourcePropertyPath, $contentType, $ouuid, $revisionId);
    }

    public function countErrors(): int
    {
        return \count($this->errors);
    }

    public function export(string $filename): void
    {
        $zip = new \ZipArchive();

        if (!$zip->open($filename, \ZipArchive::CREATE)) {
            throw new \RuntimeException(\sprintf('Impossible to create the archive %s', $filename));
        }

        foreach ($this->errors as $document) {
            foreach ($document as $error) {
                $zip->addFromString($error->getRevisionIdentifier().'_expected.html', $error->getExpected());
                $zip->addFromString($error->getRevisionIdentifier().'_received.html', $error->getReceived());
            }
        }

        $zip->close();
    }
}
