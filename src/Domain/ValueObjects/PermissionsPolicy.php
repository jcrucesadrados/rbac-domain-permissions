<?php

namespace Getorbit\RbacDomainPermissions\Domain\ValueObjects;

use Getorbit\RbacDomainPermissions\Domain\Entities\PermissionsObject;
use Getorbit\RbacDomainPermissions\Domain\Exceptions\InvalidActionForObject;

class PermissionsPolicy
{
    private function __construct(
        public readonly PermissionsObject $object,
        public readonly Action $action,
    ) {
    }

    /**
     * @throws InvalidActionForObject
     */
    public static function new(PermissionsObject $object, Action $action): self
    {
        self::isValidActionForObject($object, $action);

        return new self($object, $action);
    }

    public function key(): string
    {
        return sprintf('%s:%s', $this->object->key, $this->action->value);
    }

    /**
     * @throws InvalidActionForObject
     */
    private static function isValidActionForObject(PermissionsObject $object, Action $action): void
    {
        if (! $object->isAnAllowedAction($action)) {
            throw new InvalidActionForObject();
        }
    }
}
