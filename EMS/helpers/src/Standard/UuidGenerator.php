<?php

declare(strict_types=1);

namespace EMS\Helpers\Standard;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidGenerator
{
    public static function random(): UuidInterface
    {
        return Uuid::uuid4();
    }

    public static function fromValue(string $value): UuidInterface
    {
        return Uuid::uuid5(Uuid::NAMESPACE_URL, $value);
    }
}
