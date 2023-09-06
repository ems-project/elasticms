<?php

declare(strict_types=1);

namespace EMS\Helpers\Standard;

final class DateTime
{
    public static function create(string $time): \DateTimeImmutable
    {
        if (false === $timestamp = \strtotime($time)) {
            throw new \RuntimeException(\sprintf('Failed creating time for "%s"', $time));
        }

        $dateTime = (new \DateTimeImmutable())->setTimestamp($timestamp);

        if (!$dateTime instanceof \DateTimeImmutable) {
            throw new \RuntimeException('Failed creating datetime for timestamp %d', $timestamp);
        }

        return $dateTime;
    }

    public static function createFromFormat(string $time, string $format = \DateTimeInterface::ATOM): \DateTimeImmutable
    {
        $dateTime = \DateTimeImmutable::createFromFormat($format, $time);

        if (!$dateTime) {
            $errors = \json_encode(\DateTimeImmutable::getLastErrors(), JSON_THROW_ON_ERROR);
            throw new \RuntimeException(\sprintf('Failed creating dateTime for "%s" with format "%s", [%s]', $time, $format, $errors));
        }

        return $dateTime;
    }
}
