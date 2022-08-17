<?php

use Akseonov\Php2\Actions\Comments\CreateComment;
use Akseonov\Php2\Actions\Comments\FindCommentByUuid;
use Akseonov\Php2\Actions\Posts\CreatePost;
use Akseonov\Php2\Actions\Posts\DeletePost;
use Akseonov\Php2\Actions\Posts\FindPostByTitle;
use Akseonov\Php2\Actions\Users\CreateUser;
use Akseonov\Php2\Actions\Users\FindUserByUsername;
use Akseonov\Php2\Actions\Users\FindUserByUuid;
use Akseonov\Php2\Actions\Posts\FindPostByUuid;
use Akseonov\Php2\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use Akseonov\Php2\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Akseonov\Php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\SuccessfulResponse;
use Akseonov\Php2\Exceptions\HttpException;

require_once __DIR__ . "/vendor/autoload.php";

$router = [
    'GET' => [
        '/users/show' => new FindUserByUsername(
            new SqliteUsersRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        ),
        '/users/id' => new FindUserByUuid(
            new SqliteUsersRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        ),
        '/posts/id' => new FindPostByUuid(
            new SqlitePostsRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        ),
        '/posts/show' => new FindPostByTitle(
            new SqlitePostsRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        ),
        '/comments/id' => new FindCommentByUuid(
            new SqliteCommentsRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        )
    ],
    'POST' => [
        '/users/create' => new CreateUser(
            new SqliteUsersRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        ),
        '/posts/create' => new CreatePost(
            new SqlitePostsRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            ),
            new SqliteUsersRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        ),
        '/posts/delete' => new DeletePost(
            new SqlitePostsRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        ),
        '/comments/create' => new CreateComment(
            new SqliteCommentsRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            ),
            new SqlitePostsRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            ),
            new SqliteUsersRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        )
    ]
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