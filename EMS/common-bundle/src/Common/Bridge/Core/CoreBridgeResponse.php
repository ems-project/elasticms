<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Bridge\Core;

class CoreBridgeResponse
{
    private function __construct(
        private mixed $data = null,
        private ?\Throwable $exception = null
    ) {
    }

    public static function onSuccess(mixed $data): self
    {
        return new self(data: $data);
    }

    public static function onError(\Throwable $e): self
    {
        return new self(exception: $e);
    }

    public function success(): bool
    {
        return null === $this->exception;
    }

    public function getData(): mixed
    {
        if (null !== $this->exception) {
            throw $this->exception;
        }

        return $this->data;
    }
}
