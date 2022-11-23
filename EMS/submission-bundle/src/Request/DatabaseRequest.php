<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DatabaseRequest
{
    /** @var string */
    private $formName;
    /** @var string */
    private $instance;
    /** @var string */
    private $locale;
    /** @var array<mixed> */
    private $data;
    /** @var array<int, array{filename: string, mimeType: string, base64: string, size: string, form_field: string}> */
    private $files;
    /** @var string */
    private $label;
    /** @var \DateTime|null */
    private $expireDate;

    /**
     * @param array<string, mixed> $databaseRecord
     */
    public function __construct(array $databaseRecord)
    {
        $record = $this->resolveDatabaseRecord($databaseRecord);

        $this->formName = $record['form_name'];
        $this->instance = $record['instance'];
        $this->locale = $record['locale'];
        $this->data = $record['data'];
        $this->files = $record['files'];
        $this->label = $record['label'] ?? '';
        $formattedDate = \DateTime::createFromFormat(\DateTimeInterface::ATOM, $record['expire_date']);
        $this->expireDate = false != $formattedDate ? $formattedDate : null;
    }

    public function getFormName(): string
    {
        return $this->formName;
    }

    public function getInstance(): string
    {
        return $this->instance;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array<int, array{filename: string, mimeType: string, base64: string, size: string, form_field: string}>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getExpireDate(): ?\DateTime
    {
        return $this->expireDate;
    }

    /**
     * @param array<mixed> $databaseRecord
     *
     * @return array{form_name: string, instance: string, locale: string, data: array<mixed>, files: array<mixed>, label?: string, expire_date: string}
     */
    private function resolveDatabaseRecord(array $databaseRecord): array
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired(['form_name', 'locale', 'data', 'instance'])
            ->setDefault('files', [])
            ->setDefault('label', '')
            ->setDefault('expire_date', '')
            ->setAllowedTypes('form_name', 'string')
            ->setAllowedTypes('locale', 'string')
            ->setAllowedTypes('data', 'array')
            ->setAllowedTypes('files', 'array')
            ->setAllowedTypes('label', 'string')
            ->setAllowedTypes('expire_date', 'string')
        ;

        try {
            /** @var array{form_name: string, instance: string, locale: string, data: array<mixed>, files: array<mixed>, label?: string, expire_date: string} $resolvedDatabaseRecord */
            $resolvedDatabaseRecord = $resolver->resolve($databaseRecord);

            $fileResolver = new OptionsResolver();
            $fileResolver->setRequired(['filename', 'mimeType', 'base64', 'size', 'form_field']);

            $resolvedDatabaseRecord['files'] = \array_map(function (array $file) use ($fileResolver) {
                return $fileResolver->resolve($file);
            }, $resolvedDatabaseRecord['files']);

            return $resolvedDatabaseRecord;
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException(\sprintf('Invalid database record: %s', $e->getMessage()));
        }
    }
}
