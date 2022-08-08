<?php

use \Homework\Units\{ChildUnits_Hero, ChildUnits_Pet};

spl_autoload_register(function ($class) {
    $file = 'autoloader' . DIRECTORY_SEPARATOR .  str_replace('_', DIRECTORY_SEPARATOR, $class);
    $file = '.\\' . str_replace('\\', DIRECTORY_SEPARATOR, $file) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

$hero = new ChildUnits_Hero(1, 'Конан', 50, ['Меч', 'Щит']);
echo $hero;
$pet = new ChildUnits_Pet(1, 'Кот', 20);
echo $pet;