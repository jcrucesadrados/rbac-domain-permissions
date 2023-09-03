<?php

namespace Getorbit\RbacDomainPermissions\Domain\ValueObjects;

use Getorbit\RbacDomainPermissions\Domain\Entities\PermissionsObject;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\InvalidAllowedAction;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\ObjectNotFound;

final readonly class PermissionsObjectList
{
    private function __construct(private array $permissionsObjects)
    {
    }

    /**
    * @throws InvalidAllowedAction
    */
    public static function fromArray(array $permissionsObjectArray): self
    {
        return new self(
            array_map(fn ($object) => PermissionsObject::fromArray($object), $permissionsObjectArray),
        );
    }

    public function asArray(): array
    {
        return $this->permissionsObjects;
    }

    public function exists(string $key): bool
    {
        return array_key_exists($key, $this->permissionsObjects);
    }

    /**
     * @throws ObjectNotFound
     */
    public function getPermissionsObject(string $key): PermissionsObject
    {
        if (! $this->exists($key)) {
            throw new ObjectNotFound();
        }

        return $this->permissionsObjects[$key];
    }
}
