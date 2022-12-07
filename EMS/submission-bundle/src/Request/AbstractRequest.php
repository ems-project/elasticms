<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractRequest
{
    abstract protected function getEndpointOptionResolver(): OptionsResolver;

    /**
     * @param array<string, mixed> $endpoint
     *
     * @return array<string, mixed>
     */
    protected function resolveEndpoint(array $endpoint): array
    {
        try {
            return $this->getEndpointOptionResolver()->resolve($endpoint);
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException(\sprintf('Invalid endpoint configuration: %s', $e->getMessage()));
        }
    }

    /**
     * @param array<mixed> $files
     *
     * @return \Generator<array{path: string, contents: string}>
     */
    protected function parseFiles(array $files): \Generator
    {
        foreach ($files as $file) {
            if (!isset($file['path'])) {
                continue;
            }

            if (isset($file['content_path'])) {
                $content = \file_get_contents($file['content_path']);
            }

            if (isset($file['content_base64'])) {
                $content = \base64_decode((string) $file['content_base64']);
            }

            if (isset($content) && false !== $content) {
                yield ['path' => $file['path'], 'contents' => $content];
            }
        }
    }
}
