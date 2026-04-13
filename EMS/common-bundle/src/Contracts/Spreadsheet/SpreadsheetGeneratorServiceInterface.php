<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Spreadsheet;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface SpreadsheetGeneratorServiceInterface
{
    public const string WRITER = 'writer';
    public const string XLSX_WRITER = 'xlsx';
    public const string CSV_WRITER = 'csv';
    /** @var string[] */
    public const array FORMAT_WRITERS = [self::CSV_WRITER, self::XLSX_WRITER];
    public const string CSV_SEPARATOR = 'csv_separator';
    public const string SHEETS = 'sheets';
    public const string CONTENT_FILENAME = 'filename';
    public const string CREATOR = 'creator';
    public const string CONTENT_DISPOSITION = 'disposition';
    public const string VALUE_BINDER = 'value_binder';

    /**
     * @param array<mixed> $config
     */
    public function generateSpreadsheetFile(array $config, string $filename, bool $normalized = false): void;

    /**
     * @param array<mixed> $config
     */
    public function generateSpreadsheet(array $config): StreamedResponse;

    /**
     * @param array<mixed> $config
     */
    public function generateSpreadsheetCacheableResponse(array $config): Response;
}
