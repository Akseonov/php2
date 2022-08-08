<?php

namespace Homework\Units;

use Homework\Units\Unit;

class ChildUnits_Pet extends Unit
{
    public function Cat() {
        echo 'May ' . PHP_EOL;
    }

    // статичный метод
    public static function Ping() {
        echo 'Pong' . PHP_EOL;
    }
}