<?php

namespace Getorbit\RbacDomainPermissions\Infrastructure\Traits;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid as UuidLib;

trait Uuid
{
    private function __construct(public readonly string $uuid)
    {
    }

    public static function fromString(string $uuid): self
    {
        self::guardValidUuid($uuid);

        return new self($uuid);
    }

    private static function guardValidUuid(string $uuid): void
    {
        if (! UuidLib::isValid($uuid)) {
            throw new InvalidArgumentException('Invalid UUID.');
        }
    }
}
