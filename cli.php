<?php

use Akseonov\Php2\Blog\Commands\Arguments;
use Akseonov\Php2\Blog\Commands\CreateUserCommand;
use Akseonov\Php2\Blog\Commands\FakeData\PopulateDB;
use Akseonov\Php2\Blog\Commands\Posts\DeletePost;
use Akseonov\Php2\Blog\Commands\Users\CreateUser;
use Akseonov\Php2\Blog\Commands\Users\UpdateUser;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;

$container = require __DIR__ . '/bootstrap.php';

$logger = $container->get(LoggerInterface::class);

$application = new Application();

$commandsClasses = [
    CreateUser::class,
    UpdateUser::class,
    DeletePost::class,
    PopulateDB::class,
];

foreach ($commandsClasses as $commandClass) {
    $command = $container->get($commandClass);
    $application->add($command);
}

try {
    $application->run();
//    $command = $container->get(CreateUserCommand::class);
//    $command->handle(Arguments::fromArgv($argv));
} catch (Exception $exception) {
    $logger->error($exception->getMessage(), ['exception' => $exception]);
//    echo $exception->getMessage();
}