<?php

namespace Getorbit\RbacDomainPermissions\Infrastructure;

use Getorbit\RbacDomainPermissions\Domain\Repositories\ObjectsQueryRepository;
use Getorbit\RbacDomainPermissions\Domain\Repositories\RolesRepositoryInterface;
use Getorbit\RbacDomainPermissions\Domain\Repositories\UserRolesRepositoryInterface;
use Getorbit\RbacDomainPermissions\Domain\Services\PermissionsCheckerInterface;
use Getorbit\RbacDomainPermissions\Infrastructure\Repositories\ConstantsObjectsQueryRepository;
use Getorbit\RbacDomainPermissions\Infrastructure\Repositories\RolesRepository;
use Getorbit\RbacDomainPermissions\Infrastructure\Repositories\UserRolesRepository;
use Getorbit\RbacDomainPermissions\Infrastructure\Services\PermissionsChecker;
use Getorbit\RbacDomainPermissions\Infrastructure\Services\PermissionsService;
use Getorbit\RbacDomainPermissions\Tests\Traits\ObjectsPermissionsStub;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class RbacDomainPermissionsServiceProvider extends ServiceProvider
{
    public array $bindings = [
        ObjectsQueryRepository::class => ConstantsObjectsQueryRepository::class,
        PermissionsCheckerInterface::class => PermissionsChecker::class,
        RolesRepositoryInterface::class => RolesRepository::class,
        UserRolesRepositoryInterface::class => UserRolesRepository::class,
    ];

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/lauthz.php' => config_path('lauthz.php'),
            __DIR__.'/../../config/lauthz-rbac-model.conf' => config_path('lauthz-rbac-model.conf'),
            __DIR__.'/../../config/rbacDomainPermissions.php' => config_path('rbacDomainPermissions.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }

    public function register(): void
    {
        $this->app->when(ConstantsObjectsQueryRepository::class)
            ->needs('$objectsClasses')
            ->give($this->getObjects());

        $this->app->bind('Permissions', function () {
            return PermissionsService::make(
                resolve(ObjectsQueryRepository::class),
                resolve(PermissionsCheckerInterface::class),
                resolve(RolesRepositoryInterface::class),
                resolve(UserRolesRepositoryInterface::class),
            );
        });
    }

    private function getObjects(): array
    {
        $objectClasses = Config::get('rbacDomainPermissions.objects');

        if ($this->app->environment('testing')) {
            $objectClasses = array_merge(
                is_null($objectClasses) ? [] : $objectClasses,
                [ObjectsPermissionsStub::class],
            );
        }

        return is_null($objectClasses) ? [] : $objectClasses;
    }
}
