<?php

declare(strict_types=1);

namespace EMS\FormBundle\Submission;

use EMS\FormBundle\Components\Field\File;
use EMS\FormBundle\Components\Field\MultipleFile;
use EMS\FormBundle\FormConfig\FormConfig;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class FormData
{
    private FormConfig $formConfig;
    /** @var array<string, mixed> */
    private array $raw;
    /** @var array<string, UploadedFile> */
    private array $files = [];

    /**
     * @param FormInterface<FormInterface> $form
     */
    public function __construct(FormConfig $formConfig, FormInterface $form)
    {
        $this->formConfig = $formConfig;

        $formData = $form->getData();
        $this->raw = \is_array($formData) ? $formData : [];
    }

    /** @return array<string, mixed> */
    public function raw(): array
    {
        return $this->raw;
    }

    /** @return FormDataFile[] */
    public function getAllFiles(): array
    {
        $files = [];

        foreach ($this->raw as $formField => $value) {
            $element = $this->formConfig->getElementByName($formField);

            if (null === $element || !\in_array($element->getClassName(), [MultipleFile::class, File::class])) {
                continue;
            }

            $uploadedFiles = \is_array($value) ? $value : [$value];

            foreach ($uploadedFiles as $uploadedFile) {
                if ($uploadedFile instanceof UploadedFile) {
                    $files[] = new FormDataFile($uploadedFile, $element);
                }
            }
        }

        return $files;
    }

    public function filesAsUUid(): void
    {
        $this->recursiveFilesAsUuid($this->raw);
    }

    public function isFileUuid(string $uuid): bool
    {
        return isset($this->files[$uuid]);
    }

    public function getFileFromUuid(string $uuid): UploadedFile
    {
        if (!isset($this->files[$uuid])) {
            throw new \RuntimeException(\sprintf('File with uuid %s not found', $uuid));
        }

        return $this->files[$uuid];
    }

    /**
     * @param mixed[] $raw
     */
    private function recursiveFilesAsUuid(array &$raw): void
    {
        foreach ($raw  as $key => &$data) {
            if (\is_array($data)) {
                $this->recursiveFilesAsUuid($data);
                continue;
            }
            if (!$data instanceof UploadedFile) {
                continue;
            }
            $uuid = Uuid::uuid1()->toString();
            $this->files[$uuid] = $raw[$key];
            $raw[$key] = $uuid;
        }
    }
}
