<?php

namespace Getorbit\RbacDomainPermissions\Tests\Traits;

use Getorbit\RbacDomainPermissions\Domain\Entities\PermissionsObject;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\InvalidAllowedAction;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\PermissionsObjectList;

trait ObjectsSetupTrait
{
    private function getValidPermissionsObject(string $key = 'firstObject'): PermissionsObject
    {
        $object = ObjectsPermissionsStub::getPermissionsObjects();

        return PermissionsObject::fromArray($object[$key]);
    }

    private function getInvalidPermissionsObject(): PermissionsObject
    {
        return PermissionsObject::fromArray([
            'key' => 'myKey',
            'description' => 'description',
            'allowedActions' => ['perform', 'create'],
        ]);
    }

    /**
     * @throws InvalidAllowedAction
     */
    private function getValidPermissionsObjectList(): PermissionsObjectList
    {
        return PermissionsObjectList::fromArray(ObjectsPermissionsStub::getPermissionsObjects());
    }

    private function getRawObjects(): array
    {
        return ObjectsPermissionsStub::getPermissionsObjects();
    }
}
