<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Hashcash;

final class Token
{
    private readonly string $level;
    private readonly string $csrf;
    private readonly string $random;

    private const DELIMITER = '|';

    public function __construct(string $hashcash)
    {
        [$this->level, $this->csrf, $this->random] = \explode(Token::DELIMITER, $hashcash);
    }

    public function getLevel(): int
    {
        return \intval($this->level);
    }

    public function getCsrf(): string
    {
        return $this->csrf;
    }

    public function getRandom(): string
    {
        return $this->random;
    }
}
