<?php

declare(strict_types=1);

namespace EMS\FormBundle\Submission;

use EMS\FormBundle\FormConfig\ElementInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

final class FormDataFile
{
    public function __construct(private readonly UploadedFile $file, private readonly ElementInterface $formElement)
    {
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
        $fileName = $this->getFilename($this->file, $this->formElement->getName());

        return [
            'filename' => $fileName,
            'pathname' => $this->file->getPathname(),
            'mimeType' => $this->file->getMimeType(),
            'size' => $this->file->getSize(),
            'form_field' => $this->formElement->getName(),
        ];
    }

    private function getFilename(UploadedFile $uploadedFile, string $fieldName): string
    {
        $filename = $uploadedFile->getClientOriginalName();
        $extension = MimeTypes::getDefault()->getExtensions($uploadedFile->getClientMimeType())[0] ?? null;
        if (null !== $extension && !$this->helperEndsWith($filename, $extension)) {
            $filename .= \sprintf('.%s', $extension);
        }

        return \sprintf('%s.%s', \uniqid(\sprintf('%s.', $fieldName), false), $filename);
    }

    private function helperEndsWith(string $haystack, string $needle): bool
    {
        $length = \strlen($needle);
        if (0 === $length) {
            return true;
        }

        return \substr($haystack, -$length) === $needle;
    }
}
