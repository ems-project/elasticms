<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Response;

use EMS\FormBundle\Submission\AbstractHandleResponse;
use EMS\FormBundle\Submission\HandleRequestInterface;
use EMS\SubmissionBundle\Twig\TwigRenderer;

final class ResponseTransformer
{
    /**
     * Twig block inside the message template,
     * containing a json that will merged into the response data.
     */
    private const BLOCK_EXTRA = 'handleResponseExtra';

    public function __construct(private readonly TwigRenderer $twigRenderer)
    {
    }

    public function transform(HandleRequestInterface $handleRequest, AbstractHandleResponse $handleResponse): AbstractHandleResponse
    {
        $extra = $this->twigRenderer->renderMessageBlockJSON($handleRequest, self::BLOCK_EXTRA, [
            'response' => $handleResponse,
        ]);

        $handleResponse->setExtra($extra);

        return $handleResponse;
    }
}
