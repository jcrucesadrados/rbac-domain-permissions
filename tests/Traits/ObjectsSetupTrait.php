<?php

namespace Getorbit\RbacDomainPermissions\Tests\Traits;

use Getorbit\RbacDomainPermissions\Domain\Entities\PermissionsObject;

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

    /*

    private function getValidPermissionsObjectList(): PermissionsObjectList
    {
        return PermissionsObjectList::fromArray(ObjectsPermissionsStub::getObjects());
    }

    private function getRawObjects(): array
    {
        return ObjectsPermissionsStub::getObjects();
    }
    */
}
