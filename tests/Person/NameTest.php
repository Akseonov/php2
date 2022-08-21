<?php

namespace Akseonov\Php2\UnitTests\Person;

use Akseonov\Php2\Person\Name;
use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    public function testItReturnFirstName(): void
    {
        $name = new Name(
            'firstName',
            'lastName'
        );

        $this->assertEquals('firstName', $name->getFirstName());
    }

    public function testItReturnLastName(): void
    {
        $name = new Name(
            'firstName',
            'lastName'
        );

        $this->assertEquals('lastName', $name->getLastName());
    }

    public function testItExpectNameString(): void
    {
        $name = new Name(
            'firstName',
            'lastName'
        );

        $string = (string)$name;

        $this->assertEquals('firstName lastName', $string);
    }
}