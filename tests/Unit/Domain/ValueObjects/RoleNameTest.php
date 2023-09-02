<?php

namespace Getorbit\RbacDomainPermissions\Tests\Domain\ValueObjects;

use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleName;
use PHPUnit\Framework\TestCase;

class RoleNameTest extends TestCase
{
    /** @test */
    public function itShouldBeConstructedFromString(): void
    {
        // given
        $expectedRoleName = 'RoleName';

        // when
        $roleName = RoleName::fromString($expectedRoleName);

        // then
        $this->assertEquals($expectedRoleName, $roleName->asString());
    }

    /** @test */
    public function toStringShouldReturnExpectedString(): void
    {
        // given
        $expectedRoleName = 'RoleName';

        // when
        $roleName = RoleName::fromString($expectedRoleName);

        // then
        $this->assertEquals($expectedRoleName, (string)$roleName);
    }
}
