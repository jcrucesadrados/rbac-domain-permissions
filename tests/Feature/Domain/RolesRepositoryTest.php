<?php

namespace Getorbit\RbacDomainPermissions\Tests\Feature\Domain;

use Getorbit\RbacDomainPermissions\Domain\Aggregates\Role;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\RoleAlreadyExists;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\RoleMustHaveAtLeastOnePolicy;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\RoleNotFound;
use Getorbit\RbacDomainPermissions\Domain\Repositories\RolesRepositoryInterface;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\Action;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\PermissionsPolicy;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleName;
use Getorbit\RbacDomainPermissions\Infrastructure\Repositories\RolesRepository;
use Getorbit\RbacDomainPermissions\Tests\TestCases\DBTestCase;
use Getorbit\RbacDomainPermissions\Tests\Traits\ObjectsSetupTrait;
use Getorbit\RbacDomainPermissions\Tests\Traits\RolesSetupTrait;
use Orchestra\Testbench\Concerns\WithWorkbench;

class RolesRepositoryTest extends DBTestCase
{
    use WithWorkbench;
    use ObjectsSetupTrait;
    use RolesSetupTrait;

    private RolesRepository $rolesRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->rolesRepository = $this->app->get(RolesRepositoryInterface::class);
    }

    /** @test */
    public function itShouldBeResolved(): void
    {
        $this->assertEquals(
            RolesRepository::class,
            $this->rolesRepository::class,
        );
    }

    /** @test */
    public function getFromRoleNameShouldBeRetrievedFromTheDatabase(): void
    {
        // given
        $this->prepareRolesScenario();

        // when
        $role = $this->rolesRepository->getFromRoleName(RoleName::fromString('Role'));

        // then
        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals('Role', $role->getRoleName()->asString());
        $this->assertCount(3, $role->getPolicies());
        $this->assertArrayHasKey('firstObject:perform', $role->getPolicies());
        $this->assertArrayHasKey('firstObject:read', $role->getPolicies());
        $this->assertArrayHasKey('secondObject:create', $role->getPolicies());
    }

    /** @test */
    public function getFromRoleNameShouldThrowExceptionIfRoleDoesNotExists(): void
    {
        // given
        $this->prepareRolesScenario();

        // then
        $this->expectException(RoleNotFound::class);

        // when
        $this->rolesRepository->getFromRoleName(RoleName::fromString('NotExistingOne'));
    }

    /** @test */
    public function createShouldPersistRoleWithPolicies(): void
    {
        // given
        $firstPolicy = PermissionsPolicy::new($this->getValidPermissionsObject(), Action::Perform);
        $secondPolicy = PermissionsPolicy::new($this->getValidPermissionsObject(), Action::Read);
        $roleName = RoleName::fromString('newRoleName');
        $role = Role::new($roleName, $firstPolicy, $secondPolicy);

        // when
        $this->rolesRepository->create($role);

        // then
        $this->assertPolicyInDatabase($roleName, $firstPolicy);
        $this->assertPolicyInDatabase($roleName, $secondPolicy);
    }

    /** @test */
    public function createShouldThrowExceptionIfRoleAlreadyExists(): void
    {
        // given
        $this->prepareRolesScenario();
        $firstPolicy = PermissionsPolicy::new($this->getValidPermissionsObject(), Action::Perform);
        $roleName = RoleName::fromString('Role');

        $role = Role::new($roleName, $firstPolicy);

        // then
        $this->expectException(RoleAlreadyExists::class);

        // when
        $this->rolesRepository->create($role);
    }

    /** @test */
    public function createShouldThrowExceptionIfRoleHasNoPolicies(): void
    {
        // given
        $firstPolicy = PermissionsPolicy::new($this->getValidPermissionsObject(), Action::Perform);
        $roleName = RoleName::fromString('Role');

        $role = Role::new($roleName, $firstPolicy);
        $role->removePolicy($firstPolicy);

        $this->expectException(RoleMustHaveAtLeastOnePolicy::class);

        // when
        $this->rolesRepository->create($role);

        // then
        $this->assertDatabaseMissing('rules', ['v0' => 'Role']);
    }

    /** @test */
    public function updateShouldThrowExceptionIfRoleDoesNotExists(): void
    {
        // given
        $this->prepareRolesScenario();
        $roleName = RoleName::fromString('NoExistingRole');
        $policy = PermissionsPolicy::new(
            $this->getValidPermissionsObject('secondObject'),
            Action::Delete,
        );

        $role = Role::new($roleName, $policy);

        // then
        $this->expectException(RoleNotFound::class);

        // when
        $this->rolesRepository->update($role);
    }

    public function assertPolicyInDatabase(RoleName $roleName, PermissionsPolicy $policy): void
    {
        $this->assertDatabaseHas('rules', [
            'ptype' => 'p',
            'v0' => $roleName->asString(),
            'v1' => $policy->object->key,
            'v2' => $policy->action->value,
        ]);
    }

    public function assertMissingPolicyInDatabase(RoleName $roleName, PermissionsPolicy $policy): void
    {
        $this->assertDatabaseMissing('rules', [
            'ptype' => 'p',
            'v0' => $roleName->asString(),
            'v1' => $policy->object->key,
            'v2' => $policy->action->value,
        ]);
    }
}
