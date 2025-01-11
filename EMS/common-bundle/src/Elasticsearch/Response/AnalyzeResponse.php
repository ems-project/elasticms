<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Response;

use EMS\Helpers\Standard\Type;

class AnalyzeResponse implements \JsonSerializable, \Stringable
{
    /**
     * @param array<int, array<string, int|string>> $tokens
     */
    public function __construct(private readonly array $tokens)
    {
    }

    #[\Override]
    public function __toString(): string
    {
        $tokens = [];
        foreach ($this->tokens as $token) {
            if (!isset($token['token'])) {
                continue;
            }
            $tokens[] = Type::string($token['token']);
        }

        return \implode(', ', $tokens);
    }

    public function count(): int
    {
        return \count($this->tokens);
    }

    /**
     * @return iterable<Token>
     */
    public function getTokens(): iterable
    {
        foreach ($this->tokens as $token) {
            yield new Token($token);
        }
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->tokens;
    }
}
