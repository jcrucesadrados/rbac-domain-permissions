<?php

namespace Getorbit\RbacDomainPermissions\Tests\Stubs;

use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain\DomainContext;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain\RoleDomain;

class RoleDomainStub extends RoleDomain
{
    private const KEY = 'system';

    protected function __construct(string $id)
    {
        parent::__construct(self::KEY, $id);
    }

    public static function fromId(string $id): self
    {
        return new self($id);
    }

    public static function context(): DomainContext
    {
        return DomainContext::from(self::KEY);
    }
}
