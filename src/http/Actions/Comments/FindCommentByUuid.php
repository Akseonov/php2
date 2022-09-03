<?php

namespace Akseonov\Php2\http\Actions\Comments;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentsRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\CommentNotFoundException;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\http\Actions\ActionInterface;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class FindCommentByUuid implements ActionInterface
{
    public function __construct(
        private readonly CommentsRepositoryInterface $commentsRepository,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function handle(Request $request): Response
    {
        $this->logger->info('FindCommentByUuid action start');

        try {
            $uuid = $request->query('uuid');
        } catch (HttpException $exception) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $comment = $this->commentsRepository->get(new UUID($uuid));
        } catch (CommentNotFoundException | InvalidArgumentException $exception) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        $this->logger->info("FindCommentByUuid action find comment: $uuid");

        return new SuccessfulResponse([
            'uuid' => $comment->getUuid(),
            'user' => [
                'uuid' => $comment->getUser()->getUuid(),
                'username' => $comment->getUser()->getUsername(),
                'first_name' => $comment->getUser()->getName()->getFirstName(),
                'last_name' => $comment->getUser()->getName()->getLastName()
            ],
            'post' => [
                'uuid' => $comment->getPost()->getUuid(),
                'user' => [
                    'uuid' => $comment->getPost()->getUser()->getUuid(),
                    'username' => $comment->getPost()->getUser()->getUsername(),
                    'first_name' => $comment->getPost()->getUser()->getName()->getFirstName(),
                    'last_name' => $comment->getPost()->getUser()->getName()->getLastName()
                ],
                'title' => $comment->getPost()->getTitle(),
                'text' => $comment->getPost()->getText(),
            ],
            'text' => $comment->getText()
        ]);
    }
}