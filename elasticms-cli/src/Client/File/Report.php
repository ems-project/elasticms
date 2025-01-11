<?php

declare(strict_types=1);

namespace App\CLI\Client\File;

use App\CLI\Client\Report\AbstractReport;

class Report extends AbstractReport
{
    /** @var string[][] */
    private array $warnings = [['type', 'file', 'message']];

    public function addWarning(string $type, string $filename, string $message): void
    {
        $this->warnings[] = [
            $type,
            $filename,
            $message,
        ];
    }

    /**
     * @return string[][]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * @return array{array{name: string, rows: string[][]}}
     */
    #[\Override]
    protected function getSheets(): array
    {
        return [
            [
                'name' => 'Warnings',
                'rows' => \array_values($this->warnings),
            ],
        ];
    }
}
