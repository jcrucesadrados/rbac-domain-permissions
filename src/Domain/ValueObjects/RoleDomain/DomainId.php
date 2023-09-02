<?php

namespace Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain;

use Illuminate\Support\Str;
use InvalidArgumentException;

class DomainId
{
    private const ALL = 'all';
    private function __construct(public readonly string $value)
    {
    }

    public static function fromString(string $domainId): self
    {
        self::guardIsValidValueForDomainId($domainId);

        return new self($domainId);
    }

    public static function guardIsValidValueForDomainId(string $domainId): void
    {
        if (self::ALL !== $domainId && ! ctype_digit($domainId) && ! Str::isUuid($domainId)) {
            throw new InvalidArgumentException('Domain ID must be an integer, uuid or all');
        }
    }

    public function asString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->asString();
    }

    public function isAllDomain(): bool
    {
        return $this->value === self::ALL;
    }
}
