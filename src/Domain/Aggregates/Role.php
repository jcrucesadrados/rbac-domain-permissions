<?php

declare(strict_types=1);

namespace Getorbit\RbacDomainPermissions\Domain\Aggregates;

use Getorbit\RbacDomainPermissions\Domain\Exceptions\PolicyAlreadyExistsForRole;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\PermissionsPolicy;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleName;

class Role
{
    private RoleName $roleName;

    /**
     * @var PermissionsPolicy[]
     */
    private array $policies = [];

    private array $policiesMovements = [];

    private function __construct(RoleName $roleName, PermissionsPolicy ...$policies)
    {
        $this->roleName = $roleName;

        foreach ($policies as $policy) {
            $this->policies[$policy->key()] = $policy;
        }
    }

    public static function new(RoleName $roleName, PermissionsPolicy ...$policies): self
    {
        return new self($roleName, ...$policies);
    }

    public function getRoleName(): RoleName
    {
        return $this->roleName;
    }

    /**
     * @return PermissionsPolicy[]
     */
    public function getPolicies(): array
    {
        return $this->policies;
    }

    public function addPolicy(PermissionsPolicy $permissionsPolicy): self
    {
        $this->guardPolicyDoesNotExist($permissionsPolicy);

        if (isset($this->policiesMovements['remove'][$permissionsPolicy->key()])) {
            unset($this->policiesMovements['remove'][$permissionsPolicy->key()]);
        } else {
            $this->policiesMovements['add'][$permissionsPolicy->key()] = $permissionsPolicy;
        }

        $this->policies[$permissionsPolicy->key()] = $permissionsPolicy;

        return $this;
    }

    public function removePolicy(PermissionsPolicy $permissionsPolicy): self
    {
        if (isset($this->policiesMovements['add'][$permissionsPolicy->key()])) {
            unset($this->policiesMovements['add'][$permissionsPolicy->key()]);
        } else {
            $this->policiesMovements['remove'][$permissionsPolicy->key()] = $permissionsPolicy;
        }

        unset($this->policies[$permissionsPolicy->key()]);

        return $this;
    }

    public function getPolicyMovements(): array
    {
        return [
            'add' => $this->policiesMovements['add'] ?? [],
            'remove' => $this->policiesMovements['remove'] ?? [],
        ];
    }

    public function applyPersistedMovements(): void
    {
        $this->policiesMovements = [];
    }

    /**
     * @param PermissionsPolicy $permissionsPolicy
     * @throws PolicyAlreadyExistsForRole
     */
    private function guardPolicyDoesNotExist(PermissionsPolicy $permissionsPolicy): void
    {
        if (isset($this->policies[$permissionsPolicy->key()])) {
            throw new PolicyAlreadyExistsForRole();
        }
    }
}
