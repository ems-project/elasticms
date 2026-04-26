<?php

declare(strict_types=1);

namespace EMS\Helpers\Date;

class DateTimeConvertor
{
    public static function toDateTimeImmutable(mixed $value): ?\DateTimeImmutable
    {
        if ($value instanceof \DateTimeImmutable || null === $value) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($value);
        }

        if (\is_string($value)) {
            return new \DateTimeImmutable($value);
        }

        if (\is_array($value) && \is_string($value['date'] ?? null)) {
            return new \DateTimeImmutable($value['date']);
        }

        throw new \RuntimeException(\sprintf('Cannot convert value of type "%s".', \gettype($value)));
    }
}
