<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\CoreApi\Endpoint\User;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Common\CoreApi\Endpoint\User\Profile;
use EMS\CommonBundle\Common\CoreApi\Endpoint\User\User;
use EMS\CommonBundle\Common\CoreApi\Result;
use PHPUnit\Framework\TestCase;

final class UserAiTest extends TestCase
{
    private Client $client;
    private User $user;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->user = new User($this->client);
    }

    public function testGetProfiles(): void
    {
        $result = $this->createMock(Result::class);
        $result->method('getData')->willReturn([
            ['id' => 1, 'username' => 'user1', 'email' => 'user1@example.com'],
            ['id' => 2, 'username' => 'user2', 'email' => 'user2@example.com'],
        ]);

        $this->client->method('get')->with('/api/user-profiles')->willReturn($result);

        $profiles = $this->user->getProfiles();

        $this->assertCount(2, $profiles);
        $this->assertInstanceOf(Profile::class, $profiles[0]);
        $this->assertEquals('user1', $profiles[0]->getUsername());
        $this->assertEquals('user2', $profiles[1]->getUsername());
    }

    public function testGetProfileAuthenticated(): void
    {
        $result = $this->createMock(Result::class);
        $result->method('getData')->willReturn(['id' => 1, 'username' => 'user1', 'email' => 'user1@example.com']);

        $this->client->method('get')->with('/api/user-profile')->willReturn($result);

        $profile = $this->user->getProfileAuthenticated();

        $this->assertInstanceOf(Profile::class, $profile);
        $this->assertEquals('user1', $profile->getUsername());
    }
}
