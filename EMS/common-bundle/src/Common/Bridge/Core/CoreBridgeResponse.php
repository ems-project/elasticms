<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Bridge\Core;

use Symfony\Component\HttpKernel\Exception\HttpException;

class CoreBridgeResponse
{
    private function __construct(
        private mixed $data = null,
        private ?HttpException $exception = null
    ) {
    }

    public static function onSuccess(mixed $data): self
    {
        return new self(data: $data);
    }

    public static function onError(\Throwable $e): self
    {
        $code = (int) $e instanceof HttpException ? $e->getStatusCode() : $e->getCode();
        $httpCode = $code >= 100 && $code <= 599 ? $code : 500;

        return new self(exception: new HttpException($httpCode, $e->getMessage(), $e));
    }

    public function success(): bool
    {
        return null === $this->exception;
    }

    public function response(): mixed
    {
        if (null !== $this->exception) {
            throw $this->exception;
        }

        return $this->data;
    }
}
