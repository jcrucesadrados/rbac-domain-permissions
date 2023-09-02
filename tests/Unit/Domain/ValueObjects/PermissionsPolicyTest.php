<?php

namespace Getorbit\RbacDomainPermissions\Tests\Domain\ValueObjects;

use Getorbit\RbacDomainPermissions\Domain\Exceptions\InvalidActionForObject;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\Action;
use Getorbit\RbacDomainPermissions\Domain\Entities\PermissionsObject;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\PermissionsPolicy;
use PHPUnit\Framework\TestCase;

class PermissionsPolicyTest extends TestCase
{
    private const PERMISSION_OBJECT_KEY_VALUE = 'key';

    private PermissionsObject $permissionsObject;
    private Action $validAction;
    private PermissionsPolicy $permissionsPolicy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validAction = Action::Create;
        $this->permissionsObject = PermissionsObject::fromArray([
            'key' => self::PERMISSION_OBJECT_KEY_VALUE,
            'description' => 'description',
            'allowedActions' => [$this->validAction->value],
        ]);

        $this->permissionsPolicy = PermissionsPolicy::new($this->permissionsObject, $this->validAction);
    }

    /** @test */
    public function newShouldFailIfActionIsNotValid(): void
    {
        // given
        $invalidAction = Action::Update;

        // then
        $this->expectException(InvalidActionForObject::class);

        // when
        PermissionsPolicy::new($this->permissionsObject, $invalidAction);
    }

    /** @test */
    public function newShouldCreateNewPermissionsPolicy(): void
    {
        // then
        $this->assertInstanceOf(PermissionsPolicy::class, $this->permissionsPolicy);

        $this->assertEquals($this->permissionsObject, $this->permissionsPolicy->object);
        $this->assertEquals($this->validAction, $this->permissionsPolicy->action);
    }

    /** @test */
    public function keyShouldReturnPermissionsPolicyKey(): void
    {
        // given
        $expectedKey = sprintf('%s:%s', self::PERMISSION_OBJECT_KEY_VALUE, $this->validAction->value);

        // when
        $key = $this->permissionsPolicy->key();

        // then
        $this->assertEquals($expectedKey, $key);
    }
}
