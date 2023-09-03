<?php

namespace Getorbit\RbacDomainPermissions\Infrastructure\Repositories;

use Getorbit\RbacDomainPermissions\Domain\Entities\PermissionsObject;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\InvalidAllowedAction;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\ObjectNotFound;
use Getorbit\RbacDomainPermissions\Domain\Repositories\ObjectsQueryRepository;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\PermissionsObjectList;

class ConstantsObjectsQueryRepository implements ObjectsQueryRepository
{
    private PermissionsObjectList $objects;

    /**
     * @throws InvalidAllowedAction
     */
    public function __construct(array $objectsClasses)
    {
        $this->objects = $this->getObjectFromConstants($objectsClasses);
    }

    public function all(): PermissionsObjectList
    {
        return $this->objects;
    }

    /**
     * @throws ObjectNotFound
     */
    public function getObjectFromKey(string $key): PermissionsObject
    {
        return $this->objects->getPermissionsObject($key);
    }

    /**
     * @throws InvalidAllowedAction
     */
    private function getObjectFromConstants(array $objectsClasses): PermissionsObjectList
    {
        $permissionsObjects = [];
        foreach ($objectsClasses as $class) {
            $permissionsObjects = [...$permissionsObjects, ...$class::getPermissionsObjects()];
        }

        return PermissionsObjectList::fromArray($permissionsObjects);
    }
}
