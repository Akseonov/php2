<?php

use Akseonov\Php2\Actions\Comments\CreateComment;
use Akseonov\Php2\Actions\Comments\FindCommentByUuid;
use Akseonov\Php2\Actions\Likes\CreateCommentLike;
use Akseonov\Php2\Actions\Likes\CreatePostLike;
use Akseonov\Php2\Actions\Likes\FindCommentLikesByCommentUuid;
use Akseonov\Php2\Actions\Likes\FindPostLikesByPostUuid;
use Akseonov\Php2\Actions\Posts\CreatePost;
use Akseonov\Php2\Actions\Posts\DeletePost;
use Akseonov\Php2\Actions\Posts\FindPostByTitle;
use Akseonov\Php2\Actions\Users\CreateUser;
use Akseonov\Php2\Actions\Users\FindUserByUsername;
use Akseonov\Php2\Actions\Users\FindUserByUuid;
use Akseonov\Php2\Actions\Posts\FindPostByUuid;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\Exceptions\HttpException;

$container = require __DIR__ . '/bootstrap.php';

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
} catch (HttpException) {
    (new ErrorResponse())->send();
    return;
}

try {
    $method = $request->method();
} catch (HttpException) {
    (new ErrorResponse())->send();
    return;
}

if (!array_key_exists($method, $routes)) {
    (new ErrorResponse('Route not found'))->send();
    return;
}

if (!array_key_exists($path, $routes[$method])) {
    (new ErrorResponse('Route not found'))->send();
    return;
}

$actionClassName = $routes[$method][$path];

$action = $container->get($actionClassName);

try {
    $response = $action->handle($request);
    $response->send();
} catch (Exception $exception) {
    (new ErrorResponse($exception->getMessage()))->send();
}