<?php

declare(strict_types=1);

namespace App\Helper;

use EMS\Helpers\Standard\Json;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

class ProcessWrapper
{
    public const BUFFER_SIZE = 1024 * 1024;
    private float $timeout;
    /** @var string[] */
    private array $command;
    private ?Process $process = null;
    private ?StreamInterface $stream;

    /**
     * @param string[] $command
     */
    public function __construct(array $command, ?StreamInterface $stream = null, float $timeout = 3 * 60.0)
    {
        $this->timeout = $timeout;
        $this->command = $command;
        $this->stream = $stream;
    }

    public function start(): void
    {
        $this->initialize();
        $this->process = new Process($this->command);
        $this->process->setTimeout($this->timeout);
        $input = new InputStream();
        $this->process->setInput($input);
        $this->process->start(function () {
        }, [
            'LANG' => 'en_US.utf-8',
        ]);
        if (null === $this->stream) {
            $input->close();

            return;
        }
        if ($this->stream->isSeekable() && $this->stream->tell() > 0) {
            $this->stream->rewind();
        }
        while (!$this->stream->eof()) {
            $input->write($this->stream->read(self::BUFFER_SIZE));
        }
        $input->close();
    }

    public function getOutput(): string
    {
        if (null === $this->process) {
            $this->start();
        }
        if (null === $this->process) {
            throw new \RuntimeException('Unexpected null process');
        }
        if (!$this->process->isTerminated()) {
            $this->process->wait();
        }

        return $this->process->getOutput();
    }

    /**
     * @return mixed[]
     */
    public function getJson(): array
    {
        $output = $this->getOutput();
        if (\in_array($output, [null, 'null', ''])) {
            return [];
        }

        return Json::decode($output);
    }

    protected function initialize(): void
    {
    }
}
