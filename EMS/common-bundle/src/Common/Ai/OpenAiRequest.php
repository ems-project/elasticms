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
     * @param array<mixed> $responseSchema
     */
    public static function withResponseSchema(array $body, array $responseSchema): self
    {
        $body['text']['format'] = [
            'type' => 'json_schema',
            'name' => 'response',
            'schema' => self::generateResponseJsonSchema($responseSchema),
            'strict' => true,
        ];

        return new self($body);
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private static function generateResponseJsonSchema(array $data): array
    {
        $schema = [
            'type' => 'object',
            'properties' => [],
            'required' => [],
            'additionalProperties' => false,
        ];

        foreach ($data as $key => $value) {
            if (\is_array($value)) {
                $subSchema = self::generateResponseJsonSchema($value);
                $schema['properties'][$key] = $subSchema;
            } else {
                $schema['properties'][$key] = ['type' => 'string'];
            }
            $schema['required'][] = $key;
        }

        return $schema;
    }
}
