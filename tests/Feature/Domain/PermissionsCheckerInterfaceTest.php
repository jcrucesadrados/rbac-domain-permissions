<?php

use Getorbit\RbacDomainPermissions\Domain\Entities\User;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\InvalidActionForObject;
use Getorbit\RbacDomainPermissions\Domain\Services\PermissionsCheckerInterface;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\Action;
use Getorbit\RbacDomainPermissions\Infrastructure\Services\PermissionsChecker;
use Getorbit\RbacDomainPermissions\Tests\Stubs\RoleDomainStub;
use Getorbit\RbacDomainPermissions\Tests\Traits\ObjectsSetupTrait;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;

class PermissionsCheckerInterfaceTest extends TestCase
{
    use WithWorkbench;
    use ObjectsSetupTrait;

    private PermissionsChecker $permissionsChecker;

    public function setUp(): void
    {
        parent::setUp();

        $this->permissionsChecker = App::get(PermissionsCheckerInterface::class);
    }

    /** @test */
    public function itShouldBeResolved(): void
    {
        // then
        $this->assertEquals(
            PermissionsChecker::class,
            $this->permissionsChecker::class,
        );
    }

    /** @test */
    public function canInDomainShouldFailIfActionIsNotAllowedForAction(): void
    {
        // given
        $permissionsObject = $this->getValidPermissionsObject();
        $user = User::fromString(Str::uuid()->toString());
        $roleDomain = RoleDomainStub::fromString('system:all');
        $action = Action::Delete;

        // then
        $this->expectException(InvalidActionForObject::class);

        // when
        $this->permissionsChecker->canInDomain($user, $permissionsObject, $action, $roleDomain);
    }
}
