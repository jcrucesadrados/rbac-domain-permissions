<?php

namespace Getorbit\RbacDomainPermissions\Domain\Entities;

use Getorbit\RbacDomainPermissions\Domain\Exceptions\InvalidAllowedAction;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\Action;
use Getorbit\RbacDomainPermissions\Infrastructure\Traits\ArrayValidations;
use InvalidArgumentException;
use ValueError;

class PermissionsObject
{
    use ArrayValidations;
    private function __construct(
        public readonly string $key,
        public readonly string $description,
        public readonly array $allowedActions,
    ) {
    }

    /**
     * @param array $object
     * @return static
     * @throws InvalidArgumentException | InvalidAllowedAction
     */
    public static function fromArray(array $object): self
    {
        self::checkKeysExists(['key', 'description', 'allowedActions'], $object);
        self::checkHasAtLeastOneAllowedAction($object);

        $actions = [];
        foreach ($object['allowedActions'] as $action) {
            try {
                $actions[$action] = Action::from($action);
            } catch (ValueError) {
                throw new InvalidAllowedAction();
            }
        }

        return new self(
            $object['key'],
            $object['description'],
            $actions,
        );
    }

    /**
     * @param array $object
     * @return void
     * @throws InvalidArgumentException
     */
    private static function checkHasAtLeastOneAllowedAction(array $object): void
    {
        if (count($object['allowedActions']) === 0) {
            throw new InvalidArgumentException('Object must have at least one allowed action');
        }
    }

    public function isAnAllowedAction(Action $action): bool
    {
        return $action->equals(Action::All) || array_key_exists($action->value, $this->allowedActions);
    }
}
