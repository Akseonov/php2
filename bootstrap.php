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

require_once __DIR__ . "/vendor/autoload.php";

$container = new DIContainer();

$container->bind(
    PDO::class,
    new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
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

return $container;