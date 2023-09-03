<?php

namespace Getorbit\RbacDomainPermissions\Tests\Unit\Domain\ValueObjects\RoleDomain;

use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain\RoleDomain;
use Getorbit\RbacDomainPermissions\Tests\Stubs\RoleDomainStub;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RoleDomainTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideValidCases
     */
    public function fromStringCreatesANewRoleDomain(string $roleDomainCase, string | int $expectedDomain): void
    {
        $roleDomain = RoleDomainStub::fromString($roleDomainCase);

        $this->assertInstanceOf(RoleDomain::class, $roleDomain);
        $this->assertEquals($expectedDomain, $roleDomain->id()->value);
        $this->assertEquals(RoleDomainStub::context(), $roleDomain->context());
    }

    public static function provideValidCases(): array
    {
        $roleDomainFormat = 'system:%s';
        $uuid = Str::uuid()->toString();

        return [
            'With integer domain' => [
                'roleDomain' => sprintf($roleDomainFormat, 4),
                'expectedDomain' => 4,
            ],
            'With UUID domain' => [
                'roleDomain' => sprintf($roleDomainFormat, $uuid),
                'expectedDomain' => $uuid,
            ],
            'With all domain' => [
                'roleDomain' => sprintf($roleDomainFormat, 'all'),
                'expectedDomain' => 'all',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideInvalidCases
     */
    public function fromStringThrowsErrorWhenInvalidArgument(string $role): void
    {
        $this->expectException(InvalidArgumentException::class);
        RoleDomainStub::fromString($role);
    }

    public static function provideInvalidCases(): array
    {
        return [
            'role has unexpected elements' => [
                'system',
            ],
            'role has empty context' => [
                ':2',
            ],
            'role has invalid id' => [
                'system:test',
            ],
        ];
    }
}
