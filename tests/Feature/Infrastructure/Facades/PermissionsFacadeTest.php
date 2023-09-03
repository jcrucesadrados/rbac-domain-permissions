<?php

namespace Getorbit\RbacDomainPermissions\Tests\Infrastructure\Facades;

use Getorbit\RbacDomainPermissions\Domain\Exceptions\InvalidActionForObject;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\ObjectNotFound;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\RoleNotFound;
use Getorbit\RbacDomainPermissions\Domain\Repositories\RolesRepositoryInterface;
use Getorbit\RbacDomainPermissions\Domain\Services\PermissionsCheckerInterface;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\Action;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain\RoleDomain;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleName;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\UserPermissionsList;
use Getorbit\RbacDomainPermissions\Infrastructure\Facades\PermissionsFacade;
use Getorbit\RbacDomainPermissions\Infrastructure\Services\PermissionsChecker;
use Getorbit\RbacDomainPermissions\Tests\Stubs\RoleDomainStub;
use Getorbit\RbacDomainPermissions\Tests\TestCases\DBTestCase;
use Getorbit\RbacDomainPermissions\Tests\Traits\ObjectsSetupTrait;
use Getorbit\RbacDomainPermissions\Tests\Traits\RolesSetupTrait;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;

class PermissionsFacadeTest extends DBTestCase
{
    use ObjectsSetupTrait;
    use RolesSetupTrait;

    private const RULES_TABLE = 'rules';

    /** @test */
    public function createRoleShouldThrowExceptionIObjectIsInvalid(): void
    {
        // given
        $subject = 'RoleName';
        $object = $this->getInvalidPermissionsObject();

        // then
        $this->expectException(ObjectNotFound::class);

        // when
        PermissionsFacade::createRole(
            $subject,
            $object->key,
            $object->allowedActions['perform']->value,
        );
    }

    /** @test */
    public function createRoleShouldCreateANewRoleWithPolicy(): void
    {
        // given
        $this->prepareRolesScenario();

        [$roleName, $object] = $this->prepareScenario();

        // when
        PermissionsFacade::createRole(
            $roleName,
            $object->key,
            $object->allowedActions['perform']->value,
        );

        // then
        $this->assertDatabaseHas(
            self::RULES_TABLE,
            [
                'ptype' => 'p',
                'v0' => $roleName,
            ],
        );
    }

    /** @test */
    public function addPolicyToRoleShouldThrowExceptionIfRoleDoesNotExists(): void
    {
        // given
        $this->prepareRolesScenario();
        [$subject, $object] = $this->prepareScenario();

        // then
        $this->expectException(RoleNotFound::class);

        // when
        PermissionsFacade::addPolicyToRole(
            $subject,
            $object->key,
            $object->allowedActions['perform']->value,
        );
    }

    /** @test */
    public function addPolicyToRoleShouldThrowExceptionIfObjectIsInvalid(): void
    {
        // given
        $this->prepareRolesScenario();
        $subject = 'Role';
        $object = $this->getInvalidPermissionsObject();

        // then
        $this->expectException(ObjectNotFound::class);

        // when
        PermissionsFacade::addPolicyToRole(
            $subject,
            $object->key,
            $object->allowedActions['perform']->value,
        );
    }

    /** @test */
    public function addPolicyToRoleShouldAddPolicy(): void
    {
        // given
        $this->prepareRolesScenario();
        $object = $this->getValidPermissionsObject('thirdObject');
        $subject = 'Role';

        // when
        PermissionsFacade::addPolicyToRole(
            $subject,
            $object->key,
            $object->allowedActions['update']->value,
        );

        // then
        $this->assertDatabaseHasCount(4, 'rules', ['ptype' => 'p', 'v0' => $subject]);
    }

    /** @test */
    public function addRoleForUserInDomainShouldThrowExceptionIfRoleDoesNotExists(): void
    {
        // given
        [, , $user, $domain] = $this->prepareScenario();

        // then
        $this->expectException(RoleNotFound::class);

        // when
        PermissionsFacade::addRoleForUserInDomain(
            $user,
            'NonExistingRole',
            $domain->context()->value,
            $domain->id()->value,
        );
    }

