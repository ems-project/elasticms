<?php

declare(strict_types=1);

namespace App\CLI\Client\Audit;

use App\CLI\Client\HttpClient\HttpResult;
use App\CLI\Helper\HtmlHelper;
use App\CLI\Helper\LighthouseWrapper;
use App\CLI\Helper\Pa11yWrapper;
use App\CLI\Helper\Tika\TikaHelper;
use App\CLI\Helper\Tika\TikaPromiseInterface;
use EMS\CommonBundle\Common\Converter;
use EMS\CommonBundle\Helper\Url;
use EMS\Helpers\Standard\Json;
use Psr\Log\LoggerInterface;

class AuditManager
{
    private Pa11yWrapper $pa11yAudit;
    private LighthouseWrapper $lighthouseAudit;
    private ?TikaHelper $tikaHelper = null;
    private TikaPromiseInterface $tikaPromise;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly bool $all,
        private readonly bool $pa11y,
        private readonly bool $lighthouse,
        bool $tika,
        private readonly ?string $tikaServerUrl,
        private readonly int $tikaMaxSize,
        private readonly string $baseUrl,
    ) {
        if (!$tika && !$all) {
            return;
        }
        if (null !== $this->tikaServerUrl) {
            $this->tikaHelper = TikaHelper::initTikaServer($this->tikaServerUrl);
        } else {
            $this->tikaHelper = TikaHelper::initTikaJar();
        }
    }

    public function analyze(Url $url, HttpResult $result, Report $report, bool $alreadyAudited): AuditResult
    {
        $this->logger->notice($url->getUrl());
        $audit = new AuditResult($url, $this->baseUrl);
        $this->addRequestAudit($audit, $result);
        if (!$result->isValid()) {
            return $audit;
        }
        $this->addHtmlAudit($audit, $result, $report);
        if ($alreadyAudited) {
            return $audit;
        }

        if ($result->isHtml() && ($this->all || $this->pa11y)) {
            $this->startPa11yAudit($audit, $result);
        }
        if ($result->isHtml() && ($this->all || $this->lighthouse)) {
            $this->startLighthouseAudit($audit, $result);
        }
        $this->startTikaAudits($audit, $result);

        if ($result->isHtml() && ($this->all || $this->pa11y)) {
            $this->addPa11yAudit($audit, $result);
        }
        if ($result->isHtml() && ($this->all || $this->lighthouse)) {
            $this->addLighthouseAudit($audit, $result);
        }
        $this->addTikaAudits($audit, $result, $report);

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

    private function startPa11yAudit(AuditResult $audit, HttpResult $result): void
    {
        if (!$result->isHtml()) {
            $this->logger->notice(\sprintf('Mimetype %s not supported by the pa11y audit', $result->getMimetype()));

            return;
        }
        $this->logger->notice('Start pa11y audit');

        try {
            $this->pa11yAudit = new Pa11yWrapper($audit->getUrl()->getUrl());
            $this->pa11yAudit->start();
        } catch (\Throwable $e) {
            $this->logger->warning(\sprintf('Pa11y audit for %s failed: %s', $audit->getUrl()->getUrl(), $e->getMessage()));
        }
    }

    private function addPa11yAudit(AuditResult $audit, HttpResult $result): void
    {
        if (!$result->isHtml()) {
            return;
        }

        $this->logger->notice('Collect pa11y audit');
        try {
            $pa11y = $this->pa11yAudit->getJson();
            if (\count($pa11y) > 50) {
                $this->logger->warning(\sprintf('Pa11y audit for %s contains %d errors, only the first 50 will be kept in the audit', $audit->getUrl()->getUrl(), \count($pa11y)));
                $pa11y = \array_slice($pa11y, 0, 50);
            }
            $audit->setPa11y($pa11y);
        } catch (\Throwable $e) {
            $this->logger->warning(\sprintf('Pa11y audit for %s failed: %s', $audit->getUrl()->getUrl(), $e->getMessage()));
        }
        $this->logger->notice('Pa11y audit collected');
    }

    private function startLighthouseAudit(AuditResult $audit, HttpResult $result): void
    {
        if (!$result->isHtml()) {
            $this->logger->notice(\sprintf('Mimetype %s not supported by the Lighthouse audit', $result->getMimetype()));

            return;
        }
        $this->logger->notice('Start Lighthouse audit');
        try {
            $this->lighthouseAudit = new LighthouseWrapper($audit->getUrl()->getUrl());
            $this->lighthouseAudit->start();
        } catch (\Throwable $e) {
            $this->logger->critical(\sprintf('Lighthouse audit for %s failed: %s', $audit->getUrl()->getUrl(), $e->getMessage()));
        }
    }

    private function addLighthouseAudit(AuditResult $audit, HttpResult $result): void
    {
        if (!$result->isHtml()) {
            return;
        }
        $this->logger->notice('Collect Lighthouse audit');
        try {
            $lighthouse = $this->lighthouseAudit->getJson();
            if (\is_string($lighthouse['audits']['final-screenshot']['details']['data'] ?? null)) {
                $audit->setLighthouseScreenshot($lighthouse['audits']['final-screenshot']['details']['data']);
            }
            if (\is_array($lighthouse['runWarnings'] ?? null)) {
                foreach ($lighthouse['runWarnings'] as $warning) {
                    $audit->addWarning(\strval($warning));
                }
            }
            if (\is_float($lighthouse['categories']['performance']['score'] ?? null)) {
                $audit->setPerformance($lighthouse['categories']['performance']['score']);
            }
            if (\is_float($lighthouse['categories']['accessibility']['score'] ?? null)) {
                $audit->setAccessibility($lighthouse['categories']['accessibility']['score']);
            }
            if (\is_float($lighthouse['categories']['best-practices']['score'] ?? null)) {
                $audit->setBestPractices($lighthouse['categories']['best-practices']['score']);
            }
            if (\is_float($lighthouse['categories']['seo']['score'] ?? null)) {
                $audit->setSeo($lighthouse['categories']['seo']['score']);
            }
            unset($lighthouse['i18n']);
            unset($lighthouse['timing']);
            unset($lighthouse['audits']['full-page-screenshot']);
            unset($lighthouse['audits']['screenshot-thumbnails']);
            unset($lighthouse['audits']['final-screenshot']);
            $audit->setLighthouseReport(Json::encode($lighthouse, true));
        } catch (\Throwable $e) {
            $this->logger->critical(\sprintf('Lighthouse audit for %s failed: %s', $audit->getUrl()->getUrl(), $e->getMessage()));
        }
        $this->logger->notice('Lighthouse audit collected');
    }

    private function startTikaAudits(AuditResult $audit, HttpResult $result): void
    {
        if (null === $this->tikaHelper) {
            return;
        }
        $size = $audit->getSize();
        if ($size <= 0 || $size > $this->tikaMaxSize) {
            $audit->addWarning(\sprintf('File too big to be send to tika: %s', Converter::formatBytes($size)));

            return;
        }
        $this->tikaPromise = $this->tikaHelper->extract($result->getStream(), $result->getMimetype());

        try {
            $this->tikaPromise->startMeta();
        } catch (\Throwable $e) {
            $audit->addWarning(\sprintf('Tika meta extract error: %s', $e->getMessage()));
        }
        try {
            $this->tikaPromise->startHtml();
        } catch (\Throwable $e) {
            $audit->addWarning(\sprintf('Tika html extract error: %s', $e->getMessage()));
        }
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
            if (null === $description || 0 === \strlen($description)) {
                $report->addWarning($audit->getUrl(), ['Meta description is missing']);
            } elseif (\strlen($description) < 20) {
                $report->addWarning($audit->getUrl(), [\sprintf('Meta description is probably too short: %d', \strlen($description))]);
            } elseif (null !== $description && \strlen($description) > 200) {
                $report->addWarning($audit->getUrl(), [\sprintf('Meta description is probably too long: %d', \strlen($description))]);
            }
            $audit->setDescription($description);
        } catch (\Throwable $e) {
            $this->logger->critical(\sprintf('Crawler audit for %s failed: %s', $audit->getUrl()->getUrl(), $e->getMessage()));
        }
        $this->logger->notice('HTML parsed');
    }

    private function addTikaAudits(AuditResult $audit, HttpResult $result, Report $report): void
    {
        if (null === $this->tikaHelper) {
            return;
        }
        $size = $audit->getSize();
        if ($size <= 0 || $size > $this->tikaMaxSize) {
            return;
        }
        $this->logger->notice('Collect Tika audit');
        try {
            $audit->setTikaDatetime();
            $htmlHelper = new HtmlHelper($this->tikaPromise->getHtml(), $audit->getUrl());
            $content = $htmlHelper->getText();
            if (\strlen($content) > 300000) {
                $content = \substr($content, 0, 300000);
            }
            $audit->setContent($content);
            if (!$result->isHtml()) {
                $audit->addLinks($htmlHelper, $report);
            }

            $meta = $this->tikaPromise->getMeta();
            if (!$audit->hasLocale()) {
                $audit->setLocale($meta->getLocale());
            }
            if ($result->isHtml()) {
                return;
            }
            $audit->setTitle($meta->getTitle());
            $audit->setAuthor($meta->getCreator());
        } catch (\Throwable $e) {
            $this->logger->critical(\sprintf('Tika audit for %s failed: %s', $audit->getUrl()->getUrl(), $e->getMessage()));
        }
        $this->logger->notice('Tika audit collected');
    }
}
