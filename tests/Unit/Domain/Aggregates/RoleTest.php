<?php

namespace Getorbit\RbacDomainPermissions\Tests\Domain\Aggregates;

use Getorbit\RbacDomainPermissions\Domain\Aggregates\Role;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\PolicyAlreadyExistsForRole;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\Action;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\PermissionsPolicy;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleName;
use Getorbit\RbacDomainPermissions\Tests\Traits\ObjectsSetupTrait;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    use ObjectsSetupTrait;

    private RoleName $roleName;
    private PermissionsPolicy $policy;

    private Role $role;

    public function setUp(): void
    {
        parent::setUp();

        $this->policy = PermissionsPolicy::new($this->getValidPermissionsObject(), Action::Perform);
        $this->roleName = RoleName::fromString('RoleName');

        $this->role = Role::new($this->roleName, $this->policy);
    }

    /** @test */
    public function newShouldCreateRoleWithPolicies(): void
    {
        // then
        $this->assertInstanceOf(Role::class, $this->role);
    }

    /** @test */
    public function getRoleNameShouldReturnRoleName(): void
    {
        // then
        $this->assertInstanceOf(RoleName::class, $this->role->getRoleName());
        $this->assertEquals($this->roleName->asString(), $this->role->getRoleName()->asString());
    }

    /** @test */
    public function getPoliciesShouldReturnArrayOfPermissionsPolicies(): void
    {
        foreach ($this->role->getPolicies() as $policy) {
            $this->assertInstanceOf(PermissionsPolicy::class, $policy);
            $this->assertEquals($this->policy->key(), $policy->key());
        }
    }

    /** @test */
    public function getMovementsShouldReturnExpectedStructure(): void
    {
        // when
        $movements = $this->role->getPolicyMovements();

        // then
        $this->assertArrayHasKey('add', $movements);
        $this->assertArrayHasKey('remove', $movements);

        $this->assertEquals([], $movements['add']);
        $this->assertEquals([], $movements['remove']);
    }

    /** @test */
    public function addPolicyShouldAddItToMovementsAddIfDidNotExist(): void
    {
        // given
        $newPolicy = PermissionsPolicy::new(
            $this->getValidPermissionsObject('secondObject'),
            Action::Create,
        );

        // when
        $this->role->addPolicy($newPolicy);

        // then
        $this->assertArrayHasKey($newPolicy->key(), $this->role->getPolicyMovements()['add']);
        $this->assertArrayHasKey($newPolicy->key(), $this->role->getPolicies());
    }

    /** @test */
    public function removePolicyShouldAddItToMovementsRemoveIfDidAlreadyExists(): void
    {
        // given
        $removedPolicy = PermissionsPolicy::new($this->getValidPermissionsObject(), Action::Perform);

        // when
        $this->role->removePolicy($removedPolicy);

        // then
        $this->assertArrayHasKey($removedPolicy->key(), $this->role->getPolicyMovements()['remove']);
        $this->assertArrayNotHasKey($removedPolicy->key(), $this->role->getPolicies());
    }

    /** @test */
    public function addPolicyShouldRemoveItFromMovementsRemove(): void
    {
        // given
        $policy = PermissionsPolicy::new($this->getValidPermissionsObject(), Action::Perform);
        $this->role->removePolicy($policy);

        // when
        $this->role->addPolicy($policy);

        // then
        $this->assertArrayNotHasKey($policy->key(), $this->role->getPolicyMovements()['add']);
        $this->assertArrayNotHasKey($policy->key(), $this->role->getPolicyMovements()['remove']);
        $this->assertArrayHasKey($policy->key(), $this->role->getPolicies());
    }

    /** @test */
    public function addPolicyShouldThrowExceptionIfAlreadyExist(): void
    {
        // given
        $policy = PermissionsPolicy::new($this->getValidPermissionsObject(), Action::Perform);

        // then
        $this->expectException(PolicyAlreadyExistsForRole::class);

        // when
        $this->role->addPolicy($policy);
    }

    /** @test */
    public function removePolicyShouldRemoveItFromMovementsAdd(): void
    {
        // given
        $policy = PermissionsPolicy::new($this->getValidPermissionsObject('secondObject'), Action::Create);
        $this->role->addPolicy($policy);

        // when
        $this->role->removePolicy($policy);

        // then
        $this->assertArrayNotHasKey($policy->key(), $this->role->getPolicyMovements()['add']);
        $this->assertArrayNotHasKey($policy->key(), $this->role->getPolicyMovements()['remove']);
        $this->assertArrayNotHasKey($policy->key(), $this->role->getPolicies());
    }

    /** @test */
    public function applyPersistedMovementsShouldClearAllMovements(): void
    {
        // given
        $policyToAdd = PermissionsPolicy::new(
            $this->getValidPermissionsObject('secondObject'),
            Action::Create,
        );
        $policyToRemove = PermissionsPolicy::new(
            $this->getValidPermissionsObject(),
            Action::Perform,
        );


        // when
        $this->role->addPolicy($policyToAdd);
        $this->role->removePolicy($policyToRemove);
        $this->role->applyPersistedMovements();

        // then
        $this->assertEquals([], $this->role->getPolicyMovements()['add']);
        $this->assertEquals([], $this->role->getPolicyMovements()['remove']);
    }
}
