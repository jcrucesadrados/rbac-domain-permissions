<?php

namespace Getorbit\RbacDomainPermissions\Tests\Domain\ValueObjects;

use Getorbit\RbacDomainPermissions\Domain\ValueObjects\UserPermission;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\UserPermissionsList;
use PHPUnit\Framework\TestCase;

class UserPermissionsListTest extends TestCase
{
    /** @test */
    public function itShouldBeConstructedFromArray(): void
    {
        $list = UserPermissionsList::fromUserPermissions(
            UserPermission::fromArray([
                'role' => 'omcUsers',
                'object' => 'login',
                'action' => 'perform',
                'domain' => 'system:1',
            ]),
        );

        $this->assertInstanceOf(UserPermissionsList::class, $list);
    }

    /** @test */
    public function asGroupedArrayShouldReturnGroupedArray(): void
    {
        $list = UserPermissionsList::fromUserPermissions(
            UserPermission::fromArray([
                'role' => 'omcUsers',
                'object' => 'login',
                'action' => 'perform',
                'domain' => 'system:1',
            ]),
            UserPermission::fromArray([
                'role' => 'locationManager',
                'object' => 'insight',
                'action' => 'read',
                'domain' => 'system:4',
            ]),
            UserPermission::fromArray([
                'role' => 'locationManager',
                'object' => 'insight',
                'action' => 'update',
                'domain' => 'system:4',
            ]),
            UserPermission::fromArray([
                'role' => 'locationManager',
                'object' => 'insight',
                'action' => 'update',
                'domain' => 'system:5',
            ]),
        );

        $data = $list->asGroupedArray();
        $this->assertCount(1, $data['login']['items']);
        $this->assertSame(
            ['read', 'update'],
            $data['insight']['items']['system:4']['actions'],
        );
    }
}
