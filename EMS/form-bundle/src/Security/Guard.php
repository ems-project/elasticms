<?php

namespace EMS\FormBundle\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class Guard
{
    /** @var LoggerInterface */
    private $logger;
    /** @var int */
    private $difficulty;

    public function __construct(LoggerInterface $logger, int $difficulty)
    {
        $this->logger = $logger;
        $this->difficulty = $difficulty;
    }

    public function getDifficulty(): int
    {
        return $this->difficulty;
    }

    public function checkForm(Request $request): bool
    {
        $formData = $request->get('form', []);
        $submittedToken = $formData['_token'] ?? null;

        return $this->checkToken($request, $submittedToken);
    }

    public function checkToken(Request $request, ?string $token): bool
    {
        try {
            $this->validateHashcash($request, $token);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('guard check valid', [$e]);

            return false;
        }
    }

    private function validateHashcash(Request $request, ?string $token): void
    {
        if ($request->isMethodSafe() || 0 === $this->difficulty) {
            return;
        }

        if (!\is_string($token)) {
            throw new \Exception('guard check validation requires a non empty string csrf token in the submitted token');
        }

        $header = $request->headers->get('x-hashcash');

        if (!\is_string($header)) {
            throw new \Exception('x-hashcash header missing');
        }

        $hashCash = new HashcashToken($header, $token);

        if (!$hashCash->isValid($this->difficulty)) {
            throw new \Exception('invalid hashcash token');
        }
    }
}
