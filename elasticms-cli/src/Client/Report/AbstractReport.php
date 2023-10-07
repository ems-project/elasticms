<?php

declare(strict_types=1);

namespace App\CLI\Client\Report;

use EMS\CommonBundle\Common\SpreadsheetGeneratorService;
use EMS\CommonBundle\Contracts\SpreadsheetGeneratorServiceInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;

abstract class AbstractReport
{
    private readonly SpreadsheetGeneratorService $spreadsheetGeneratorService;

    public function __construct()
    {
        $this->spreadsheetGeneratorService = new SpreadsheetGeneratorService();
    }

    public function generateXslxReport(): string
    {
        $config = [
            SpreadsheetGeneratorServiceInterface::CONTENT_DISPOSITION => HeaderUtils::DISPOSITION_ATTACHMENT,
            SpreadsheetGeneratorServiceInterface::WRITER => SpreadsheetGeneratorServiceInterface::XLSX_WRITER,
            SpreadsheetGeneratorServiceInterface::CONTENT_FILENAME => 'Audit-Report.xlsx',
            SpreadsheetGeneratorServiceInterface::SHEETS => $this->getSheets(),
        ];
        $tmpFilename = \tempnam(\sys_get_temp_dir(), 'Audit-Report-').'.xlsx';
        if (!\is_string($tmpFilename)) {
            throw new \RuntimeException('Not able to generate a temporary filename');
        }
        $this->spreadsheetGeneratorService->generateSpreadsheetFile($config, $tmpFilename);

        return $tmpFilename;
    }

    /**
     * @return array{array{name: string, rows: string[][]}}
     */
    abstract protected function getSheets(): array;
}
