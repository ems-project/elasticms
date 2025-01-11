<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class EmschRequest extends Request
{
    public const string ATTRIBUTE_EMSCH_CACHE = '_emsch_cache';
    public const string ATTRIBUTE_SUB_REQUEST = '_emsch_sub_request';

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
            'key' => \strval($emschCache['key']),
            'limit' => isset($emschCache['limit']) ? \intval($emschCache['limit']) : 300,
        ];
    }
}
