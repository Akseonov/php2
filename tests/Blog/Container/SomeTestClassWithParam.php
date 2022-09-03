<?php

namespace Akseonov\Php2\UnitTests\Blog\Container;

class SomeTestClassWithParam
{
    public function __construct(
        private int $value
    )
    {
    }

    public function getValue(): int
    {
        return $this->value;
    }
}