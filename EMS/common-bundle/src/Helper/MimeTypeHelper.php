<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Helper;

use Symfony\Component\Mime\MimeTypes;

class MimeTypeHelper
{
    public const TEXT_PLAIN = 'text/plain';
    public const APPLICATION_OCTET_STREAM = 'application/octet-stream';
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
        if (self::TEXT_PLAIN === $mimeType) {
            $ext = \pathinfo($filename, PATHINFO_EXTENSION);
            $mimeType = $this->mimeTypes->getMimeTypes($ext)[0] ?? $mimeType;
        }

        return $mimeType ?? self::APPLICATION_OCTET_STREAM;
    }
}
