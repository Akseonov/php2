<?php

namespace Akseonov\Php2\Actions\Likes;

use Akseonov\Php2\Actions\ActionInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostLikesRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\LikesPostNotFoundException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;

class FindPostLikesByPostUuid implements ActionInterface
{
    public function __construct(
        private PostLikesRepositoryInterface $postLikesRepository,
    )
    {
    }

    public function handle(Request $request): Response
    {
        try {
            $uuid = $request->query('uuid');
        } catch (HttpException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $postLikes = $this->postLikesRepository->getByPostUuid(new UUID($uuid));
        } catch (PostNotFoundException |
            UserNotFoundException |
            LikesPostNotFoundException |
            InvalidArgumentException $exception
        ) {
            return new ErrorResponse($exception->getMessage());
        }

        $likes = [];

        foreach ($postLikes as $like) {
            $likes[] = [
                'uuid' => $like->getUuid(),
                'post' => [
                    'uuid' => $like->getPost()->getUuid(),
                    'user' => [
                        'uuid' => $like->getPost()->getUser()->getUuid(),
                        'username' => $like->getPost()->getUser()->getUsername(),
                        'first_name' => $like->getPost()->getUser()->getName()->getFirstName(),
                        'last_name' => $like->getPost()->getUser()->getName()->getLastName(),
                    ],
                    'title' => $like->getPost()->getTitle(),
                    'text' => $like->getPost()->getText(),
                ],
                'user' => [
                    'uuid' => $like->getUser()->getUuid(),
                    'username' => $like->getUser()->getUsername(),
                    'first_name' => $like->getUser()->getName()->getFirstName(),
                    'last_name' => $like->getUser()->getName()->getLastName(),
                ]
            ];
        }

        return new SuccessfulResponse($likes);
    }
}