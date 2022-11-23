<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Processor;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use ZipStream\Option\Archive;
use ZipStream\ZipStream;

class Zip
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function generate(): StreamInterface
    {
        $stream = \fopen('php://temp', 'r+');
        if (false === $stream) {
            throw new \RuntimeException('Unexpected false temporary stream');
        }

        $option = new Archive();
        $option->setZeroHeader(true);
        $option->setEnableZip64(false);
        $option->setOutputStream($stream);
        $zip = new ZipStream(null, $option);
        foreach ($this->config->getFiles() as $file) {
            $zip->addFileFromPsr7Stream($file['filename'], $file['stream']);
        }

        $zip->finish();

        return new Stream($stream);
    }
}
