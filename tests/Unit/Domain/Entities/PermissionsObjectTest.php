<?php

namespace Getorbit\RbacDomainPermissions\Tests\Unit\Domain\Entities;

use Getorbit\RbacDomainPermissions\Domain\Exceptions\InvalidAllowedAction;
use Getorbit\RbacDomainPermissions\Domain\ValueObjects\Action;
use Getorbit\RbacDomainPermissions\Domain\Entities\PermissionsObject;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PermissionsObjectTest extends TestCase
{
    private array $permissionsData;
    private PermissionsObject $permissionsObject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->permissionsData = [
            'key' => 'key',
            'description' => 'description',
            'allowedActions' => ['create' => Action::Create->value],
        ];

        $this->permissionsObject = PermissionsObject::fromArray($this->permissionsData);
    }

    /**
     * @test
     * @dataProvider provideMissingFieldsCases
     */
    public function fromArrayShouldThrowErrorWhenMissingFields(array $data): void
    {
        // then
        $this->expectException(InvalidArgumentException::class);

        // when
        PermissionsObject::fromArray($data);
    }

    public static function provideMissingFieldsCases(): array
    {
        return [
            'Missing key' => [
                'data' => [
                    'description' => 'description',
                    'allowedActions' => ['allowedAction'],
                ],
            ],
            'Missing description' => [
                'data' => [
                    'key' => 'key',
                    'allowedActions' => ['allowedAction'],
                ],
            ],
            'Missing allowedActions' => [
                'data' => [
                    'key' => 'key',
                    'description' => 'description',
                ],
            ],
        ];
    }

    /** @test */
    public function fromArrayShouldCheckThatDataContainsAtLeastOneAllowedAction(): void
    {
        // given
        $data = [
            'key' => 'key',
            'description' => 'description',
            'allowedActions' => [],
        ];

        // then
        $this->expectException(InvalidArgumentException::class);

        // when
        PermissionsObject::fromArray($data);
    }

    /** @test */
    public function fromArrayShouldFailIfAnAllowedActionIsNotValidAction(): void
    {
        // given
        $data = [
            'key' => 'key',
            'description' => 'description',
            'allowedActions' => ['speak'],
        ];

        // then
        $this->expectException(InvalidAllowedAction::class);

        // when
        PermissionsObject::fromArray($data);
    }

    /** @test */
    public function itShouldBeCreatedFromArray(): void
    {
        // then
        $this->assertInstanceOf(PermissionsObject::class, $this->permissionsObject);

        $this->assertEquals($this->permissionsData['key'], $this->permissionsObject->key);
        $this->assertEquals($this->permissionsData['description'], $this->permissionsObject->description);
        $this->assertEquals(
            $this->permissionsData['allowedActions'],
            array_map(
                fn (Action $action) => $action->value,
                $this->permissionsObject->allowedActions,
            ),
        );
    }

    /**
     * @test
     * @dataProvider provideActionsForAllowedActions
     */
    public function isAnAllowedActionShouldCheckActionIsAllowedForPermissionsObject(
        Action $action,
        bool $expectation,
    ): void {
        // when
        $isAllowed = $this->permissionsObject->isAnAllowedAction($action);

        // then
        $this->assertEquals($expectation, $isAllowed);
    }

    public static function provideActionsForAllowedActions(): array
    {
        return [
            'Allowed action' => [
                'action' => Action::Create,
                'expectation' => true,
            ],
            'Not allowed Action' => [
                'action' => Action::Update,
                'expectation' => false,
            ],
        ];
    }
}