    /** @test */
    public function addRoleForUserInDomainShouldAddRoleForUser(): void
    {
        // given
        [$role, $object, $user, $domain] = $this->prepareScenario();
        PermissionsFacade::createRole($role, $object->key, $object->allowedActions['perform']->value);

        // when
        PermissionsFacade::addRoleForUserInDomain(
            $user,
            $role,
            $domain->context()->value,
            $domain->id()->value,
        );

        // then
        $this->assertDatabaseHas(
            self::RULES_TABLE,
            [
                'ptype' => 'g',
                'v0' => $user,
                'v1' => $role,
                'v2' => $domain->asString(),
            ],
        );
    }

    /** @test */
    public function canShouldThrowExceptionIfActionIsNotValidForObject(): void
    {
        // given
        $this->prepareRolesScenario();
        [, $object, $user, $domain] = $this->prepareScenario();

        PermissionsFacade::addRoleForUserInDomain(
            $user,
            'Role',
            $domain->context()->value,
            $domain->id()->value,
        );

        // then
        $this->expectException(InvalidActionForObject::class);

        // when
        PermissionsFacade::canWithDomain(
            $user,
            $object->key,
            Action::Delete->value,
            $domain->context()->value,
            $domain->id()->value,
        );
    }

    /** @test */
    public function canWithDomainShouldReturnTrueIfUserHasPermissions(): void
    {
        // given
        $this->mockPermissionsChecker(true);
        $this->prepareRolesScenario();
        [, $object, $user, $domain] = $this->prepareScenario();
        PermissionsFacade::addRoleForUserInDomain($user, 'Role', $domain->context()->value, $domain->id());

        // when
        $can = PermissionsFacade::canWithDomain(
            $user,
            $object->key,
            Action::Perform->value,
            $domain->context()->value,
            $domain->id()->value,
        );

        // then
        $this->assertTrue($can);
    }

    /** @test */
    public function canWithDomainShouldReturnFalseIfUserHasNotPermissions(): void
    {
        // given
        $this->mockPermissionsChecker(false);
        $this->prepareRolesScenario();
        [, , $user, $domain] = $this->prepareScenario();
        $object = $this->getValidPermissionsObject('thirdObject');
        PermissionsFacade::addRoleForUserInDomain(
            $user,
            'Role',
            $domain->context()->value,
            $domain->id()->value,
        );

        // when
        $can = PermissionsFacade::canWithDomain(
            $user,
            $object->key,
            Action::Delete->value,
            $domain->context()->value,
            $domain->id()->value,
        );

        // then
        $this->assertFalse($can);
    }

    /** @test */
    public function getUserRoleDomainIdsShouldReturnListOfDomains(): void
    {
        // given
        [$role, $object, $user, $domain] = $this->prepareScenario(RoleDomainStub::fromId(3));
        PermissionsFacade::createRole($role, $object->key, Action::Perform->value);
        PermissionsFacade::addRoleForUserInDomain(
            $user,
            $role,
            $domain->context()->value,
            $domain->id()->value,
        );

        // when
        $domainIds = PermissionsFacade::getUserRoleDomainIds($user, RoleDomainStub::class);

        // then
        $this->assertCount(1, $domainIds);
        $this->assertEquals($domain->getId(), $domainIds[0]);
    }

    /** @test */
    public function getUserShouldReturnUserPermissionsList(): void
    {
        // given
        [$role, $object, $user, $domain] = $this->prepareScenario(RoleDomainStub::fromId(3));
        PermissionsFacade::createRole($role, $object->key, Action::Perform->value);
        PermissionsFacade::addRoleForUserInDomain(
            $user,
            $role,
            $domain->context()->value,
            $domain->id()->value,
        );

        // when
        $response = PermissionsFacade::getUserPermissions($user);

        // then
        $this->assertInstanceOf(UserPermissionsList::class, $response);
    }

