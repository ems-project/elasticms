<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Environment;

use EMS\ClientHelperBundle\Helper\Local\LocalEnvironment;
use EMS\Helpers\Standard\Hash;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class Environment
{
    public const string ENVIRONMENT_ATTRIBUTE = '_environment';
    public const string BACKEND_ATTRIBUTE = '_backend';
    public const string LOCALE_ATTRIBUTE = '_locale';
    public const string REGEX_CONFIG = 'regex';
    public const string ROUTE_PREFIX = 'route_prefix';
    public const string BACKEND_CONFIG = 'backend';
    public const string REQUEST_CONFIG = 'request';
    public const string ALIAS_CONFIG = 'alias';
    public const string REMOTE_CLUSTER = 'remote_cluster';
    public const string DEFAULT = 'default';
    public const string ROUTER = 'router';
    private bool $active = false;
    private readonly bool $default;
    private readonly bool $routerEnabled;
    private readonly string $alias;
    private readonly ?string $regex;
    private readonly ?string $routePrefix;
    private readonly ?string $backend;
    /** @var array<string, mixed> */
    private array $request = [];
    /** @var array<mixed> */
    private array $options;
    private readonly string $hash;
    private ?LocalEnvironment $local = null;
    private readonly ?string $remoteCluster;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(private readonly string $name, array $config)
    {
        $this->alias = $config[self::ALIAS_CONFIG] ?? $name;
        $this->remoteCluster = $config[self::REMOTE_CLUSTER] ?? null;
        $this->regex = $config[self::REGEX_CONFIG] ?? null;
        $this->routePrefix = $config[self::ROUTE_PREFIX] ?? null;
        $this->backend = $config[self::BACKEND_CONFIG] ?? null;
        $this->request = $config[self::REQUEST_CONFIG] ?? [];
        $this->options = $config;
        $this->default = (bool) ($config[self::DEFAULT] ?? false);
        $this->routerEnabled = (bool) ($config[self::ROUTER] ?? true);
        $this->hash = Hash::array($config, $name);
    }

    public function getBackendUrl(): ?string
    {
        return $this->options[self::BACKEND_CONFIG] ?? null;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getRoutePrefix(): ?string
    {
        return $this->routePrefix;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAlias(): string
    {
        return $this->getAliasIdentifier();
    }

    public function getAliasForCacheKey(): string
    {
        return $this->getAliasIdentifier('_');
    }

    private function getAliasIdentifier(string $remoteClusterSeparator = ':'): string
    {
        if (null === $this->remoteCluster) {
            return $this->alias;
        }

        return \implode($remoteClusterSeparator, [$this->remoteCluster, $this->alias]);
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function isRouterEnabled(): bool
    {
        return $this->routerEnabled;
    }

    public function makeActive(): void
    {
        $this->active = true;
    }

    public function matchRequest(Request $request): bool
    {
        if (null !== $this->routePrefix) {
            $requestPrefix = \substr($request->getPathInfo(), 0, \strlen($this->routePrefix));
            if ($requestPrefix === $this->routePrefix) {
                return true;
            }
        }

        if (null === $this->regex) {
            return false;
        }

        $url = \vsprintf('%s://%s%s', [$request->getScheme(), $request->getHttpHost(), $request->getBasePath()]);

        return 1 === \preg_match($this->regex, $url);
    }

    public function modifyRequest(Request $request): void
    {
        $request->attributes->set(self::ENVIRONMENT_ATTRIBUTE, $this->name);
        $request->attributes->set(self::BACKEND_ATTRIBUTE, $this->backend);

        foreach ($this->request as $key => $value) {
            if (self::ENVIRONMENT_ATTRIBUTE === $key) {
                continue;
            }

            $request->attributes->set($key, $value);
            if (self::LOCALE_ATTRIBUTE === $key) {
                $request->setLocale($value);
            }
        }
    }

    /**
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function getOption(string $propertyPath, $default = null)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        if (!$propertyAccessor->isReadable($this->options, $propertyPath)) {
            return $default;
        }

        return $propertyAccessor->getValue($this->options, $propertyPath);
    }

    public function hasOption(string $option): bool
    {
        return isset($this->options[$option]);
    }

    public function isLocalPulled(): bool
    {
        return null !== $this->local ? $this->local->isPulled() : false;
    }

    public function getLocal(): LocalEnvironment
    {
        if (null === $this->local) {
            throw new \RuntimeException('No local environment found!');
        }

        return $this->local;
    }

    public function setLocal(?LocalEnvironment $local): void
    {
        $this->local = $local;
    }
}
