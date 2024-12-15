<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Helper;

use EMS\Helpers\Html\MimeTypes as MimeTypeHeader;
use Symfony\Component\Mime\MimeTypes;

class MimeTypeHelper
{
    public const TEXT_PLAIN = 'text/plain';
    private static ?self $instance = null;
    private MimeTypes $mimeTypes;

    private function __construct()
    {
        $this->mimeTypes = new MimeTypes();
    }

    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function guessMimeType(string $filename): string
    {
        $mimeType = $this->mimeTypes->guessMimeType($filename);
        if (\str_starts_with($mimeType ?? '', 'text/')) {
            $ext = \pathinfo($filename, PATHINFO_EXTENSION);
            $mimeType = $this->mimeTypes->getMimeTypes($ext)[0] ?? $mimeType;
        }

        return $mimeType ?? MimeTypeHeader::APPLICATION_OCTET_STREAM->value;
    }
}
