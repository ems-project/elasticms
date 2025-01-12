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
    /** @var array<string, mixed> */
    private array $raw;
    /** @var array<string, UploadedFile> */
    private array $files = [];
    /** @var FormDataFile[] */
    private array $allFiles = [];

    /**
     * @param FormInterface<mixed> $form
     */
    public function __construct(private readonly FormConfig $formConfig, FormInterface $form)
    {
        /** @var mixed $formData */
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
        if (!empty($this->allFiles)) {
            return $this->allFiles;
        }

        foreach ($this->raw as $formField => $value) {
            $element = $this->formConfig->getElementByName($formField);

            if (null === $element || !\in_array($element->getClassName(), [MultipleFile::class, File::class])) {
                continue;
            }

            $uploadedFiles = \is_array($value) ? $value : [$value];

            foreach ($uploadedFiles as $uploadedFile) {
                if ($uploadedFile instanceof UploadedFile) {
                    $this->allFiles[] = new FormDataFile($uploadedFile, $element);
                }
            }
        }

        return $this->allFiles;
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
        foreach ($raw as $key => &$data) {
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
