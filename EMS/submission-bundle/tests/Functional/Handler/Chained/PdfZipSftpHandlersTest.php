<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\Handler\Chained;

use EMS\SubmissionBundle\Handler\PdfHandler;
use EMS\SubmissionBundle\Handler\SftpHandler;
use EMS\SubmissionBundle\Handler\ZipHandler;
use EMS\SubmissionBundle\Response\PdfHandleResponse;
use EMS\SubmissionBundle\Response\SftpHandleResponse;
use EMS\SubmissionBundle\Response\ZipHandleResponse;
use EMS\SubmissionBundle\Tests\Functional\App\FilesystemFactory;
use Symfony\Component\Filesystem\Filesystem;

final class PdfZipSftpHandlersTest extends AbstractChainedTest
{
    /** @var FilesystemFactory */
    private $filesystemFactory;
    /** @var PdfHandler */
    private $pdfHandler;
    /** @var SftpHandler */
    private $sftpHandler;
    /** @var ZipHandler */
    private $zipHandler;
    private string $tempFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystemFactory = $this->container->get('emss.filesystem.factory');
        $this->pdfHandler = $this->container->get('functional_test.emss.handler.pdf');
        $this->sftpHandler = $this->container->get('functional_test.emss.handler.sftp');
        $this->zipHandler = $this->container->get('functional_test.emss.handler.zip');

        $filesystem = new Filesystem();
        $this->tempFile = $filesystem->tempnam(\sys_get_temp_dir(), 'emss');

        // $this->filesystemFactory->setFlagNullAdapter(false); uncomment for enabling sftp
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        \unlink($this->tempFile);
    }

    public function testPdfZipSftpChain(): void
    {
        $pdfEndpoint = \json_encode(['filename' => 'form.pdf']);
        $pdfMessage = \file_get_contents(__DIR__.'/../../fixtures/twig/chainedPdfZipSftp/message_pdf.twig');
        $pdfHandleRequest = $this->createRequest(PdfHandler::class, $pdfEndpoint, $pdfMessage);
        /** @var PdfHandleResponse $pdfHandleResponse */
        $pdfHandleResponse = $this->pdfHandler->handle($pdfHandleRequest);

        $this->responseCollector->addResponse($pdfHandleResponse);

        $zipEndpointJson = \json_encode(['filename' => 'chain.zip']);
        $zipMessage = \file_get_contents(__DIR__.'/../../fixtures/twig/chainedPdfZipSftp/message_zip.twig');
        $zipHandleRequest = $this->createRequest(ZipHandler::class, $zipEndpointJson, $zipMessage);
        /** @var ZipHandleResponse $zipHandleRespsonse */
        $zipHandleResponse = $this->zipHandler->handle($zipHandleRequest);

        $this->responseCollector->addResponse($zipHandleResponse);

        $sftpEndpointJson = \json_encode(['host' => 'localhost']);
        $sftpMessage = \file_get_contents(__DIR__.'/../../fixtures/twig/chainedPdfZipSftp/message_sftp.twig');
        $sftpHandleRequest = $this->createRequest(SftpHandler::class, $sftpEndpointJson, $sftpMessage);
        /** @var SftpHandleResponse $sftpHandleResponse */
        $sftpHandleResponse = $this->sftpHandler->handle($sftpHandleRequest);

        \file_put_contents($this->tempFile, $sftpHandleResponse->getTransportedFiles()[0]['contents']);
        $zip = new \ZipArchive();
        $zip->open($this->tempFile);

        $this->assertCount(1, $sftpHandleResponse->getTransportedFiles());
        $this->assertEquals('chain.zip', $sftpHandleResponse->getTransportedFiles()[0]['path']);
        $binaryPdf = $pdfHandleResponse->getContentRaw();
        $this->assertEquals($binaryPdf, $zip->getFromName('form.pdf'));
    }
}
