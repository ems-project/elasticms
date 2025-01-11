<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

use EMS\Helpers\Standard\DateTime;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final readonly class DatabaseRequest
{
    private string $formName;
    private string $instance;
    private string $locale;
    /** @var array<mixed> */
    private array $data;
    /** @var array<int, array{filename: string, mimeType: string, base64: string, size: string, form_field: string}> */
    private array $files;
    private string $label;
    private ?\DateTimeImmutable $expireDate;

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
        $this->expireDate = isset($record['expire_date']) ? DateTime::create($record['expire_date']) : null;
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

    public function getExpireDate(): ?\DateTimeInterface
    {
        return $this->expireDate ? \DateTime::createFromImmutable($this->expireDate) : null;
    }

    /**
     * @param array<mixed> $databaseRecord
     *
     * @return array{form_name: string, instance: string, locale: string, data: array<mixed>, files: array<mixed>, label?: string, expire_date: ?string}
     */
    private function resolveDatabaseRecord(array $databaseRecord): array
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired(['form_name', 'locale', 'data', 'instance'])
            ->setDefault('files', [])
            ->setDefault('label', '')
            ->setDefault('expire_date', null)
            ->setAllowedTypes('form_name', 'string')
            ->setAllowedTypes('locale', 'string')
            ->setAllowedTypes('data', 'array')
            ->setAllowedTypes('files', 'array')
            ->setAllowedTypes('label', 'string')
            ->setAllowedTypes('expire_date', ['string', 'null'])
        ;

        try {
            /** @var array{form_name: string, instance: string, locale: string, data: array<mixed>, files: array<mixed>, label?: string, expire_date: string} $resolvedDatabaseRecord */
            $resolvedDatabaseRecord = $resolver->resolve($databaseRecord);

            $fileResolver = new OptionsResolver();
            $fileResolver->setRequired(['filename', 'mimeType', 'base64', 'size', 'form_field']);

            $resolvedDatabaseRecord['files'] = \array_map(fn (array $file) => $fileResolver->resolve($file), $resolvedDatabaseRecord['files']);

            return $resolvedDatabaseRecord;
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException(\sprintf('Invalid database record: %s', $e->getMessage()));
        }
    }
}
