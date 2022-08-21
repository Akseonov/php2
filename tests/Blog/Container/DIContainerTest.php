<?php

namespace Akseonov\Php2\UnitTests\Blog\Container;

use Akseonov\Php2\Blog\Container\DIContainer;
use Akseonov\Php2\Exceptions\NotFoundException;
use PHPUnit\Framework\TestCase;

class DIContainerTest extends TestCase
{
    public function testItThrowsAnExceptionIfCannotResolveType(): void
    {
        $container = new DIContainer();

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Cannot resolve type: Akseonov\Php2\UnitTests\Blog\Container\SomeClass');

        $container->get(SomeClass::class);
    }

    public function testItResolvesSomeTestClass(): void
    {
        $container = new DIContainer();

        $object = $container->get(SomeTestClass::class);

        $this->assertInstanceOf(
            SomeTestClass::class,
            $object
        );
    }

    /**
     * @throws NotFoundException
     */
    public function testItResolvesClassByContainer(): void
    {
        $container = new DIContainer();

        $container->bind(
            SomeTestInterface::class,
            SomeTestClass::class
        );

        $object = $container->get(SomeTestInterface::class);

        $this->assertInstanceOf(SomeTestClass::class, $object);
    }

    /**
     * @throws NotFoundException
     */
    public function testItReturnPredefinedObject(): void
    {
        $container = new DIContainer();

        $container->bind(
            SomeTestClassWithParam::class,
            new SomeTestClassWithParam(42)
        );

        $object = $container->get(SomeTestClassWithParam::class);

        $this->assertInstanceOf(SomeTestClassWithParam::class, $object);
        $this->assertSame(42, $object->getValue());
    }

    public function testItResolvesClassWithDependencies(): void
    {
        $container = new DIContainer();

        $container->bind(
            SomeTestClassWithParam::class,
            new SomeTestClassWithParam(42),
        );

        $object = $container->get(SomeTestClassWithDependenceOrAnother::class);

        $this->assertInstanceOf(
            SomeTestClassWithDependenceOrAnother::class,
            $object
        );
    }

    public function testItReturnFalseIfClassNotExists(): void
    {
        $container = new DIContainer();

        $has = $container->has(Some::class);

        $this->assertEquals(false, $has);
    }

    public function testItReturnTrueIfClassExists(): void
    {
        $container = new DIContainer();

        $has = $container->has(SomeTestClass::class);

        $this->assertEquals(true, $has);
    }
}