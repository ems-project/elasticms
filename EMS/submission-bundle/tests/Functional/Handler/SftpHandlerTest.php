<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use EMS\SubmissionBundle\Response\SftpHandleResponse;
use EMS\SubmissionBundle\Tests\Functional\App\FilesystemFactory;

final class SftpHandlerTest extends AbstractHandlerTest
{
    /** @var FilesystemFactory */
    private $filesystemFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystemFactory = $this->container->get('emss.filesystem.factory');

        // $this->filesystemFactory->setFlagNullAdapter(false); uncomment for enabling sftp
    }

    protected function getHandler(): AbstractHandler
    {
        return $this->container->get('functional_test.emss.handler.sftp');
    }

    public function testSubmitFormData(): void
    {
        $host = '127.0.0.1';
        $endpoint = \json_encode([
            'host' => $host,
            'root' => '/',
            'username' => 'tester',
            'password' => 'password',
            'privateKey' => 'dGVzdCA2NCBlbmNvZGluZw==', // base64 'test 64 encoding'
        ]);
        $message = \file_get_contents(__DIR__.'/../fixtures/twig/message_sftp.twig');
        /** @var SftpHandleResponse $handleResponse */
        $handleResponse = $this->handle($this->createFormUploadFiles(), $endpoint, $message);

        $this->assertEquals(
            '{"status":"success","data":"Submission send by sftp."}',
            $handleResponse->getResponse()
        );
        $this->assertEquals($host, $handleResponse->getSftpRequest()->getEndpoint()['host']);
        $this->assertEquals('test 64 encoding', $handleResponse->getSftpRequest()->getEndpoint()['privateKey']);
        $this->assertCount(2, $handleResponse->getTransportedFiles());
    }

    public function testEmptyEndpoint(): void
    {
        $message = \file_get_contents(__DIR__.'/../fixtures/twig/message_sftp.twig');
        /** @var SftpHandleResponse $handleResponse */
        $handleResponse = $this->handle($this->createFormUploadFiles(), '', $message);

        $this->assertEquals(
            '{"status":"error","data":"Submission failed, contact your admin. (Invalid endpoint configuration: The required option \"host\" is missing.)"}',
            $handleResponse->getResponse()
        );
    }

    public function testInvalidJsonEndpoint(): void
    {
        $message = \file_get_contents(__DIR__.'/../fixtures/twig/message_sftp.twig');
        /** @var SftpHandleResponse $handleResponse */
        $handleResponse = $this->handle($this->createFormUploadFiles(), 'test', $message);

        $this->assertEquals(
            '{"status":"error","data":"Submission failed, contact your admin. (invalid json!)"}',
            $handleResponse->getResponse()
        );
    }

    public function testEndpointPrivateKeyBase64Decoding()
    {
        $endpoint = \json_encode(['host' => '127.0.0.1', 'privateKey' => '']);
        /** @var SftpHandleResponse $handleResponse */
        $handleResponse = $this->handle($this->createFormUploadFiles(), $endpoint, '');
        $this->assertEquals('', $handleResponse->getSftpRequest()->getEndpoint()['privateKey']);

        $endpoint = \json_encode(['host' => '127.0.0.1', 'privateKey' => '___']);
        /** @var SftpHandleResponse $handleResponse */
        $handleResponse = $this->handle($this->createFormUploadFiles(), $endpoint, '');
        $this->assertEquals('invalid base64 encoding', $handleResponse->getSftpRequest()->getEndpoint()['privateKey']);
    }

    public function testInvalidMessage(): void
    {
        $message = '{% block files %}test{% endblock files %}';

        /** @var SftpHandleResponse $handleResponse */
        $handleResponse = $this->handle($this->createFormUploadFiles(), '{"host": "127.0.0.1"}', $message);

        $this->assertEquals(
            '{"status":"error","data":"Submission failed, contact your admin. (invalid json!)"}',
            $handleResponse->getResponse()
        );
    }

    public function testWrongFileMessage(): void
    {
        $message = '{% block files %}[{"test": "test"}, {"path": "test.txt", "content_base64": "dGVzdA==" }]{% endblock files %}';

        /** @var SftpHandleResponse $handleResponse */
        $handleResponse = $this->handle($this->createFormUploadFiles(), '{"host": "127.0.0.1"}', $message);

        $this->assertEquals(
            '{"status":"success","data":"Submission send by sftp."}',
            $handleResponse->getResponse()
        );
        $this->assertCount(1, $handleResponse->getTransportedFiles());
        $this->assertEquals('test.txt', $handleResponse->getTransportedFiles()[0]['path']);
        $this->assertEquals('test', $handleResponse->getTransportedFiles()[0]['contents']);
    }

    public function testFailConnection(): void
    {
        $this->filesystemFactory->setFlagNullAdapter(false); // enable ftp

        $endpoint = \json_encode([
            'host' => '127.0.0.1',
            'root' => '/',
        ]);
        $message = \file_get_contents(__DIR__.'/../fixtures/twig/message_sftp.twig');
        /** @var SftpHandleResponse $handleResponse */
        $handleResponse = $this->handle($this->createFormUploadFiles(), $endpoint, $message);

        $jsonResponse = \json_decode($handleResponse->getResponse(), true);

        $this->assertEquals('error', $jsonResponse['status']);
    }
}
