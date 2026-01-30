<?php

declare(strict_types=1);

namespace EMS\Xliff\Formater;

use EMS\Helpers\Html\HtmlHelper;

class HtmlFormater implements FormaterInterface
{
    public function format(string $input): string
    {
        return HtmlHelper::prettyPrint(HtmlHelper::stripZeroWidthCharacters($input));
    }
}
