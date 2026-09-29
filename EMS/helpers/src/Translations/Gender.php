<?php

declare(strict_types=1);

namespace EMS\Helpers\Translations;

enum Gender: string
{
    case Male = 'male';
    case Female = 'female';
    case Neutral = 'neutral';
}