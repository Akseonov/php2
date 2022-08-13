<?php

namespace Akseonov\Php2\Blog\UnitTests;

use PHPUnit\Framework\TestCase;

class HelloTest extends TestCase
{
    public function testItWorks(): void
    {
        $this->assertTrue(true);
    }

    public function testAdd(): void
    {
        $this->assertEquals(4, 2+2);
    }
}