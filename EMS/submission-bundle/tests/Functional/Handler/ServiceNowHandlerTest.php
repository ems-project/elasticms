<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use EMS\SubmissionBundle\Tests\Functional\App\ResponseFactory;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ServiceNowHandlerTest extends AbstractHandlerTest
{
    private string $credentials;
    /** @var ResponseFactory */
    private $responseFactory;
    private array $endpoint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->credentials = \base64_encode(\sprintf('%s:%s', 'userA', 'passB')); // see config.yml
        $this->responseFactory = $this->container->get(ResponseFactory::class);
        $this->endpoint = [
            'host' => 'https://example.service-now.com',
            'table' => 'table_name',
            'bodyEndpoint' => '/api/now/v1/table',
            'attachmentEndpoint' => '/api/now/v1/attachment/file',
            'username' => "{{'service-now-instance-a%.%user'|emss_connection}}",
            'password' => "{{'service-now-instance-a%.%password'|emss_connection}}",
        ];
    }

    protected function getHandler(): AbstractHandler
    {
        return $this->container->get('functional_test.emss.handler.service_now');
    }

    public function testSubmitFormData(): void
    {
        $message = \json_encode([
           'body' => [
               'title' => 'Test serviceNow',
               'name' => '{{ data.first_name }}',
           ],
        ]);

        $this->responseFactory->setCallback(function (string $method, string $url, array $options = []) {
            if ('POST' === $method && 'https://example.service-now.com/api/now/v1/table/table_name' === $url) {
                $this->assertEquals('{"title":"Test serviceNow","name":"testFirstName"}', $options['body']);
                $this->assertEquals('19', $options['timeout']); // see config.yml

                $this->assertSame([
                    'Accept: application/json',
                    'Content-Type: application/json',
                    \sprintf('Authorization: Basic %s', $this->credentials),
                    'Content-Length: 50',
                ], $options['headers']);

                return new MockResponse('{"message": "example"}');
            }

            throw new \Exception(\sprintf('response not mocked for %s', $url));
        });

        $this->assertEquals(
            '{"status":"success","data":"{\"message\": \"example\"}"}',
            $this->handle($this->createForm(), \json_encode($this->endpoint, JSON_THROW_ON_ERROR), $message)->getResponse()
        );
    }

    public function testSubmitMultipleFiles(): void
    {
        $message = \file_get_contents(__DIR__.'/../fixtures/twig/message_service_now.twig');

        $attachmentUrl = 'https://example.service-now.com/api/now/v1/attachment/file';
        $sysId = 98765;
        $attachmentUrls = [
            $attachmentUrl.'?file_name=attachment.txt&table_name=table_name&table_sys_id='.$sysId,
            $attachmentUrl.'?file_name=attachment2.txt&table_name=table_name&table_sys_id='.$sysId,
        ];

        $this->responseFactory->setCallback(
            function (string $method, string $url, array $options = []) use ($attachmentUrls, $sysId) {
                if ('https://example.service-now.com/api/now/v1/table/table_name' === $url) {
                    $this->assertEquals('{"title":"Test serviceNow","info":"Uploaded 2 files"}', $options['body']);

                    return new MockResponse(\json_encode(['result' => ['sys_id' => $sysId]]));
                }

                if ($url === $attachmentUrls[0]) {
                    $this->assertEquals('Text example attachment', $options['body']);
                }

                if (\in_array($url, $attachmentUrls)) {
                    $fileName = $options['query']['file_name'] ?? '';

                    $this->assertSame([
                        'Content-Type: text/plain',
                        \sprintf('Authorization: Basic %s', $this->credentials),
                        'Accept: */*',
                        \sprintf('Content-Length: %d', 'attachment.txt' === $fileName ? 23 : 24),
                    ], $options['headers']);

                    return new MockResponse('{}');
                }

                throw new \Exception(\sprintf('response not mocked for %s', $url));
            }
        );

        $handle = $this->handle($this->createFormUploadFiles(), \json_encode($this->endpoint, JSON_THROW_ON_ERROR), $message);

        $this->assertEquals(
            '{"status":"success","data":"{\"result\":{\"sys_id\":98765}}"}',
            $handle->getResponse()
        );
    }

    public function testPostAttachmentFails()
    {
        $message = \file_get_contents(__DIR__.'/../fixtures/twig/message_service_now.twig');

        $this->responseFactory->setCallback(function (string $method, string $url, array $options = []) {
            if ('https://example.service-now.com/api/now/v1/table/table_name' === $url) {
                return new MockResponse('{"message": "upload success"}');
            }

            return new MockResponse('{}', ['http_code' => 404]);
        });

        $this->assertEquals(
            '{"status":"error","data":"Submission failed, contact your admin. (Attachment submission failed: HTTP 404 returned for \"https:\/\/example.service-now.com\/api\/now\/v1\/attachment\/file?file_name=attachment.txt&table_name=table_name&table_sys_id=\".)"}',
            $this->handle($this->createFormUploadFiles(), \json_encode($this->endpoint, JSON_THROW_ON_ERROR), $message)->getResponse()
        );
    }

    public function testResponseFailure()
    {
        $message = \file_get_contents(__DIR__.'/../fixtures/twig/message_service_now.twig');
        $fileUrl = 'https://example.service-now.com/api/now/v1/attachment/file?file_name=attachment.txt&table_name=table_name&table_sys_id=';

        $this->responseFactory->setCallback(function (string $method, string $url, array $options = []) use ($fileUrl) {
            if ('POST' === $method && 'https://example.service-now.com/api/now/v1/table/table_name' === $url) {
                return new MockResponse('{"status": "failure", "message": "example"}');
            }

            if ($fileUrl === $url) {
                $this->assertEmpty($options['query']['table_sys_id']);
            }

            return new MockResponse('{}');
        });

        $this->assertEquals(
            '{"status":"error","data":"{\"status\": \"failure\", \"message\": \"example\"}"}',
            $this->handle($this->createFormUploadFiles(), \json_encode($this->endpoint, JSON_THROW_ON_ERROR), $message)->getResponse()
        );
    }

    public function testResponseInvalidJson()
    {
        $message = \file_get_contents(__DIR__.'/../fixtures/twig/message_service_now.twig');
        $fileUrl = 'https://example.service-now.com/api/now/v1/attachment/file?file_name=attachment.txt&table_name=table_name&table_sys_id=';

        $this->responseFactory->setCallback(function (string $method, string $url, array $options = []) use ($fileUrl) {
            if ('POST' === $method && 'https://example.service-now.com/api/now/v1/table/table_name' === $url) {
                return new MockResponse('invalid json test');
            }

            if ($fileUrl === $url) {
                $this->assertEmpty($options['query']['table_sys_id']);
            }

            return new MockResponse('{}');
        });

        $this->assertEquals(
            '{"status":"error","data":"invalid json test"}',
            $this->handle($this->createFormUploadFiles(), \json_encode($this->endpoint, JSON_THROW_ON_ERROR), $message)->getResponse()
        );
    }
}
