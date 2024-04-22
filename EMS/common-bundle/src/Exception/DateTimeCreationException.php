<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Exception;

final class DateTimeCreationException extends \RuntimeException
{
    private function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data, string $key, int $code = 0, ?\Throwable $previous = null): DateTimeCreationException
    {
        $input = $data[$key] ?? '[ERROR: key out of bound]';
        $message = \sprintf('Could not create a DateTime from input value: "%s", with key: "%s"', $input, $key);

        return new static($message, $code, $previous);
    }
}
