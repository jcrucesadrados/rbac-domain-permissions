<?php

namespace Getorbit\RbacDomainPermissions\Tests\Unit\Domain\ValueObjects;

use Getorbit\RbacDomainPermissions\Domain\ValueObjects\UserPermission;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UserPermissionTest extends TestCase
{
    /** @test */
    public function itShouldBeConstructedFromArray(): void
    {
        $permission = UserPermission::fromArray([
            'role' => 'omcUsers',
            'object' => 'login',
            'action' => 'perform',
            'domain' => 'system:1',
        ]);

        $this->assertInstanceOf(UserPermission::class, $permission);
    }

    /** @test */
    public function fromArrayShouldThrowExceptionIfInvalidParams(): void
    {
        // then
        $this->expectException(InvalidArgumentException::class);

        // when
        UserPermission::fromArray([
            'wrong' => 'omcUsers',
            'params' => 'login',
        ]);
    }
}
