<?php

namespace Getorbit\RbacDomainPermissions\Domain\ValueObjects;

final readonly class UserPermissionsList
{
    public array $permissionItems;

    private function __construct(UserPermission ...$userPermissions)
    {
        $this->permissionItems = $userPermissions;
    }

    public static function fromUserPermissions(UserPermission ...$userPermission): self
    {
        return new self(...$userPermission);
    }

    /**
     * @return array
     */
    public function asGroupedArray(): array
    {
        $response = [];
        /** @var UserPermission $permissionObject */
        foreach ($this->permissionItems as $permissionObject) {
            $response[$permissionObject->object] = isset($response[$permissionObject->object])
                ? $this->mergePermission(
                    $response[$permissionObject->object],
                    $permissionObject,
                )
                : $this->getPermission($permissionObject);
        }

        return $response;
    }

    /**
     * @param UserPermission $permission
     * @return array
     */
    private function getPermission(UserPermission $permission): array
    {
        return [
            'name' => $permission->object,
            'items' => [
                $permission->domain->asString() => $this->getDomain($permission),
            ],
        ];
    }

    /**
     * @param UserPermission $permission
     * @return array
     */
    private function getDomain(UserPermission $permission): array
    {
        return [
            'domain' => $permission->domain->id()->value,
            'context' => $permission->domain->context()->value,
            'actions' => [
                $permission->action,
            ],
        ];
    }

    /**
     * @param array $existingPermission
     * @param UserPermission $permission
     * @return array
     */
    private function mergePermission(
        array $existingPermission,
        UserPermission $permission,
    ): array {
        $domain = $permission->domain->asString();
        if (isset($existingPermission['items'][$domain]['actions'])) {
            $existingPermission['items'][$domain]['actions'][] = $permission->action;

            return $existingPermission;
        }

        $existingPermission['items'][$domain] = $this->getDomain($permission);

        return $existingPermission;
    }
}
