<?php

namespace Getorbit\RbacDomainPermissions\Tests\Domain\ValueObjects\RoleDomain;

use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain\RoleDomain;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain\SystemRoleDomain;
use PHPUnit\Framework\TestCase;

class SystemRoleDomainTest extends TestCase
{
    /** @test */
    public function fromIdCreatesANewRoleDomain(): void
    {
        $roleDomain = SystemRoleDomain::fromId('3');

        $this->assertInstanceOf(RoleDomain::class, $roleDomain);
        $this->assertEquals('3', $roleDomain->id());
        $this->assertEquals(SystemRoleDomain::context()->value, $roleDomain->context()->value);
    }

    /** @test */
    public function asStringReturnExpectedString(): void
    {
        $roleDomain = SystemRoleDomain::fromId(3);

        $this->assertEquals(
            sprintf('%s:%s', SystemRoleDomain::context()->value, 3),
            $roleDomain->asString(),
        );
    }

    /** @test */
    public function toStringReturnExpectedString(): void
    {
        $roleDomain = SystemRoleDomain::fromId(3);

        $this->assertEquals(
            sprintf('%s:%s', SystemRoleDomain::context()->value, 3),
            (string) $roleDomain,
        );
    }
}
