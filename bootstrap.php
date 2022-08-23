<?php

use Akseonov\Php2\Blog\Container\DIContainer;
use Akseonov\Php2\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use Akseonov\Php2\Blog\Repositories\LikesRepository\SqliteCommentLikesRepository;
use Akseonov\Php2\Blog\Repositories\LikesRepository\SqlitePostLikesRepository;
use Akseonov\Php2\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentLikesRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostLikesRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\http\Auth\IdentificationInterface;
use Akseonov\Php2\http\Auth\JsonBodyUuidIdentification;
use Dotenv\Dotenv;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

require_once __DIR__ . "/vendor/autoload.php";
Dotenv::createImmutable(__DIR__)->safeLoad();

$container = new DIContainer();

$container->bind(
    PDO::class,
    new PDO('sqlite:' . __DIR__ . '/' . $_SERVER['SQLITE_DB_PATH'])
);

$container->bind(
    PostsRepositoryInterface::class,
    SqlitePostsRepository::class
);

$container->bind(
    UsersRepositoryInterface::class,
    SqliteUsersRepository::class
);

$container->bind(
    CommentsRepositoryInterface::class,
    SqliteCommentsRepository::class
);

$container->bind(
    PostLikesRepositoryInterface::class,
    SqlitePostLikesRepository::class
);

$container->bind(
    CommentLikesRepositoryInterface::class,
    SqliteCommentLikesRepository::class
);

$container->bind(
    IdentificationInterface::class,
    JsonBodyUuidIdentification::class
);

$logger = (new Logger('blog'));

if ($_SERVER['LOG_TO_FILES'] === 'yes') {
    $logger
        ->pushHandler(
            new StreamHandler(__DIR__ . '/logs/blog.log')
        )
        ->pushHandler(
            new StreamHandler(
                __DIR__ . '/logs/blog.error.log',
                level: Logger::ERROR,
                bubble: true,
            )
        );
}

if ($_SERVER['LOG_TO_CONSOLE'] === 'yes') {
    $logger
        ->pushHandler(
            new StreamHandler("php://stdout")
        );
}

$container->bind(
    LoggerInterface::class,
    $logger
);

return $container;