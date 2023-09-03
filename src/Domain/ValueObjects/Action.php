<?php

namespace Getorbit\RbacDomainPermissions\Domain\ValueObjects;

enum Action: string
{
    case Perform = 'perform';
    case Create = 'create';
    case Read = 'read';
    case Update = 'update';
    case Delete = 'delete';
    case All = 'all';

    public function equals(Action $otherAction): bool
    {
        return $this === $otherAction;
    }
}
