<?php

namespace Getorbit\RbacDomainPermissions\Domain\Services;

use Getorbit\RbacDomainPermissions\Domain\Entities\PermissionsObject;
use Getorbit\RbacDomainPermissions\Domain\Entities\User;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\InvalidActionForObject;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\Action;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain\RoleDomain;

interface PermissionsCheckerInterface
{
    /**
     * @throws InvalidActionForObject
     */
    public function canInDomain(User $user, PermissionsObject $object, Action $action, RoleDomain $domain): bool;
}
