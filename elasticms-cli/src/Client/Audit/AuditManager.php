<?php

declare(strict_types=1);

namespace App\CLI\Client\Audit;

use App\CLI\Client\HttpClient\CacheManager;
use App\CLI\Client\HttpClient\HttpResult;
use App\CLI\Client\WebToElasticms\Helper\Url;
use App\CLI\Helper\AsyncResponse;
use App\CLI\Helper\HtmlHelper;
use App\CLI\Helper\LighthouseWrapper;
use App\CLI\Helper\Pa11yWrapper;
use App\CLI\Helper\TikaClient;
use App\CLI\Helper\TikaMetaResponse;
use App\CLI\Helper\TikaWrapper;
use EMS\CommonBundle\Common\Converter;
use EMS\Helpers\Standard\Json;
use Psr\Log\LoggerInterface;

class AuditManager
{
    private Pa11yWrapper $pa11yAudit;
    private LighthouseWrapper $lighthouseAudit;
    private TikaWrapper $tikaLocaleAudit;
    private TikaWrapper $tikaTextAudit;
    private TikaWrapper $tikaLinksAudit;
    private TikaWrapper $tikaMetaAudit;
    private TikaMetaResponse $metaRequest;
    private AsyncResponse $htmlRequest;

    public function __construct(private readonly CacheManager $cacheManager, private readonly LoggerInterface $logger, private readonly bool $all, private readonly bool $pa11y, private readonly bool $lighthouse, private readonly bool $tika, private readonly bool $tikaJar, private readonly string $tikaServerUrl, private readonly int $tikaMaxSize)
    {
    }

    public function analyze(Url $url, HttpResult $result, Report $report): AuditResult
    {
        $this->logger->notice($url->getUrl());
        $audit = new AuditResult($url);
        $this->addRequestAudit($audit, $result);
        if (!$result->isValid()) {
            return $audit;
        }
        $this->addHtmlAudit($audit, $result, $report);
        if ($result->isHtml() && ($this->all || $this->pa11y)) {
            $this->startPa11yAudit($audit, $result);
        }
        if ($result->isHtml() && ($this->all || $this->lighthouse)) {
            $this->startLighthouseAudit($audit, $result);
        }
        if ($this->tikaJar && $this->tika) {
            throw new \RuntimeException('--tika and --tika-jar can not be activated at the same time');
        }
        if (($this->all && !$this->tikaJar) || $this->tika) {
            $this->startTikaAudits($audit, $result);
        }
        if ($this->tikaJar) {
            $this->startTikaJarAudits($audit, $result);
        }

        if ($result->isHtml() && ($this->all || $this->pa11y)) {
            $this->addPa11yAudit($audit, $result);
        }
        if ($result->isHtml() && ($this->all || $this->lighthouse)) {
            $this->addLighthouseAudit($audit, $result);
        }
        if ($this->all || $this->tika) {
            $this->addTikaAudits($audit, $result, $report);
        }
        if ($this->tikaJar) {
            $this->addTikaJarAudits($audit, $result, $report);
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
            $audit->setPa11y($this->pa11yAudit->getJson());
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

    private function startTikaJarAudits(AuditResult $audit, HttpResult $result): void
    {
        $this->logger->notice('Start Tika Jar audit');
        try {
            $stream = $result->getStream();
            $this->tikaLocaleAudit = TikaWrapper::getLocale($stream, $this->cacheManager->getCacheFolder());
            $this->tikaTextAudit = TikaWrapper::getText($stream, $this->cacheManager->getCacheFolder());
            $this->tikaLinksAudit = TikaWrapper::getHtml($stream, $this->cacheManager->getCacheFolder());
            $this->tikaMetaAudit = TikaWrapper::getJsonMetadata($stream, $this->cacheManager->getCacheFolder());
            $this->tikaLocaleAudit->start();
            $this->tikaTextAudit->start();
            $this->tikaLinksAudit->start();
            $this->tikaMetaAudit->start();
        } catch (\Throwable $e) {
            $this->logger->critical(\sprintf('Tika audit for %s failed: %s', $audit->getUrl()->getUrl(), $e->getMessage()));
        }
    }

    private function addTikaJarAudits(AuditResult $audit, HttpResult $result, Report $report): void
    {
        $this->logger->notice('Collect Tika Jar audit');
        try {
            $audit->setLocale($this->tikaLocaleAudit->getOutput());
            $audit->setContent($this->tikaTextAudit->getOutput());
            $audit->setTikaDatetime();
            if ($result->isHtml()) {
                return;
            }
            $htmlHelper = new HtmlHelper($this->tikaLinksAudit->getOutput(), $audit->getUrl());
            $audit->addLinks($htmlHelper, $report);
            $meta = $this->tikaMetaAudit->getJson();
            $audit->setTitle(null === ($meta['dc:title'] ?? null) ? null : \trim((string) $meta['dc:title']));
            $audit->setAuthor(null === ($meta['dc:author'] ?? null) ? null : \trim((string) $meta['dc:author']));
        } catch (\Throwable $e) {
            $this->logger->critical(\sprintf('Tika audit for %s failed: %s', $audit->getUrl()->getUrl(), $e->getMessage()));
        }
        $this->logger->notice('Tika audit collected');
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

    private function startTikaAudits(AuditResult $audit, HttpResult $result): void
    {
        $size = $audit->getSize();
        if ($size <= 0 || $size > $this->tikaMaxSize) {
            $audit->addWarning(\sprintf('File too big to be send to tika: %s', Converter::formatBytes($size)));

            return;
        }
        $this->metaRequest = (new TikaClient($this->tikaServerUrl))->meta($result->getStream(), $result->getMimetype());
        $this->htmlRequest = (new TikaClient($this->tikaServerUrl))->html($result->getStream(), $result->getMimetype());
    }

    private function addTikaAudits(AuditResult $audit, HttpResult $result, Report $report): void
    {
        $size = $audit->getSize();
        if ($size <= 0 || $size > $this->tikaMaxSize) {
            $audit->addWarning(\sprintf('File too big to be send to tika: %s', Converter::formatBytes($size)));

            return;
        }
        $this->logger->notice('Collect Tika audit');
        try {
            $htmlHelper = new HtmlHelper($this->htmlRequest->getContent(), $audit->getUrl());
            $audit->setLocale($this->metaRequest->getLocale());
            $audit->setContent($htmlHelper->getText());
            $audit->setTikaDatetime();
            if ($result->isHtml()) {
                return;
            }
            $audit->addLinks($htmlHelper, $report);
            $audit->setTitle($this->metaRequest->getTitle());
            $audit->setAuthor($this->metaRequest->getCreator());
        } catch (\Throwable $e) {
            $this->logger->critical(\sprintf('Tika audit for %s failed: %s', $audit->getUrl()->getUrl(), $e->getMessage()));
        }
        $this->logger->notice('Tika audit collected');
    }
}
