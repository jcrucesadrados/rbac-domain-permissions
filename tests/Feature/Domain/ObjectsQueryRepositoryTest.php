<?php

namespace Getorbit\RbacDomainPermissions\Tests\Feature\Domain;

use Getorbit\RbacDomainPermissions\Domain\Entities\PermissionsObject;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\ObjectNotFound;
use Getorbit\RbacDomainPermissions\Domain\Repositories\ObjectsQueryRepository;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\PermissionsObjectList;
use Getorbit\RbacDomainPermissions\Infrastructure\Repositories\ConstantsObjectsQueryRepository;
use Getorbit\RbacDomainPermissions\Tests\Traits\ObjectsSetupTrait;
use Illuminate\Support\Facades\App;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;

class ObjectsQueryRepositoryTest extends TestCase
{
    use WithWorkbench;
    use ObjectsSetupTrait;

    private ConstantsObjectsQueryRepository $objectsQueryRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->objectsQueryRepository = App::get(ObjectsQueryRepository::class);
    }

    /** @test */
    public function itShouldBeResolved(): void
    {
        $this->assertEquals(
            ConstantsObjectsQueryRepository::class,
            $this->objectsQueryRepository::class,
        );
    }

    /** @test */
    public function itShouldReturnPermissionsList(): void
    {
        // given
        $expected = $this->getValidPermissionsObjectList();

        // when
        $objects = $this->objectsQueryRepository->all();

        // then
        $this->assertInstanceOf(PermissionsObjectList::class, $objects);
        $this->assertEquals($expected->asArray()['firstObject'], $objects->asArray()['firstObject']);
        $this->assertEquals($expected->asArray()['secondObject'], $objects->asArray()['secondObject']);
        $this->assertEquals($expected->asArray()['thirdObject'], $objects->asArray()['thirdObject']);
    }

    /** @test */
    public function getObjectFromKeyShouldThrowExceptionIfObjectDoesNotExists(): void
    {
        $this->expectException(ObjectNotFound::class);
        $this->objectsQueryRepository->getObjectFromKey('nonExistingKey');
    }

    /** @test */
    public function getObjectFromKeyShouldReturnPermissionsObjectIfExists(): void
    {
        // give
        $expected = $this->getValidPermissionsObject();

        // when
        $permissionsObject = $this->objectsQueryRepository->getObjectFromKey('firstObject');

        // then
        $this->assertInstanceOf(PermissionsObject::class, $permissionsObject);
        $this->assertEquals($expected->key, $permissionsObject->key);
        $this->assertEquals($expected->description, $permissionsObject->description);
        $this->assertEquals($expected->allowedActions, $permissionsObject->allowedActions);
    }
}
