<?php

declare(strict_types=1);

namespace App\CLI\Client\Audit;

use App\CLI\Client\HttpClient\UrlReport;
use App\CLI\Client\Report\AbstractReport;
use EMS\CommonBundle\Helper\Url;

class Report extends AbstractReport
{
    /** @var string[][] */
    private array $accessibilityErrors = [['URL', 'WCAG2AA', 'Accessibility\'s score']];
    /** @var string[][] */
    private array $securityErrors = [['URL', 'Missing headers']];
    /** @var string[][] */
    private array $brokenLinks = [['URL', 'Status Code', 'Error message', 'Referers']];
    /** @var string[][] */
    private array $ignoredLinks = [['URL', 'Error message', 'Referrers']];
    /** @var string[][] */
    private array $warnings = [['URL', 'Warning message', 'Referrer']];

    public function addAccessibilityError(string $url, int $errorCount, ?float $score): void
    {
        $this->accessibilityErrors[] = [$url, \strval($errorCount), null === $score ? '' : \strval($score)];
    }

    public function addSecurityError(string $url, int $count): void
    {
        $this->securityErrors[] = [$url, \strval($count)];
    }

    public function addBrokenLink(UrlReport $urlReport): void
    {
        $hash = \sha1(\implode(':', [
            $urlReport->getUrl()->getUrl(),
            \strval($urlReport->getStatusCode()),
            $urlReport->getMessage() ?? '',
        ]));
        if (!isset($this->brokenLinks[$hash])) {
            $this->brokenLinks[$hash] = [
                'url' => $urlReport->getUrl()->getUrl(),
                'status_code' => \strval($urlReport->getStatusCode()),
                'message' => $urlReport->getMessage() ?? '',
                'referrers' => $urlReport->getUrl()->getReferer() ?? '',
            ];
        } elseif (\strlen($this->brokenLinks[$hash]['referrers']) > 1000) {
            $this->brokenLinks[$hash]['referrers'] .= '.';
        } else {
            $this->brokenLinks[$hash]['referrers'] .= ','.($urlReport->getUrl()->getReferer() ?? '');
        }
    }

    /**
     * @param string[] $warnings
     */
    public function addWarning(Url $url, array $warnings): void
    {
        foreach ($warnings as $warning) {
            $this->warnings[] = [$url->getUrl(), $warning, $url->getReferer() ?? ''];
        }
    }

    /**
     * @return string[][]
     */
    public function getAccessibilityErrors(): array
    {
        return $this->accessibilityErrors;
    }

    /**
     * @param string[][] $accessibilityErrors
     */
    public function setAccessibilityErrors(array $accessibilityErrors): void
    {
        $this->accessibilityErrors = $accessibilityErrors;
    }

    /**
     * @return string[][]
     */
    public function getSecurityErrors(): array
    {
        return $this->securityErrors;
    }

    /**
     * @param string[][] $securityErrors
     */
    public function setSecurityErrors(array $securityErrors): void
    {
        $this->securityErrors = $securityErrors;
    }

    /**
     * @return string[][]
     */
    public function getBrokenLinks(): array
    {
        return $this->brokenLinks;
    }

    /**
     * @param string[][] $brokenLinks
     */
    public function setBrokenLinks(array $brokenLinks): void
    {
        $this->brokenLinks = $brokenLinks;
    }

    /**
     * @return string[][]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * @param string[][] $warnings
     */
    public function setWarnings(array $warnings): void
    {
        $this->warnings = $warnings;
    }

    public function addIgnoredUrl(Url $url, string $message): void
    {
        $this->addIgnoredUrlWithReferer($url->getUrl(), $url->getReferer(), $message);
    }

    public function addIgnoredUrlWithReferer(string $url, ?string $referer, string $message): void
    {
        $hash = \sha1(\implode(':', [
            $url,
            $message,
        ]));
        if (!isset($this->ignoredLinks[$hash])) {
            $this->ignoredLinks[$hash] = [
                'url' => $url,
                'message' => $message,
                'referrers' => $referer ?? '',
            ];
        } elseif (\strlen($this->ignoredLinks[$hash]['referrers']) > 1000) {
            $this->ignoredLinks[$hash]['referrers'] .= '.';
        } else {
            $this->ignoredLinks[$hash]['referrers'] .= ','.($referer ?? '');
        }
    }

    /**
     * @return string[][]
     */
    public function getIgnoredLinks(): array
    {
        return $this->ignoredLinks;
    }

    /**
     * @param string[][] $ignoredLinks
     */
    public function setIgnoredLinks(array $ignoredLinks): void
    {
        $this->ignoredLinks = $ignoredLinks;
    }

    /**
     * @return array{array{name: string, rows: string[][]}}
     */
    #[\Override]
    protected function getSheets(): array
    {
        return [
            [
                'name' => 'Broken links',
                'rows' => \array_values($this->brokenLinks),
            ],
            [
                'name' => 'Ignored links',
                'rows' => \array_values($this->ignoredLinks),
            ],
            [
                'name' => 'Warnings',
                'rows' => \array_values($this->warnings),
            ],
            [
                'name' => 'Accessibility',
                'rows' => \array_values($this->accessibilityErrors),
            ],
            [
                'name' => 'Security',
                'rows' => \array_values($this->securityErrors),
            ],
        ];
    }
}
