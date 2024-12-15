<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface SpreadsheetGeneratorServiceInterface
{
    public const WRITER = 'writer';
    public const XLSX_WRITER = 'xlsx';
    public const CSV_WRITER = 'csv';
    public const FORMAT_WRITERS = [self::CSV_WRITER, self::XLSX_WRITER];
    public const CSV_SEPARATOR = 'csv_separator';
    public const SHEETS = 'sheets';
    public const CONTENT_FILENAME = 'filename';
    public const CONTENT_DISPOSITION = 'disposition';
    public const CELL_DATA = 'data';
    public const CELL_STYLE = 'style';

    /**
     * @param array<mixed> $config
     */
    public function generateSpreadsheetFile(array $config, string $filename): void;

    /**
     * @param array<mixed> $config
     */
    public function generateSpreadsheet(array $config): StreamedResponse;

    /**
     * @param array<mixed> $config
     */
    public function generateSpreadsheetCacheableResponse(array $config): Response;
}
