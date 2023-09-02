<?php

namespace Getorbit\RbacDomainPermissions\Tests\Domain\ValueObjects;

use Getorbit\RbacDomainPermissions\Domain\Entities\PermissionsObject;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\ObjectNotFound;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\PermissionsObjectList;
use Getorbit\RbacDomainPermissions\Tests\Traits\ObjectsSetupTrait;
use PHPUnit\Framework\TestCase;

class PermissionsObjectListTest extends TestCase
{
    use ObjectsSetupTrait;

    /** @test */
    public function itShouldBeConstructedFromArray(): void
    {
        $rawObjects = $this->getRawObjects();
        $list = PermissionsObjectList::fromArray($rawObjects);

        $this->assertInstanceOf(PermissionsObjectList::class, $list);
    }

    /** @test */
    public function asArrayShouldReturnArrayOfPermissionsObjects(): void
    {
        // given
        $rawObjects = $this->getRawObjects();
        $expected = array_map(fn ($object) => PermissionsObject::fromArray($object), $rawObjects);
        $list = $this->getValidPermissionsObjectList();

        // then
        $this->assertEquals($expected, $list->asArray());
    }

    /**
     * @test
     * @dataProvider provideKeysAndExpectationsForExists
     */
    public function existsShouldReturnExpectedBoolean(string $key, bool $expected): void
    {
        // given
        $list = $this->getValidPermissionsObjectList();

        // then
        $this->assertEquals($expected, $list->exists($key));
    }

    /** @test */
    public function getPermissionsObjectShouldThrowExceptionIfKeyDoesNotExists(): void
    {
        // given
        $list = $this->getValidPermissionsObjectList();

        // then
        $this->expectException(ObjectNotFound::class);

        // when
        $list->getPermissionsObject('nonExistingKey');
    }

    /** @test */
    public function getPermissionsObjectShouldReturnPermissionsObject(): void
    {
        // given
        $list = $this->getValidPermissionsObjectList();
        $expected = $this->getValidPermissionsObject();

        // when
        $permissionsObject = $list->getPermissionsObject($expected->key);

        // then
        $this->assertEquals($expected->key, $permissionsObject->key);
        $this->assertEquals($expected->description, $permissionsObject->description);
        $this->assertEquals($expected->allowedActions, $permissionsObject->allowedActions);
    }

    public static function provideKeysAndExpectationsForExists(): array
    {
        return [
            'with existing key' => ['firstObject', true],
            'with non existing key' => ['OtherObject', false],
        ];
    }
}
