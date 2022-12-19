<?php

declare(strict_types=1);

/**
 * @deprecated
 */

namespace EMS\CommonBundle\Common\Standard;

final class Json
{
    /**
     * @deprecated
     */
    public static function encode(mixed $value, bool $pretty = false): string
    {
        @\trigger_error(\sprintf('The function %s::encode has been deprecated, use %s::encode instead', self::class, \EMS\Helpers\Standard\Json::class), E_USER_DEPRECATED);

        return \EMS\Helpers\Standard\Json::encode($value, $pretty);
    }

    /**
     * @deprecated
     */
    public static function escape(string $value, bool $pretty = false): string
    {
        @\trigger_error(\sprintf('The function %s::escape has been deprecated, use %s::escape instead', self::class, \EMS\Helpers\Standard\Json::class), E_USER_DEPRECATED);

        return \EMS\Helpers\Standard\Json::escape($value, $pretty);
    }

    /**
     * @deprecated
     *
     * @return array<mixed>
     */
    public static function decode(string $value): array
    {
        @\trigger_error(\sprintf('The function %s::decode has been deprecated, use %s::decode instead', self::class, \EMS\Helpers\Standard\Json::class), E_USER_DEPRECATED);

        return \EMS\Helpers\Standard\Json::decode($value);
    }

    /**
     * @deprecated
     *
     * @return array<mixed>
     */
    public static function decodeFile(string $path): array
    {
        @\trigger_error(\sprintf('The function %s::decodeFile has been deprecated, use %s::decodeFile instead', self::class, \EMS\Helpers\Standard\Json::class), E_USER_DEPRECATED);

        return \EMS\Helpers\Standard\Json::decodeFile($path);
    }

    /**
     * @deprecated
     */
    public static function isJson(string $string): bool
    {
        @\trigger_error(\sprintf('The function %s::isJson has been deprecated, use %s::isJson instead', self::class, \EMS\Helpers\Standard\Json::class), E_USER_DEPRECATED);

        return \EMS\Helpers\Standard\Json::isJson($string);
    }
}
