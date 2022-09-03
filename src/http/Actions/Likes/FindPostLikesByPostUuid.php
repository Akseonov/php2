<?php

namespace Akseonov\Php2\http\Actions\Likes;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostLikesRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\LikesPostNotFoundException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\Actions\ActionInterface;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class FindPostLikesByPostUuid implements ActionInterface
{
    public function __construct(
        private readonly PostLikesRepositoryInterface $postLikesRepository,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function handle(Request $request): Response
    {
        $this->logger->info('FindPostLikesByPostUuid action start');

        try {
            $uuid = $request->query('uuid');
        } catch (HttpException $exception) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $postLikes = $this->postLikesRepository->getByPostUuid(new UUID($uuid));
        } catch (PostNotFoundException |
            UserNotFoundException |
            LikesPostNotFoundException |
            InvalidArgumentException $exception
        ) {
            $this->logger->warning($exception->getMessage());
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

        $this->logger->info("FindPostLikesByPostUuid action find likes for post: $uuid");

        return new SuccessfulResponse($likes);
    }
}