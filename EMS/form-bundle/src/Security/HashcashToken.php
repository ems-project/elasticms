<?php

namespace EMS\FormBundle\Security;

use Symfony\Component\String\ByteString;

class HashcashToken
{
    private readonly string $hash;
    private readonly string $nonce;
    private readonly string $data;

    public function __construct(private readonly string $header, private readonly string $token)
    {
        [$hash, $nonce, $data] = \explode('|', $header);

        $this->hash = $hash;
        $this->nonce = $nonce;
        $this->data = $data;
    }

    public function isValid(int $difficulty): bool
    {
        if ($this->data !== $this->token) {
            return false;
        }

        $hashcashLevel = \floor(\log($difficulty, 2) / 4.0);
        if (!\preg_match(\sprintf('/^0{%d}/', $hashcashLevel), $this->hash)) {
            return false;
        }

        $data = ['difficulty' => $difficulty, 'data' => $this->data, 'nonce' => $this->nonce];

        return $this->hash === \hash('sha256', \implode('|', $data));
    }

    public static function generate(string $token, int $difficulty): self
    {
        $hashcashLevel = \intval(\floor(\log($difficulty, 2) / 4.0));
        $regex = \sprintf('/^0{%d}/', $hashcashLevel);

        do {
            $random = ByteString::fromRandom(13, '0123456789');
            $hash = \hash('sha256', \implode('|', [$difficulty, $token, $random]));
        } while (!\preg_match($regex, $hash));

        return new self(\implode('|', [$hash, $random, $token]), $token);
    }

    public function getHeader(): string
    {
        return $this->header;
    }
}
