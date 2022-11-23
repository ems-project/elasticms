<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Service\Pdf;

final class PdfOutput
{
    /** @var callable */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function make(): string
    {
        try {
            $content = ($this->callback)();

            return \is_string($content) ? $content : 'No content';
        } catch (\Exception $e) {
            return \sprintf('Error getting content: %s', $e->getMessage());
        }
    }
}
