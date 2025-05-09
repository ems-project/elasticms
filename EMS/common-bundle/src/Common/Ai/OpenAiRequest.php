<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Ai;

readonly class OpenAiRequest
{
    public function __construct(
        /** @var array<mixed> $body */
        public array $body
    ) {
    }

    /**
     * @param array<mixed> $body
     */
    public static function withResponseSchema(array $body, OpenAiResponseSchema $schema): self
    {
        $body['text']['format'] = [
            'type' => 'json_schema',
            'name' => 'response',
            'schema' => $schema->toArray(),
            'strict' => true,
        ];

        return new self($body);
    }
}
