<?php

namespace EMS\FormBundle\Submission;

use EMS\Helpers\Standard\Json;

abstract class AbstractHandleResponse implements HandleResponseInterface
{
    protected string $status;
    /** @var mixed[] */
    protected array $extra = [];

    final public const STATUS_SUCCESS = 'success';
    final public const STATUS_ERROR = 'error';

    public function __construct(string $status, protected string $data)
    {
        if (self::STATUS_SUCCESS !== $status && self::STATUS_ERROR !== $status) {
            throw new \Exception(\sprintf('Invalid status for response: %s', $status));
        }
        $this->status = $status;
    }

    /** @return mixed[] */
    public function getExtra(): array
    {
        return $this->extra;
    }

    /** @param mixed[] $extra */
    public function setExtra(array $extra): void
    {
        $this->extra = $extra;
    }

    #[\Override]
    public function getStatus(): string
    {
        return $this->status;
    }

    #[\Override]
    public function getResponse(): string
    {
        try {
            return Json::encode(\array_merge([
                'status' => $this->status,
                'data' => $this->data,
            ], $this->extra));
        } catch (\Throwable) {
            return '';
        }
    }

    #[\Override]
    public function getSummary(): array
    {
        /** @var array{status: string, data: string, success: string} $summary */
        $summary = \array_merge([
            'status' => $this->status,
            'data' => $this->data,
            'success' => (self::STATUS_SUCCESS === $this->status),
        ], $this->extra);

        return $summary;
    }
}
