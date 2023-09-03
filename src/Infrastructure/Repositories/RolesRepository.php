<?php

namespace Getorbit\RbacDomainPermissions\Infrastructure\Repositories;

use Carbon\Carbon;
use Getorbit\RbacDomainPermissions\Domain\Aggregates\Role;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\InvalidActionForObject;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\ObjectNotFound;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\RoleAlreadyExists;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\RoleMustHaveAtLeastOnePolicy;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\RoleNotFound;
use Getorbit\RbacDomainPermissions\Domain\Repositories\ObjectsQueryRepository;
use Getorbit\RbacDomainPermissions\Domain\Repositories\RolesRepositoryInterface;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\Action;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\PermissionsPolicy;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleName;
use Illuminate\Support\Facades\DB;
use Throwable;

class RolesRepository implements RolesRepositoryInterface
{
    private const RULES_TABLE = 'rules';

    public function __construct(private ObjectsQueryRepository $objectsQueryRepository)
    {
    }

    /**
     * @throws RoleNotFound|ObjectNotFound|InvalidActionForObject
     */
    public function getFromRoleName(RoleName $roleName): Role
    {
        $this->guardRoleExists($roleName);

        return Role::new(
            $roleName,
            ...array_map(
                function ($policy) {
                    return PermissionsPolicy::new(
                        $this->objectsQueryRepository->getObjectFromKey($policy->v1),
                        Action::from($policy->v2),
                    );
                },
                $this->queryPoliciesFromRole($roleName),
            ),
        );
    }

    /**
     * @throws Throwable|RoleAlreadyExists|RoleMustHaveAtLeastOnePolicy
     */
    public function create(Role $roleAggregate): void
    {
        $this->guardRoleDoesNotExists($roleAggregate->getRoleName());
        $this->guardRoleHasAtLeastOnePolicy($roleAggregate);

        DB::beginTransaction();

        try {
            $this->persistAddPolicies($roleAggregate->getRoleName(), $roleAggregate->getPolicies());

            DB::commit();
            $roleAggregate->applyPersistedMovements();
        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * @throws Throwable|RoleNotFound
     */
    public function update(Role $roleAggregate): void
    {
        $this->guardRoleExists($roleAggregate->getRoleName());

        DB::beginTransaction();
        $movements = $roleAggregate->getPolicyMovements();

        try {
            if ($movements['add'] !== []) {
                $this->persistAddPolicies($roleAggregate->getRoleName(), $movements['add']);
            }

            if ($movements['remove'] !== []) {
                $this->persistRemovePolicies($roleAggregate->getRoleName(), $movements['remove']);
            }

            DB::commit();
            $roleAggregate->applyPersistedMovements();
        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    private function queryPoliciesFromRole(RoleName $roleName): array
    {
        return DB::table(self::RULES_TABLE)
            ->where('ptype', '=', 'p')
            ->where('v0', '=', $roleName->asString())
            ->get(['v1', 'v2'])
            ->toArray();
    }

    private function persistAddPolicies(RoleName $roleName, array $policies): void
    {
        DB::table(self::RULES_TABLE)
            ->insert(
                array_map(
                    fn ($policy) => [
                        'ptype' => 'p',
                        'v0' => $roleName->asString(),
                        'v1' => $policy->object->key,
                        'v2' => $policy->action->value,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ],
                    $policies,
                ),
            );
    }

    private function persistRemovePolicies(RoleName $roleName, array $policies): void
    {
        if (empty($policies)) {
            return;
        }

        foreach ($policies as $policy) {
            DB::table(self::RULES_TABLE)
                ->where('ptype', '=', 'p')
                ->where('v0', '=', $roleName->asString())
                ->where('v1', '=', $policy->object->key)
                ->where('v2', '=', $policy->action->value)
                ->delete();
        }
    }

    /**
     * @throws RoleMustHaveAtLeastOnePolicy
     */
    private function guardRoleHasAtLeastOnePolicy(Role $roleAggregate): void
    {
        if (empty($roleAggregate->getPolicies())) {
            throw new RoleMustHaveAtLeastOnePolicy();
        }
    }

    /**
     * @throws RoleAlreadyExists
     */
    private function guardRoleDoesNotExists(RoleName $roleName): void
    {
        if ($this->doesRoleExists($roleName)) {
            throw new RoleAlreadyExists();
        }
    }

    /**
     * @throws RoleNotFound
     */
    private function guardRoleExists(RoleName $roleName): void
    {
        if (! $this->doesRoleExists($roleName)) {
            throw new RoleNotFound();
        }
    }

    private function doesRoleExists(RoleName $roleName): bool
    {
        return 0 !== count($this->queryPoliciesFromRole($roleName));
    }
}
