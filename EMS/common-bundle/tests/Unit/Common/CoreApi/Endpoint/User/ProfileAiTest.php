<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\CoreApi\Endpoint\User;

use EMS\CommonBundle\Common\CoreApi\Endpoint\User\Profile;
use PHPUnit\Framework\TestCase;

final class ProfileAiTest extends TestCase
{
    private Profile $profile;

    #[\Override]
    protected function setUp(): void
    {
        $data = [
            'id' => 1,
            'username' => 'testUser',
            'email' => 'test@example.com',
            'displayName' => 'Test User',
            'roles' => ['ROLE_USER', 'ROLE_ADMIN'],
            'circles' => ['circle1', 'circle2'],
            'lastLogin' => '2023-10-06T15:00:00+00:00',
            'expirationDate' => '2023-10-06T15:00:00+00:00',
            'userOptions' => [
                'option1' => 'ok',
                'option2' => 'nope',
                'customOption' => [
                    'customOption1' => 'nope',
                    'customOption2' => 'ok',
                ],
            ],
        ];
        $this->profile = new Profile($data);
    }

    public function testGetId(): void
    {
        $this->assertSame(1, $this->profile->getId());
    }

    public function testGetUsername(): void
    {
        $this->assertSame('testUser', $this->profile->getUsername());
    }

    public function testGetEmail(): void
    {
        $this->assertSame('test@example.com', $this->profile->getEmail());
    }

    public function testGetDisplayName(): void
    {
        $this->assertSame('Test User', $this->profile->getDisplayName());
    }

    public function testGetRoles(): void
    {
        $this->assertSame(['ROLE_USER', 'ROLE_ADMIN'], $this->profile->getRoles());
    }

    public function testGetCircles(): void
    {
        $this->assertSame(['circle1', 'circle2'], $this->profile->getCircles());
    }

    public function testGetLastLogin(): void
    {
        $expectedDate = \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, '2023-10-06T15:00:00+00:00');
        $this->assertEquals($expectedDate, $this->profile->getLastLogin());
    }

    public function testGetExpirationDate(): void
    {
        $expectedDate = \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, '2023-10-06T15:00:00+00:00');
        $this->assertEquals($expectedDate, $this->profile->getExpirationDate());
    }

    public function testGetUserOptions(): void
    {
        $this->assertSame(['option1' => 'ok', 'option2' => 'nope', 'customOption' => ['customOption1' => 'nope', 'customOption2' => 'ok']], $this->profile->getUserOptions());
    }
}
