<?php

declare(strict_types=1);

namespace App\CLI\Client\Audit;

use App\CLI\Client\HttpClient\UrlReport;
use App\CLI\Helper\HtmlHelper;
use EMS\CommonBundle\Exception\NotParsableUrlException;
use EMS\CommonBundle\Helper\Url;

class AuditResult
{
    private string $hash;
    /** @var Url[] */
    private array $links = [];
    /** @var Url[] */
    private array $internalLinks = [];
    /** @var UrlReport[] */
    private array $externalLinks = [];
    /** @var SecurityWarning[] */
    private array $securityWarnings = [];
    private int $statusCode = 0;
    private ?string $errorMessage = null;
    /** @var string[] */
    private array $warnings = [];
    private ?string $mimetype = null;
    private readonly \DateTimeImmutable $datetime;
    private ?string $locale = null;
    private ?string $content = null;
    private bool $valid = true;
    private ?string $metaTitle = null;
    private ?string $title = null;
    private ?string $canonical = null;
    private ?string $author = null;
    private int $size = 0;
    private ?string $description = null;

    /**
     * @param string[] $labels
     */
    public function __construct(private readonly Url $url, private readonly string $baseUrl, private readonly array $labels)
    {
        $this->datetime = new \DateTimeImmutable();
    }

    public function getUrl(): Url
    {
        return $this->url;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return Url[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setErrorMessage(?string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function addSecurityWaring(string $type, string $value): void
    {
        $this->securityWarnings[] = new SecurityWarning($type, $value);
    }

    public function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
    }

    /**
     * @return string[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function setMimetype(string $mimetype): void
    {
        $this->mimetype = $mimetype;
    }

    public function hasLocale(): bool
    {
        return null !== $this->locale;
    }

    public function setLocale(?string $locale): void
    {
        if (null === $locale || '' === \trim($locale)) {
            $this->locale = null;

            return;
        }
        $this->locale = \trim($locale);
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return mixed[]
     */
    public function getRawData(): array
    {
        $security = [];
        foreach ($this->securityWarnings as $securityWarning) {
            $security[] = [
                'type' => $securityWarning->getType(),
                'value' => $securityWarning->getValue(),
            ];
        }
        $links = [];
        foreach ($this->externalLinks as $link) {
            $links[] = [
                'url' => $link->getUrl()->getUrl(),
                'message' => $link->getMessage(),
                'status_code' => $link->getStatusCode(),
            ];
        }

        return \array_filter([
            'url' => $this->url->getUrl(null, false, false),
            'base_url' => $this->baseUrl,
            'referer' => $this->url->getReferer(),
            'referer_label' => $this->url->getRefererLabel(),
            'import_hash_resources' => $this->hash,
            'security' => $security,
            'status_code' => $this->statusCode,
            'warning' => $this->warnings[0] ?? null,
            'mimetype' => $this->mimetype,
            'error' => $this->errorMessage,
            'host' => $this->url->getHost(),
            'links' => $links,
            'locale' => $this->locale,
            'meta_title' => $this->metaTitle,
            'title' => $this->title,
            'canonical' => $this->canonical,
            'content' => $this->content,
            'description' => $this->description,
            'size' => $this->size,
            'author' => $this->author,
            'timestamp' => $this->datetime->format('c'),
            'labels' => $this->labels,
        ], fn ($k) => null !== $k);
    }

    public function addInternalLink(Url $link): void
    {
        $this->internalLinks[] = $link;
    }

    /**
     * @return Url[]
     */
    public function getInternalLinks(): array
    {
        return $this->internalLinks;
    }

    public function addExternalLink(UrlReport $testUrl): void
    {
        $this->externalLinks[] = $testUrl;
    }

    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getUrlReport(): UrlReport
    {
        return new UrlReport($this->getUrl(), $this->getStatusCode(), $this->errorMessage);
    }

    /**
     * @return SecurityWarning[]
     */
    public function getSecurityWarnings(): array
    {
        return $this->securityWarnings;
    }

    public function setMetaTitle(?string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function setCanonical(?string $canonical): void
    {
        $this->canonical = $canonical;
    }

    public function setAuthor(?string $author): void
    {
        $this->author = $author;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function addLinks(HtmlHelper $htmlHelper, Report $report): void
    {
        foreach ($htmlHelper->getLinks() as $href => $label) {
            try {
                $this->links[$href] = new Url($href, $htmlHelper->getReferer()->getUrl(), $label);
            } catch (NotParsableUrlException $e) {
                $report->addIgnoredUrlWithReferer($e->getUrl(), $e->getReferer(), $e->getMessage());
            }
        }
    }
}
