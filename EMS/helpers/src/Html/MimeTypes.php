<?php

declare(strict_types=1);

namespace EMS\Helpers\Html;

enum MimeTypes: string
{
    case APPLICATION_ZIP = 'application/zip';
    case APPLICATION_XML = 'application/xml';
    case IMAGE_PNG = 'image/png';
    case IMAGE_JPEG = 'image/jpeg';
    case IMAGE_WEBP = 'image/webp';
}
