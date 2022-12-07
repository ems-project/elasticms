<?php

namespace EMS\FormBundle\Security;

class HashcashToken
{
    private readonly string $hash;
    private readonly string $nonce;
    private readonly string $data;

    public function __construct(string $header, private readonly string $token)
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
}
