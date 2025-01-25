<?php

declare(strict_types=1);

namespace App\CLI\Client\Audit;

use App\CLI\Client\HttpClient\HttpResult;
use App\CLI\Helper\HtmlHelper;
use EMS\CommonBundle\Helper\Url;
use Psr\Log\LoggerInterface;

class AuditManager
{
    /**
     * @param string[] $labels
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $baseUrl,
        private readonly array $labels,
    ) {
    }

    public function analyze(Url $url, HttpResult $result, Report $report, bool $alreadyAudited): AuditResult
    {
        $this->logger->notice($url->getUrl());
        $audit = new AuditResult($url, $this->baseUrl, $this->labels);
        $this->addRequestAudit($audit, $result);
        if (!$result->isValid()) {
            return $audit;
        }
        $this->addHtmlAudit($audit, $result, $report);
        if ($alreadyAudited) {
            return $audit;
        }

        return $audit;
    }

    private function addRequestAudit(AuditResult $audit, HttpResult $result): void
    {
        $audit->setErrorMessage($result->getErrorMessage());
        if (0 !== \strcmp(\strtolower($audit->getUrl()->getPath()), $audit->getUrl()->getPath())) {
            $audit->addWarning('The URL\'s path is case sensitive');
        }
        if (!$result->hasResponse()) {
            $audit->setValid(false);

            return;
        }

        $this->hashFromResources($result, $audit);
        $audit->setStatusCode($result->getResponse()->getStatusCode());
        $audit->setMimetype($result->getMimetype());

        foreach (['Strict-Transport-Security', 'Content-Security-Policy', 'X-Frame-Options', 'X-Content-Type-Options', 'Referrer-Policy', 'Permissions-Policy'] as $header) {
            if ($result->getResponse()->hasHeader($header)) {
                continue;
            }
            $audit->addSecurityWaring('missing-header', $header);
        }
    }

    private function hashFromResources(HttpResult $result, AuditResult $audit): void
    {
        $hashContext = \hash_init('sha1');
        $handler = $result->getStream();
        $size = 0;
        if (0 !== $handler->tell()) {
            $handler->rewind();
        }
        while (!$handler->eof()) {
            $chunk = $handler->read(1024 * 1024);
            $size += \strlen($chunk);
            \hash_update($hashContext, $chunk);
        }
        $audit->setHash(\hash_final($hashContext));
        $audit->setSize($size);
        $this->logger->notice(\sprintf('Size: %d', $size));
    }

    private function addHtmlAudit(AuditResult $audit, HttpResult $result, Report $report): void
    {
        if (!$result->isHtml()) {
            $this->logger->notice(\sprintf('Mimetype %s not supported by the Html Audit', $result->getMimetype()));

            return;
        }

        $this->logger->notice('Parse HTML');
        try {
            $stream = $result->getResponse()->getBody();
            $stream->rewind();
            $htmlHelper = new HtmlHelper($stream->getContents(), $audit->getUrl());
            $audit->addLinks($htmlHelper, $report);
            $audit->setMetaTitle($htmlHelper->getUniqueTextValue($report, 'title'));
            $audit->setTitle($htmlHelper->getUniqueTextValue($report, 'h1'));
            $audit->setCanonical($htmlHelper->getUniqueTextAttr($report, 'link[rel="canonical"]', 'href'));
            $audit->setAuthor($htmlHelper->getUniqueTextAttr($report, 'meta[name="author"]', 'content', false));
            $description = $htmlHelper->getUniqueTextAttr($report, 'meta[name="description"]', 'content');
            if (null === $description || '' === $description) {
                $report->addWarning($audit->getUrl(), ['Meta description is missing']);
            } elseif (\strlen($description) < 20) {
                $report->addWarning($audit->getUrl(), [\sprintf('Meta description is probably too short: %d', \strlen($description))]);
            } elseif (\strlen($description) > 200) {
                $report->addWarning($audit->getUrl(), [\sprintf('Meta description is probably too long: %d', \strlen($description))]);
            }
            $audit->setDescription($description);
        } catch (\Throwable $e) {
            $this->logger->critical(\sprintf('Crawler audit for %s failed: %s', $audit->getUrl()->getUrl(), $e->getMessage()));
        }
        $this->logger->notice('HTML parsed');
    }
}
