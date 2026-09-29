<?php

declare(strict_types=1);

namespace EMS\Helpers\Translations;

enum Number: string
{
    case Singular = 'singular';
    case Plural = 'plural';
}