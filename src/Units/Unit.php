<?php

namespace Akseonov\Php2\Units;

class Unit
{
    protected ?int $id;
    public ?string $name;
    public ?int $hp = 100;

    public function __construct(int $id = null, string $name = null, int $hp = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->hp = $hp;
    }

    public function id():int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return 'Привет от ' . $this->name . PHP_EOL; //PHP_EOL - из-за работы в консоли
    }
}