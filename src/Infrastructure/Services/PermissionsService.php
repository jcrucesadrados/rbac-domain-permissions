<?php

namespace Getorbit\RbacDomainPermissions\Infrastructure\Services;

use Getorbit\RbacDomainPermissions\Domain\Aggregates\Role;
use Getorbit\RbacDomainPermissions\Domain\Entities\User;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\InvalidActionForObject;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\ObjectNotFound;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\RoleMustHaveAtLeastOnePolicy;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\RoleNotFound;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\UserHasNotRoleForDomain;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\Action;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\PermissionsPolicy;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain\DomainContext;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain\RoleDomain;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleName;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\UserId;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\UserPermissionsList;
use InvalidArgumentException;
use Throwable;

class PermissionsService
{
    private static ObjectsQueryRepository $objectsQueryRepository;
    private static PermissionsCheckerInterface $permissionsChecker;
    private static RolesRepositoryInterface $rolesRepository;
    private static UserRolesRepositoryInterface $userRolesRepository;

    final public function __construct()
    {
        // final constructor is required to avoid unsafe use of new static()
    }

    public static function make(
        ObjectsQueryRepository $objectsQueryRepository,
        PermissionsCheckerInterface $permissionsChecker,
        RolesRepositoryInterface $rolesRepository,
        UserRolesRepositoryInterface $userRolesRepository,
    ): static {
        self::$objectsQueryRepository = $objectsQueryRepository;
        self::$permissionsChecker = $permissionsChecker;
        self::$rolesRepository = $rolesRepository;
        self::$userRolesRepository = $userRolesRepository;

        return new static();
    }

    /**
     * @throws Throwable|RoleMustHaveAtLeastOnePolicy|ObjectNotFound
     */
    public static function createRole(string $role, string $object, string $action): Role
    {
        [$roleName, $policy] = self::getDomainObjects($role, $object, $action);
        $role = Role::new($roleName, $policy);

        self::$rolesRepository->create($role);

        return $role;
    }

    /**
     * @throws Throwable|RoleMustHaveAtLeastOnePolicy|ObjectNotFound
     */
    public static function deleteRole(string $role): void
    {
        $role = self::$rolesRepository->getFromRoleName(RoleName::fromString($role));

        $users = self::$userRolesRepository->getUsersForRole($role);
        foreach ($users as $user) {
            $domain = RoleDomain::getDomainData($user->domain);
            self::removeRoleForUserInDomain($user->userId, $user->role, $domain[0], $domain[1]);
        }

        foreach ($role->getPolicies() as $policy) {
            self::removePolicyFromRole(
                $role->getRoleName()->asString(),
                $policy->object->key,
                $policy->action->value,
            );
        }
    }

    /**
     * @throws Throwable|ObjectNotFound|RoleNotFound
     */
    public static function addPolicyToRole(string $role, string $object, string $action): Role
    {
        [$roleName, $policy] = self::getDomainObjects($role, $object, $action);

        $role = self::$rolesRepository->getFromRoleName($roleName);
        $role->addPolicy($policy);

        self::$rolesRepository->update($role);

        return $role;
    }

    /**
     * @throws Throwable|ObjectNotFound|RoleNotFound
     */
    public static function removePolicyFromRole(string $role, string $object, string $action): Role
    {
        [$roleName, $policy] = self::getDomainObjects($role, $object, $action);

        $role = self::$rolesRepository->getFromRoleName($roleName);
        $role->removePolicy($policy);

        self::$rolesRepository->update($role);


        return $role;
    }

    /**
     * @throws ObjectNotFound|RoleNotFound
     */
    public static function addRoleForUserInDomain(
        string $userId,
        string $role,
        string $context,
        string $domain,
    ): void {
        $roleDomain = self::getRoleDomain($context, $domain);

        self::$userRolesRepository->addRoleForUserInDomain(
            User::fromString($userId),
            self::$rolesRepository->getFromRoleName(RoleName::fromString($role)),
            $roleDomain,
        );

    }

    /**
     * @throws UserHasNotRoleForDomain|ObjectNotFound|RoleNotFound
     */
    public static function removeRoleForUserInDomain(
        string $userId,
        string $role,
        string $context,
        string $domain,
    ): void {
        $roleDomain = self::getRoleDomain($context, $domain);

        self::$userRolesRepository->removeUserForRoleInDomain(
            User::fromString($userId),
            self::$rolesRepository->getFromRoleName(RoleName::fromString($role)),
            $roleDomain,
        );

    }

    /**
     * @param string $userId
     * @param string $object
     * @param string $action
     * @param string $context
     * @param string $domain
     * @return bool
     * @throws InvalidActionForObject
     * @throws ObjectNotFound
     */
    public static function canWithDomain(
        string $userId,
        string $object,
        string $action,
        string $context,
        string $domain,
    ): bool {
        return self::$permissionsChecker->canInDomain(
            User::fromString($userId),
            self::$objectsQueryRepository->getObjectFromKey($object),
            Action::from($action),
            self::getRoleDomain($context, $domain),
        );
    }

    /**
     * @param string $userId
     * @return UserPermissionsList
     */
    public static function getUserPermissions(string $userId): UserPermissionsList
    {
        return self::$userRolesRepository->getPermissionsByUserId(UserId::fromString($userId));
    }

    /**
     * @param string $userId
     * @param string $roleDomainClass
     * @return array
     */
    public function getUserRoleDomainIds(string $userId, string $roleDomainClass): array
    {
        if (! is_subclass_of($roleDomainClass, RoleDomain::class)) {
            throw new InvalidArgumentException();
        }

        return array_map(
            fn (RoleDomain $roleDomain) => $roleDomain->id(),
            self::$userRolesRepository->getDomainsForUserContext(
                User::fromString($userId),
                $roleDomainClass::context()->value,
                fn ($item) => $roleDomainClass::fromString($item),
            ),
        );
    }

    /**
     * @param string $role
     * @param string $object
     * @param string $action
     * @return array
     * @throws InvalidActionForObject
     * @throws ObjectNotFound
     */
    private static function getDomainObjects(string $role, string $object, string $action): array
    {
        $roleName = RoleName::fromString($role);

        $permissionsObject = self::$objectsQueryRepository->getObjectFromKey($object);
        $permissionsAction = Action::from($action);
        $policy = PermissionsPolicy::new(
            $permissionsObject,
            $permissionsAction,
        );

        return [$roleName, $policy, $permissionsObject, $permissionsAction];
    }

    /**
     * @param string $context
     * @param string $domain
     * @return RoleDomain
     */
    private static function getRoleDomain(string $context, string $domain): RoleDomain
    {
        $context = DomainContext::from($context);

        /** @var $domainRoleClass RoleDomain */
        $domainRoleClass = $context->getRoleDomainClass();

        return $domainRoleClass::fromId($domain);
    }
}
