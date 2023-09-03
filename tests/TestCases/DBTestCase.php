<?php

namespace Getorbit\RbacDomainPermissions\Tests\TestCases;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;

use function Orchestra\Testbench\workbench_path;

class DBTestCase extends TestCase
{
    use WithWorkbench;
    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(workbench_path('database/migrations'));
    }
}
