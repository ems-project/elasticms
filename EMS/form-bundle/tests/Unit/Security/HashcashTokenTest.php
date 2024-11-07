<?php

declare(strict_types=1);

namespace EMS\FormBundle\Tests\Unit\Security;

use EMS\FormBundle\Security\HashcashToken;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\ByteString;

class HashcashTokenTest extends TestCase
{
    public function testGenerate()
    {
        $difficulty = 16384;
        $token = HashcashToken::generate(ByteString::fromRandom(36)->toString(), $difficulty);
        $this->assertTrue($token->isValid($difficulty));
    }
}
