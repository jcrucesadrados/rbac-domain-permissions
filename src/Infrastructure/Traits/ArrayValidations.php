<?php

namespace Getorbit\RbacDomainPermissions\Infrastructure\Traits;

use InvalidArgumentException;

trait ArrayValidations
{
    private static function checkKeysExists(array $expectedKeys, array $data): void
    {
        foreach ($expectedKeys as $key) {
            if (! array_key_exists($key, $data)) {
                throw new InvalidArgumentException(sprintf('%s is required', $key));
            }
        }
    }

    private static function checkKeysExistsInChild(array $expectedKeys, array $data): void
    {
        foreach ($data as $child) {
            self::checkKeysExists($expectedKeys, $child);
        }
    }
}
