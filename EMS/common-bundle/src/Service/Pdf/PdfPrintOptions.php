<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Service\Pdf;

use EMS\Helpers\File\TempDirectory;

class PdfPrintOptions
{
    private readonly string $filename;
    private readonly bool $attachment;
    private readonly bool $compress;
    private readonly bool $html5Parsing;
    private readonly bool $isPhpEnabled;
    private readonly string $orientation;
    private readonly string $size;
    private readonly ?string $chroot;

    public const FILENAME = 'filename';
    public const ATTACHMENT = 'attachment';
    public const COMPRESS = 'compress';
    public const HTML5_PARSING = 'html5Parsing';
    public const PHP_ENABLED = 'phpEnabled';
    public const ORIENTATION = 'orientation';
    public const SIZE = 'size';
    public const CHROOT = 'chroot';
    private TempDirectory $tempDirectory;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options)
    {
        $this->tempDirectory = TempDirectory::create();
        $this->filename = $options[self::FILENAME] ?? 'export.pdf';
        $this->attachment = $options[self::ATTACHMENT] ?? true;
        $this->compress = $options[self::COMPRESS] ?? true;
        $this->html5Parsing = $options[self::HTML5_PARSING] ?? true;
        $this->isPhpEnabled = $options[self::PHP_ENABLED] ?? false;
        $this->orientation = $options[self::ORIENTATION] ?? 'portrait';
        $this->size = $options[self::SIZE] ?? 'a4';
        $this->chroot = $options[self::CHROOT] ?? $this->tempDirectory->path;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getOrientation(): string
    {
        return $this->orientation;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function isAttachment(): bool
    {
        return $this->attachment;
    }

    public function isCompress(): bool
    {
        return $this->compress;
    }

    public function isHtml5Parsing(): bool
    {
        return $this->html5Parsing;
    }

    public function isPhpEnabled(): bool
    {
        return $this->isPhpEnabled;
    }

    public function getChroot(): ?string
    {
        return $this->chroot;
    }
}
