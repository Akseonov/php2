<?php

use Akseonov\Php2\Blog\Commands\Arguments;
use Akseonov\Php2\Blog\Commands\CreateCommentCommand;
use Akseonov\Php2\Blog\Commands\CreatePostCommand;
use Akseonov\Php2\Blog\Commands\CreateUserCommand;
use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use Akseonov\Php2\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Akseonov\Php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Person\Name;

require_once __DIR__ . '/vendor/autoload.php';


$connection = new PDO('sqlite:' . __DIR__ . '/blog.sqlite');

$usersRepository = new SqliteUsersRepository($connection);
//$usersRepository = new InMemoryUsersRepository();
$commandUser = new CreateUserCommand($usersRepository);


//$postsRepository = new SqlitePostsRepository($connection);
//$commandPost = new CreatePostCommand($postsRepository);


//$commentsRepository = new SqliteCommentsRepository($connection);
//$commandComment = new CreateCommentCommand($commentsRepository);

try {
//    $commandUser->handle(Arguments::fromArgv($argv));

//    $usersRepository->save(new User(UUID::random(), 'admin', new Name('Peter', 'Romanov')));
    echo $usersRepository->get(new UUID('a3e78b09-23ae-44fd-9939-865f688894f5'));
//    echo $usersRepository->getByUsername('ivan');



//    $commandPost->handle(Arguments::fromArgv($argv));

//    $postsRepository->save(new Post(UUID::random(), UUID::random(), 'Привет', 'Аптека улица фонарь'));
//    echo $postsRepository->get(new UUID('56c541f7-6665-4e67-8e0d-41e544855b6d'));
//    echo $postsRepository->getByTitle('Имя мне Моисей');


//    $commandComment->handle(Arguments::fromArgv($argv));

//    $commentsRepository->save(new Comment(UUID::random(), UUID::random(), UUID::random(), 'Мой первый комментарий'));
//    echo $commentsRepository->get(new UUID('b63ec77a-9eca-4560-a3fa-c061704a13cd'));
} catch (Exception $exception) {
    echo $exception->getMessage();
}