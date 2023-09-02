<?php

namespace Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain;

use InvalidArgumentException;

enum DomainContext: string
{
    case system = 'system';

    public function getRoleDomainClass(): string
    {
        return match ($this) {
            DomainContext::system => SystemRoleDomain::class,
        };
    }

    public static function guardIsValidContext(string $context): void
    {
        $cases = array_map(
            fn (DomainContext $case) => $case->value,
            DomainContext::cases(),
        );

        if (! in_array($context, $cases)) {
            throw new InvalidArgumentException('Invalid context value');
        }
    }
}
