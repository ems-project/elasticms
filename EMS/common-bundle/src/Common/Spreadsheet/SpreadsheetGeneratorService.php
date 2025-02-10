<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Spreadsheet;

use EMS\CommonBundle\Common\Converter;
use EMS\CommonBundle\Contracts\Spreadsheet\SpreadsheetGeneratorServiceInterface;
use EMS\Helpers\File\TempFile;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class SpreadsheetGeneratorService implements SpreadsheetGeneratorServiceInterface
{
    /**
     * @param array{writer: string, filename: string, disposition: string, sheets: array<mixed>} $config
     */
    #[\Override]
    public function generateSpreadsheetFile(array $config, string $filename): void
    {
        $config = $this->resolveOptions($config);

        match ($config[self::WRITER]) {
            self::XLSX_WRITER => $this->getXlsxStreamedFile($config, $filename),
            self::CSV_WRITER => $this->getCsvStreamedFile($config, $filename),
            default => throw new \RuntimeException('Unknown Spreadsheet writer'),
        };
    }

    /**
     * @param array<mixed> $config
     */
    #[\Override]
    public function generateSpreadsheet(array $config): StreamedResponse
    {
        $config = $this->resolveOptions($config);

        $response = match ($config[self::WRITER]) {
            self::XLSX_WRITER => $this->getXlsxStreamedResponse($config),
            self::CSV_WRITER => $this->getCsvStreamedResponse($config),
            default => throw new \RuntimeException('Unknown Spreadsheet writer'),
        };

        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    /**
     * @param array<mixed> $config
     */
    #[\Override]
    public function generateSpreadsheetCacheableResponse(array $config): Response
    {
        $config = $this->resolveOptions($config);

        $response = match ($config[self::WRITER]) {
            self::XLSX_WRITER => $this->getXlsxResponse($config),
            self::CSV_WRITER => $this->getCsvResponse($config),
            default => throw new \RuntimeException('Unknown Spreadsheet writer'),
        };

        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    /**
     * @param array<mixed> $config
     */
    private function buildUpSheets(array $config): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $cache = new Psr16Cache(new FilesystemAdapter());
        Settings::setCache($cache);

        $i = 0;
        $maxCol = 1;
        foreach ($config[self::SHEETS] as $sheetConfig) {
            $sheet = (0 === $i) ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet($i);
            $sheet->setTitle($sheetConfig['name']);
            $j = 1;

            foreach ($sheetConfig['rows'] as $row) {
                $k = 1;
                foreach ($row as $value) {
                    if (\array_key_exists('validations', $sheetConfig) && null != $sheetConfig['validations'][$k - 1]) {
                        $spreadsheetValidation = $sheetConfig['validations'][$k - 1];
                        $validation = $spreadsheetValidation->addValidation($sheet->getCell(Coordinate::stringFromColumnIndex($k).$j)->getDataValidation());
                        $sheet->setDataValidation(Coordinate::stringFromColumnIndex($k).$j, $validation);
                    }

                    if (!\is_array($value)) {
                        $value = [self::CELL_DATA => $value];
                    }
                    $value = $this->resolveOptionsCell($value);
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($k).$j, Converter::stringify($value[self::CELL_DATA]));
                    if (!empty($value[self::CELL_STYLE])) {
                        $sheet->getStyle(Coordinate::stringFromColumnIndex($k).$j)
                            ->applyFromArray($value[self::CELL_STYLE]);
                    }
                    ++$k;
                    $maxCol = $k > $maxCol ? $k : $maxCol;
                }
                for ($z = 1; $z <= $maxCol; ++$z) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($z))->setAutoSize(true);
                }
                ++$j;
            }
            ++$i;
        }

        if (isset($config['active_sheet'])) {
            $spreadsheet->setActiveSheetIndex($config['active_sheet']);
        }

        return $spreadsheet;
    }

    /**
     * @return array<string, mixed>
     */
    private static function getDefaults(): array
    {
        return [
            self::CONTENT_FILENAME => 'spreadsheet',
            self::CONTENT_DISPOSITION => 'attachment',
            self::WRITER => self::XLSX_WRITER,
            self::CSV_SEPARATOR => ',',
            'active_sheet' => 0,
        ];
    }

    /**
     * @param array<mixed> $config
     *
     * @return array{writer: string, filename: string, disposition: string, sheets: array<mixed>, csv_separator: string}
     */
    private function resolveOptions(array $config): array
    {
        $defaults = self::getDefaults();

        $resolver = new OptionsResolver();
        $resolver->setDefaults($defaults);
        $resolver->setRequired([self::WRITER, self::CONTENT_FILENAME, self::SHEETS, self::CONTENT_DISPOSITION]);
        $resolver->setAllowedTypes(self::CONTENT_DISPOSITION, ['string']);
        $resolver->setAllowedValues(self::WRITER, [self::XLSX_WRITER, self::CSV_WRITER]);
        $resolver->setAllowedValues(self::CONTENT_DISPOSITION, ['attachment', 'inline']);

        /** @var array{writer: string, filename: string, disposition: string, sheets: array<mixed>, csv_separator: string} $resolved */
        $resolved = $resolver->resolve($config);

        return $resolved;
    }

    /**
     * @param array<mixed> $config
     *
     * @return array{data: string, style: array<mixed>}
     */
    private function resolveOptionsCell(array $config): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([self::CELL_STYLE => []]);
        $resolver->setRequired([self::CELL_DATA]);
        $resolver->setAllowedTypes(self::CELL_STYLE, ['array']);

        /** @var array{data: string, style: array<mixed>} $resolved */
        $resolved = $resolver->resolve($config);

        return $resolved;
    }

    /**
     * @param array{writer: string, filename: string, disposition: string, sheets: array<mixed>} $config
     */
    private function getXlsxStreamedFile(array $config, string $filename): void
    {
        $spreadsheet = $this->buildUpSheets($config);
        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);
    }

    /**
     * @param array{writer: string, filename: string, disposition: string, sheets: array<mixed>} $config
     */
    private function getXlsxStreamedResponse(array $config): StreamedResponse
    {
        $spreadsheet = $this->buildUpSheets($config);
        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $this->attachResponseHeader($response, $config, 'application/vnd.ms-excel');

        return $response;
    }

    /**
     * @param array{writer: string, filename: string, disposition: string, sheets: array<mixed>} $config
     */
    private function getXlsxResponse(array $config): Response
    {
        $spreadsheet = $this->buildUpSheets($config);

        $writer = new Xlsx($spreadsheet);
        $tempFile = TempFile::create();
        $writer->save($tempFile->path);
        $response = new Response($tempFile->getContents());
        $this->attachResponseHeader($response, $config, 'application/vnd.ms-excel');

        return $response;
    }

    /**
     * @param array{writer: string, filename: string, disposition: string, sheets: array<mixed>, csv_separator: string} $config
     */
    private function getCsvStreamedFile(array $config, string $filename): void
    {
        if (1 !== \count($config[self::SHEETS])) {
            throw new \RuntimeException('Exactly one sheet is expected by the CSV writer');
        }

        $handle = \fopen($filename, 'r+');
        if (false === $handle) {
            throw new \RuntimeException(\sprintf('Unexpected error while opening %s', $filename));
        }

        foreach ($config[self::SHEETS][0]['rows'] ?? [] as $row) {
            \fputcsv($handle, $row, $config[self::CSV_SEPARATOR]);
        }
        \fclose($handle);
    }

    /**
     * @param array{writer: string, filename: string, disposition: string, sheets: array<mixed>, csv_separator: string} $config
     */
    private function getCsvStreamedResponse(array $config): StreamedResponse
    {
        if (1 !== \count($config[self::SHEETS])) {
            throw new \RuntimeException('Exactly one sheet is expected by the CSV writer');
        }

        $response = new StreamedResponse(
            function () use ($config) {
                $handle = \fopen('php://output', 'r+');
                if (false === $handle) {
                    throw new \RuntimeException('Unexpected error while opening php://output');
                }

                foreach ($config[self::SHEETS][0]['rows'] ?? [] as $row) {
                    \fputcsv($handle, $row, $config[self::CSV_SEPARATOR]);
                }
            }
        );
        $this->attachResponseHeader($response, $config, 'text/csv; charset=utf-8');

        return $response;
    }

    /**
     * @param array{writer: string, filename: string, disposition: string, sheets: array<mixed>} $config
     */
    private function getCsvResponse(array $config): Response
    {
        if (1 !== \count($config[self::SHEETS])) {
            throw new \RuntimeException('Exactly one sheet is expected by the CSV writer');
        }

        $encoders = [new CsvEncoder([CsvEncoder::NO_HEADERS_KEY => true])];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $csvContent = $serializer->serialize($config[self::SHEETS][0]['rows'], $config[self::WRITER]);

        $response = new Response($csvContent);
        $this->attachResponseHeader($response, $config, 'text/csv; charset=utf-8');

        return $response;
    }

    /**
     * @param array{writer: string, filename: string, disposition: string, sheets: array<mixed>} $config
     */
    private function attachResponseHeader(Response|StreamedResponse $response, array $config, string $type): void
    {
        $response->headers->set('Content-Type', $type);
        $response->headers->set('Content-Disposition', \sprintf('%s;filename="%s.%s"', $config[self::CONTENT_DISPOSITION], $config[self::CONTENT_FILENAME], $config[self::WRITER]));
    }
}
