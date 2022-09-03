<?php

use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\http\Actions\Auth\LogIn;
use Akseonov\Php2\http\Actions\Auth\LogOut;
use Akseonov\Php2\http\Actions\Comments\CreateComment;
use Akseonov\Php2\http\Actions\Comments\FindCommentByUuid;
use Akseonov\Php2\http\Actions\Likes\CreateCommentLike;
use Akseonov\Php2\http\Actions\Likes\CreatePostLike;
use Akseonov\Php2\http\Actions\Likes\FindCommentLikesByCommentUuid;
use Akseonov\Php2\http\Actions\Likes\FindPostLikesByPostUuid;
use Akseonov\Php2\http\Actions\Posts\CreatePost;
use Akseonov\Php2\http\Actions\Posts\DeletePost;
use Akseonov\Php2\http\Actions\Posts\FindPostByTitle;
use Akseonov\Php2\http\Actions\Posts\FindPostByUuid;
use Akseonov\Php2\http\Actions\Users\CreateUser;
use Akseonov\Php2\http\Actions\Users\FindUserByUsername;
use Akseonov\Php2\http\Actions\Users\FindUserByUuid;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Psr\Log\LoggerInterface;

$container = require __DIR__ . '/bootstrap.php';

$logger = $container->get(LoggerInterface::class);

$routes = [
    'GET' => [
        '/users/show' => FindUserByUsername::class,
        '/users/id' => FindUserByUuid::class,
        '/posts/id' => FindPostByUuid::class,
        '/posts/show' => FindPostByTitle::class,
        '/comments/id' => FindCommentByUuid::class,
        '/posts/likes/show' => FindPostLikesByPostUuid::class,
        '/comments/likes/show' => FindCommentLikesByCommentUuid::class,
    ],
    'POST' => [
        '/login' => LogIn::class,
        '/logout' => LogOut::class,
        '/users/create' => CreateUser::class,
        '/posts/create' => CreatePost::class,
        '/comments/create' => CreateComment::class,
        '/posts/likes/create' => CreatePostLike::class,
        '/comments/likes/create' => CreateCommentLike::class,
    ],
    'DELETE' => [
        '/posts/delete' => DeletePost::class,
    ]
];

$request = new Request(
    $_GET,
    $_SERVER,
    file_get_contents('php://input')
);

try {
    $path = $request->path();
} catch (HttpException $exception) {
    $logger->warning($exception->getMessage());
    (new ErrorResponse())->send();
    return;
}

try {
    $method = $request->method();
} catch (HttpException $exception) {
    $logger->warning($exception->getMessage());
    (new ErrorResponse())->send();
    return;
}

if (!array_key_exists($method, $routes)) {
    $message = "Route not found: $method";
    $logger->notice($message);
    (new ErrorResponse($message))->send();
    return;
}

if (!array_key_exists($path, $routes[$method])) {
    $message = "Route not found: $path";
    $logger->notice($message);
    (new ErrorResponse($message))->send();
    return;
}

$actionClassName = $routes[$method][$path];

$action = $container->get($actionClassName);

try {
    $response = $action->handle($request);
    $response->send();
} catch (Exception $exception) {
    $logger->error($exception->getMessage(), ['exception' => $exception]);
    (new ErrorResponse($exception->getMessage()))->send();
}