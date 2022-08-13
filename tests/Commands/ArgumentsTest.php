<?php

namespace Akseonov\Php2\Blog\UnitTests\Commands;

use Akseonov\Php2\Blog\Commands\Arguments;
use Akseonov\Php2\Blog\Exceptions\ArgumentsException;
use PHPUnit\Framework\TestCase;

class ArgumentsTest extends TestCase
{
    /**
     * @throws ArgumentsException
     */
    public function testItReturnsArgumentsValueByName(): void
    {
        $arguments = new Arguments(['some_key' => 'some_value']);
        $value = $arguments->get('some_key');
        $this->assertEquals('some_value', $value);
    }

    /**
     * @throws ArgumentsException
     */
    public function testItReturnsValuesAsStrings(): void
    {
        $arguments = new Arguments(['some_key' => 123]);
        $value = $arguments->get('some_key');
        $this->assertEquals('123', $value);
    }

    public function testItThrowsAnExceptionWhenArgumentIsAbsent(): void
    {
        $arguments = new Arguments([]);
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage("No such argument: some_key");
        $arguments->get('some_key');
    }

    public function argumentsProvider(): iterable
    {
        return [
            ['some_string', 'some_string'],
            [' some_string', 'some_string'],
            [' some_string ', 'some_string'],
            [123, '123'],
            [12.3, '12.3'],
        ];
    }

    /**
     * @dataProvider argumentsProvider
     * @throws ArgumentsException
     */
    public function testItConvertsArgumentsToStrings(
        $inputValue,
        $expectedValue
    ): void
    {
        $arguments = new Arguments(['some_key' => $inputValue]);
        $value = $arguments->get('some_key');
        $this->assertEquals($expectedValue, $value);
    }
}