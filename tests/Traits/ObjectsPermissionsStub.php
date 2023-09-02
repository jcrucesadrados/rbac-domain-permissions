<?php

namespace Getorbit\RbacDomainPermissions\Tests\Traits;

use Getorbit\RbacDomainPermissions\Domain\ValueObjects\Action;

class ObjectsPermissionsStub
{
    public static function getPermissionsObjects(): array
    {
        return [
            'firstObject' => [
                'key' => 'firstObject',
                'description' => 'firstObject description',
                'allowedActions' => [
                    Action::Perform->value,
                    Action::Read->value,
                ],
            ],
            'secondObject' => [
                'key' => 'secondObject',
                'description' => 'SecondObject description',
                'allowedActions' => [
                    Action::Create->value,
                    Action::Delete->value,
                ],
            ],
            'thirdObject' => [
                'key' => 'thirdObject',
                'description' => 'thirdObject description',
                'allowedActions' => [
                    Action::Update->value,
                    Action::Delete->value,
                ],
            ],
        ];
    }
}
