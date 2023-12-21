<?php

declare(strict_types=1);

namespace EMS\FormBundle\Submission;

use EMS\FormBundle\FormConfig\ElementInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

final class FormDataFile
{
    private readonly string $filename;

    public function __construct(private readonly UploadedFile $file, private readonly ElementInterface $formElement)
    {
        $filename = $file->getClientOriginalName();
        $extension = MimeTypes::getDefault()->getExtensions($file->getClientMimeType())[0] ?? null;
        if (null !== $extension && !\str_ends_with(\strtolower($filename), ".$extension")) {
            $filename .= \sprintf('.%s', $extension);
        }
        $this->filename = \sprintf('%s.%s', \uniqid(\sprintf('%s.', $this->formElement->getName()), false), $filename);
    }

    public function base64(): ?string
    {
        $content = \file_get_contents($this->file->getPathname());

        return $content ? \base64_encode($content) : null;
    }

    public function getFile(): UploadedFile
    {
        return $this->file;
    }

    /** @return array<string, int|string|false|null> */
    public function toArray(): array
    {
        return [
            'filename' => $this->filename,
            'pathname' => $this->file->getPathname(),
            'mimeType' => $this->file->getMimeType(),
            'size' => $this->file->getSize(),
            'form_field' => $this->formElement->getName(),
        ];
    }
}
