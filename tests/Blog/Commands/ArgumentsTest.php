<?php

namespace Akseonov\Php2\UnitTests\Blog\Commands;

use Akseonov\Php2\Blog\Commands\Arguments;
use Akseonov\Php2\Exceptions\ArgumentsException;
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
        $arguments = new Arguments(['some_key' => 123, 'empty_arg' => '']);
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

    public function testItConvertCommandStringToArguments(): void
    {
        $arguments = Arguments::fromArgv([
            'author_uuid=a3e78b09-23ae-44fd-9939-865f688894f5',
            'text',
            'post_uuid='
        ]);

        $this->assertEquals(new Arguments([
            'author_uuid' => 'a3e78b09-23ae-44fd-9939-865f688894f5',
        ]), $arguments);
    }
}