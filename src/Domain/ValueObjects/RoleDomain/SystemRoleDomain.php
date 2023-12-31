<?php

namespace Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain;

class SystemRoleDomain extends RoleDomain
{
    private const KEY = 'system';

    protected function __construct(string $id)
    {
        parent::__construct(self::KEY, $id);
    }

    public static function fromId(string | int $id): self
    {
        return new self($id);
    }

    public static function context(): DomainContext
    {
        return DomainContext::from(self::KEY);
    }
}
