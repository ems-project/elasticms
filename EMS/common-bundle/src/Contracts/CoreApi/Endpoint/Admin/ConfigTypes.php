<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin;

enum ConfigTypes: string
{
    case WYSIWYG_STYLE_SET = 'wysiwyg-style-set';
}
