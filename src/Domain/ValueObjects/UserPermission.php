<?php

namespace Getorbit\RbacDomainPermissions\Domain\ValueObjects;

use Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain\RoleDomain;
use Getorbit\RbacDomainPermissions\Infrastructure\Traits\ArrayValidations;

final readonly class UserPermission
{
    use ArrayValidations;

    private const REQUIRED_ATTRIBUTES = [
        'role',
        'object',
        'action',
        'domain',
    ];

    private function __construct(
        public string $role,
        public string $object,
        public string $action,
        public RoleDomain $domain,
    ) {
    }

    public static function fromArray(array $userPermission): self
    {
        self::checkKeysExists(self::REQUIRED_ATTRIBUTES, $userPermission);

        /** @var RoleDomain $domainClass */
        $context = RoleDomain::getContext($userPermission['domain']);
        $domainClass = $context->getRoleDomainClass();

        return new self(
            $userPermission['role'],
            $userPermission['object'],
            $userPermission['action'],
            $domainClass::fromString($userPermission['domain']),
        );
    }
}
