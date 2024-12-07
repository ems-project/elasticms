<?php

declare(strict_types=1);

namespace EMS\Helpers\Html;

enum MimeTypes: string
{
    case APPLICATION_ZIP = 'application/zip';
    case APPLICATION_XLIFF_LEGACY = 'application/x-xliff+xml';
    case APPLICATION_XLIFF = 'application/xliff+xml';
    case APPLICATION_XML = 'application/xml';
    case APPLICATION_OCTET_STREAM = 'application/octet-stream';
    case IMAGE_PNG = 'image/png';
    case IMAGE_JPEG = 'image/jpeg';
    case IMAGE_WEBP = 'image/webp';
}
