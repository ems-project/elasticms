<?php

declare(strict_types=1);

namespace App\CLI\Helper;

class TextHelper
{
    /**
     * @deprecated TextHelper::trim is now deprecated, use EMS\Helpers\Standard\Text::superTrim
     */
    public static function trim(string $content): string
    {
        \trigger_error('TextHelper::trim is now deprecated, use EMS\Helpers\Standard\Text::superTrim', E_USER_DEPRECATED);

        return \trim(\preg_replace('!\s+!', ' ', $content) ?? '');
    }
}
