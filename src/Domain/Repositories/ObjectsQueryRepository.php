<?php

namespace Getorbit\RbacDomainPermissions\Domain\Repositories;

use Getorbit\RbacDomainPermissions\Domain\Entities\PermissionsObject;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\ObjectNotFound;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\PermissionsObjectList;

interface ObjectsQueryRepository
{
    public function all(): PermissionsObjectList;

    /**
     * @throws ObjectNotFound
     */
    public function getObjectFromKey(string $key): PermissionsObject;
}
