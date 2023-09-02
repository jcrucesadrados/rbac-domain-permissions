<?php

namespace Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain;

interface RoleDomainInterface
{
    public function asString(): string;

    public function __toString(): string;

    public static function fromId(string $id): self;

    public function isAllDomain(): bool;
}
