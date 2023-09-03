<?php

namespace Getorbit\RbacDomainPermissions\Infrastructure\Repositories;

use Carbon\Carbon;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\UserAlreadyHasRoleForSelectedDomain;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\UserHasNotRoleForDomain;
use Getorbit\RbacDomainPermissions\Domain\Repositories\UserRolesRepositoryInterface;
use Getorbit\RbacDomainPermissions\Domain\Aggregates\Role;
use Getorbit\RbacDomainPermissions\Domain\Entities\User;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain\RoleDomain;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\UserId;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\UserPermission;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\UserPermissionsList;
use Illuminate\Support\Facades\DB;

class UserRolesRepository implements UserRolesRepositoryInterface
{
    private const RULES_TABLE = 'rules';

    /**
     * @throws UserAlreadyHasRoleForSelectedDomain
     */
    public function addRoleForUserInDomain(User $user, Role $role, RoleDomain $domain): void
    {
        $this->guardUserHasNotTheRoleForDomain($user, $role, $domain);

        $this->addRoleToUserInDomainQuery(
            $domain->isAllDomain(),
            $user,
            $role,
            $domain,
        );
    }

    public function getDomainsForUserAndRole(User $user, Role $role): array
    {
        return DB::table(self::RULES_TABLE)
            ->where('ptype', 'like', 'g%')
            ->where('v0', '=', $user->userId->asString())
            ->where('v1', '=', $role->getRoleName()->asString())
            ->pluck('v2')
            ->toArray();
    }

    public function getUsersForRole(Role $role): array
    {
        return DB::table(self::RULES_TABLE)
            ->where('ptype', 'like', 'g%')
            ->where('v1', '=', $role->getRoleName()->asString())
            ->get(['v0 as userId', 'v1 as role', 'v2 as domain'])
            ->toArray();
    }

    /**
     * @throws UserHasNotRoleForDomain
     */
    public function removeUserForRoleInDomain(User $user, Role $role, RoleDomain $domain): void
    {
        $this->guardUserHasTheRoleForDomain($user, $role, $domain);
        DB::table(self::RULES_TABLE)
            ->where('ptype', 'like', 'g%')
            ->where('v0', '=', $user->userId->asString())
            ->where('v1', '=', $role->getRoleName()->asString())
            ->where('v2', '=', $domain->asString())
            ->delete();
    }

    public function getDomainsForUserContext(User $user, string $key, callable $callback): array
    {
        return DB::table(self::RULES_TABLE)
            ->where('ptype', 'like', 'g%')
            ->where('v0', '=', $user->userId)
            ->where('v2', 'like', sprintf('%s%%', $key))
            ->pluck('v2')
            ->map($callback)->toArray();
    }

    public function getPermissionsByUserId(UserId $userId): UserPermissionsList
    {
        $permissions = DB::table('rules', 'r1')
            ->select([
                'r2.v0 as role',
                'r2.v1 as object',
                'r2.v2 as action',
                'r1.v2 as domain',
            ])
            ->join('rules as r2', 'r1.v1', '=', 'r2.v0')
            ->where('r1.ptype', 'like', 'g%')
            ->where('r1.v0', '=', $userId->asString())
            ->orderBy('object')
            ->orderBy('domain')
            ->get();

        return UserPermissionsList::fromUserPermissions(
            ...$permissions->map(
                fn ($item) => UserPermission::fromArray(
                    [
                        'role' => $item->role,
                        'object' => $item->object,
                        'action' => $item->action,
                        'domain' => $item->domain,
                    ],
                ),
            )->toArray(),
        );
    }

    private function checkUserHasRoleForDomain(User $user, Role $role, RoleDomain $domain): bool
    {
        $domains = $this->getDomainsForUserAndRole($user, $role);

        return in_array($domain->asString(), $domains);
    }

    /**
     * @throws UserAlreadyHasRoleForSelectedDomain
     */
    private function guardUserHasNotTheRoleForDomain(User $user, Role $role, RoleDomain $domain): void
    {
        if ($this->checkUserHasRoleForDomain($user, $role, $domain)) {
            throw new UserAlreadyHasRoleForSelectedDomain();
        }
    }

    /**
     * @throws UserHasNotRoleForDomain
     */
    private function guardUserHasTheRoleForDomain(User $user, Role $role, RoleDomain $domain): void
    {
        if (! $this->checkUserHasRoleForDomain($user, $role, $domain)) {
            throw new UserHasNotRoleForDomain();
        }
    }

    private function addRoleToUserInDomainQuery(
        bool $isAdmin,
        User $user,
        Role $role,
        RoleDomain $domain,
    ): void {
        DB::table(self::RULES_TABLE)
            ->insert([
                'ptype' => $isAdmin ? 'g2' : 'g',
                'v0' => $user->userId->asString(),
                'v1' => $role->getRoleName()->asString(),
                'v2' => $domain->asString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
    }
}
