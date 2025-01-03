<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\UserApi;

use EMS\ClientHelperBundle\Exception\UserApiResponseException;
use EMS\CommonBundle\Helper\EmsFields;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class FileService
{
    public function __construct(private readonly ClientFactory $client, private readonly LoggerInterface $logger)
    {
    }

    public function uploadFile(Request $request): JsonResponse
    {
        $client = $this->client->createClient(['X-Auth-Token' => $request->headers->get('X-Auth-Token')]);

        $responses = [];
        if (1 !== $request->files->count()) {
            throw new \RuntimeException(\sprintf('The upload file api only supports single file upload, %d files have been given', $request->files->count()));
        }
        foreach ($request->files as $file) {
            $responses = $this->upload($client, $file);
        }
        $encodedResponse = \json_encode($responses);
        if (false === $encodedResponse) {
            $this->logger->error('Unexpected json_encode error of file upload\'s response messages : {error}', ['error' => \json_last_error_msg()]);
            $encodedResponse = '{}';
        }

        return JsonResponse::fromJsonString($encodedResponse);
    }

    /**
     * @return array<string>
     */
    private function upload(Client $client, UploadedFile $file): array
    {
        try {
            $response = $client->post('api/file/upload', [
                'multipart' => [
                    [
                        'name' => 'upload',
                        'contents' => \fopen($file->getPathname(), 'r'),
                        'filename' => $file->getClientOriginalName(),
                    ],
                ],
            ]);

            $json = \json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            $success = 1 === $json['uploaded'];
            if (!$success) {
                throw UserApiResponseException::forFileUpload($response, $file);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return (isset($json)) ? $this->parseEmsResponse($json) : [];
    }

    /**
     * @param array<string> $response
     *
     * @return array<string>
     */
    private function parseEmsResponse(array $response): array
    {
        // TODO: remove this hack once the ems back is returning the file hash as parameter
        if (!isset($response[EmsFields::CONTENT_FILE_HASH_FIELD_]) && isset($response['url'])) {
            $output_array = [];
            \preg_match('/\/data\/file\/view\/(?P<hash>.*)\?.*/', $response['url'], $output_array);
            if (isset($output_array['hash'])) {
                $response[EmsFields::CONTENT_FILE_HASH_FIELD_] = $output_array['hash'];
            }
        }

        return $response;
    }
}
