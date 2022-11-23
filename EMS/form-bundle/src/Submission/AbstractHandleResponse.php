<?php

namespace EMS\FormBundle\Submission;

abstract class AbstractHandleResponse implements HandleResponseInterface
{
    protected string $status;
    protected string $data;
    /** @var mixed[] */
    protected array $extra = [];

    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';

    public function __construct(string $status, string $data)
    {
        if (self::STATUS_SUCCESS !== $status && self::STATUS_ERROR !== $status) {
            throw new \Exception(\sprintf('Invalid status for response: %s', $status));
        }
        $this->status = $status;
        $this->data = $data;
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getResponse(): string
    {
        try {
            return \json_encode(\array_merge([
                'status' => $this->status,
                'data' => $this->data,
            ], $this->extra), JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * {@inheritDoc}
     */
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
