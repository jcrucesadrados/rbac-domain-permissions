<?php

namespace Getorbit\RbacDomainPermissions\Infrastructure\Services;

use Getorbit\RbacDomainPermissions\Domain\Entities\PermissionsObject;
use Getorbit\RbacDomainPermissions\Domain\Entities\User;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\InvalidActionForObject;
use Getorbit\RbacDomainPermissions\Domain\Services\PermissionsCheckerInterface;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\Action;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain\RoleDomain;
use Lauthz\Facades\Enforcer;

class PermissionsChecker implements PermissionsCheckerInterface
{
    /**
     * @throws InvalidActionForObject
     */
    public function canInDomain(User $user, PermissionsObject $object, Action $action, RoleDomain $domain): bool
    {
        if (! $object->isAnAllowedAction($action)) {
            throw new InvalidActionForObject();
        }

        return Enforcer::enforce($user->userId->asString(), $domain->asString(), $object->key, $action->value);
    }
}
