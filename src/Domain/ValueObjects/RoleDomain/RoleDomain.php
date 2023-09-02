<?php

namespace Getorbit\RbacDomainPermissions\Domain\ValueObjects\RoleDomain;

use Getorbit\RbacDomainPermissions\Infrastructure\Traits\ArrayValidations;
use InvalidArgumentException;

abstract class RoleDomain implements RoleDomainInterface
{
    use ArrayValidations;

    public const ALL = 'all';

    protected DomainContext $context;

    protected DomainId $id;

    public function __construct(string $context, string $id)
    {
        $this->context = DomainContext::from($context);
        $this->id = DomainId::fromString($id);
    }

    public static function getDomainData(string $domain): array
    {
        return explode(':', $domain);
    }

    public static function getContext(string $domain): DomainContext
    {
        $domainArray = self::getDomainData($domain);
        self::applyStringGuards($domainArray);

        return DomainContext::from($domainArray[0]);
    }

    public static function fromString(string $domain): self
    {
        $domainArray = self::getDomainData($domain);
        self::applyStringGuards($domainArray);

        return static::fromId($domainArray[1]);
    }

    public function isAllDomain(): bool
    {
        return $this->id->isAllDomain();
    }

    private static function applyStringGuards(array $domainArray): void
    {
        self::guardHasExpectedElements($domainArray);
        DomainContext::guardIsValidContext($domainArray[0]);
        DomainId::guardIsValidValueForDomainId($domainArray[1]);
    }

    private static function guardHasExpectedElements(array $domain): void
    {
        if (count($domain) != 2) {
            throw new InvalidArgumentException();
        }
    }

    public function asString(): string
    {
        return sprintf('%s:%s', $this->context->value, $this->id->value);
    }

    public function __toString(): string
    {
        return $this->asString();
    }

    public function id(): DomainId
    {
        return $this->id;
    }

    abstract public static function fromId(string $id): self;

    abstract public static function context(): DomainContext;
}
