<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class HttpRequest extends AbstractRequest
{
    /** @var array{method: string, url: string, ignore_body_value: string|null}|array<mixed> */
    private readonly array $endpoint;

    private const HTTP_OPTIONS = [
        'auth_basic' => null,
        'auth_bearer' => null,
        'headers' => [],
        'timeout' => 30,
        'query' => [],
        'max_redirects' => 20,
    ];

    /**
     * @param array<string, mixed> $endpoint
     */
    public function __construct(array $endpoint, private readonly string $body)
    {
        /** @var array{method: string, url: string, ignore_body_value: string|null} $endpoint */
        $endpoint = $this->resolveEndpoint($endpoint);

        $this->endpoint = $endpoint;
    }

    public function getMethod(): string
    {
        return $this->endpoint['method'];
    }

    public function getUrl(): string
    {
        return $this->endpoint['url'];
    }

    /**
     * @return array<string, mixed>
     */
    public function getHttpOptions(): array
    {
        $options = [
            'body' => $this->body,
        ];

        foreach (self::HTTP_OPTIONS as $optionName => $default) {
            $options[$optionName] = $this->endpoint[$optionName] ?? $default;
        }

        if (!isset($options['headers']['Content-Length'])) {
            $options['headers']['Content-Length'] = \strlen($this->body);
        }

        return $options;
    }

    public function getIgnoreBodyValue(): ?string
    {
        return $this->endpoint['ignore_body_value'];
    }

    protected function getEndpointOptionResolver(): OptionsResolver
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setRequired(['url', 'method'])
            ->setDefaults(\array_merge(self::HTTP_OPTIONS, [
                'method' => Request::METHOD_POST,
                'ignore_body_value' => null,
            ]))
        ;

        return $optionsResolver;
    }
}
