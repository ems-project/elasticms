<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\Form;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

interface FormInterface
{
    /**
     * @param array{
     *     form_name: string,
     *     instance: string,
     *     locale: string,
     *     data: array<string, mixed>,
     *     files?: array<int, array{filename: string, mimeType: string, size: int, form_field:string, base64: string}>,
     *     label: string,
     *     expire_date: string
     * } $data
     *
     * @return array<string, mixed>
     */
    public function submit(array $data): array;

    /**
     * @return array<string, mixed>
     */
    public function getSubmission(string $submissionId, ?string $property = null): array;

    public function getSubmissionFile(string $submissionId, ?string $submissionFileId): ResponseInterface;

    public function getSubmissionFileAsStreamResponse(string $submissionId, ?string $submissionFileId): StreamedResponse;

    public function createVerification(string $value): string;

    public function getVerification(string $value): string;
}
