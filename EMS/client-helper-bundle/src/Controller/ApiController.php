<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\ClientHelperBundle\Helper\Api\ApiService;
use EMS\ClientHelperBundle\Helper\Hashcash\HashcashHelper;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class ApiController
{
    public function __construct(
        private readonly ApiService $service,
        private readonly HashcashHelper $hashcashHelper
    ) {
    }

    public function contentTypes(string $apiName): JsonResponse
    {
        return $this->service->getContentTypes($apiName)->getResponse();
    }

    public function contentType(Request $request, string $apiName, string $contentType): JsonResponse
    {
        $scrollId = $request->query->get('scroll');
        $size = \intval($request->query->get('size'));
        /** @var string[] $filter */
        $filter = $request->query->all('filter');

        return $this->service->getContentType($apiName, $contentType, $filter, $size, $scrollId)->getResponse();
    }

    public function getSubmissionFile(string $apiName, string $submissionId, string $submissionFileId): Response
    {
        $coreApi = $this->service->getApiClient($apiName)->coreApi;

        try {
            return $coreApi->form()->getSubmissionFileAsStreamResponse($submissionId, $submissionFileId);
        } catch (ClientException $e) {
            return throw new HttpException($e->getCode());
        }
    }

    public function document(string $apiName, string $contentType, string $ouuid): JsonResponse
    {
        return $this->service->getDocument($apiName, $contentType, $ouuid)->getResponse();
    }

    public function handleFormPostRequest(Request $request, string $apiName, string $contentType, ?string $ouuid, string $csrfId, string $validationTemplate, int $hashcashLevel, string $hashAlgo): JsonResponse
    {
        $this->hashcashHelper->validateHashcash($request, $csrfId, $hashcashLevel, $hashAlgo);
        $rawData = $this->service->treatFormRequest($request, $apiName, $validationTemplate);

        if (null === $rawData) {
            return new JsonResponse(['success' => false, 'message' => 'Empty data']);
        }

        try {
            $ouuid = $this->service->index($apiName, $contentType, $ouuid, $rawData);

            return new JsonResponse(['success' => true, 'ouuid' => $ouuid]);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function createDocumentFromForm(Request $request, string $apiName, string $contentType, ?string $ouuid, string $redirectUrl, ?string $validationTemplate = null): RedirectResponse
    {
        $rawData = $this->service->treatFormRequest($request, $apiName, $validationTemplate);

        $ouuid = $this->service->index($apiName, $contentType, $ouuid, $rawData);
        $url = \str_replace(['%ouuid%', '%contenttype%'], [$ouuid, $contentType], $redirectUrl);

        return new RedirectResponse($url);
    }

    public function updateDocumentFromForm(Request $request, string $apiName, string $contentType, string $ouuid, string $redirectUrl, ?string $validationTemplate = null): RedirectResponse
    {
        $rawData = $this->service->treatFormRequest($request, $apiName, $validationTemplate);

        $ouuid = $this->service->index($apiName, $contentType, $ouuid, $rawData, true);
        $url = \str_replace(['%ouuid%', '%contenttype%'], [$ouuid, $contentType], $redirectUrl);

        return new RedirectResponse($url);
    }
}
