<?php

namespace Getorbit\RbacDomainPermissions\Tests\Domain\ValueObjects\RoleDomain;

use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain\DomainContext;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DomainContextTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideValues
     */
    public function guardIsValidContextShouldValidateContext(bool $expectException, string $value): void
    {
        // then
        match($expectException) {
            true => $this->expectException(InvalidArgumentException::class),
            false => $this->expectNotToPerformAssertions(),
        };

        // when
        DomainContext::guardIsValidContext($value);
    }

    public static function provideValues(): array
    {
        return [
            'With valid context' => [false, 'system'],
            'With invalid context' => [true, 'itShouldFail'],
        ];
    }
}
