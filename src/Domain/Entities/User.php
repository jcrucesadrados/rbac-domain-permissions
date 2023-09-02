<?php

namespace Getorbit\RbacDomainPermissions\Domain\Entities;

use Getorbit\RbacDomainPermissions\Domain\ValueObjects\UserId;

readonly class User
{
    public function __construct(public UserId $userId)
    {
    }

    public static function fromString(string $id): self
    {
        return new self(UserId::fromString($id));
    }
}
