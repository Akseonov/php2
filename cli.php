<?php

use Akseonov\Php2\Blog\Commands\Arguments;
use Akseonov\Php2\Blog\Commands\CreateUserCommand;

$container = require __DIR__ . '/bootstrap.php';


try {
    $command = $container->get(CreateUserCommand::class);
    $command->handle(Arguments::fromArgv($argv));
} catch (Exception $exception) {
    echo $exception->getMessage();
}