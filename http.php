<?php

use Akseonov\Php2\Actions\Users\CreateUser;
use Akseonov\Php2\Actions\Users\FindByUsername;
use Akseonov\Php2\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Akseonov\Php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\SuccessfulResponse;
use Akseonov\Php2\Exceptions\HttpException;

require_once __DIR__ . "/vendor/autoload.php";

$router = [
    'GET' => [
        '/users/show' => new FindByUsername(
            new SqliteUsersRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        ),
    ],
    'POST' => [
        '/users/create' => new CreateUser(
            new SqliteUsersRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        ),
    ]
//    'posts/show' => new FindByUuid(
//        new SqlitePostsRepository(
//            new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
//        )
//    )
];

try {
    $request = new Request(
        $_GET,
        $_SERVER,
        file_get_contents('php://input')
    );

    $path = $request->path();
} catch (HttpException) {
    (new ErrorResponse())->send();
    return;
}

if (!array_key_exists($path, $router)) {
    (new ErrorResponse('Not found'))->send();
    return;
}

try {
    $method = $request->method();
} catch (HttpException) {
    (new ErrorResponse())->send();
    return;
}

if (!array_key_exists($path, $router[$method])) {
    (new ErrorResponse('Not found'))->send();
    return;
}

$action = $router[$method][$path];

try {
    $response = $action->handle($request);
    $response->send();
} catch (Exception $exception) {
    (new ErrorResponse($exception->getMessage()))->send();
}