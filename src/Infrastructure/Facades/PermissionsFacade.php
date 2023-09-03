<?php

namespace Getorbit\RbacDomainPermissions\Infrastructure\Facades;

use Getorbit\RbacDomainPermissions\Domain\Aggregates\Role;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\UserPermissionsList;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Role createRole(string $role, string $object, string $action)
 * @method static void deleteRole(string $role)
 * @method static Role addPolicyToRole(string $role, string $object, string $action)
 * @method static Role removePolicyFromRole(string $role, string $object, string $action)
 * @method static void addRoleForUserInDomain(string $userId, string $role, string $context, string|int $domain)
 * @method static UserPermissionsList getUserPermissions(string $userId)
 * @method static void removeRoleForUserInDomain(string $userId, string $role, string $context, string|int $domain)
 * @method static bool canWithDomain(string $userId, string $object, string $action, string $context, string $domain)
 * @method static array getUserRoleDomainIds(string $userId, string $roleDomainClass)
 */
class PermissionsFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'Permissions';
    }
}
