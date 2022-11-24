<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Exception;

use Psr\Http\Message\ResponseInterface;

final class UserApiResponseException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function forFileUpload(ResponseInterface $response, \SplFileInfo $file): UserApiResponseException
    {
        return new self(\vsprintf('Upload file %s failed [%d]: %s', [
            $file->getFilename(),
            $response->getStatusCode(),
            $response->getBody()->getContents(),
        ]));
    }
}
