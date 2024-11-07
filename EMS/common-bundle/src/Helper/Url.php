<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Helper;

use EMS\CommonBundle\Exception\NotParsableUrlException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Url
{
    private const ABSOLUTE_SCHEME = ['mailto', 'javascript', 'tel'];
    private string $scheme;
    private string $host;
    private ?int $port;
    private ?string $user;
    private ?string $password;
    private string $path;
    private ?string $query;
    private ?string $fragment;
    private ?string $referer;

    public function __construct(string $url, ?string $referer = null, private readonly ?string $refererLabel = null)
    {
        $parsed = self::mb_parse_url($url, $referer);
        $relativeParsed = [];
        if (null !== $referer) {
            $relativeParsed = self::mb_parse_url($referer, $referer);
        }

        $sameHost = ($parsed['scheme'] ?? null) === ($relativeParsed['scheme'] ?? null) && ($parsed['host'] ?? null) === ($relativeParsed['host'] ?? null) && ($parsed['port'] ?? null) === ($relativeParsed['port'] ?? null);
        $sameHost = $sameHost || (null === ($parsed['scheme'] ?? null) && null === ($parsed['host'] ?? null) && null === ($parsed['port'] ?? null));

        $this->referer = null === $referer ? null : (new Url($referer))->getUrl(null, true);

        if (!$sameHost) {
            $scheme = $parsed['scheme'] ?? $relativeParsed['scheme'] ?? null;
            if (null === $scheme) {
                throw new NotParsableUrlException($url, $referer, 'unexpected null scheme');
            }
            $this->scheme = (string) $scheme;
            $host = $parsed['host'] ?? null;
            if (null === $host) {
                throw new NotParsableUrlException($url, $referer, 'unexpected null host');
            }
            $this->host = $host;
            $this->user = $parsed['user'] ?? null;
            $this->password = $parsed['pass'] ?? null;
            $this->port = $parsed['port'] ?? null;
            $this->query = $parsed['query'] ?? null;
            $this->fragment = $parsed['fragment'] ?? null;
            $this->path = $this->cleanPath($parsed['path'] ?? '/');

            return;
        }

        $scheme = $parsed['scheme'] ?? $relativeParsed['scheme'] ?? null;
        if (null === $scheme) {
            throw new NotParsableUrlException($url, $referer, 'unexpected null scheme');
        }
        $this->scheme = (string) $scheme;
        $host = $parsed['host'] ?? $relativeParsed['host'] ?? null;
        if (null === $host) {
            throw new NotParsableUrlException($url, $referer, 'unexpected null host');
        }
        $this->host = (string) $host;

        $this->user = $parsed['user'] ?? (isset($relativeParsed['user']) ? (string) $relativeParsed['user'] : null);
        $this->password = $parsed['pass'] ?? (isset($relativeParsed['pass']) ? (string) $relativeParsed['pass'] : null);
        $this->port = $parsed['port'] ?? (isset($relativeParsed['port']) ? (int) $relativeParsed['port'] : null);
        $this->query = $parsed['query'] ?? null;
        $this->fragment = $parsed['fragment'] ?? null;

        $relativeTo = isset($relativeParsed['path']) ? (string) $relativeParsed['path'] : '/';
        $this->path = $this->getAbsolutePath($parsed['path'] ?? '/', $relativeTo);
    }

    public function serialize(string $format = JsonEncoder::FORMAT): string
    {
        return self::getSerializer()->serialize($this, $format, [AbstractNormalizer::IGNORED_ATTRIBUTES => [
            'query',
            'scheme',
            'host',
            'port',
            'user',
            'password',
            'path',
            'fragment',
            'filename',
            'crawlable',
            'id',
        ]]);
    }

    public static function deserialize(string $data, string $format = JsonEncoder::FORMAT): Url
    {
        $url = self::getSerializer()->deserialize($data, Url::class, $format);
        if (!$url instanceof Url) {
            throw new \RuntimeException('Unexpected non Cache object');
        }

        return $url;
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
            new JsonEncoder(new JsonEncode([JsonEncode::OPTIONS => JSON_UNESCAPED_SLASHES]), null),
        ]);
    }

    private function getAbsolutePath(string $path, string $relativeToPath): string
    {
        if (\in_array($this->getScheme(), self::ABSOLUTE_SCHEME)) {
            return $path;
        }
        if ('/' !== \substr($relativeToPath, \strlen($relativeToPath) - 1)) {
            $lastSlash = \strripos($relativeToPath, '/');
            if (false === $lastSlash) {
                $relativeToPath .= '/';
            } else {
                $relativeToPath = \substr($relativeToPath, 0, $lastSlash + 1);
            }
        }

        if (!\str_starts_with($path, '/')) {
            $path = $relativeToPath.$path;
        }

        return $this->cleanPath($path);
    }

    public function getUrl(string $path = null, bool $withFragment = false, bool $withPassword = true, bool $withQuery = true): string
    {
        if (null !== $path) {
            return (new Url($path, $this->getUrl()))->getUrl(null, $withFragment);
        }
        if (\in_array($this->getScheme(), self::ABSOLUTE_SCHEME)) {
            $url = \sprintf('%s:', $this->scheme);
        } elseif (null !== $this->user && null !== $this->password && $withPassword) {
            $url = \sprintf('%s://%s:%s@%s', $this->scheme, $this->user, $this->password, $this->host);
        } else {
            $url = \sprintf('%s://%s', $this->scheme, $this->host);
        }
        if (null !== $this->port) {
            $url = \sprintf('%s:%d%s', $url, $this->port, $this->path);
        } else {
            $url = \sprintf('%s%s', $url, $this->path);
        }
        if ($withQuery && null !== $this->query) {
            $url = \sprintf('%s?%s', $url, $this->query);
        }
        if ($withFragment && null !== $this->fragment) {
            $url = \sprintf('%s#%s', $url, $this->fragment);
        }

        return $url;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function getFragment(): ?string
    {
        return $this->fragment;
    }

    public function getFilename(): string
    {
        $exploded = \explode('/', $this->path);
        $name = \array_pop($exploded);
        if ('' === $name) {
            return 'index.html';
        }

        return $name;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }

    public function isCrawlable(): bool
    {
        return \in_array($this->getScheme(), ['http', 'https']);
    }

    /**
     * @return array{scheme?: string, host?: string, port?: int, user?: string, pass?: string, query?: string, path?: string, fragment?: string}
     */
    public static function mb_parse_url(string $url, ?string $referer = null): array
    {
        $enc_url = \preg_replace_callback(
            '%[^:/@?&=#]+%usD',
            fn ($matches) => \urlencode((string) $matches[0]),
            $url
        );

        if (null === $enc_url) {
            throw new NotParsableUrlException($url, $referer, 'url encoding issue');
        }

        $parts = \parse_url($enc_url);

        if (false === $parts) {
            throw new NotParsableUrlException($url, $referer, 'parsing issue');
        }

        foreach ($parts as $name => $value) {
            if (\is_int($value)) {
                continue;
            }
            $parts[$name] = \urldecode($value);
        }

        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefaults([
            'scheme' => null,
            'host' => null,
            'port' => null,
            'user' => null,
            'pass' => null,
            'path' => null,
            'query' => null,
            'fragment' => null,
        ]);
        $optionsResolver->setAllowedTypes('scheme', ['string', 'null']);
        $optionsResolver->setAllowedTypes('host', ['string', 'null']);
        $optionsResolver->setAllowedTypes('port', ['int', 'null']);
        $optionsResolver->setAllowedTypes('user', ['string', 'null']);
        $optionsResolver->setAllowedTypes('pass', ['string', 'null']);
        $optionsResolver->setAllowedTypes('path', ['string', 'null']);
        $optionsResolver->setAllowedTypes('query', ['string', 'null']);
        $optionsResolver->setAllowedTypes('fragment', ['string', 'null']);

        /* @var array{scheme?: string, host?: string, port?: int, user?: string, pass?: string, query?: string, path?: string, fragment?: string} $resolved */
        $resolved = $optionsResolver->resolve($parts);

        return $resolved;
    }

    public function getRefererLabel(): ?string
    {
        return $this->refererLabel;
    }

    private function cleanPath(string $path): string
    {
        $patterns = ['#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#'];
        for ($n = 1; $n > 0;) {
            $path = \preg_replace($patterns, '/', $path, -1, $n);
            if (!\is_string($path)) {
                throw new \RuntimeException(\sprintf('Unexpected non string path %s', $path));
            }
        }
        if (!\str_starts_with($path, '/')) {
            $path = '/'.$path;
        }

        return \str_replace('../', '', $path);
    }
}
