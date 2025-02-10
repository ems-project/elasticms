<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Rapport;

use App\CLI\Client\HttpClient\CacheManager;
use App\CLI\Client\WebToElasticms\Config\Document;
use App\CLI\Client\WebToElasticms\Config\Extractor;
use App\CLI\Client\WebToElasticms\Config\WebResource;
use EMS\CommonBundle\Common\Spreadsheet\SpreadsheetGeneratorService;
use EMS\CommonBundle\Contracts\Spreadsheet\SpreadsheetGeneratorServiceInterface;
use EMS\CommonBundle\Helper\Url;
use Symfony\Component\HttpFoundation\HeaderUtils;

class Rapport
{
    /** @var string[][] */
    private array $missingInternalUrls = [['Path', 'URL', 'Code', 'Message', 'Referrers']];
    /** @var string[][] */
    private array $nothingExtracted = [['Type', 'OUUID', 'URLs']];
    /** @var string[][] */
    private array $extractErrors = [['Type', 'URL', 'Locale', 'Selector', 'Strategy', 'Property', 'Attribute', 'Count']];
    /** @var string[][] */
    private array $urlsInError = [['Doc\'s URLs', 'URLs', 'Code', 'Message', 'Type']];
    /** @var string[][] */
    private array $dataLinksInError = [['Path', 'Referrers']];
    /** @var string[][] */
    private array $assetsInError = [['Path', 'Referrers', 'Message']];
    /** @var string[][] */
    private array $updatedDocuments = [['CRUD', 'Content Type', 'OUUID', 'Locale', 'URL']];

    private readonly string $filename;
    private readonly SpreadsheetGeneratorService $spreadsheetGeneratorService;

    public function __construct(private readonly CacheManager $cacheManager, string $folder)
    {
        $this->filename = $folder.DIRECTORY_SEPARATOR.\sprintf('WebToElasticms-Rapport-%s.xlsx', \date('Ymd-His'));
        $this->spreadsheetGeneratorService = new SpreadsheetGeneratorService();
    }

    public function save(): void
    {
        $config = [
            SpreadsheetGeneratorServiceInterface::CONTENT_DISPOSITION => HeaderUtils::DISPOSITION_ATTACHMENT,
            SpreadsheetGeneratorServiceInterface::WRITER => SpreadsheetGeneratorServiceInterface::XLSX_WRITER,
            SpreadsheetGeneratorServiceInterface::CONTENT_FILENAME => 'WebToElasticms-Rapport.xlsx',
            SpreadsheetGeneratorServiceInterface::SHEETS => [
                [
                    'name' => 'URLs in error',
                    'rows' => \array_values($this->urlsInError),
                ],
                [
                    'name' => 'Broken internal links',
                    'rows' => \array_values($this->missingInternalUrls),
                ],
                [
                    'name' => 'Extract errors',
                    'rows' => \array_values($this->extractErrors),
                ],
                [
                    'name' => 'Nothing extracted',
                    'rows' => \array_values($this->nothingExtracted),
                ],
                [
                    'name' => 'DataLinks error',
                    'rows' => \array_values($this->dataLinksInError),
                ],
                [
                    'name' => 'Asset in error',
                    'rows' => \array_values($this->assetsInError),
                ],
                [
                    'name' => 'Updated documents',
                    'rows' => \array_values($this->updatedDocuments),
                ],
            ],
        ];
        $this->spreadsheetGeneratorService->generateSpreadsheetFile($config, $this->filename);
    }

    public function inUrlsNotFounds(Url $url): bool
    {
        if (!isset($this->missingInternalUrls[$url->getPath()])) {
            return false;
        }
        $this->missingInternalUrls[$url->getPath()][] = $url->getReferer() ?? '';

        return true;
    }

    public function inDataLinkNotFounds(string $path, string $currentUrl): void
    {
        $this->dataLinksInError[] = [$path, $currentUrl];
    }

    public function inAssetsError(string $path, ?string $currentUrl, ?string $message): void
    {
        $this->assetsInError[] = [$path, $currentUrl ?? 'null', $message ?? ''];
    }

    public function addUrlNotFound(Url $url): void
    {
        $urlReport = $this->cacheManager->testUrl($url);
        $this->missingInternalUrls[$url->getPath()] = [$url->getPath(), $url->getUrl(), (string) $urlReport->getStatusCode(), $urlReport->getMessage() ?? '', $url->getReferer() ?? 'N/A'];
    }

    public function addResourceInError(WebResource $resource, Url $url, int $code, string $message, string $type = 'import'): void
    {
        $this->urlsInError[] = [$resource->getUrl(), $url->getUrl(), (string) $code, $message, $type];
    }

    public function addNothingExtracted(Document $document): void
    {
        $data = [
            $document->getType(),
            $document->getOuuid(),
        ];
        foreach ($document->getResources() as $resource) {
            $data[] = $resource->getUrl();
        }

        $this->nothingExtracted[] = $data;
    }

    public function addExtractError(WebResource $resource, Extractor $extractor, int $count): void
    {
        $this->extractErrors[] = [$resource->getType(), $resource->getUrl(), $resource->getLocale(), $extractor->getSelector(), $extractor->getStrategy(), $extractor->getProperty(), $extractor->getAttribute() ?? '', (string) $count];
    }

    public function addNewDocument(Document $document): void
    {
        $this->addUpdateDocument($document, 'new');
    }

    public function addUpdateDocument(Document $document, string $updateType = 'update'): void
    {
        foreach ($document->getResources() as $resource) {
            $this->updatedDocuments[] = [$updateType, $document->getType(), $document->getOuuid(), $resource->getLocale(), $resource->getUrl()];
        }
    }
}
