<?php

namespace Getorbit\RbacDomainPermissions\Tests\Domain\ValueObjects;

use Getorbit\RbacDomainPermissions\Domain\ValueObjects\Action;
use Orchestra\Testbench\TestCase;

class ActionTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideCasesForActionEquals
     */
    public function equalsShouldReturnExpectedValue(Action $first, Action $second, bool $areEquals): void
    {
        // then
        $this->assertEquals($areEquals, $first->equals($second));
    }

    public static function provideCasesForActionEquals(): array
    {
        return [
            'Equal actions' => [
                'first' => Action::Create,
                'second' => Action::Create,
                'areEqual' => true,
            ],
            'Different actions' => [
                'first' => Action::Create,
                'second' => Action::Update,
                'areEqual' => false,
            ],
        ];
    }
}
