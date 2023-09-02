<?php

namespace Getorbit\RbacDomainPermissions\Tests\Domain\ValueObjects\RoleDomain;

use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain\DomainId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DomainIdTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideValues
     */
    public function itShouldBeConstructedOnlyIfIsValidValue(bool $expectException, string $value): void
    {
        // given
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }
        // when
        $domainId = DomainId::fromString($value);

        // then
        if (! $expectException) {
            $this->assertInstanceOf(DomainId::class, $domainId);
        }
    }

    public static function provideValues(): array
    {
        return [
            'With number as value' => [false, '3'],
            'With all as value' => [false, 'all'],
            'With decimal as value' => [true, '13.2'],
            'With letters and numbers' => [true, '2as'],
            'With letters' => [true, 'itShouldFail'],
        ];
    }

    /** @test */
    public function asStringShouldReturnStringValue(): void
    {
        // given
        $value = '3';
        // when
        $domainId = DomainId::fromString($value);

        // then
        $this->assertEquals($value, $domainId->asString());
    }

    /** @test */
    public function toStringShouldReturnStringValue(): void
    {
        // given
        $value = '3';
        // when
        $domainId = DomainId::fromString($value);

        // then
        $this->assertEquals($value, (string)$domainId);
    }

    /**
     * @test
     * @dataProvider provideValidCases
     */
    public function isAllDomainShouldReturnIfDomainIsAll(bool $expected, string $value): void
    {
        // when
        $domainId = DomainId::fromString($value);

        // then
        $this->assertEquals($expected, $domainId->isAllDomain());
    }

    public static function provideValidCases(): array
    {
        return [
            'Is not all' => [false, '3'],
            'Is all' => [true, 'all'],
        ];
    }
}
