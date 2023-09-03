<?php

namespace Getorbit\RbacDomainPermissions\Tests\Unit\Domain\ValueObjects;

use Getorbit\RbacDomainPermissions\Tests\Stubs\UuidStub;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UuidTraitTest extends TestCase
{
    /** @test */
    public function fromStringShouldFailIfDataIsNotValidUuid(): void
    {
        // then
        $this->expectException(InvalidArgumentException::class);

        // when
        UuidStub::fromString('not-valid-uuid');
    }

    /** @test */
    public function fromStringShouldCreateUuid(): void
    {
        // given
        $uuidString = Str::uuid()->toString();

        // when
        $uuid = UuidStub::fromString($uuidString);

        // then
        $this->assertInstanceOf(UuidStub::class, $uuid);
        $this->assertEquals($uuidString, $uuid->uuid);
    }
}
