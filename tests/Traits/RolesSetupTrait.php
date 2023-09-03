<?php

namespace Getorbit\RbacDomainPermissions\Tests\Traits;

use Getorbit\RbacDomainPermissions\Domain\ValueObjects\Action;
use Illuminate\Support\Facades\DB;

trait RolesSetupTrait
{
    public function prepareRolesScenario(): void
    {
        DB::table('rules')
            ->insert([
                [
                    'ptype' => 'p',
                    'v0' => 'Role',
                    'v1' => $this->getValidPermissionsObject()->key,
                    'v2' => Action::Perform->value,
                ],
                [
                    'ptype' => 'p',
                    'v0' => 'Role',
                    'v1' => $this->getValidPermissionsObject()->key,
                    'v2' => Action::Read->value,
                ],
                [
                    'ptype' => 'p',
                    'v0' => 'Role',
                    'v1' => $this->getValidPermissionsObject('secondObject')->key,
                    'v2' => Action::Create->value,
                ],
                [
                    'ptype' => 'p',
                    'v0' => 'OtherRole',
                    'v1' => $this->getValidPermissionsObject('thirdObject')->key,
                    'v2' => Action::Update->value,
                ],
            ]);
    }
}
