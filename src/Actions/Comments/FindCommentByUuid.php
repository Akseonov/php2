<?php

namespace Akseonov\Php2\Actions\Comments;

use Akseonov\Php2\Actions\ActionInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentsRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\CommentNotFoundException;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;

class FindCommentByUuid implements ActionInterface
{
    public function __construct(
        private CommentsRepositoryInterface $commentsRepository
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
            $commentUuid = new UUID($uuid);
        } catch (InvalidArgumentException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $comment = $this->commentsRepository->get($commentUuid);
        } catch (CommentNotFoundException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

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