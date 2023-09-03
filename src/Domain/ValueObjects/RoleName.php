<?php

namespace Getorbit\RbacDomainPermissions\Domain\ValueObjects;

class RoleName
{
    private function __construct(private readonly string $roleName)
    {
    }

    public static function fromString(string $roleName): self
    {
        return new self($roleName);
    }

    public function asString(): string
    {
        return $this->roleName;
    }

    public function __toString(): string
    {
        return $this->asString();
    }
}
