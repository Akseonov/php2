<?php

namespace Akseonov\Php2\Units;

use Akseonov\Php2\Units\Unit;

class Pet extends Unit
{
    public function Cat() {
        echo 'May ' . PHP_EOL;
    }

    // статичный метод
    public static function Ping() {
        echo 'Pong' . PHP_EOL;
    }
}