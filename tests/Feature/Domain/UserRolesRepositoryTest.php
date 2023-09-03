<?php

namespace Getorbit\RbacDomainPermissions\Tests\Feature\Domain;

use Getorbit\RbacDomainPermissions\Domain\Aggregates\Role;
use Getorbit\RbacDomainPermissions\Domain\Entities\User;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\UserAlreadyHasRoleForSelectedDomain;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\UserHasNotRoleForDomain;
use Getorbit\RbacDomainPermissions\Domain\Repositories\RolesRepositoryInterface;
use Getorbit\RbacDomainPermissions\Domain\Repositories\UserRolesRepositoryInterface;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain\RoleDomain;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleName;
use Getorbit\RbacDomainPermissions\Infrastructure\Repositories\UserRolesRepository;
use Getorbit\RbacDomainPermissions\Tests\Stubs\RoleDomainStub;
use Getorbit\RbacDomainPermissions\Tests\TestCases\DBTestCase;
use Getorbit\RbacDomainPermissions\Tests\Traits\ObjectsSetupTrait;
use Getorbit\RbacDomainPermissions\Tests\Traits\RolesSetupTrait;
use Illuminate\Support\Str;

class UserRolesRepositoryTest extends DBTestCase
{
    use ObjectsSetupTrait;
    use RolesSetupTrait;

    private const RULES_TABLE = 'rules';

    private UserRolesRepositoryInterface $userRolesRepository;
    private Role $role;
    private User $user;
    private RoleDomain $domain;
    private RoleDomain $allDomain;

    public function setUp(): void
    {
        parent::setUp();

        $this->prepareRolesScenario();
        $rolesRepository = $this->app->get(RolesRepositoryInterface::class);

        $this->role = $rolesRepository->getFromRoleName(RoleName::fromString('Role'));
        $this->user = User::fromString(Str::uuid()->toString());
        $this->domain = RoleDomainStub::fromId(1);
        $this->allDomain = RoleDomainStub::fromId('all');
        $this->userRolesRepository = $this->app->get(UserRolesRepositoryInterface::class);
    }

    /** @test */
    public function repositoryShouldBeResolved(): void
    {
        $this->assertInstanceOf(UserRolesRepository::class, $this->userRolesRepository);
    }

    /** @test */
    public function addRoleForUserInDomainShouldAddRoleToUserForTheDomain(): void
    {
        // when
        $this->userRolesRepository->addRoleForUserInDomain($this->user, $this->role, $this->domain);

        // then
        $this->assertDatabaseHas(
            self::RULES_TABLE,
            [
                'ptype' => 'g',
                'v0' => $this->user->userId->asString(),
                'v1' => $this->role->getRoleName()->asString(),
                'v2' => $this->domain->asString(),
            ],
        );
    }

    /** @test */
    public function addRoleForUserInDomainWithAllDomainsShouldAddRoleToUserForTheDomain(): void
    {
        // when
        $this->userRolesRepository->addRoleForUserInDomain($this->user, $this->role, $this->allDomain);

        // then
        $this->assertDatabaseHas(
            self::RULES_TABLE,
            [
                'ptype' => 'g2',
                'v0' => $this->user->userId->asString(),
                'v1' => $this->role->getRoleName()->asString(),
                'v2' => $this->allDomain->asString(),
            ],
        );
    }

    /** @test */
    public function addRoleForUserInDomainShouldThrowExceptionWhenDuplicatingTheOperation(): void
    {
        // then
        $this->expectException(UserAlreadyHasRoleForSelectedDomain::class);

        // when
        $this->userRolesRepository->addRoleForUserInDomain($this->user, $this->role, $this->domain);
        $this->userRolesRepository->addRoleForUserInDomain($this->user, $this->role, $this->domain);
    }

    /** @test */
    public function getDomainsForUserAndRoleShouldReturnProperDomains(): void
    {
        // given
        $this->userRolesRepository->addRoleForUserInDomain($this->user, $this->role, $this->domain);

        // when
        $domains = $this->userRolesRepository->getDomainsForUserAndRole($this->user, $this->role);

        // then
        $this->assertEquals($this->domain->asString(), $domains[0]);
    }

    /** @test */
    public function getUsersForRoleShouldReturnUsers(): void
    {
        // given
        $userIds = [
            Str::uuid()->toString(),
            Str::uuid()->toString(),
        ];

        foreach ($userIds as $userId) {
            $this->userRolesRepository->addRoleForUserInDomain(User::fromString($userId), $this->role, $this->domain);
        }

        $expected = array_map(
            fn ($item) => [
                'userId' => (string)$item,
                'role' => $this->role->getRoleName()->asString(),
                'domain' => $this->domain->asString(),
            ],
            $userIds,
        );

        // when
        $users = $this->userRolesRepository->getUsersForRole($this->role);

        // then
        $this->assertEquals($expected, array_map(fn ($item) => (array)$item, $users));
    }

    /** @test */
    public function removeUserForRoleInDomainShouldThrowExceptionIfUserHasNotTheRole(): void
    {
        // then
        $this->expectException(UserHasNotRoleForDomain::class);

        // when
        $this->userRolesRepository->removeUserForRoleInDomain($this->user, $this->role, $this->domain);
    }

    /** @test */
    public function removeUserForRoleShouldOnlyRemoveExpectedRoleAndDomain(): void
    {
        // given
        $otherDomain = RoleDomainStub::fromId('3');
        $this->userRolesRepository->addRoleForUserInDomain($this->user, $this->role, $this->domain);
        $this->userRolesRepository->addRoleForUserInDomain($this->user, $this->role, $otherDomain);

        // when
        $this->userRolesRepository->removeUserForRoleInDomain($this->user, $this->role, $this->domain);

        // then
        $this->assertDatabaseHas(
            self::RULES_TABLE,
            [
                'ptype' => 'g',
                'v0' => $this->user->userId->asString(),
                'v1' => $this->role->getRoleName()->asString(),
                'v2' => $otherDomain->asString(),
            ],
        );

        $this->assertDatabaseMissing(
            self::RULES_TABLE,
            [
                'ptype' => 'g',
                'v0' => $this->user->userId->asString(),
                'v1' => $this->role->getRoleName()->asString(),
                'v2' => $this->domain->asString(),
            ],
        );
    }
}
