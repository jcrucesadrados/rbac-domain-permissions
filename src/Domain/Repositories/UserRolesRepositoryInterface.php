<?php

namespace Getorbit\RbacDomainPermissions\Domain\Repositories;

use Getorbit\RbacDomainPermissions\Domain\Aggregates\Role;
use Getorbit\RbacDomainPermissions\Domain\Entities\User;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\UserHasNotRoleForDomain;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain\RoleDomain;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\UserId;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\UserPermissionsList;

interface UserRolesRepositoryInterface
{
    public function addRoleForUserInDomain(User $user, Role $role, RoleDomain $domain): void;

    public function getDomainsForUserAndRole(User $user, Role $role): array;

    public function getDomainsForUserContext(User $user, string $key, callable $callback): array;

    public function getUsersForRole(Role $role): array;

    /**
     * @throws UserHasNotRoleForDomain
     */
    public function removeUserForRoleInDomain(User $user, Role $role, RoleDomain $domain): void;

    public function getPermissionsByUserId(UserId $userId): UserPermissionsList;
}
