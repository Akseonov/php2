<?php

namespace Akseonov\Php2\http\Actions\Likes;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentLikesRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\CommentNotFoundException;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\LikesCommentNotFoundException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\Actions\ActionInterface;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class FindCommentLikesByCommentUuid implements ActionInterface
{
    public function __construct(
        private readonly CommentLikesRepositoryInterface $commentLikesRepository,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function handle(Request $request): Response
    {
        $this->logger->info('FindCommentLikesByCommentUuid action start');

        try {
            $uuid = $request->query('uuid');
        } catch (HttpException $exception) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $postLikes = $this->commentLikesRepository->getByCommentUuid(new UUID($uuid));
        } catch (CommentNotFoundException |
            PostNotFoundException |
            UserNotFoundException |
            LikesCommentNotFoundException |
            InvalidArgumentException $exception
        ) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        $likes = [];

        foreach ($postLikes as $like) {
            $likes[] = [
                'uuid' => $like->getUuid(),
                'comment' => [
                    'uuid' => $like->getComment()->getUuid(),
                    'post' => [
                        'uuid' => $like->getComment()->getPost()->getUuid(),
                        'user' => [
                            'uuid' => $like->getComment()->getPost()->getUser()->getUuid(),
                            'username' => $like->getComment()->getPost()->getUser()->getUsername(),
                            'first_name' => $like->getComment()->getPost()->getUser()->getName()->getFirstName(),
                            'last_name' => $like->getComment()->getPost()->getUser()->getName()->getLastName()
                        ],
                        'title' => $like->getComment()->getPost()->getTitle(),
                        'text' => $like->getComment()->getPost()->getText(),
                    ],
                    'user' => [
                        'uuid' => $like->getComment()->getUser()->getUuid(),
                        'username' => $like->getComment()->getUser()->getUsername(),
                        'first_name' => $like->getComment()->getUser()->getName()->getFirstName(),
                        'last_name' => $like->getComment()->getUser()->getName()->getLastName()
                    ],
                    'text' => $like->getComment()->getText(),
                ],
                'user' => [
                    'uuid' => $like->getUser()->getUuid(),
                    'username' => $like->getUser()->getUsername(),
                    'first_name' => $like->getUser()->getName()->getFirstName(),
                    'last_name' => $like->getUser()->getName()->getLastName(),
                ]
            ];
        }

        $this->logger->info("FindCommentLikesByPostUuid action find likes for post: $uuid");

        return new SuccessfulResponse($likes);
    }
}