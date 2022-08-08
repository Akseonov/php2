<?php

use Akseonov\Php2\Blog\Commands\CreateUserCommand;
use Akseonov\Php2\Blog\Repositories\InMemoryUsersRepository;
use Akseonov\Php2\Blog\Repositories\SqliteUsersRepository;
use Akseonov\Php2\Blog\Commands\Arguments;

require_once __DIR__ . '/vendor/autoload.php';


$connection = new PDO('sqlite:' . __DIR__ . '/blog.sqlite');

$usersRepository = new SqliteUsersRepository($connection);
//$usersRepository = new InMemoryUsersRepository();

$command = new CreateUserCommand($usersRepository);

try {
    $command->handle(Arguments::fromArgv($argv));

//    $usersRepository->save(new User(UUID::random(), 'admin', new Name('Peter', 'Romanov')));
//    echo $usersRepository->get(new UUID('1906077b-c349-4a2c-9c88-000ff8875e8e'));
    echo $usersRepository->getByUsername('ivan');
} catch (Exception $exception) {
    echo $exception->getMessage();
}