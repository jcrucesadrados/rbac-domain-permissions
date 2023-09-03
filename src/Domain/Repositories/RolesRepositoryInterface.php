<?php

namespace Getorbit\RbacDomainPermissions\Domain\Repositories;

use Getorbit\RbacDomainPermissions\Domain\Aggregates\Role;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\ObjectNotFound;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\RoleAlreadyExists;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\RoleMustHaveAtLeastOnePolicy;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\RoleNotFound;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleName;
use Throwable;

interface RolesRepositoryInterface
{
    /**
     * @throws RoleNotFound|ObjectNotFound
     */
    public function getFromRoleName(RoleName $roleName): Role;

    /**
     * @throws Throwable|RoleAlreadyExists|RoleMustHaveAtLeastOnePolicy
     */
    public function create(Role $roleAggregate): void;

    /**
     * @throws Throwable|RoleNotFound
     */
    public function update(Role $roleAggregate): void;
}
