<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use EMS\Helpers\File\TempFile;
use EMS\SubmissionBundle\Response\ZipHandleResponse;

final class ZipHandlerTest extends AbstractHandlerTest
{
    private TempFile $tempFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempFile = TempFile::create();
        $this->tempFile->setAutoClean();
    }

    protected function getHandler(): AbstractHandler
    {
        return $this->container->get('functional_test.emss.handler.zip');
    }

    public function testSubmitFormData(): void
    {
        $endpoint = \json_encode(['filename' => 'test.zip']);
        $message = \file_get_contents(__DIR__.'/../fixtures/twig/message_zip.twig');
        /** @var ZipHandleResponse $handleResponse */
        $handleResponse = $this->handle($this->createFormUploadFiles(), $endpoint, $message);

        $content = $handleResponse->getContent();
        \file_put_contents($this->tempFile->path, \base64_decode($content));
        $zip = new \ZipArchive();
        $zip->open($this->tempFile->path);

        $this->assertEquals(
            '{"status":"success","data":"Submission zip ready."}',
            $handleResponse->getResponse()
        );
        $this->assertCount(2, \iterator_to_array($handleResponse->getZipRequest()->getFiles()));
        $this->assertEquals('test.zip', $handleResponse->getFilename());
        $this->assertEquals(2, $zip->numFiles);
        $this->assertEquals('Text example attachment', $zip->getFromName('test/attachment.txt'));
    }

    public function testEmptyEndpoint(): void
    {
        $message = \file_get_contents(__DIR__.'/../fixtures/twig/message_zip.twig');
        /** @var ZipHandleResponse $handleResponse */
        $handleResponse = $this->handle($this->createFormUploadFiles(), '', $message);

        $this->assertEquals('handle.zip', $handleResponse->getFilename());
    }

    public function testInvalidJsonEndpoint(): void
    {
        $message = \file_get_contents(__DIR__.'/../fixtures/twig/message_zip.twig');
        /** @var ZipHandleResponse $handleResponse */
        $handleResponse = $this->handle($this->createFormUploadFiles(), 'test', $message);

        $this->assertEquals(
            '{"status":"error","data":"Submission failed, contact your admin. (invalid json!)"}',
            $handleResponse->getResponse()
        );
    }

    public function testInvalidMessage(): void
    {
        $message = '{% block files %}test{% endblock files %}';

        /** @var ZipHandleResponse $handleResponse */
        $handleResponse = $this->handle($this->createFormUploadFiles(), '', $message);

        $this->assertEquals(
            '{"status":"error","data":"Submission failed, contact your admin. (invalid json!)"}',
            $handleResponse->getResponse()
        );
    }

    public function testWrongFileMessage(): void
    {
        $message = '{% block files %}[{"test": "test"}, {"path": "test.txt", "content_base64": "dGVzdA==" }]{% endblock files %}';

        /** @var ZipHandleResponse $handleResponse */
        $handleResponse = $this->handle($this->createFormUploadFiles(), '', $message);

        $this->assertEquals(
            '{"status":"success","data":"Submission zip ready."}',
            $handleResponse->getResponse()
        );

        $content = $handleResponse->getContent();
        \file_put_contents($this->tempFile->path, \base64_decode($content));
        $zip = new \ZipArchive();
        $zip->open($this->tempFile->path);

        $this->assertCount(1, \iterator_to_array($handleResponse->getZipRequest()->getFiles()));
        $this->assertEquals(1, $zip->numFiles);
        $this->assertEquals('test', $zip->getFromName('test.txt'));
    }

    public function testFailedHandleResponseExtra(): void
    {
        $message = '{% block handleResponseExtra %}{{ test }}{% endblock %}';

        /** @var ZipHandleResponse $handleResponse */
        $handleResponse = $this->handle($this->createFormUploadFiles(), '', $message);
        $decodedResponse = \json_decode($handleResponse->getResponse(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals('error', $decodedResponse['status']);
    }
}