    /** @test */
    public function getUserRoleDomainIdsShouldThrowExceptionIfNotRoleDomainClassnameGiven(): void
    {
        // given
        [$role, $object, $user, $domain] = $this->prepareScenario(RoleDomainStub::fromId(3));
        PermissionsFacade::createRole($role, $object->key, Action::Perform->value);
        PermissionsFacade::addRoleForUserInDomain(
            $user,
            $role,
            $domain->context()->value,
            $domain->id()->value,
        );

        // then
        $this->expectException(InvalidArgumentException::class);

        // when
        PermissionsFacade::getUserRoleDomainIds($user, RoleDomain::class);
    }

    /** @test */
    public function removePolicyFromRoleShouldRemovePolicyAndPersistEvent(): void
    {
        // given
        $this->prepareRolesScenario();
        $roleRepository = $this->app->get(RolesRepositoryInterface::class);
        $role = $roleRepository->getFromRoleName(RoleName::fromString('Role'));
        $policy = $role->getPolicies()['firstObject:perform'];

        // when
        PermissionsFacade::removePolicyFromRole('Role', $policy->object->key, $policy->action->value);

        // then
        $this->assertDatabaseMissing(
            self::RULES_TABLE,
            [
                'ptype' => 'p',
                'v0' => $role->getRoleName()->asString(),
                'v1' => $policy->object->key,
                'v2' => $policy->action->value,
            ],
        );
    }

    /** @test */
    public function removeRoleForUserInDomainShouldOnlyRemoveSpecificRole(): void
    {
        // given
        $this->prepareRolesScenario();
        $userId = Str::uuid()->toString();
        $roleRepository = $this->app->get(RolesRepositoryInterface::class);
        $role = $roleRepository->getFromRoleName(RoleName::fromString('Role'));

        PermissionsFacade::addRoleForUserInDomain(
            $userId,
            $role->getRoleName()->asString(),
            RoleDomainStub::context()->value,
            345,
        );

        PermissionsFacade::addRoleForUserInDomain(
            $userId,
            $role->getRoleName()->asString(),
            RoleDomainStub::context()->value,
            874,
        );


        // when
        PermissionsFacade::removeRoleForUserInDomain(
            $userId,
            $role->getRoleName()->asString(),
            RoleDomainStub::context()->value,
            874,
        );

        // then
        $this->assertDatabaseMissing(
            self::RULES_TABLE,
            [
                'ptype' => 'g',
                'v0' => $userId,
                'v1' => $role->getRoleName()->asString(),
                'v2' => (RoleDomainStub::fromId(874))->asString(),
            ],
        );

    }

    /** @test */
    public function deleteRoleShouldRemovePoliciesAndUsersFromRolesAndDeleteRole(): void
    {
        // given
        $roleName = 'testRole';
        $object = $this->getValidPermissionsObject()->key;
        $action = Action::Perform->value;
        $domain = RoleDomainStub::fromId(3);
        $user = Str::uuid()->toString();

        PermissionsFacade::createRole($roleName, $object, $action);
        PermissionsFacade::addRoleForUserInDomain(
            $user,
            $roleName,
            $domain->context()->value,
            $domain->id()->value,
        );

        // when
        PermissionsFacade::deleteRole($roleName);

        // then
        $this->assertDatabaseMissing(
            self::RULES_TABLE,
            [
                'ptype' => 'g',
                'v0' => $user,
                'v1' => $roleName,
                'v2' => $domain->asString(),
            ],
        );

        $this->assertDatabaseMissing(
            self::RULES_TABLE,
            [
                'ptype' => 'p',
                'v0' => $roleName,
            ],
        );
    }

    private function prepareScenario(?RoleDomain $roleDomain = null): array
    {
        $user = Str::uuid()->toString();
        $role = 'RoleName';
        $object = $this->getValidPermissionsObject();
        $domain = $roleDomain ?? RoleDomainStub::fromId(1);

        return [$role, $object, $user, $domain];
    }

    private function mockPermissionsChecker(bool $expectedResponse): void
    {
        $this->instance(
            PermissionsCheckerInterface::class,
            Mockery::mock(
                PermissionsChecker::class,
                function (MockInterface $mock) use ($expectedResponse) {
                    $mock->shouldReceive('canInDomain')
                        ->andReturn($expectedResponse);
                },
            ),
        );
    }
}
