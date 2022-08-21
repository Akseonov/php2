<?php

namespace Akseonov\Php2\UnitTests\Blog\Container;

class SomeTestClassWithDependenceOrAnother
{
    public function __construct(
        private SomeTestClass $firstClass,
        private SomeTestClassWithParam $secondClass,
    )
    {
    }


}