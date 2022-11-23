<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

final class Response
{
    /** @var array<mixed> */
    private array $data;

    /**
     * @param mixed $data
     */
    public function addData(string $name, $data): void
    {
        if (!\is_array($data)) {
            $this->data[$name] = $data;

            return;
        }

        if (!isset($this->data[$name])) {
            $this->data[$name] = [];
        }

        $this->data[$name][] = $data;
    }

    public function getResponse(): JsonResponse
    {
        return new JsonResponse($this->data);
    }

    /**
     * @return array<string, array{href: string, rel: string, type: string}>
     */
    public static function createLink(string $name, string $href, string $rel, string $type = 'GET'): array
    {
        return [
            $name => [
                'href' => $href,
                'rel' => $rel,
                'type' => $type,
            ],
        ];
    }
}
