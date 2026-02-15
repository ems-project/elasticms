<?php

declare(strict_types=1);

namespace App\CLI\Helper;

use EMS\Helpers\Standard\Text;

class TextHelper
{
    #[\Deprecated(message: 'TextHelper::trim is now deprecated, use '.Text::class.'::superTrim')]
    public static function trim(string $content): string
    {
        \trigger_error('TextHelper::trim is now deprecated, use '.Text::class.'::superTrim', E_USER_DEPRECATED);

        return \trim(\preg_replace('!\s+!', ' ', $content) ?? '');
    }
}
