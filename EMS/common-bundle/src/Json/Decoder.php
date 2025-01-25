<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Json;

class Decoder
{
    public function jsonMenuDecode(string $text, string $glue): JsonMenu
    {
        return new JsonMenu($text, $glue);
    }

    public function jsonMenuNestedDecode(string $json): JsonMenuNested
    {
        return JsonMenuNested::fromStructure($json);
    }
}
