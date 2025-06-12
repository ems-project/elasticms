<?php


declare(strict_types=1);

namespace EMS\CoreBundle\Command\Submission;

use EMS\Helpers\Standard\Json;

final readonly class ExportConfig
{
    public function __construct(
        public array   $columns,
        public ?string $filter,
        public ?string $filename,
        public array   $emailsTo,
        public string  $subject,
        public ?string $format,
    ) {}

    public static function fromJson(string $json): self
    {
        $data = Json::decode($json);

        if (!isset($data['columns'], $data['emails-to'], $data['subject'])) {
            throw new \InvalidArgumentException('Invalid config JSON: missing required fields');
        }

        return new self(
            $data['columns'],
            $data['filter'] ?? null,
            $data['filename'] ?? null,
            $data['emails-to'],
            $data['subject'],
            $data['format'] ?? null
        );
    }
}
