<?php

declare(strict_types=1);

namespace App\CLI\Client\Audit;

use App\CLI\Client\WebToElasticms\Helper\Url;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Cache
{
    private const HASH_SEED = 'AuditHashSeed';
    /** @var array<string, Url> */
    private array $urls = [];
    /** @var string[] */
    private array $hosts = [];
    private ?string $lastUpdated = null;
    private ?string $current = null;
    private ?string $status = null;
    private \DateTimeImmutable $startedDatetime;
    private int $startedAt;
    private Report $report;

    public function __construct(?Url $baseUrl = null)
    {
        if (null !== $baseUrl) {
            $this->addUrl($baseUrl);
        } else {
            $this->urls = [];
        }
        $this->startedDatetime = new \DateTimeImmutable();
        $this->startedAt = 0;
        $this->report = new Report();
    }

    public function serialize(string $format = JsonEncoder::FORMAT): string
    {
        return self::getSerializer()->serialize($this, $format);
    }

    public static function deserialize(string $data, string $format = JsonEncoder::FORMAT): Cache
    {
        $config = self::getSerializer()->deserialize($data, Cache::class, $format);
        if (!$config instanceof Cache) {
            throw new \RuntimeException('Unexpected non Cache object');
        }

        return $config;
    }

    private static function getSerializer(): Serializer
    {
        $reflectionExtractor = new ReflectionExtractor();
        $phpDocExtractor = new PhpDocExtractor();
        $propertyTypeExtractor = new PropertyInfoExtractor([$reflectionExtractor], [$phpDocExtractor, $reflectionExtractor], [$phpDocExtractor], [$reflectionExtractor], [$reflectionExtractor]);

        return new Serializer([
            new ArrayDenormalizer(),
            new ObjectNormalizer(null, null, null, $propertyTypeExtractor),
        ], [
            new XmlEncoder(),
            new JsonEncoder(new JsonEncode([JsonEncode::OPTIONS => JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES]), null),
        ]);
    }

    public function save(string $jsonPath, bool $finish = false): bool
    {
        if ($finish) {
            $this->lastUpdated = null;
        }

        return false !== \file_put_contents($jsonPath, $this->serialize());
    }

    /**
     * @return Url[]
     */
    public function getUrls(): array
    {
        return $this->urls;
    }

    /**
     * @param Url[] $urls
     */
    public function setUrls(array $urls): void
    {
        $this->urls = $urls;
    }

    public function getLastUpdated(): ?string
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(?string $lastUpdated): void
    {
        $this->lastUpdated = $lastUpdated;
    }

    public function hasNext(): bool
    {
        return null !== $this->nextId();
    }

    public function next(): Url
    {
        $this->lastUpdated = $this->current;
        $this->current = $this->nextId();

        return $this->current();
    }

    public function current(): Url
    {
        if (!isset($this->urls[$this->current])) {
            throw new \RuntimeException('Missing next url');
        }

        return $this->urls[$this->current];
    }

    /**
     * @return string[]
     */
    public function getHosts(): array
    {
        return $this->hosts;
    }

    public function addUrl(Url $url): void
    {
        $hash = $this->getUrlHash($url, true);
        if (isset($this->urls[$hash])) {
            return;
        }
        if (!\in_array($url->getHost(), $this->hosts)) {
            $this->hosts[] = $url->getHost();
        }
        $this->urls[$hash] = $url;
    }

    public function getUrlHash(Url $url, bool $withQuery = false): string
    {
        return \sha1(\join('$', [self::HASH_SEED, $url->getUrl(null, false, false, $withQuery)]));
    }

    public function progress(OutputInterface $output): void
    {
        $this->rewindOutput($output);
        $treated = $this->currentPos() + 1;
        $total = \count($this->urls);
        $now = new \DateTimeImmutable();
        $counter = \doubleval($treated - $this->startedAt);
        $duration = \doubleval($now->getTimestamp() - $this->startedDatetime->getTimestamp());
        if ($counter < 1 || $duration < 1) {
            $this->status = 'Starting...';
            $output->write($this->status);

            return;
        }
        $rate = $duration / $counter;
        $estimateSeconds = \round($rate * ($total - $treated));
        $estimateDatetime = new \DateTimeImmutable(\sprintf('+%s seconds', $estimateSeconds));
        $dateIntervalFormat = $estimateSeconds > (24 * 60 * 60) ? '%a days %h:%I:%S' : '%h:%I:%S';
        $this->status = \sprintf('%d urls audited, %d urls pending, %d urls found, rate %01.2f url/min, EAC in %s', $treated, $total - $treated, $total, 60.0 / $rate, $estimateDatetime->diff(new \DateTimeImmutable())->format($dateIntervalFormat));
        $output->write($this->status);
    }

    public function progressFinish(OutputInterface $output, int $counter): void
    {
        $this->rewindOutput($output);
        $this->status = null;
        $output->writeln(\sprintf('%d/%d urls have been audited', $counter, \count($this->urls)));
    }

    protected function rewindOutput(OutputInterface $output): void
    {
        if (null !== $this->status) {
            $length = \strlen($this->status);
            $output->write(\sprintf("\033[%dD%s\033[%dD", $length, \str_repeat(' ', $length), $length));
        }
    }

    public function resume(): void
    {
        if (null !== $this->lastUpdated) {
            $this->current = $this->lastUpdated;
            $this->startedAt = $this->currentPos();
        } else {
            $this->reset();
        }
    }

    public function reset(): void
    {
        $this->report = new Report();
        $this->startedDatetime = new \DateTimeImmutable();
        $this->lastUpdated = null;
    }

    private function nextId(): ?string
    {
        $keys = \array_keys($this->urls);
        if (null === $this->current) {
            return $keys[0] ?? null;
        }
        $currentPos = \array_search($this->current, $keys, true);
        if (false === $currentPos) {
            throw new \RuntimeException(\sprintf('Current position %s not found', $this->current ?? 'null'));
        }
        $nextPos = $currentPos + 1;

        return $keys[$nextPos] ?? null;
    }

    private function currentPos(): int
    {
        $keys = \array_keys($this->urls);
        $position = \array_search($this->current, $keys);

        return $position ?: 0;
    }

    public function inHosts(string $host): bool
    {
        return \in_array($host, $this->hosts);
    }

    /**
     * @param string[] $hosts
     */
    public function setHosts(array $hosts): void
    {
        $this->hosts = $hosts;
    }

    public function setReport(Report $report): void
    {
        $this->report = $report;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    public function getStartedDate(): string
    {
        return $this->startedDatetime->format(\DateTimeImmutable::ATOM);
    }

    public function setStartedDate(string $date): void
    {
        $parsedDate = \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, $date);

        if (false === $parsedDate) {
            throw new \RuntimeException(\sprintf('Unexpected false date from %s', $date));
        }
        $this->startedDatetime = $parsedDate;
    }
}
