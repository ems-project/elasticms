<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class ZipRequest extends AbstractRequest
{
    /** @var array{filename: string} */
    private array $endpoint;
    /** @var array<mixed> */
    private array $files;

    /**
     * @param array<string, mixed> $endpoint
     * @param array<mixed>         $files
     */
    public function __construct(array $endpoint, array $files)
    {
        /** @var array{filename: string} $endpoint */
        $endpoint = $this->resolveEndpoint($endpoint);

        $this->endpoint = $endpoint;
        $this->files = $files;
    }

    public function getFilename(): string
    {
        return $this->endpoint['filename'];
    }

    /**
     * @return \Generator<array{path: string, contents: string}>
     */
    public function getFiles(): \Generator
    {
        return $this->parseFiles($this->files);
    }

    protected function getEndpointOptionResolver(): OptionsResolver
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefaults(['filename' => 'handle.zip']);

        return $optionsResolver;
    }
}
