<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Processor;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use ZipStream\CompressionMethod;
use ZipStream\OperationMode;
use ZipStream\ZipStream;

class Zip
{
    public function __construct(private readonly Config $config)
    {
    }

    public function generate(): StreamInterface
    {
        $stream = \fopen('php://temp', 'r+');
        if (false === $stream) {
            throw new \RuntimeException('Unexpected false temporary stream');
        }

        $zip = new ZipStream(OperationMode::NORMAL, '', $stream, CompressionMethod::DEFLATE, 6, false, true);
        foreach ($this->config->getFiles() as $file) {
            $zip->addFileFromPsr7Stream($file['filename'], $file['stream']);
        }

        $zip->finish();

        return new Stream($stream);
    }
}
