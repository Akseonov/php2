<?php

namespace Homework\Units;

use Homework\Units\Unit;

class ChildUnits_Hero extends Unit
{
    private array $inventory = []; //можно получить доступ только из класса
    protected array $store = []; //можно получить доступ из класса и из его досерних классов

    public function __construct(int $id = null, string $name = null, int $hp = null, array $inventory = [], array $store = [])
    {
        parent::__construct($id, $name, $hp);
        $this->inventory = $inventory;
        $this->store = $store;
    }
}