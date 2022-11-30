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
use EMS\CommonBundle\Common\Standard\Json;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

class AuditManager
{
    private LoggerInterface $logger;
    private bool $lighthouse;
    private bool $pa11y;
    private bool $tika;
    private bool $tikaJar;
    private CacheManager $cacheManager;
    private bool $all;
    private Pa11yWrapper $pa11yAudit;
    private LighthouseWrapper $lighthouseAudit;
    private TikaWrapper $tikaLocaleAudit;
    private TikaWrapper $tikaTextAudit;
    private TikaWrapper $tikaLinksAudit;
    private TikaWrapper $tikaMetaAudit;
    private TikaClient $tikaClient;
    private TikaMetaResponse $metaRequest;
    private AsyncResponse $htmlRequest;

    public function __construct(CacheManager $cacheManager, LoggerInterface $logger, bool $all, bool $pa11y, bool $lighthouse, bool $tika, bool $tikaJar, string $tikaServerUrl)
    {
        $this->cacheManager = $cacheManager;
        $this->logger = $logger;
        $this->pa11y = $pa11y;
        $this->lighthouse = $lighthouse;
        $this->tikaJar = $tikaJar;
        $this->tika = $tika;
        $this->all = $all;
        $this->tikaClient = new TikaClient($tikaServerUrl);
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
            $this->startTikaAudits($result);
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
            $this->addTikaAudits($audit, $result);
        }
        if ($this->tikaJar) {
            $this->addTikaJarAudits($audit, $result);
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

    private function addTikaJarAudits(AuditResult $audit, HttpResult $result): void
    {
        $this->logger->notice('Collect Tika Jar audit');
        try {
            $audit->setLocale($this->tikaLocaleAudit->getOutput());
            $audit->setContent($this->tikaTextAudit->getOutput());
            $audit->setTikaDatetime();
            if ($result->isHtml()) {
                return;
            }
            $html = new HtmlHelper($this->tikaLinksAudit->getOutput());
            foreach ($html->getLinks() as $link) {
                $audit->addLinks(new Url($link, $audit->getUrl()->getUrl()));
            }
            $meta = $this->tikaMetaAudit->getJson();
            $audit->setTitle(null === ($meta['dc:title'] ?? null) ? null : \trim($meta['dc:title']));
            $audit->setAuthor(null === ($meta['dc:author'] ?? null) ? null : \trim($meta['dc:author']));
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
            $crawler = new Crawler($stream->getContents());
            $content = $crawler->filter('a');
            for ($i = 0; $i < $content->count(); ++$i) {
                $item = $content->eq($i);
                $href = $item->attr('href');
                if (null === $href || 0 === \strlen($href) || '#' === \substr($href, 0, 1)) {
                    continue;
                }
                $audit->addLinks(new Url($href, $audit->getUrl()->getUrl()));
            }
            $audit->setMetaTitle($this->getUniqueTextValue($report, $audit, $crawler, 'title'));
            $audit->setTitle($this->getUniqueTextValue($report, $audit, $crawler, 'h1'));
            $audit->setCanonical($this->getUniqueTextAttr($report, $audit, $crawler, 'link[rel="canonical"]', 'href'));
            $audit->setAuthor($this->getUniqueTextAttr($report, $audit, $crawler, 'meta[name="author"]', 'content', false));
            $description = $this->getUniqueTextAttr($report, $audit, $crawler, 'meta[name="description"]', 'content');
            if (null !== $description && \strlen($description) < 20) {
                $report->addWarning($audit->getUrl(), [\sprintf('Meta description is probably too short: %d', \strlen($description))]);
            }
            if (null !== $description && \strlen($description) > 200) {
                $report->addWarning($audit->getUrl(), [\sprintf('Meta description is probably too long: %d', \strlen($description))]);
            }
            $audit->setDescription($description);
        } catch (\Throwable $e) {
            $this->logger->critical(\sprintf('Crawler audit for %s failed: %s', $audit->getUrl()->getUrl(), $e->getMessage()));
        }
        $this->logger->notice('HTML parsed');
    }

    private function getUniqueTextValue(Report $report, AuditResult $audit, Crawler $crawler, string $selector): ?string
    {
        $tag = $crawler->filter($selector);
        if (0 === $tag->count() || 0 === \strlen(\trim($tag->eq(0)->text()))) {
            $report->addWarning($audit->getUrl(), [\sprintf('%s is missing', $selector)]);

            return null;
        }
        if ($tag->count() > 1) {
            $report->addWarning($audit->getUrl(), [\sprintf('%s is present %d times', $selector, $tag->count())]);
        }

        return \trim($tag->eq(0)->text());
    }

    private function getUniqueTextAttr(Report $report, AuditResult $audit, Crawler $crawler, string $selector, string $attr, bool $withWarnings = true): ?string
    {
        $tag = $crawler->filter($selector);
        if (0 === $tag->count() || 0 === \strlen(\trim($tag->eq(0)->attr($attr) ?? ''))) {
            if ($withWarnings) {
                $report->addWarning($audit->getUrl(), [\sprintf('%s is missing', $selector)]);
            }

            return null;
        }
        if ($tag->count() > 1) {
            $report->addWarning($audit->getUrl(), [\sprintf('%s is present %d times', $selector, $tag->count())]);
        }

        return \trim($tag->eq(0)->attr($attr) ?? '');
    }

    private function startTikaAudits(HttpResult $result): void
    {
        $this->metaRequest = $this->tikaClient->meta($result->getStream());
        $this->htmlRequest = $this->tikaClient->html($result->getStream());
    }

    private function addTikaAudits(AuditResult $audit, HttpResult $result): void
    {
        $this->logger->notice('Collect Tika audit');
        try {
            $html = new HtmlHelper($this->htmlRequest->getContent());
            $audit->setLocale($this->metaRequest->getLocale());
            $audit->setContent($html->getText());
            $audit->setTikaDatetime();
            if ($result->isHtml()) {
                return;
            }
            foreach ($html->getLinks() as $link) {
                $audit->addLinks(new Url($link, $audit->getUrl()->getUrl()));
            }
            $audit->setTitle($this->metaRequest->getTitle());
            $audit->setAuthor($this->metaRequest->getCreator());
        } catch (\Throwable $e) {
            $this->logger->critical(\sprintf('Tika audit for %s failed: %s', $audit->getUrl()->getUrl(), $e->getMessage()));
        }
        $this->logger->notice('Tika audit collected');
    }
}
