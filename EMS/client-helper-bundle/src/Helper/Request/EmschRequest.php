<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Request;

use EMS\ClientHelperBundle\Routes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class EmschRequest extends Request
{
    public const string ATTRIBUTE_EMSCH_CACHE = '_emsch_cache';
    public const string ATTRIBUTE_SUB_REQUEST = '_emsch_sub_request';
    public const string ATTRIBUTE_PATH = '_emsch_path';
    public const string ATTRIBUTE_ROUTE_PREFIX = '_emsch_route_prefix';
    public const string ATTRIBUTE_INLINE_EDITOR = '_emsch_inline_editor';

    public static function fromRequest(Request $request): self
    {
        foreach ($request->files->all() as $key => $file) {
            if (null !== $file) {
                continue;
            }
            $request->files->remove($key);
        }

        return new self(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
            $request->getContent(),
        );
    }

    public function closeSession(): void
    {
        $session = $this->session;

        if ($session instanceof SessionInterface && $session->isStarted()) {
            $session->save();
        }
    }

    public function getEmschPath(): string
    {
        return $this->attributes->getString(self::ATTRIBUTE_PATH);
    }

    public function getEmschRoutePrefix(): string
    {
        $prefix = \trim($this->attributes->getString(self::ATTRIBUTE_ROUTE_PREFIX));

        if ('' === $prefix) {
            return '';
        }

        return \str_starts_with($prefix, '/') ? $prefix : '/'.$prefix;
    }

    public function getEmschCacheKey(): string
    {
        return RequestHelper::replace($this, $this->getEmschCache()['key']);
    }

    public function getEmschCacheLimit(): int
    {
        return $this->getEmschCache()['limit'];
    }

    public function hasEmschCache(): bool
    {
        return $this->attributes->has(self::ATTRIBUTE_EMSCH_CACHE);
    }

    public function isProfilerEnabled(): bool
    {
        return $this->attributes->get('_profiler', true);
    }

    public function isInlineEditor(): bool
    {
        return Routes::INLINE_EDIT_EDITOR === $this->attributes->get('_route');
    }

    public function isInlineEditorEnabled(): bool
    {
        return true === $this->attributes->get(self::ATTRIBUTE_INLINE_EDITOR);
    }

    public function isSubRequest(): bool
    {
        return $this->attributes->get(self::ATTRIBUTE_SUB_REQUEST, false);
    }

    public function makeSubRequest(): void
    {
        $this->attributes->remove(self::ATTRIBUTE_EMSCH_CACHE);
        $this->attributes->set(self::ATTRIBUTE_SUB_REQUEST, true);
    }

    /**
     * @return array{key: string, limit: int}
     */
    private function getEmschCache(): array
    {
        $emschCache = $this->attributes->get(self::ATTRIBUTE_EMSCH_CACHE, false);

        if (!$emschCache) {
            throw new \RuntimeException('No emsch cache defined!');
        }

        if (!isset($emschCache['key'])) {
            throw new \RuntimeException('Missing required emschCache.key');
        }

        return [
            'key' => (string) $emschCache['key'],
            'limit' => isset($emschCache['limit']) ? (int) ($emschCache['limit']) : 300,
        ];
    }
}
